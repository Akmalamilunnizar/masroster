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
        if (Schema::hasColumn('produk', 'active_lstm_version') || Schema::hasColumn('produk', 'active_prophet_version')) {
            Schema::table('produk', function (Blueprint $table) {
                $drops = [];

                if (Schema::hasColumn('produk', 'active_lstm_version')) {
                    $drops[] = 'active_lstm_version';
                }

                if (Schema::hasColumn('produk', 'active_prophet_version')) {
                    $drops[] = 'active_prophet_version';
                }

                if (!empty($drops)) {
                    $table->dropColumn($drops);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            if (!Schema::hasColumn('produk', 'active_lstm_version')) {
                $table->string('active_lstm_version', 60)->nullable()->after('forecast_model');
            }

            if (!Schema::hasColumn('produk', 'active_prophet_version')) {
                $table->string('active_prophet_version', 60)->nullable()->after('active_lstm_version');
            }
        });
    }
};
