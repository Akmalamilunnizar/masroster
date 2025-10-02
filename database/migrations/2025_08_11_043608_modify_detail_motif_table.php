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
        // Drop the existing detail_motif table
        Schema::dropIfExists('detail_motif');

        // Create new detail_motif table with tipe_roster to motif_roster relationship
        Schema::create('detail_motif', function (Blueprint $table) {
            $table->integer('id_tipe');
            $table->integer('id_motif');
            
            // Add foreign key constraints
            $table->foreign('id_tipe')->references('IdTipe')->on('tipe_roster');
            $table->foreign('id_motif')->references('IdMotif')->on('motif_roster');
            
            // Add unique constraint to prevent duplicate connections
            $table->unique(['id_tipe', 'id_motif']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the new detail_motif table
        Schema::dropIfExists('detail_motif');

        // Recreate the original detail_motif table
        Schema::create('detail_motif', function (Blueprint $table) {
            $table->integer('id_jenis');
            $table->integer('id_motif');
            
            // Add foreign key constraints for rollback
            $table->foreign('id_jenis')->references('IdJenisBarang')->on('jenisbarang');
            $table->foreign('id_motif')->references('IdMotif')->on('motif_roster');
            
            // Add unique constraint
            $table->unique(['id_jenis', 'id_motif']);
        });
    }
};
