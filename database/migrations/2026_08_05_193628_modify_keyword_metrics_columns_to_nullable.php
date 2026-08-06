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
        Schema::table('keyword_metrics', function (Blueprint $table) {
            $table->integer('search_volume')->nullable()->change();
            $table->decimal('cpc', 8, 2)->nullable()->change();
            $table->string('competition')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('keyword_metrics', function (Blueprint $table) {
            $table->integer('search_volume')->nullable(false)->default(0)->change();
            $table->decimal('cpc', 8, 2)->nullable()->change();
            $table->string('competition')->nullable()->change();
        });
    }
};
