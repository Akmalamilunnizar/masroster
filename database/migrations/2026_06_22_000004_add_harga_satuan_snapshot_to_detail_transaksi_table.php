<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('detail_transaksi', function (Blueprint $table): void {
            if (!Schema::hasColumn('detail_transaksi', 'harga_satuan')) {
                $table->integer('harga_satuan')
                    ->nullable()
                    ->after('id_ukuran')
                    ->comment('Immutable unit price snapshot at the time of checkout or approval');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_transaksi', function (Blueprint $table): void {
            if (Schema::hasColumn('detail_transaksi', 'harga_satuan')) {
                $table->dropColumn('harga_satuan');
            }
        });
    }
};