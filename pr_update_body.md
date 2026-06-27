Security: validate local payloads, avoid Request mutation, reduce logging

Summary
-------
This branch hardens ecommerce flows and improves testability and logging hygiene.

What changed
------------
- Enforced zero-trust request handling: controllers validate inputs into local arrays and never mutate `Request` objects.
- Server-side authority for monetary values: prices, discounts, and totals are looked up/recomputed on the server during checkout; client-supplied monetary fields are ignored.
- Controller hardening: safer pivot inserts for product sizes (supports legacy `IdRoster` and `produk_id`), Schema guards (`Schema::hasTable` / `hasColumn`) for test DB compatibility.
- Webhook validation: Midtrans webhook signatures validated before processing.
- Reduced sensitive logging: replaced full request dumps with minimal metadata (keys, counts, ids).
- Tests: added/updated tests including `CartTamperingTest` and `PaymentWebhookTest` to prevent regressions.
- Formatting: repo formatted with Laravel Pint.

Test results (local)
--------------------
- PHPUnit: full suite passed locally (37 tests, 178 assertions).
- Laravel Pint: formatting run and repo cleaned.

Migrations / Backwards Compatibility
-----------------------------------
- Migrations add an integer `id` to `produk` and re-establish `produk_id` foreign keys; `sku` remains the public identifier.
- Controllers tolerate legacy schema shapes and pivot columns to allow staged migrations.

Notes for reviewers
-------------------
- Focus review on checkout, cart and product pivot areas (`app/Http/Controllers/Api/V1/*`) and on new/updated tests in `tests/Feature`.
- Recommend running the migrations on staging (MySQL) and running the payment webhook integration tests before merging.

Files changed (high-level)
-------------------------
- Many controllers hardened and formatted. See the branch diff for the full file list.

Next steps
----------
- I can add integration tests targeting MySQL and/or update the PR with a detailed migration checklist if desired.

Migration checklist (recommended before merge)
---------------------------------------------
Run these steps in staging (MySQL) before merging to avoid downtime or FK issues in production.

1. Create a full database backup of staging/production (dump + copy):

	```bash
	mysqldump -u $USER -p --single-transaction --routines --triggers --databases masroster > masroster_backup.sql
	```

2. Run migrations in a staging environment with `--step` to inspect behavior:

	```bash
	php artisan migrate --force --path=database/migrations/2026_06_15_* --step
	```

3. Validate foreign keys and child table linkages:
	- Ensure `produk.id` exists and `produk_id` FK columns are populated on child tables.
	- Confirm `detail_transaksi.harga_satuan_snapshot` is not-null for new orders.

4. Run MySQL integration tests (add to CI matrix) to catch schema-only issues:

	```bash
	# set DB_CONNECTION=mysql and provide TEST DB credentials
	php artisan test --testsuite=Feature
	```

5. Verify pivot transitions:
	- Confirm `produk_size` pivot rows include `produk_id`; when migrating legacy data, populate `produk_id` from `IdRoster`→`sku`→`produk.id` mapping.

6. Confirm webhook processors with live-like payloads (use a staging Midtrans sandbox account):
	- Replay a signed webhook and ensure signature validation and idempotency.

7. Run smoke tests on checkout and admin product CRUD flows.

8. Schedule a short maintenance window if the migration requires locks or long-running DDL.

9. After merge: monitor error logs and critical transaction metrics for at least one deploy cycle.

Commands and tips
-----------------
- Format code (already run locally): `vendor/bin/pint`
- Run full test suite locally (SQLite): `php artisan test`
- Run MySQL tests locally by setting `DB_CONNECTION=mysql` in a local `.env.testing` and running: `php artisan test`

If you'd like, I can add a migration runbook (step-by-step commands and SQL snippets) to this PR or a separate `DEPLOY.md`.
