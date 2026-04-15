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
        Schema::create('model_histories', function (Blueprint $table) {
            $table->id();
            $table->string('id_roster', 13);
            $table->string('model_type', 20);
            $table->string('version_id', 60);
            $table->float('wmape_score')->nullable();
            $table->float('mae_score')->nullable();
            $table->float('rmse_score')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['id_roster', 'model_type', 'is_active'], 'model_histories_roster_type_active_idx');
            $table->index(['model_type', 'created_at'], 'model_histories_type_created_idx');
            $table->index('version_id', 'model_histories_version_idx');
            $table->unique(['id_roster', 'model_type', 'version_id'], 'model_histories_unique_version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_histories');
    }
};
