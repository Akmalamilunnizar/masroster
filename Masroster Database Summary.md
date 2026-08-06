# Masroster — Database Summary

This file provides a concise overview of the main database tables, relationships, and recent migration decisions relevant for ecommerce and forecasting.

## Core Tables

- `produk` — canonical product table. Stores product metadata, SKU, forecasting columns (MAE/WMAPE/RMSE), model version info, and stock-related flags.
- `detail_harga` — price table for products by size/variant; used for authoritative price lookups during checkout.
- `size` / `produk_size` (pivot) — product sizes/variants; pivot may historically reference `IdRoster` on older schema, now supports `produk_id` integer foreign key.
- `transaksi` — order header/transaction table. Includes references to customer, payment, status, and workflow fields.
- `detail_transaksi` — transaction lines (items). Contains immutable snapshot fields such as `harga_satuan_snapshot` to keep historical accuracy.

## Supporting Tables

- `users` — customers and admin accounts; recent migrations add B2B/B2C flags and address linking.
- `addresses` — customer shipping addresses; referenced by transactions or detail_harga when applicable.
- `detail_motif`, `detail_tipe`, `motif_roster`, `tipe_roster` — legacy classification/detail tables used by product variants and motif/tipo mappings.
- `kategori` / `subcategory` / `category` — catalog classification used by frontend searches and reports.

## Relationships & Foreign Keys

- Products to sizes: many-to-many via `produk_size` pivot (watch for legacy `IdRoster` column in older DBs).
- Transactions to products: `transaksi` 1-to-many `detail_transaksi` with `produk_id` (or legacy roster id) and price snapshoting.
- Prices: `detail_harga` links to product + size and acts as authoritative price source for checkout.

## Migration Notes (recent)

- Introduced integer `id` on `produk` and re-established `produk_id` foreign keys on child tables to simplify ORM relations and testing.
- Performed primary key swap and renamed legacy `idRoster`/`IdRoster` to `sku` as public product identifier while `id` remains the numeric PK.
- Added `harga_satuan_snapshot` to `detail_transaksi` to capture price at purchase time and prevent total/price mismatches after price updates.

## Testing Considerations

- Tests run under SQLite; code guards schema differences using `Schema::hasTable` / `Schema::hasColumn` checks to avoid brittle migrations assumptions.
- Pivot handling in controllers now tolerates both `produk_id` and legacy `IdRoster` pivot shapes.

## Operational Recommendations

- Keep `detail_transaksi.harga_satuan_snapshot` populated at checkout to ensure historical reporting and refunds are accurate.
- Before applying migration that renames primary identifiers in production, run the migration on a staging copy and ensure foreign keys are re-linked.
- Add lightweight integration tests that run migrations against MySQL to catch schema drift not visible in SQLite.

If you want, I can expand this into a full ER diagram or add per-table column maps and migration references.
Masroster — Database Summary

Purpose
-------
Concise developer-facing reference for the main schema, recent migration changes, and notes about test DB differences (SQLite vs MySQL).

Main Tables (high-level)
------------------------
- `produk`
  - Primary: `id` (integer, canonical PK)
  - Public identifier: `sku` (string, unique)
  - Forecasting columns: `mae_score`, `wmape_score`, `rmse`, `model_version`, etc.
  - Notes: `id` was added to support foreign keys; code supports legacy `IdRoster` where present.

- `detail_harga`
  - Stores pricing per product/size/variant.
  - Recently gained `address_id` for per-address pricing (migration 2026_06_22_000002).

- `detail_transaksi`
  - Transaction line items; recent addition: `harga_satuan_snapshot` to keep immutable price snapshots.

- `transaksi`
  - Orders/transactions. Contains workflow status columns (e.g., `ceo_workflow_status` added 2026_06_22_000003).

- Pivot tables (sizes, motifs)
  - Example: `produk_size` / `detail_motif` — historically some pivots used `IdRoster` as FK; migrations transitioned to `produk_id` integer FK.
  - Code includes guarded inserts that detect whether pivot expects `produk_id` or `IdRoster` and writes appropriately.

Recent Migration Highlights
--------------------------
- Added `id` integer to `produk` and created `produk_id` references on child tables to normalize relations.
- Performed a primary key swap so `sku` remains the stable public code while `id` is used for relational integrity.
- Added forecast metric and versioning columns to `produk` to support per-product model tracking.
- Added snapshot column to `detail_transaksi` so historical order amounts remain immutable.

Testing Notes (SQLite vs MySQL)
------------------------------
- The test suite runs primarily on SQLite for speed; some legacy tables/columns (e.g., `detail_motif`, `IdRoster`) may not exist in tests.
- Code uses `Schema::hasTable` and `Schema::hasColumn` guards where needed to avoid test failures caused by migration differences.
- When writing migrations that affect production schema, also add complementary test migrations or update `tests/Support/MasrosterTestSchema.php` to mirror required tables for CI.

Operational Guidance
--------------------
- Always compute monetary values server-side during checkout; store snapshots in `detail_transaksi` when creating orders.
- When altering product identity (sku ↔ IdRoster), ensure pivot tables and imports are migrated incrementally and code supports both shapes during transition.
- For webhook/event processors, validate payload signatures and persist only verified changes to `transaksi` and related tables.

If you want, I can generate a per-table detailed column map (CSV or markdown table) for each table mentioned and add it to this file.
