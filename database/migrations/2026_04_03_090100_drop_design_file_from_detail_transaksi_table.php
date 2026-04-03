<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('detail_transaksi') && Schema::hasColumn('detail_transaksi', 'design_file')) {
            Schema::table('detail_transaksi', function (Blueprint $table): void {
                $table->dropColumn('design_file');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('detail_transaksi') && !Schema::hasColumn('detail_transaksi', 'design_file')) {
            Schema::table('detail_transaksi', function (Blueprint $table): void {
                $table->string('design_file')->nullable()->after('SubTotal');
            });
        }
    }
};
