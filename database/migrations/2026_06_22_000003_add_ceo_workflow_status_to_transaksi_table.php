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
        Schema::table('transaksi', function (Blueprint $table): void {
            if (!Schema::hasColumn('transaksi', 'workflow_status')) {
                $table->string('workflow_status', 30)
                    ->default('Draft')
                    ->comment('CEO approval and payment workflow state: Draft, Menunggu Pembayaran, Paid');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table): void {
            if (Schema::hasColumn('transaksi', 'workflow_status')) {
                $table->dropColumn('workflow_status');
            }
        });
    }
};