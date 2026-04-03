# Masroster Project Summary

## 1. Gambaran Umum
Masroster adalah sistem **e-commerce, manajemen inventory, dan forecasting permintaan** untuk bisnis produk roster.

Fokus sistem:
- Penjualan produk roster standar dengan ukuran yang sudah didefinisikan
- Kontrol stok masuk/keluar dan monitoring status stok
- Prediksi demand untuk rekomendasi restock yang proaktif

## 2. Tujuan Bisnis
- Menyediakan proses pembelian roster yang konsisten dan cepat untuk pelanggan
- Menjaga akurasi data operasional melalui panel admin
- Mendukung keputusan pembelian bahan/produksi dengan forecast demand bulanan

## 3. Teknologi Inti
- **Backend utama**: Laravel (PHP)
- **Frontend**: Blade templates + aset statis
- **Database**: MySQL
- **Auth & role**: Laravel Auth + middleware `auth` dan `role:admin`
- **AI forecasting service**: Flask (Python)
- **Model forecasting**: LSTM dan Prophet dengan fallback SMA

## 4. Arsitektur Tingkat Tinggi
### 4.1 Laravel (Transaksi & Operasional)
Laravel menangani:
- Catalog produk roster
- Cart, checkout, shipping, dan order confirmation
- Master data admin (produk, ukuran, tipe, motif, supplier, customer)
- Stok dan transaksi
- Trigger batch forecasting

### 4.2 Flask (Forecasting Service)
Flask menangani:
- Training model (`/train/lstm`, `/train/prophet`)
- Prediksi (`/predictlstm`, `/predictprophet`)
- Monitoring service (`/health`)

### 4.3 Integrasi
Laravel memanggil Flask melalui HTTP internal untuk forecasting. Data transaksi tetap diproses penuh di Laravel agar stabil dan cepat untuk user.

## 5. Modul Fungsional Utama
### 5.1 Storefront
- Dashboard toko (`/tokodashboard`)
- Pencarian produk
- Detail produk
- Riwayat pesanan pelanggan

### 5.2 Cart & Checkout
- Cart berbasis session
- Pilihan ukuran dari master ukuran yang tersedia
- Simpan alamat, metode kirim, ongkir, dan catatan order
- Konfirmasi order menjadi transaksi permanen

### 5.3 Address Management
- CRUD alamat pelanggan
- Set default address
- Pemilihan alamat aktif untuk checkout

### 5.4 Transaksi
- Header transaksi di `transaksi`
- Detail item transaksi di `detail_transaksi`
- Status pembayaran dan status pesanan
- Approve/reject pesanan oleh admin
- Cetak invoice

### 5.5 Forecasting & Stock Monitoring
- Forecast manual dan batch
- Penyimpanan hasil forecast pada tabel `produk`
- Klasifikasi status stok (`critical`, `low`, `safe`, `overstock`)
- Rekomendasi restock untuk item berisiko

## 6. Alur Data Kritis
### 6.1 Order Lifecycle
1. User menambahkan produk roster ke cart
2. User mengisi detail pengiriman
3. User konfirmasi order
4. Sistem menyimpan transaksi secara atomik
5. Session checkout dibersihkan

### 6.2 Forecast Lifecycle
1. Admin menjalankan batch forecast
2. Laravel memverifikasi kesehatan service Flask
3. Prediksi demand dihitung (AI atau fallback SMA)
4. Hasil disimpan ke kolom cache forecast pada `produk`
5. Dashboard menampilkan alert status stok + restock recommendation

## 7. Aturan Bisnis Utama
- Segmentasi data demand:
  - `QtyProduk > 100` -> `Borongan`
  - selain itu -> `Eceran`
- Safety stock default: `70`
- Mapping model berdasarkan framework + data type (LSTM/Prophet)

## 8. Route Surface (Ringkas)
- Public/storefront: `/`, `/tokodashboard`, `/shop`, `/pesanan`
- Checkout (auth): `/cart`, `/details`, `/shipping`, `/review`, `/confirm-order`
- Address (auth): `/addresses` dan endpoint default/update/delete
- Admin transaksi: `/admin/all-transaksi` dan endpoint approval/invoice
- Forecast admin: `/admin/forecast`, `/admin/forecast/run-batch`, `/admin/forecast/flask-health`

## 9. Kondisi Implementasi
- Forecasting sudah berbasis batch + cache
- Dashboard admin menampilkan ringkasan status stok dan rekomendasi restock
- Integrasi Flask berjalan dengan fallback saat service offline

## 10. Kelebihan Arsitektur
- Pemisahan concern transaksi vs forecasting
- Dashboard cepat karena membaca cache forecast
- Degradasi terkontrol saat AI service gagal
- Mendukung pengambilan keputusan inventory yang lebih proaktif

## 11. Rekomendasi Lanjutan
- Tambah CI checks untuk regresi flow order + forecast
- Tambah alert otomatis untuk item critical
- Review periodik parameter model forecasting

## 12. File Kunci
- `routes/web.php`
- `app/Http/Controllers/Api/V1/CartController.php`
- `app/Http/Controllers/Api/V1/OrderController.php`
- `app/Http/Controllers/Api/V1/DashboardController.php`
- `app/Http/Controllers/Api/V1/ForecastController.php`
- `app/Console/Commands/ForecastAllProducts.php`
- `app/Models/Produk.php`
- `app/Models/Transaksi.php`
- `app/Models/DetailTransaksi.php`
- `FORECAST_SYSTEM_DOCUMENTATION.md`
