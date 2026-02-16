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
        Schema::table('produk', function (Blueprint $table) {
            // Forecast data from AI models (LSTM/Prophet)
            $table->float('forecasted_demand', 8, 2)->nullable()->comment('Next month predicted demand from AI');
            $table->string('forecast_model', 20)->nullable()->comment('Model used: lstm, prophet, or sma');
            
            // Safety stock (default batch size for resellers)
            $table->integer('safety_stock')->default(70)->comment('Minimum stock threshold (1 batch = 70 pcs)');
            
            // Calculated status based on forecast
            $table->enum('forecast_status', ['critical', 'low', 'safe', 'overstock'])->default('safe');
            
            // Timestamp of last forecast calculation
            $table->timestamp('last_forecast_at')->nullable()->comment('When forecast was last calculated');
            
            // Index for faster queries on status
            $table->index('forecast_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->dropColumn([
                'forecasted_demand',
                'forecast_model',
                'safety_stock',
                'forecast_status',
                'last_forecast_at'
            ]);
        });
    }
};
