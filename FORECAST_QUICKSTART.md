# 🚀 Quick Start Guide - Batch Forecasting

## Run Your First Forecast

```bash
# Make sure Flask server is running on port 5000
php artisan app:forecast-all --model=lstm
```

## View Results

Navigate to: **`/admin/forecast/stock`**

## Schedule for Monthly Auto-Run

Already configured! ✅ Runs automatically on the 1st of each month at 2:00 AM.

To enable, add to crontab:
```bash
* * * * * cd /path/to/masroster && php artisan schedule:run >> /dev/null 2>&1
```

## Test Command

```bash
# With LSTM model
php artisan app:forecast-all --model=lstm

# With Prophet model  
php artisan app:forecast-all --model=prophet

# Force recalculation
php artisan app:forecast-all --model=lstm --force
```

## Check Scheduled Tasks

```bash
php artisan schedule:list
```

---

## Status Meanings

- 🔴 **Critical** - Stock < 70 (urgent!)
- 🟡 **Low** - Stock < (Forecast + 70)
- 🟢 **Safe** - Optimal levels
- 🟠 **Overstock** - Stock > 3× threshold

---

## Troubleshooting

**Flask not available?** → Command auto-falls back to Simple Moving Average

**Need to update forecasts?** → Run with `--force` flag

**Check logs:** `storage/logs/laravel.log`

---

📖 **Full documentation:** `FORECAST_SYSTEM_DOCUMENTATION.md`
