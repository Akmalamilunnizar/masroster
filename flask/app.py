from flask import Flask, request, jsonify
import pandas as pd
import numpy as np
from sklearn.metrics import mean_absolute_error, mean_squared_error
from sklearn.preprocessing import MinMaxScaler
import joblib
import warnings
import logging
import os
import json

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
MODEL_REGISTRY_PATH = os.path.join(MODEL_DIR, 'model_registry.json')

LSTM_MODEL_PATH = os.path.join(MODEL_DIR, 'lstm_model.h5')
LSTM_SCALER_PATH = os.path.join(MODEL_DIR, 'lstm_scaler.pkl')
LSTM_META_PATH = os.path.join(MODEL_DIR, 'lstm_meta.pkl')
PROPHET_MODEL_PATH = os.path.join(MODEL_DIR, 'prophet_model.pkl')

DEFAULT_MODEL_BY_FRAMEWORK = {
    ('LSTM', 'Eceran'): 'lstm_eceran_tuned',
    ('LSTM', 'Borongan'): 'lstm_borongan',
    ('Prophet', 'Eceran'): 'prophet_eceran_tuned',
    ('Prophet', 'Borongan'): 'prophet_borongan',
}


def resolve_artifact_path(path_value):
    """Resolve artifact path from registry (absolute/relative/workspace) into real file path."""
    if not path_value:
        return ''

    raw_path = os.path.normpath(str(path_value))
    candidates = [
        raw_path,
        os.path.join(os.getcwd(), raw_path),
        os.path.join(os.path.dirname(__file__), raw_path),
        os.path.join(MODEL_DIR, os.path.basename(raw_path))
    ]

    for candidate in candidates:
        if os.path.exists(candidate):
            return candidate

    # Fallback to models directory by basename.
    return os.path.join(MODEL_DIR, os.path.basename(raw_path))


def load_registry():
    """Load model registry and normalize paths."""
    if not os.path.exists(MODEL_REGISTRY_PATH):
        return []

    try:
        with open(MODEL_REGISTRY_PATH, 'r', encoding='utf-8') as f:
            rows = json.load(f)

        normalized = []
        for row in rows if isinstance(rows, list) else []:
            item = dict(row)
            item['artifact_resolved'] = resolve_artifact_path(item.get('artifact_path', ''))
            item['scaler_resolved'] = resolve_artifact_path(item.get('scaler_path', ''))
            item['meta_resolved'] = resolve_artifact_path(item.get('meta_path', ''))
            normalized.append(item)
        return normalized
    except Exception:
        return []


def build_model_index(registry_rows):
    index = {}
    for row in registry_rows:
        name = row.get('model_name')
        if name:
            index[name] = row
    return index


MODEL_REGISTRY = load_registry()
MODEL_INDEX = build_model_index(MODEL_REGISTRY)


def refresh_registry():
    """Reload registry from disk to catch newly exported models without restarting server."""
    global MODEL_REGISTRY, MODEL_INDEX
    MODEL_REGISTRY = load_registry()
    MODEL_INDEX = build_model_index(MODEL_REGISTRY)


def get_model_entry(framework, model_name=None, data_type='Eceran'):
    """Get a model entry by exact name or framework+data_type with smart default selection."""
    refresh_registry()

    if model_name:
        entry = MODEL_INDEX.get(model_name)
        if entry is None:
            raise ValueError(f'Model tidak ditemukan di registry: {model_name}')
        if str(entry.get('framework', '')).lower() != framework.lower():
            raise ValueError(f'Model {model_name} bukan framework {framework}.')
        return entry

    candidates = [
        row for row in MODEL_REGISTRY
        if str(row.get('framework', '')).lower() == framework.lower()
        and str(row.get('data_type', '')).lower() == str(data_type).lower()
    ]

    if not candidates:
        raise ValueError(f'Tidak ada model {framework} untuk data_type={data_type} di registry.')

    default_name = DEFAULT_MODEL_BY_FRAMEWORK.get((framework, data_type))
    if default_name and default_name in MODEL_INDEX:
        return MODEL_INDEX[default_name]

    preferred_keywords = ['tuned', 'advanced', 'original']
    for keyword in preferred_keywords:
        for candidate in candidates:
            model_key = str(candidate.get('model_name', '')).lower()
            if keyword in model_key:
                return candidate

    return candidates[0]


def prepare_series(tanggal, values, freq='W'):
    """Convert input arrays to regularly sampled series for forecasting."""
    df = pd.DataFrame({
        'ds': pd.to_datetime(tanggal),
        'y': [float(x) for x in values]
    })
    df = df.dropna(subset=['ds']).sort_values('ds')
    df = df.set_index('ds').resample(freq).sum().reset_index()
    df['y'] = pd.to_numeric(df['y'], errors='coerce').fillna(0)
    return df


# ═══════════════════════════════════════════════════════════════════════
# HEALTH CHECK
# ═══════════════════════════════════════════════════════════════════════
@app.route('/health', methods=['GET'])
def health():
    refresh_registry()
    lstm_ready = any(str(row.get('framework', '')).lower() == 'lstm' for row in MODEL_REGISTRY)
    prophet_ready = any(str(row.get('framework', '')).lower() == 'prophet' for row in MODEL_REGISTRY)

    available = []
    for row in MODEL_REGISTRY:
        artifact_path = row.get('artifact_resolved', '')
        available.append({
            'model_name': row.get('model_name', ''),
            'framework': row.get('framework', ''),
            'data_type': row.get('data_type', ''),
            'artifact_ready': bool(artifact_path and os.path.exists(artifact_path))
        })

    return jsonify({
        'status': 'ok',
        'registry_path': MODEL_REGISTRY_PATH,
        'registry_loaded': len(MODEL_REGISTRY),
        'models': {
            'lstm': 'ready' if lstm_ready else 'not trained',
            'prophet': 'ready' if prophet_ready else 'not trained'
        },
        'available_models': available
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
        data = request.get_json()
        bulan = data.get('bulan', [])
        terjual = data.get('terjual', [])
        model_name = data.get('model_name')
        data_type = data.get('data_type', 'Eceran')

        if len(bulan) < 3 or len(terjual) < 3:
            return jsonify({
                'error': 'Minimal 3 bulan data terbaru diperlukan untuk prediksi.'
            }), 400

        if len(bulan) != len(terjual):
            return jsonify({'error': 'Panjang array bulan dan terjual harus sama.'}), 400

        entry = get_model_entry('LSTM', model_name=model_name, data_type=data_type)
        model_path = entry.get('artifact_resolved', '')
        scaler_path = entry.get('scaler_resolved', '')

        if not os.path.exists(model_path):
            return jsonify({'error': f'Artifact model tidak ditemukan: {model_path}'}), 400
        if not scaler_path or not os.path.exists(scaler_path):
            return jsonify({'error': f'Scaler tidak ditemukan: {scaler_path}'}), 400

        # Load model, scaler, and metadata
        from tensorflow.keras.models import load_model
        model = load_model(model_path)
        scaler = joblib.load(scaler_path)

        seq_length = 3
        if str(entry.get('look_back', '')).strip() != '':
            seq_length = int(entry.get('look_back', 3))
        elif entry.get('meta_resolved') and os.path.exists(entry.get('meta_resolved')):
            try:
                with open(entry.get('meta_resolved'), 'r', encoding='utf-8') as f:
                    meta_json = json.load(f)
                seq_length = int(meta_json.get('look_back', meta_json.get('seq_length', 3)))
            except Exception:
                seq_length = 3

        use_log_target = bool(entry.get('log_target', False))

        # Prepare and transform the latest data.
        df_series = prepare_series(bulan, terjual, freq='W')
        values = df_series['y'].values.reshape(-1, 1)

        if len(values) <= seq_length:
            return jsonify({
                'error': f'Data setelah resample kurang. Butuh > {seq_length} titik untuk model ini.'
            }), 400

        transformed = np.log1p(values) if use_log_target else values
        scaled = scaler.transform(transformed)

        def inverse_output(pred_scaled):
            pred_transformed = scaler.inverse_transform(pred_scaled).flatten()
            return np.expm1(pred_transformed) if use_log_target else pred_transformed

        # ── Evaluate on last known month ──
        eval_seq = scaled[-(seq_length + 1):-1].reshape(1, seq_length, 1)
        eval_pred_scaled = model.predict(eval_seq, verbose=0)
        eval_pred = float(np.maximum(inverse_output(eval_pred_scaled)[0], 0))
        actual = float(values[-1][0])
        mae = float(abs(actual - eval_pred))
        rmse = float(np.sqrt((actual - eval_pred) ** 2))

        # ── Forecast next 1 period ──
        forecast_input = scaled[-seq_length:].reshape(1, seq_length, 1)
        forecast_scaled = model.predict(forecast_input, verbose=0)
        forecast_value = np.maximum(inverse_output(forecast_scaled), 0)

        result = {
            'forecast': [round(float(v), 2) for v in forecast_value],
            'mae': round(mae, 2),
            'rmse': round(rmse, 2),
            'model': 'LSTM',
            'model_name': entry.get('model_name'),
            'data_type': entry.get('data_type')
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
        data = request.get_json()
        bulan = data.get('bulan', [])
        terjual = data.get('terjual', [])
        model_name = data.get('model_name')
        data_type = data.get('data_type', 'Eceran')

        if len(bulan) < 3 or len(terjual) < 3:
            return jsonify({'error': 'Minimal 3 data diperlukan untuk prediksi Prophet.'}), 400

        if len(bulan) != len(terjual):
            return jsonify({'error': 'Panjang array bulan dan terjual harus sama.'}), 400

        entry = get_model_entry('Prophet', model_name=model_name, data_type=data_type)
        model_path = entry.get('artifact_resolved', '')
        if not os.path.exists(model_path):
            return jsonify({'error': f'Artifact model tidak ditemukan: {model_path}'}), 400

        from prophet import Prophet
        from prophet.serialize import model_from_json

        # Load saved model
        with open(model_path, 'r', encoding='utf-8') as f:
            model = model_from_json(f.read())

        # Prepare data for evaluation
        df = prepare_series(bulan, terjual, freq='W')

        # ── Evaluate on last known point ──
        actual = df.iloc[-1]['y']
        eval_forecast = model.predict(df[['ds']])
        eval_pred = max(eval_forecast.iloc[-1]['yhat'], 0)

        mae = float(abs(actual - eval_pred))
        rmse = float(np.sqrt((actual - eval_pred) ** 2))

        # ── Forecast next 1 period ──
        future = model.make_future_dataframe(periods=1, freq='W')
        forecast = model.predict(future)
        forecast_value = max(forecast.iloc[-1]['yhat'], 0)

        result = {
            'forecast': [round(float(forecast_value), 2)],
            'mae': round(mae, 2),
            'rmse': round(rmse, 2),
            'model': 'PROPHET',
            'model_name': entry.get('model_name'),
            'data_type': entry.get('data_type')
        }
        return jsonify(result)

    except Exception as e:
        app.logger.error(f'Prophet predict error: {str(e)}')
        return jsonify({'error': str(e)}), 500


# ═══════════════════════════════════════════════════════════════════════
if __name__ == '__main__':
    print(f"📁 Models directory: {MODEL_DIR}")
    refresh_registry()
    print(f"📄 Registry path: {MODEL_REGISTRY_PATH}")
    print(f"📊 Registry models loaded: {len(MODEL_REGISTRY)}")
    print(f"🔍 Legacy LSTM model: {'✅ Ready' if os.path.exists(LSTM_MODEL_PATH) else '❌ Not trained'}")
    print(f"🔍 Legacy Prophet model: {'✅ Ready' if os.path.exists(PROPHET_MODEL_PATH) else '❌ Not trained'}")
    print(f"🚀 Starting Flask API on http://0.0.0.0:5000")
    app.run(debug=True, host='0.0.0.0', port=5000)
