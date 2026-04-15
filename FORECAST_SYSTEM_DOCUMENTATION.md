# Forecast System Documentation

## Overview
Sistem forecasting Masroster memakai pendekatan **batch + cache** untuk mendukung keputusan restock produk roster.

Tujuan utama:
- Menghitung demand bulan berikutnya secara terjadwal
- Menyimpan hasil ke database agar dashboard cepat
- Menampilkan alert stok dan rekomendasi restock yang actionable

## Data Model Forecast di Tabel `produk`
Kolom utama yang dipakai:
- `forecasted_demand` (float)
- `forecast_model` (`lstm`, `prophet`, `sma`)
- `safety_stock` (integer, default 70)
- `forecast_status` (`critical`, `low`, `safe`, `overstock`)
- `last_forecast_at` (timestamp)

## Registry Model History
Seluruh versi model disimpan di tabel `model_histories` sebagai single source of truth.

Kolom utama:
- `id_roster`
- `model_type` (`lstm`, `prophet`)
- `version_id`
- `wmape_score`
- `mae_score`
- `rmse_score`
- `is_active`

## Aturan Klasifikasi Status
- `critical`: `stock < safety_stock`
- `low`: `stock < (forecasted_demand + safety_stock)`
- `overstock`: `stock > 3 * (forecasted_demand + safety_stock)`
- `safe`: selain kondisi di atas

## Command Batch Forecast
Command utama:
```bash
php artisan app:forecast-all --model=lstm
```

Opsi:
- `--model=lstm`
- `--model=prophet`
- `--force` untuk re-calculate meskipun data masih fresh

Contoh:
```bash
php artisan app:forecast-all --model=lstm --force
```

## Jadwal Otomatis
Forecast batch dijadwalkan bulanan via Laravel scheduler.

Cek jadwal:
```bash
php artisan schedule:list
```

Jalankan scheduler manual:
```bash
php artisan schedule:run
```

## Integrasi Flask
Endpoint yang digunakan:
- `GET /health`
- `POST /train/lstm`
- `POST /train/prophet`
- `POST /predictlstm`
- `POST /predictprophet`

Payload inti prediksi:
```json
{
  "bulan": ["2025-01", "2025-02"],
  "terjual": [120, 150],
  "model_version": "v123456"
}
```

Batch dan manual forecast mengirim `model_version` yang diambil dari `model_histories`.

## Fallback Behavior
Jika Flask tidak tersedia:
- Fast inference dibatalkan
- Log warning dicatat di `storage/logs/laravel.log`

## Dashboard Utility
Dashboard admin menampilkan:
- Ringkasan jumlah item per status (`critical`, `low`, `safe`, `overstock`)
- Tabel rekomendasi restock untuk item `critical` dan `low`
- Quantity rekomendasi berdasarkan `forecasted_demand + safety_stock - stock`

## Verifikasi Operasional
1. Jalankan migration forecast fields jika belum ada.
2. Jalankan command batch:
```bash
php artisan app:forecast-all --model=lstm
```
3. Buka halaman admin forecast/dashboard untuk memastikan status dan rekomendasi tampil.
4. Periksa log jika terjadi timeout atau service offline.
5. Cek `model_histories` untuk memastikan baris aktif per `id_roster` dan `model_type` sudah benar.

## Troubleshooting
- Forecast kosong: jalankan ulang dengan `--force`.
- Semua model jadi `sma`: cek endpoint `/health` Flask.
- Batch lambat: cek response time Flask dan ukuran data historis.

## File Kunci
- `app/Console/Commands/ForecastAllProducts.php`
- `app/Http/Controllers/Api/V1/ForecastController.php`
- `app/Http/Controllers/Api/V1/DashboardController.php`
- `app/Models/Produk.php`
- `resources/views/admin/forecast/stock.blade.php`
- `resources/views/admin/dashboard.blade.php`
