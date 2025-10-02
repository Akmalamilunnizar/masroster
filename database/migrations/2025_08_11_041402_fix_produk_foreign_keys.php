<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the incorrect foreign key constraint
        Schema::table('produk', function (Blueprint $table) {
            $table->dropForeign('produk_ibfk_1');
        });

        // Add the correct foreign key constraints
        Schema::table('produk', function (Blueprint $table) {
            // Fix id_jenis to reference jenisbarang.IdJenisBarang
            $table->foreign('id_jenis')->references('IdJenisBarang')->on('jenisbarang');
            
            // Add foreign key for id_tipe to reference tipe_roster.IdTipe
            $table->foreign('id_tipe')->references('IdTipe')->on('tipe_roster');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the correct foreign key constraints
        Schema::table('produk', function (Blueprint $table) {
            $table->dropForeign(['id_jenis']);
            $table->dropForeign(['id_tipe']);
        });

        // Restore the incorrect constraint (for rollback)
        Schema::table('produk', function (Blueprint $table) {
            $table->foreign('id_jenis')->references('IdTipe')->on('tipe_roster');
        });
    }
};
