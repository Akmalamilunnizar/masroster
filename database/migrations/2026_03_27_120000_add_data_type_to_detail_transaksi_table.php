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
        if (!Schema::hasColumn('detail_transaksi', 'data_type')) {
            Schema::table('detail_transaksi', function (Blueprint $table) {
                $table->string('data_type', 20)
                    ->default('Eceran')
                    ->after('QtyProduk')
                    ->comment('Auto classified from QtyProduk (>100 Borongan, else Eceran)');
                $table->index('data_type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('detail_transaksi', 'data_type')) {
            Schema::table('detail_transaksi', function (Blueprint $table) {
                $table->dropIndex(['data_type']);
                $table->dropColumn('data_type');
            });
        }
    }
};
