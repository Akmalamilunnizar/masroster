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
        // Update detail_harga table: change id_supplier to id_user
        Schema::table('detail_harga', function (Blueprint $table) {
            // Drop the old id_supplier column
            $table->dropColumn('id_supplier');
        });

        Schema::table('detail_harga', function (Blueprint $table) {
            // Add new id_user column
            $table->string('id_user', 6)->after('id_roster');
        });

        // Update transaksi table: remove address_id
        Schema::table('transaksi', function (Blueprint $table) {
            // Drop the address_id column (no foreign key constraint exists)
            $table->dropColumn('address_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert detail_harga table changes
        Schema::table('detail_harga', function (Blueprint $table) {
            $table->dropColumn('id_user');
        });

        Schema::table('detail_harga', function (Blueprint $table) {
            $table->string('id_supplier', 6)->after('id_roster');
        });

        // Revert transaksi table changes
        Schema::table('transaksi', function (Blueprint $table) {
            $table->unsignedBigInteger('address_id')->after('id');
        });
    }
};
