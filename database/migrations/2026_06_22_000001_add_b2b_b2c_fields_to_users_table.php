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
        Schema::table('users', function (Blueprint $table): void {
            if (!Schema::hasColumn('users', 'tipe_user')) {
                $table->enum('tipe_user', ['end_customer', 'retailer'])
                    ->default('end_customer');
            }

            if (!Schema::hasColumn('users', 'status_verifikasi')) {
                $table->enum('status_verifikasi', ['pending', 'approved', 'rejected'])
                    ->default('pending');
            }

            if (!Schema::hasColumn('users', 'foto_toko')) {
                $table->string('foto_toko')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'foto_toko')) {
                $table->dropColumn('foto_toko');
            }

            if (Schema::hasColumn('users', 'status_verifikasi')) {
                $table->dropColumn('status_verifikasi');
            }

            if (Schema::hasColumn('users', 'tipe_user')) {
                $table->dropColumn('tipe_user');
            }
        });
    }
};
