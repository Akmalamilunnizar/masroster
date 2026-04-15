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
        if (!Schema::hasColumn('produk', 'rmse_score')) {
            Schema::table('produk', function (Blueprint $table) {
                if (Schema::hasColumn('produk', 'mae_score')) {
                    $table->float('rmse_score')->nullable()->after('mae_score');
                    return;
                }

                $table->float('rmse_score')->nullable();
            });
        }

        if (!Schema::hasColumn('produk', 'active_lstm_version')) {
            Schema::table('produk', function (Blueprint $table) {
                if (Schema::hasColumn('produk', 'forecast_model')) {
                    $table->string('active_lstm_version', 60)->nullable()->after('forecast_model');
                    return;
                }

                $table->string('active_lstm_version', 60)->nullable();
            });
        }

        if (!Schema::hasColumn('produk', 'active_prophet_version')) {
            Schema::table('produk', function (Blueprint $table) {
                if (Schema::hasColumn('produk', 'active_lstm_version')) {
                    $table->string('active_prophet_version', 60)->nullable()->after('active_lstm_version');
                    return;
                }

                $table->string('active_prophet_version', 60)->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('produk', 'active_prophet_version')) {
            Schema::table('produk', function (Blueprint $table) {
                $table->dropColumn('active_prophet_version');
            });
        }

        if (Schema::hasColumn('produk', 'active_lstm_version')) {
            Schema::table('produk', function (Blueprint $table) {
                $table->dropColumn('active_lstm_version');
            });
        }

        if (Schema::hasColumn('produk', 'rmse_score')) {
            Schema::table('produk', function (Blueprint $table) {
                $table->dropColumn('rmse_score');
            });
        }
    }
};
