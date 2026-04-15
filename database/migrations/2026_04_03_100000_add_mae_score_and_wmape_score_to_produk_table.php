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
        if (!Schema::hasColumn('produk', 'mae_score')) {
            Schema::table('produk', function (Blueprint $table) {
                if (Schema::hasColumn('produk', 'forecasted_demand')) {
                    $table->float('mae_score')->nullable()->after('forecasted_demand');
                    return;
                }

                $table->float('mae_score')->nullable();
            });
        }

        if (!Schema::hasColumn('produk', 'wmape_score')) {
            Schema::table('produk', function (Blueprint $table) {
                if (Schema::hasColumn('produk', 'mae_score')) {
                    $table->float('wmape_score')->nullable()->after('mae_score');
                    return;
                }

                $table->float('wmape_score')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('produk', 'wmape_score')) {
            Schema::table('produk', function (Blueprint $table) {
                $table->dropColumn('wmape_score');
            });
        }

        if (Schema::hasColumn('produk', 'mae_score')) {
            Schema::table('produk', function (Blueprint $table) {
                $table->dropColumn('mae_score');
            });
        }
    }
};
