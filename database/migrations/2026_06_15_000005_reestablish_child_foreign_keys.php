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
     * Re-establish foreign key constraints now that produk has id as the primary key.
     * Child tables now reference produk.id via their new produk_id columns.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        // Re-establish FK on produk_size
        if (!$this->hasConstraint('produk_size', 'produk_id', $driver)) {
            Schema::table('produk_size', function (Blueprint $table) {
                $table->foreign('produk_id', 'produk_size_produk_id_fk')
                    ->references('id')
                    ->on('produk')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        }

        // Re-establish FK on detail_transaksi
        if (!$this->hasConstraint('detail_transaksi', 'produk_id', $driver)) {
            Schema::table('detail_transaksi', function (Blueprint $table) {
                $table->foreign('produk_id', 'detail_transaksi_produk_id_fk')
                    ->references('id')
                    ->on('produk')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        }

        // Re-establish FK on detail_harga
        if (!$this->hasConstraint('detail_harga', 'produk_id', $driver)) {
            Schema::table('detail_harga', function (Blueprint $table) {
                $table->foreign('produk_id', 'detail_harga_produk_id_fk')
                    ->references('id')
                    ->on('produk')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        }

        // Re-establish FK on model_histories
        if (!$this->hasConstraint('model_histories', 'produk_id', $driver)) {
            Schema::table('model_histories', function (Blueprint $table) {
                $table->foreign('produk_id', 'model_histories_produk_id_fk')
                    ->references('id')
                    ->on('produk')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produk_size', function (Blueprint $table) {
            $table->dropForeign('produk_size_produk_id_fk');
        });

        Schema::table('detail_transaksi', function (Blueprint $table) {
            $table->dropForeign('detail_transaksi_produk_id_fk');
        });

        Schema::table('detail_harga', function (Blueprint $table) {
            $table->dropForeign('detail_harga_produk_id_fk');
        });

        Schema::table('model_histories', function (Blueprint $table) {
            $table->dropForeign('model_histories_produk_id_fk');
        });
    }

    private function hasConstraint(string $table, string $column, string $driver): bool
    {
        if ($driver === 'mysql') {
            $constraints = DB::select(
                "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL",
                [$table, $column]
            );
            return count($constraints) > 0;
        } elseif ($driver === 'sqlite') {
            $fks = DB::select("PRAGMA foreign_key_list('$table')");
            foreach ($fks as $fk) {
                if ($fk->from === $column) {
                    return true;
                }
            }
            return false;
        } elseif ($driver === 'pgsql') {
            $constraints = DB::select(
                "SELECT constraint_name FROM information_schema.key_column_usage WHERE table_name = ? AND column_name = ? AND referenced_table_name IS NOT NULL",
                [$table, $column]
            );
            return count($constraints) > 0;
        }
        return false;
    }
};
