<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add `produk_id` columns to child tables (produk_size, detail_transaksi, detail_harga, model_histories)
     * and populate them by joining with the produk table to map IdRoster -> id.
     */
    public function up(): void
    {
        // Add produk_id to produk_size
        if (! Schema::hasColumn('produk_size', 'produk_id')) {
            Schema::table('produk_size', function (Blueprint $table) {
                $table->unsignedBigInteger('produk_id')->nullable()->after('IdRoster');
            });

            DB::statement(<<<'SQL'
                UPDATE produk_size
                SET produk_id = (
                    SELECT id FROM produk WHERE produk.IdRoster = produk_size.IdRoster LIMIT 1
                )
            SQL);
        }

        // Add produk_id to detail_transaksi
        if (! Schema::hasColumn('detail_transaksi', 'produk_id')) {
            Schema::table('detail_transaksi', function (Blueprint $table) {
                $table->unsignedBigInteger('produk_id')->nullable()->after('IdRoster');
            });

            DB::statement(<<<'SQL'
                UPDATE detail_transaksi
                SET produk_id = (
                    SELECT id FROM produk WHERE produk.IdRoster = detail_transaksi.IdRoster LIMIT 1
                )
            SQL);
        }

        // Add produk_id to detail_harga
        if (! Schema::hasColumn('detail_harga', 'produk_id')) {
            Schema::table('detail_harga', function (Blueprint $table) {
                $table->unsignedBigInteger('produk_id')->nullable()->after('id_roster');
            });

            DB::statement(<<<'SQL'
                UPDATE detail_harga
                SET produk_id = (
                    SELECT id FROM produk WHERE produk.IdRoster = detail_harga.id_roster LIMIT 1
                )
            SQL);
        }

        // Add produk_id to model_histories
        if (! Schema::hasColumn('model_histories', 'produk_id')) {
            Schema::table('model_histories', function (Blueprint $table) {
                $table->unsignedBigInteger('produk_id')->nullable()->after('id_roster');
            });

            DB::statement(<<<'SQL'
                UPDATE model_histories
                SET produk_id = (
                    SELECT id FROM produk WHERE produk.IdRoster = model_histories.id_roster LIMIT 1
                )
            SQL);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produk_size', function (Blueprint $table) {
            if (Schema::hasColumn('produk_size', 'produk_id')) {
                $table->dropColumn('produk_id');
            }
        });

        Schema::table('detail_transaksi', function (Blueprint $table) {
            if (Schema::hasColumn('detail_transaksi', 'produk_id')) {
                $table->dropColumn('produk_id');
            }
        });

        Schema::table('detail_harga', function (Blueprint $table) {
            if (Schema::hasColumn('detail_harga', 'produk_id')) {
                $table->dropColumn('produk_id');
            }
        });

        Schema::table('model_histories', function (Blueprint $table) {
            if (Schema::hasColumn('model_histories', 'produk_id')) {
                $table->dropColumn('produk_id');
            }
        });
    }
};
