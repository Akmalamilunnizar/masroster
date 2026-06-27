<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * CRITICAL STEPS:
     * 1. Drop all foreign key constraints that reference produk(IdRoster)
     * 2. Rename the `IdRoster` column to `sku`
     * 3. Add UNIQUE index on `sku`
     * 4. Make `id` the PRIMARY KEY with AUTO_INCREMENT
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        // Step 1: Drop existing foreign keys that reference produk
        // (This is important: child tables can't change their FK while the parent has a different PK)
        if ($driver === 'mysql') {
            $constraints = DB::select("\n                SELECT TABLE_NAME, CONSTRAINT_NAME\n                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE\n                WHERE REFERENCED_TABLE_SCHEMA = DATABASE()\n                  AND REFERENCED_TABLE_NAME = 'produk'\n                  AND COLUMN_NAME IN ('IdRoster', 'id_roster')\n            ");

            foreach ($constraints as $constraint) {
                DB::statement(sprintf(
                    'ALTER TABLE `%s` DROP FOREIGN KEY `%s`',
                    $constraint->TABLE_NAME,
                    $constraint->CONSTRAINT_NAME
                ));
            }
        }

        // Step 2: Rename IdRoster to sku only if the legacy column still exists.
        if (Schema::hasColumn('produk', 'IdRoster') && !Schema::hasColumn('produk', 'sku')) {
            Schema::table('produk', function (Blueprint $table) use ($driver) {
                if ($driver !== 'sqlite') {
                    $table->renameColumn('IdRoster', 'sku');
                }
            });

            if ($driver === 'sqlite') {
                DB::statement(<<<SQL
                    ALTER TABLE produk RENAME COLUMN IdRoster TO sku
                SQL);
            }
        }

        // Step 3: Add UNIQUE index on sku
        if (!$this->hasUniqueIndex('produk', 'produk_sku_unique')) {
            Schema::table('produk', function (Blueprint $table) {
                $table->unique('sku', 'produk_sku_unique');
            });
        }

        // Step 4: Make id the PRIMARY KEY with AUTO_INCREMENT
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE produk DROP PRIMARY KEY');
            DB::statement('ALTER TABLE produk ADD PRIMARY KEY (id)');
            DB::statement('ALTER TABLE produk MODIFY COLUMN id BIGINT UNSIGNED AUTO_INCREMENT');
        } elseif ($driver === 'sqlite') {
            // SQLite handles this differently; we set the id as the primary key implicitly via INTEGER PRIMARY KEY
            // Since we can't alter after creation, we accept that autoincrement behavior is implicit
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE produk DROP CONSTRAINT produk_pkey CASCADE');
            DB::statement('ALTER TABLE produk ADD PRIMARY KEY (id)');
            DB::statement('CREATE SEQUENCE produk_id_seq OWNED BY produk.id');
            DB::statement('ALTER TABLE produk ALTER COLUMN id SET DEFAULT nextval(\'produk_id_seq\')');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        // Step 1: Drop the UNIQUE index on sku
        if ($this->hasUniqueIndex('produk', 'produk_sku_unique')) {
            Schema::table('produk', function (Blueprint $table) {
                $table->dropUnique('produk_sku_unique');
            });
        }

        // Step 2: Make sku the PRIMARY KEY again
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE produk DROP PRIMARY KEY');
            DB::statement('ALTER TABLE produk ADD PRIMARY KEY (sku)');
            DB::statement('ALTER TABLE produk MODIFY COLUMN id BIGINT UNSIGNED NULL');
        } elseif ($driver === 'sqlite') {
            // SQLite doesn't support dropping and re-adding primary keys easily
            // This migration is destructive and not easily reversible without table recreation
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE produk DROP CONSTRAINT produk_pkey');
            DB::statement('DROP SEQUENCE produk_id_seq');
            DB::statement('ALTER TABLE produk ADD PRIMARY KEY (sku)');
            DB::statement('ALTER TABLE produk ALTER COLUMN id DROP DEFAULT');
        }

        // Step 3: Rename sku back to IdRoster
        if ($driver === 'sqlite') {
            DB::statement('ALTER TABLE produk RENAME COLUMN sku TO IdRoster');
        } else {
            Schema::table('produk', function (Blueprint $table) {
                $table->renameColumn('sku', 'IdRoster');
            });
        }
    }

    protected function hasUniqueIndex(string $table, string $indexName): bool
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            $result = DB::select(
                'SELECT COUNT(*) AS aggregate FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?',
                [$table, $indexName]
            );

            return (int) ($result[0]->aggregate ?? 0) > 0;
        }

        if ($driver === 'pgsql') {
            $result = DB::select(
                'SELECT COUNT(*) AS aggregate FROM pg_indexes WHERE schemaname = current_schema() AND tablename = ? AND indexname = ?',
                [$table, $indexName]
            );

            return (int) ($result[0]->aggregate ?? 0) > 0;
        }

        if ($driver === 'sqlite') {
            $result = DB::select(
                'SELECT COUNT(*) AS aggregate FROM sqlite_master WHERE type = ? AND tbl_name = ? AND name = ?',
                ['index', $table, $indexName]
            );

            return (int) ($result[0]->aggregate ?? 0) > 0;
        }

        return false;
    }
};
