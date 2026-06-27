<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add a nullable `id` column to the produk table (will become the new primary key later).
     * Populate it with deterministic sequential values based on IdRoster ordering.
     */
    public function up(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->nullable()->first();
        });

        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement(<<<'SQL'
                UPDATE produk
                SET id = (
                    SELECT COUNT(*)
                    FROM (
                        SELECT @row := @row + 1 as row_num, IdRoster
                        FROM produk, (SELECT @row := 0) as init
                        ORDER BY IdRoster ASC
                    ) as subquery
                    WHERE subquery.IdRoster <= produk.IdRoster
                )
            SQL);
        } elseif ($driver === 'sqlite') {
            DB::statement(<<<'SQL'
                UPDATE produk
                SET id = (
                    SELECT ROW_NUMBER() OVER (ORDER BY IdRoster ASC)
                    FROM produk p
                    WHERE p.IdRoster = produk.IdRoster
                )
            SQL);
        } elseif ($driver === 'pgsql') {
            DB::statement(<<<'SQL'
                UPDATE produk
                SET id = row_number() OVER (ORDER BY "IdRoster" ASC)
            SQL);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->dropColumn('id');
        });
    }
};
