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
        Schema::table('detail_harga', function (Blueprint $table) {
            // Add id_ukuran column after id_user
            $table->integer('id_ukuran')->after('id_user');
            
            // Add foreign key constraint
            $table->foreign('id_ukuran')->references('id_ukuran')->on('size')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_harga', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['id_ukuran']);
            
            // Drop the column
            $table->dropColumn('id_ukuran');
        });
    }
};
