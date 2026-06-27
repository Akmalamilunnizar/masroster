# Masroster Database Summary

## 1. Overview

Masroster uses a MySQL/MariaDB database to support an e-commerce storefront, order management, inventory tracking, and forecasting-driven restock decisions.

Current database characteristics:
- Engine: MariaDB 11.4.x / MySQL-compatible
- Charset: utf8mb4
- Core design: product catalog, customer orders, address management, pricing tiers, and forecast caches
- Identity refactor: `produk` now uses numeric `id` plus business `sku` instead of only `IdRoster`

## 2. Main Business Areas

### E-commerce
- Product catalog browsing and detail pages
- Session-based cart and checkout flow
- Order creation, approval, and invoice handling
- Customer address selection for shipping

### Inventory
- Stock tracking on `produk`
- Product size variants through `produk_size`
- Transaction detail snapshots for sales and fulfillment

### Forecasting
- Forecast results cached on `produk`
- Historical model metadata stored in `model_histories`
- Stock status classification stored in product records

## 3. Core Tables

### `produk`
Product master table.
Important columns:
- `id` bigint unsigned, internal primary key target
- `sku` varchar(13), business identifier shown in the app
- `NamaProduk`
- `id_jenis`, `id_tipe`, `id_motif`
- `stock`
- `forecasted_demand`, `mae_score`, `rmse_score`, `wmape_score`
- `forecast_model`, `safety_stock`, `forecast_status`, `last_forecast_at`

Notes:
- The database dump shows `sku` as the current primary key in the live database snapshot.
- `id` exists as the modern numeric identity used by the refactor path.

### `detail_harga`
Pricing table for product/size/user/location combinations.
Important columns:
- `id_roster` legacy roster identifier
- `produk_id` modern FK to `produk.id`
- `id_user` pricing owner / customer segment reference
- `id_ukuran`
- `harga`
- `address_id` location-specific pricing scope

Pricing rules:
- `id_user = 0` and `address_id IS NULL` represent the global default price.
- Address-scoped pricing supports retailer/end-customer negotiation.

### `detail_transaksi`
Line-item table for completed orders.
Important columns:
- `IdTransaksi`
- `IdRoster` legacy roster code
- `produk_id`
- `id_ukuran`
- `harga_satuan`
- `QtyProduk`
- `data_type` (`Eceran` / `Borongan`)
- `SubTotal`

Notes:
- `harga_satuan` is the immutable snapshot used for final billing.
- `SubTotal` stores the persisted line total.

### `transaksi`
Order header table.
Important columns:
- `IdTransaksi`
- `id_admin`
- `id_customer`
- `address_id`
- `Bayar`
- `GrandTotal`
- `tglTransaksi`
- `StatusPembayaran`
- `StatusPesanan`
- `workflow_status`
- `shipping_method`, `delivery_method`, `shipping_type`, `ongkir`, `notes`

Workflow notes:
- `workflow_status` supports the CEO approval/payment flow.
- Existing status fields remain string-based for application-level validation.

### `users`
Application user table.
Important columns in the current schema:
- `id`
- `f_name`
- `email`
- `nomor_telepon`
- `username`
- `user`
- `img`
- `alamat`
- `tipe_user`
- `status_verifikasi`
- `foto_toko`

Account types:
- `end_customer`
- `retailer`

Verification states:
- `pending`
- `approved`
- `rejected`

### `addresses`
Customer shipping address table.
Important columns:
- `id`
- `user_id`
- `label`
- `recipient_name`
- `phone_number`
- `city`
- `postal_code`
- `full_address`
- `is_default`

### `produk_size`
Product size variant table.
Important columns:
- `IdRoster` legacy code
- `produk_id`
- `id_ukuran`
- `harga`

### `model_histories`
Forecast model history table.
Important columns:
- `id`
- `produk_id`
- `id_roster`
- `model_type`
- `version_id`
- `wmape_score`, `mae_score`, `rmse_score`
- `is_active`

## 4. Relationship Map

- `users` 1:N `addresses`
- `users` 1:N `transaksi` as customer
- `users` 1:N `transaksi` as admin
- `produk` 1:N `produk_size`
- `produk` 1:N `detail_harga`
- `produk` 1:N `detail_transaksi`
- `produk` 1:N `model_histories`
- `transaksi` 1:N `detail_transaksi`
- `addresses` 1:N `transaksi`

## 5. Migration State

The repository uses a phased migration history to move from legacy roster identity handling to the current dual-key product model.

Completed phases in the current database state:
- Product refactor with `id` and `sku`
- Child table backfill to `produk_id`
- Re-established foreign keys
- New B2B/B2C account and pricing fields
- Workflow status support for CEO approval and payment capture

## 6. Operational Notes

- The database already contains live transactional data, so schema changes should be additive whenever possible.
- Legacy roster fields such as `IdRoster` still exist in some tables for transition compatibility.
- Any checkout or pricing logic should prefer `produk_id` / `id` and fall back to legacy roster identifiers only when needed.
- Forecasting jobs read sales history from `detail_transaksi` joined to `transaksi`.

## 7. Practical Summary

In short, the database supports three major concerns:
1. Selling roster products through an e-commerce checkout flow.
2. Managing inventory and address-scoped pricing for customer and retailer workflows.
3. Feeding historical sales into forecasting models and storing the results on product records.

This makes the schema a hybrid of commerce, operations, and forecasting data, with the current design centered on `produk` as the main identity anchor.
