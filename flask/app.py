from flask import Flask, request, jsonify
import pandas as pd
import numpy as np
from sklearn.metrics import mean_absolute_error, mean_squared_error
from sklearn.preprocessing import MinMaxScaler
import joblib
import warnings
import logging
import os

# Suppress verbose output
os.environ['TF_CPP_MIN_LOG_LEVEL'] = '3'
warnings.filterwarnings('ignore')
logging.getLogger('cmdstanpy').setLevel(logging.WARNING)
logging.getLogger('prophet').setLevel(logging.WARNING)

app = Flask(__name__)

# ─── Config ──────────────────────────────────────────────────────────
MIN_DATA_POINTS = 6
MODEL_DIR = os.path.join(os.path.dirname(__file__), 'models')
os.makedirs(MODEL_DIR, exist_ok=True)

LSTM_MODEL_PATH = os.path.join(MODEL_DIR, 'lstm_model.h5')
LSTM_SCALER_PATH = os.path.join(MODEL_DIR, 'lstm_scaler.pkl')
LSTM_META_PATH = os.path.join(MODEL_DIR, 'lstm_meta.pkl')
PROPHET_MODEL_PATH = os.path.join(MODEL_DIR, 'prophet_model.pkl')


# ═══════════════════════════════════════════════════════════════════════
# HEALTH CHECK
# ═══════════════════════════════════════════════════════════════════════
@app.route('/health', methods=['GET'])
def health():
    lstm_ready = os.path.exists(LSTM_MODEL_PATH)
    prophet_ready = os.path.exists(PROPHET_MODEL_PATH)
    return jsonify({
        'status': 'ok',
        'models': {
            'lstm': 'ready' if lstm_ready else 'not trained',
            'prophet': 'ready' if prophet_ready else 'not trained'
        }
    })


# ═══════════════════════════════════════════════════════════════════════
# HELPER: Prepare data
# ═══════════════════════════════════════════════════════════════════════
def prepare_dataframe(bulan, terjual):
    """Convert input arrays to a monthly DataFrame."""
    df = pd.DataFrame({
        'Tanggal': pd.to_datetime(bulan),
        'Jumlah': [float(x) for x in terjual]
    })
    df = df.set_index('Tanggal')
    df = df.resample('ME').sum()
    df['Jumlah'] = pd.to_numeric(df['Jumlah'], errors='coerce').fillna(0)
    return df


def create_sequences(data, seq_length):
    """Create LSTM input sequences."""
    X, y = [], []
    for i in range(len(data) - seq_length):
        X.append(data[i:i + seq_length])
        y.append(data[i + seq_length])
    return np.array(X), np.array(y)


# ═══════════════════════════════════════════════════════════════════════
# TRAIN LSTM — POST /train/lstm
# Trains the model and saves to disk
# ═══════════════════════════════════════════════════════════════════════
@app.route('/train/lstm', methods=['POST'])
def train_lstm():
    try:
        data = request.get_json()
        bulan = data['bulan']
        terjual = data['terjual']

        if len(bulan) < MIN_DATA_POINTS:
            return jsonify({
                'error': f'Minimal {MIN_DATA_POINTS} bulan data diperlukan. '
                         f'Saat ini hanya {len(bulan)} bulan.'
            }), 400

        df = prepare_dataframe(bulan, terjual)
        values = df['Jumlah'].values.reshape(-1, 1)

        # ── Normalize ──
        scaler = MinMaxScaler(feature_range=(0, 1))
        scaled = scaler.fit_transform(values)

        # ── Sequences ──
        seq_length = min(3, len(scaled) - 1)
        if seq_length < 1:
            seq_length = 1

        X_train, y_train = create_sequences(scaled, seq_length)
        if len(X_train) == 0:
            return jsonify({'error': 'Data tidak cukup untuk sequence LSTM.'}), 400

        X_train = X_train.reshape(X_train.shape[0], X_train.shape[1], 1)

        # ── Build & Train ──
        from tensorflow.keras.models import Sequential
        from tensorflow.keras.layers import LSTM, Dense
        from tensorflow.keras.callbacks import EarlyStopping

        model = Sequential([
            LSTM(50, activation='relu', input_shape=(seq_length, 1), return_sequences=True),
            LSTM(30, activation='relu'),
            Dense(1)
        ])
        model.compile(optimizer='adam', loss='mse')

        early_stop = EarlyStopping(monitor='loss', patience=10, restore_best_weights=True)
        history = model.fit(X_train, y_train, epochs=100, batch_size=1,
                            verbose=0, callbacks=[early_stop])

        # ── Evaluate (predict last known month) ──
        last_seq = scaled[-(seq_length + 1):-1].reshape(1, seq_length, 1)
        pred_scaled = model.predict(last_seq, verbose=0)
        pred_value = scaler.inverse_transform(pred_scaled).flatten()[0]
        actual_value = values[-1][0]

        mae = float(abs(actual_value - max(pred_value, 0)))
        rmse = float(np.sqrt((actual_value - max(pred_value, 0)) ** 2))

        # ── Save model, scaler, and metadata ──
        model.save(LSTM_MODEL_PATH)
        joblib.dump(scaler, LSTM_SCALER_PATH)
        joblib.dump({'seq_length': seq_length}, LSTM_META_PATH)

        return jsonify({
            'message': 'LSTM model trained and saved successfully',
            'epochs_run': len(history.history['loss']),
            'final_loss': round(float(history.history['loss'][-1]), 6),
            'mae': round(mae, 2),
            'rmse': round(rmse, 2),
            'model_path': LSTM_MODEL_PATH
        })

    except Exception as e:
        app.logger.error(f'LSTM training error: {str(e)}')
        return jsonify({'error': str(e)}), 500


# ═══════════════════════════════════════════════════════════════════════
# TRAIN PROPHET — POST /train/prophet
# Trains the model and saves to disk
# ═══════════════════════════════════════════════════════════════════════
@app.route('/train/prophet', methods=['POST'])
def train_prophet():
    try:
        data = request.get_json()
        bulan = data['bulan']
        terjual = data['terjual']

        if len(bulan) < MIN_DATA_POINTS:
            return jsonify({
                'error': f'Minimal {MIN_DATA_POINTS} bulan data diperlukan. '
                         f'Saat ini hanya {len(bulan)} bulan.'
            }), 400

        from prophet import Prophet
        from prophet.serialize import model_to_json

        # Prophet needs 'ds' and 'y'
        df = pd.DataFrame({
            'ds': pd.to_datetime(bulan),
            'y': [float(x) for x in terjual]
        })
        df = df.set_index('ds').resample('ME').sum().reset_index()

        # ── Evaluate: train on all-1, test on last ──
        train_df = df.iloc[:-1].copy()
        test_actual = df.iloc[-1]['y']

        eval_model = Prophet(
            yearly_seasonality=False,
            weekly_seasonality=False,
            daily_seasonality=False,
            seasonality_mode='multiplicative'
        )
        eval_model.fit(train_df)
        eval_future = eval_model.make_future_dataframe(periods=1, freq='ME')
        eval_forecast = eval_model.predict(eval_future)
        test_pred = max(eval_forecast.iloc[-1]['yhat'], 0)

        mae = float(abs(test_actual - test_pred))
        rmse = float(np.sqrt((test_actual - test_pred) ** 2))

        # ── Train final model on ALL data ──
        full_model = Prophet(
            yearly_seasonality=False,
            weekly_seasonality=False,
            daily_seasonality=False,
            seasonality_mode='multiplicative'
        )
        full_model.fit(df)

        # ── Save model ──
        with open(PROPHET_MODEL_PATH, 'w') as f:
            f.write(model_to_json(full_model))

        return jsonify({
            'message': 'Prophet model trained and saved successfully',
            'mae': round(mae, 2),
            'rmse': round(rmse, 2),
            'data_points': len(df),
            'model_path': PROPHET_MODEL_PATH
        })

    except Exception as e:
        app.logger.error(f'Prophet training error: {str(e)}')
        return jsonify({'error': str(e)}), 500


# ═══════════════════════════════════════════════════════════════════════
# PREDICT LSTM — POST /predictlstm
# Loads saved model, predicts next month
# ═══════════════════════════════════════════════════════════════════════
@app.route('/predictlstm', methods=['POST'])
def predict_lstm():
    try:
        # Check if model exists
        if not os.path.exists(LSTM_MODEL_PATH):
            return jsonify({
                'error': 'Model LSTM belum di-train. Panggil POST /train/lstm terlebih dahulu.'
            }), 400

        data = request.get_json()
        bulan = data['bulan']
        terjual = data['terjual']

        if len(bulan) < 3:
            return jsonify({
                'error': 'Minimal 3 bulan data terbaru diperlukan untuk prediksi.'
            }), 400

        # Load model, scaler, and metadata
        from tensorflow.keras.models import load_model
        model = load_model(LSTM_MODEL_PATH)
        scaler = joblib.load(LSTM_SCALER_PATH)
        meta = joblib.load(LSTM_META_PATH)
        seq_length = meta['seq_length']

        # Prepare the latest data
        df = prepare_dataframe(bulan, terjual)
        values = df['Jumlah'].values.reshape(-1, 1)
        scaled = scaler.transform(values)

        # ── Evaluate on last known month ──
        if len(scaled) > seq_length:
            eval_seq = scaled[-(seq_length + 1):-1].reshape(1, seq_length, 1)
            eval_pred_scaled = model.predict(eval_seq, verbose=0)
            eval_pred = scaler.inverse_transform(eval_pred_scaled).flatten()[0]
            actual = values[-1][0]
            mae = float(abs(actual - max(eval_pred, 0)))
            rmse = float(np.sqrt((actual - max(eval_pred, 0)) ** 2))
        else:
            mae = 0.0
            rmse = 0.0

        # ── Forecast next 1 month ──
        forecast_input = scaled[-seq_length:].reshape(1, seq_length, 1)
        forecast_scaled = model.predict(forecast_input, verbose=0)
        forecast_value = scaler.inverse_transform(forecast_scaled).flatten()
        forecast_value = np.maximum(forecast_value, 0)

        result = {
            'forecast': [round(float(v), 2) for v in forecast_value],
            'mae': round(mae, 2),
            'rmse': round(rmse, 2),
            'model': 'LSTM'
        }
        return jsonify(result)

    except Exception as e:
        app.logger.error(f'LSTM predict error: {str(e)}')
        return jsonify({'error': str(e)}), 500


# ═══════════════════════════════════════════════════════════════════════
# PREDICT PROPHET — POST /predictprophet
# Loads saved model, predicts next month
# ═══════════════════════════════════════════════════════════════════════
@app.route('/predictprophet', methods=['POST'])
def predict_prophet():
    try:
        # Check if model exists
        if not os.path.exists(PROPHET_MODEL_PATH):
            return jsonify({
                'error': 'Model Prophet belum di-train. Panggil POST /train/prophet terlebih dahulu.'
            }), 400

        data = request.get_json()
        bulan = data['bulan']
        terjual = data['terjual']

        from prophet import Prophet
        from prophet.serialize import model_from_json

        # Load saved model
        with open(PROPHET_MODEL_PATH, 'r') as f:
            model = model_from_json(f.read())

        # Prepare data for evaluation
        df = pd.DataFrame({
            'ds': pd.to_datetime(bulan),
            'y': [float(x) for x in terjual]
        })
        df = df.set_index('ds').resample('ME').sum().reset_index()

        # ── Evaluate on last known month ──
        actual = df.iloc[-1]['y']
        eval_forecast = model.predict(df[['ds']])
        eval_pred = max(eval_forecast.iloc[-1]['yhat'], 0)

        mae = float(abs(actual - eval_pred))
        rmse = float(np.sqrt((actual - eval_pred) ** 2))

        # ── Forecast next 1 month ──
        future = model.make_future_dataframe(periods=1, freq='ME')
        forecast = model.predict(future)
        forecast_value = max(forecast.iloc[-1]['yhat'], 0)

        result = {
            'forecast': [round(float(forecast_value), 2)],
            'mae': round(mae, 2),
            'rmse': round(rmse, 2),
            'model': 'PROPHET'
        }
        return jsonify(result)

    except Exception as e:
        app.logger.error(f'Prophet predict error: {str(e)}')
        return jsonify({'error': str(e)}), 500


# ═══════════════════════════════════════════════════════════════════════
if __name__ == '__main__':
    print(f"📁 Models directory: {MODEL_DIR}")
    print(f"🔍 LSTM model: {'✅ Ready' if os.path.exists(LSTM_MODEL_PATH) else '❌ Not trained'}")
    print(f"🔍 Prophet model: {'✅ Ready' if os.path.exists(PROPHET_MODEL_PATH) else '❌ Not trained'}")
    print(f"🚀 Starting Flask API on http://0.0.0.0:5000")
    app.run(debug=True, host='0.0.0.0', port=5000)
