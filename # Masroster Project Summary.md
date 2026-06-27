# Masroster Project Summary

## 1. Gambaran Umum

Masroster adalah **platform e-commerce terpadu untuk penjualan produk roster** dengan fitur manajemen inventory real-time dan sistem forecasting demand berbasis AI untuk mendukung keputusan restock.

**Fokus Utama: E-Commerce**
- Platform pembelian roster online yang user-friendly untuk pelanggan retail dan wholesale
- Proses checkout yang terstruktur: cart → shipping info → order review → confirmasi
- Manajemen pesanan end-to-end untuk pelanggan dan admin
- Katalog produk dengan variant ukuran yang fleksibel

**Fokus Sekunder: Forecasting & Inventory**
- Prediksi demand otomatis untuk mendukung keputusan restock proaktif
- Monitoring status stok real-time untuk mencegah stockout atau overstock
- Rekomendasi action untuk item dengan stok kritis

---

## 2. Tujuan Bisnis

**Penjualan & Customer Experience:**
- Menyediakan proses pembelian roster yang **cepat, konsisten, dan nyaman** bagi pelanggan
- Mendukung segmentasi penjualan: **Eceran** (retail individual) dan **Borongan** (wholesale bulk order)
- Meningkatkan customer lifetime value melalui kemudahan reorder dan tracking pesanan

**Operasional & Inventory Management:**
- Menjaga akurasi data stok real-time dengan trigger automatic untuk stok masuk/keluar
- Memberikan visibilitas penuh kepada admin terhadap inventory status
- Mendukung keputusan pembelian bahan baku/produksi dengan **prediksi demand bulanan berbasis AI**

---

## 3. Teknologi Inti

| Layer | Teknologi | Fungsi |
|-------|-----------|--------|
| **Backend** | Laravel 11 (PHP) | Logika bisnis transaksi, inventory, API |
| **Frontend** | Blade templates + Tailwind CSS | UI customer-facing dan admin dashboard |
| **Database** | MySQL | Penyimpanan transaksi, produk, customer, forecast cache |
| **Auth** | Laravel Auth | Autentikasi customer dan admin dengan role-based access |
| **AI Forecasting** | Flask (Python) | Service dedicated untuk training + prediksi model LSTM/Prophet |
| **Job Queue** | Laravel Queue | Batch forecasting dan background jobs |

---

## 4. Arsitektur Sistem

### 4.1 Laravel (Core E-Commerce & Operasional)
**Tanggung jawab utama:**
- **E-Commerce Pipeline**: Product catalog → Cart → Checkout → Order management → Invoice
- **Customer Management**: Profil customer, multiple addresses, order history
- **Inventory & Transaksi**: Stok tracking, transaksi header/detail, supplier management
- **Admin Dashboard**: Monitoring sales, approving orders, viewing stock status & restock recommendations
- **Integration Point**: Call Flask service untuk forecasting saat admin trigger manual atau batch job jalan

### 4.2 Flask Forecasting Service
**Tanggung jawab:**
- Model training: LSTM dan Prophet untuk time-series demand prediction
- API endpoints untuk predict: `/predictlstm`, `/predictprophet`
- Health check: `/health` untuk memastikan service availability
- Fallback handling: Jika Flask down, sistem gunakan Simple Moving Average (SMA) sebagai fallback

### 4.3 Alur Integrasi
```
1. Transaksi terjadi di Laravel → Ditulis ke DB
2. Admin trigger forecast batch
3. Laravel query data transaksi historis
4. Laravel panggil Flask untuk predict
5. Hasil forecast disimpan di cache (kolom produk)
6. Dashboard admin tampilkan restock recommendations
```

**Design principle:** Transaksi diproses 100% di Laravel untuk kecepatan & reliability. Forecasting decouple sebagai sidecar service dengan graceful fallback.

---

## 5. Customer Journey & E-Commerce Workflows

### 5.1 Storefront & Product Discovery
- **Landing page** (`/`): Showcase featured products
- **Dashboard toko** (`/tokodashboard`): Personalized customer dashboard dengan order history
- **Pencarian & filter**: Browse produk by jenis, tipe, motif, price
- **Detail produk**: Gambar, deskripsi, variant ukuran tersedia, harga per ukuran

### 5.2 Shopping Cart & Size Selection
- **Cart management**: Berbasis Laravel session (stateful untuk user yang belum login)
- **Size picker**: Setiap produk punya variant ukuran yang terdefisisi (e.g. S, M, L, XL)
- **Quantity control**: User specify jumlah per ukuran
- **Persistent cart**: Data cart ditampilkan di `/cart` dengan opsi edit/remove

### 5.3 Checkout Process (Multi-Step)
1. **Cart review** (`/cart`): Review items, qty, harga
2. **Shipping info** (`/shipping`): Pilih alamat tujuan atau input baru
   - Address management: CRUD alamat simpan, set default, active address untuk checkout
3. **Order review** (`/review`): Final confirmation sebelum pembayaran
4. **Confirm order** (`/confirm-order`): Submit order, generate transaksi atomik di DB
5. **Checkout complete**: Session dibersihkan, customer diarahkan ke order confirmation page

### 5.4 Order Management
- **Customer view** (`/pesanan`): Riwayat order dengan status (pending, approved, shipped, delivered)
- **Admin approval** (`/admin/all-transaksi`): Admin review & approve/reject pending orders
- **Admin actions**: 
  - Approve order → update status, trigger fulfillment
  - Reject order → notify customer
  - Print invoice (`/admin/transaksi/print-invoice`) → for fulfillment
  - View order detail → shipping, customer info, item breakdown

---

## 6. Modul Fungsional Utama

### 6.1 Product Management
- Master produk dengan SKU, nama, kategori (jenis, tipe, motif)
- Variant management: Ukuran tersedia per produk
- Pricing: Support multiple pricing tiers (per ukuran, per quantity range)
- Stock tracking: Real-time inventory count, audit log untuk stok masuk/keluar
- Legacy support: Migration dari string-based PK (`IdRoster`) ke modern numeric ID dengan `sku` identifier

### 6.2 Supplier & Inventory
- Supplier master: Supplier yang menjual bahan baku
- Purchase order: Tracking supply inbound
- Stock movement: Trigger-based automatic stok masuk (barang masuk) dan stok keluar (transaksi order)
- Stock status classification: `critical` (< safety stock), `low`, `safe`, `overstock`

### 6.3 Customer & Address
- Customer registration & profile
- Multiple address management per customer
- Default address handling untuk convenience
- Order history tracking per customer

### 6.4 Forecasting & Restock Intelligence
- **Manual forecast**: Admin run forecast untuk satu atau semua produk
- **Batch forecast**: Scheduled job untuk prediksi rutin
- **Forecast models**: 
  - LSTM (deep learning) untuk pattern kompleks
  - Prophet (Facebook) untuk trend + seasonality
  - SMA fallback jika Flask down
- **Result caching**: Forecast results disimpan di produk table
- **Restock recommendation**: Dashboard suggest action based on:
  - Forecast demand vs current stock
  - Safety stock threshold (default 70 units)
  - Segmentasi eceran vs borongan
  - Forecasted critical items

---

## 7. Aturan Bisnis Utama

### Segmentasi Penjualan
```
Quantity > 100 → Borongan (wholesale)
Quantity ≤ 100 → Eceran (retail)
```
*(Terpisah model forecast untuk setiap segment)*

### Inventory Thresholds
- **Safety stock**: 70 units (default)
- **Critical threshold**: < safety stock → trigger restock recommendation
- **Overstock threshold**: Monitor untuk excessive inventory

### Demand Forecasting
- Training data: Historical transaksi per segment (eceran vs borongan)
- Forecast period: 30 hari ke depan
- Model selection: LSTM/Prophet per data type, fallback SMA
- Update frequency: Manual trigger atau scheduled batch job

---

## 8. Database Schema Overview

### Key Tables
| Table | Purpose | Notes |
|-------|---------|-------|
| `produk` | Master produk roster | Includes SKU, stok count, forecast cache |
| `transaksi` | Order header | Customer, status, total amount |
| `detail_transaksi` | Order items | Line items dengan quantity, harga, ukuran |
| `produk_size` | Variant ukuran | Size definition per produk |
| `customer` | Customer profiles | Alamat, contact, segment |
| `alamat_pelanggan` | Customer addresses | Shipping addresses |
| `detail_harga` | Pricing tiers | Price per ukuran / quantity range |
| `model_histories` | Forecast results | Cache forecast predictions per produk |

### Current Migration Work
**Refactoring Phase:** Decoupling primary key from business identifier
- Old: `IdRoster` VARCHAR(13) as primary key (anti-pattern)
- New: `id` BIGINT AUTO_INCREMENT as PK, `sku` VARCHAR(13) as business identifier
- Timeline: Phased 5-migration approach with zero-downtime support
- Status: ✅ All migrations created, model refactoring complete, view updates in progress

---

## 9. Route Surface & API Endpoints

### Public/Customer Routes
| Route | Purpose |
|-------|---------|
| `/` | Landing page |
| `/tokodashboard` | Customer dashboard (with auth) |
| `/shop` | Product listing & search |
| `/pesanan` | Order history |
| `/cart` | Shopping cart |
| `/shipping` | Shipping address selection |
| `/review` | Order review before confirmation |
| `/confirm-order` | Submit order (POST) |

### Address Management
| Endpoint | Action |
|----------|--------|
| `/addresses` | View all addresses |
| `/addresses/create` | Add new address |
| `/addresses/{id}/edit` | Update address |
| `/addresses/{id}/delete` | Remove address |
| `/addresses/{id}/default` | Set as default |

### Admin Routes
| Route | Purpose |
|-------|---------|
| `/admin/all-transaksi` | Order management dashboard |
| `/admin/all-transaksi/{id}/approve` | Approve order |
| `/admin/all-transaksi/{id}/reject` | Reject order |
| `/admin/transaksi/print-invoice/{id}` | Generate invoice |
| `/admin/allproduk` | Product catalog management |
| `/admin/editproduk/{id}` | Edit product |
| `/admin/forecast` | Forecast dashboard |
| `/admin/forecast/run-batch` | Trigger batch forecast |
| `/admin/forecast/flask-health` | Check Flask service health |

---

## 10. Implementasi Status

### E-Commerce Pipeline ✅
- [x] Product catalog & search
- [x] Shopping cart (session-based)
- [x] Multi-step checkout flow
- [x] Customer address management
- [x] Order creation & tracking
- [x] Admin order approval workflow
- [x] Invoice generation

### Inventory Management ✅
- [x] Real-time stock tracking
- [x] Trigger-based stock movement (masuk/keluar)
- [x] Stock status classification

### Forecasting & Intelligence ✅
- [x] LSTM & Prophet models
- [x] Batch forecast scheduler
- [x] Cache-based forecast results
- [x] Restock recommendations
- [x] Flask integration with SMA fallback

### Current Work 🔄
- Primary key refactoring: Phase 4 (view updates) in progress
  - Goal: Decouple business identifier (SKU) from database primary key
  - Status: All migrations + model/controller refactoring done, blade templates partially updated

---

## 11. Keunggulan Arsitektur

### E-Commerce Benefits
- **Fast checkout**: Minimal DB queries, session-based cart untuk quick user flow
- **Flexible product variants**: Size selection per product dengan price calculation otomatis
- **Order traceability**: Full order lifecycle visible ke customer dan admin
- **Scalable customer base**: Laravel session handling + pagination untuk large customer volume

### Inventory & Forecasting Benefits
- **Proactive restock**: AI-powered demand prediction mencegah stockout
- **Graceful degradation**: SMA fallback saat Flask service down → sistem tetap functional
- **Separated concerns**: Transaksi core di Laravel (fast & reliable), forecasting di Flask (scalable & specialized)
- **Cache efficiency**: Forecast results di-cache untuk quick dashboard load

### Architecture Resilience
- **Zero-downtime migration**: Phased primary key refactoring dengan dual-schema support
- **Atomic transactions**: Order confirmation berjalan atomically untuk data consistency
- **Trigger-based automation**: Inventory sync otomatis via MySQL triggers (no polling needed)

---

## 12. Rekomendasi Lanjutan

### Immediate Priorities
1. **Complete Phase 4 refactoring**: Finish remaining blade template updates untuk legacy ID removal
2. **Run full test suite**: Validate migration integrity sebelum production deploy
3. **Load test checkout flow**: Ensure performance under concurrent customer load

### Short-term (1-2 months)
1. **Payment gateway integration**: Add Midtrans/GCash/bank transfer payment processing
2. **Email notifications**: Order confirmation, shipment tracking, forecast alerts
3. **Inventory alerting**: Real-time Slack/email alerts untuk critical stock items
4. **Analytics dashboard**: Revenue trend, top products, customer segmentation metrics

### Medium-term (3-6 months)
1. **Mobile app**: Native mobile experience untuk customer (React Native / Flutter)
2. **Advanced forecasting**: Add seasonality tuning, holiday detection, trend analysis
3. **Recommendation engine**: Upsell/cross-sell berdasarkan purchase history & trends
4. **B2B portal**: Dedicated wholesale customer portal dengan bulk ordering, credit terms

### Long-term (6+ months)
1. **Multi-warehouse inventory**: Support distributed fulfillment dari multiple locations
2. **Supplier integration**: Direct API integration untuk automated reordering
3. **Price optimization**: Dynamic pricing based on demand forecasts
4. **Customer churn prediction**: Identify at-risk customers untuk retention campaigns

---

## 13. Zero-Trust Security

Masroster treats every request, session, and external callback as untrusted until verified. Security is applied as a cross-cutting layer across checkout, admin, forecasting, and payment flows rather than as a separate afterthought.

### Payment Gateway Hardening
- Midtrans Snap token generation tetap server-side only.
- Webhook / callback wajib diverifikasi dengan signature check sebelum status transaksi diubah.
- Payment state hanya boleh bergerak melalui controller yang terotorisasi dan tercatat di log.

### IDOR Protection
- Semua akses detail order, alamat, produk, dan transaksi harus dibatasi oleh ownership check atau role check.
- Query berbasis ID tidak boleh langsung dipercaya dari client tanpa verifikasi user yang sedang login.

### Mass Assignment Guardrails
- Field sensitif seperti role, status verifikasi, harga, stok, dan status pembayaran wajib di-whitelist.
- Registrasi retailer hanya boleh mengisi field yang memang disediakan untuk flow tersebut.

### Rate Limiting & Abuse Control
- Login, checkout, registrasi, dan webhook Midtrans perlu throttling untuk mencegah brute force dan spam.
- Endpoint forecasting dan admin action penting juga perlu pembatasan untuk menjaga stabilitas layanan.

### XSS Mitigation
- Output Blade harus tetap escaped secara default.
- Input teks yang tampil kembali di UI perlu divalidasi dan disanitasi sesuai kebutuhan field.

### Security Logging & Audit Trail
- Aksi sensitif seperti approve/reject order, perubahan status pembayaran, dan update data penting harus tercatat.
- Log keamanan membantu tracing insiden, investigasi anomali, dan audit operasional.

---

## 14. File Kunci (E-Commerce Focused)

### Customer-Facing Controllers
- `app/Http/Controllers/Api/V1/CartController.php` - Cart logic
- `app/Http/Controllers/Api/V1/OrderController.php` - Order creation & checkout
- `app/Http/Controllers/Api/V1/DashboardController.php` - Customer dashboard

### Admin Controllers
- `app/Http/Controllers/Api/V1/ProdukController.php` - Product management (refactored for dual-key)
- `app/Http/Controllers/Api/V1/TransaksiController.php` - Order approval & management
- `app/Http/Controllers/Api/V1/ForecastController.php` - Forecasting triggers

### Models (Refactored Dual-Key Support)
- `app/Models/Produk.php` - Product with dynamic key detection
- `app/Models/Transaksi.php` - Order header
- `app/Models/DetailTransaksi.php` - Order items
- `app/Models/DetailHarga.php` - Pricing
- `app/Models/Customer.php` - Customer profile

### Routes
- `routes/web.php` - All public & auth customer routes
- `routes/admin.php` - Admin-only routes

### Tests & Schema
- `tests/Support/MasrosterTestSchema.php` - Test schema with dual-key support
- `database/migrations/` - All production migrations including Phase 1 (PK refactor)

### Documentation
- `FORECAST_SYSTEM_DOCUMENTATION.md` - Detailed forecasting system spec
- `FORECAST_QUICKSTART.md` - Quick reference untuk forecasting

---

## 15. Quick Start untuk Developer

### Environment Setup
```bash
cd Laravel/masroster
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

### Run Locally
```bash
php artisan serve          # Laravel on localhost:8000
# In separate terminal:
python flask/app.py        # Flask forecasting service on localhost:5000
```

### Testing
```bash
php artisan test           # Run full test suite dengan MasrosterTestSchema
```

### Deployment Checklist
1. Apply Phase 1 migrations (`php artisan migrate`)
2. Run test suite to validate schema transformation
3. Deploy view updates (Phase 4 blade templates)
4. Monitor Flask service health & order processing
5. Validate forecast accuracy after first batch run
