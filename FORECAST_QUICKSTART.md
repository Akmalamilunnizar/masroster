# Forecast Quickstart

## 1. Jalankan Batch Forecast
```bash
php artisan app:forecast-all --model=lstm
```

Alternatif:
```bash
php artisan app:forecast-all --model=prophet
php artisan app:forecast-all --model=lstm --force
```

## 2. Lihat Hasil
Buka:
- `/admin/forecast/stock`
- `/admin/dashboard`

Yang perlu dicek:
- status stok (`critical`, `low`, `safe`, `overstock`)
- rekomendasi restock untuk item berisiko

## 3. Aktifkan Scheduler
Tambahkan cron:
```bash
* * * * * cd /path/to/masroster && php artisan schedule:run >> /dev/null 2>&1
```

Cek jadwal:
```bash
php artisan schedule:list
```

## 4. Jika Flask Tidak Aktif
Sistem otomatis fallback ke SMA. Cek log:
```bash
tail -f storage/logs/laravel.log
```

## 5. Referensi
Dokumentasi lengkap: `FORECAST_SYSTEM_DOCUMENTATION.md`
