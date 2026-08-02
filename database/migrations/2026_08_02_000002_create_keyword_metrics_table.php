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
        Schema::create('keyword_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keyword_research_log_id')
                ->constrained('keyword_research_logs')
                ->onDelete('cascade');
            $table->string('keyword');
            $table->integer('search_volume')->default(0);
            $table->decimal('cpc', 8, 2)->nullable();
            $table->string('competition')->nullable();
            $table->json('monthly_trend')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keyword_metrics');
    }
};
