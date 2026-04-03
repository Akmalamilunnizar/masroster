<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('detail_transaksi') && Schema::hasColumn('detail_transaksi', 'CustomUkuran')) {
            Schema::table('detail_transaksi', function (Blueprint $table): void {
                $table->dropColumn('CustomUkuran');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('detail_transaksi') && !Schema::hasColumn('detail_transaksi', 'CustomUkuran')) {
            Schema::table('detail_transaksi', function (Blueprint $table): void {
                $table->string('CustomUkuran', 100)->nullable()->after('id_ukuran');
            });
        }
    }
};
