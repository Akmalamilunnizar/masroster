<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Drop the legacy inventory (barangmasuk, barangkeluar, detail_barangmasuk, detail_barangkeluar)
     * tables and their associated triggers (stokMasuk, stokKeluar).
     *
     * IMPORTANT: These tables and triggers are no longer used by the application.
     * A backup is recommended before running this migration.
     */
    public function up(): void
    {
        // Drop triggers if they exist
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('DROP TRIGGER IF EXISTS stokMasuk');
            DB::statement('DROP TRIGGER IF EXISTS stokKeluar');
        } elseif ($driver === 'sqlite') {
            DB::unprepared('DROP TRIGGER IF EXISTS stokMasuk');
            DB::unprepared('DROP TRIGGER IF EXISTS stokKeluar');
        }

        // Drop the detail tables first (they reference the main tables via foreign keys)
        Schema::dropIfExists('detail_barangmasuk');
        Schema::dropIfExists('detail_barangkeluar');

        // Drop the main tables
        Schema::dropIfExists('barangmasuk');
        Schema::dropIfExists('barangkeluar');
    }

    /**
     * Reverse the migrations.
     *
     * Recreate the legacy tables and triggers. NOTE: No data is restored.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            // Recreate barangmasuk
            Schema::create('barangmasuk', function (Blueprint $table) {
                $table->string('IdMasuk', 6)->primary();
                $table->string('username', 20);
                $table->date('tglMasuk')->nullable();
            });

            // Recreate barangkeluar
            Schema::create('barangkeluar', function (Blueprint $table) {
                $table->string('IdKeluar', 6)->primary();
                $table->string('username', 20)->nullable();
                $table->date('tglKeluar')->nullable();
            });

            // Recreate detail_barangmasuk
            Schema::create('detail_barangmasuk', function (Blueprint $table) {
                $table->string('IdMasuk', 6)->nullable();
                $table->string('IdRoster', 13)->nullable();
                $table->integer('QtyMasuk')->nullable();
                $table->integer('HargaSatuan');
                $table->integer('SubTotal');
                $table->timestamps();
            });

            // Recreate detail_barangkeluar
            Schema::create('detail_barangkeluar', function (Blueprint $table) {
                $table->string('IdKeluar', 6)->nullable();
                $table->string('IdRoster', 13)->nullable();
                $table->integer('QtyKeluar')->nullable();
                $table->timestamps();
            });

            // Recreate triggers
            DB::statement(<<<SQL
                CREATE TRIGGER stokMasuk AFTER INSERT ON detail_barangmasuk
                FOR EACH ROW BEGIN
                    UPDATE produk SET stock = stock + NEW.QtyMasuk WHERE IdRoster = NEW.IdRoster;
                END;
            SQL);

            DB::statement(<<<SQL
                CREATE TRIGGER stokKeluar AFTER INSERT ON detail_barangkeluar
                FOR EACH ROW BEGIN
                    UPDATE produk SET stock = stock - NEW.QtyKeluar WHERE IdRoster = NEW.IdRoster;
                END;
            SQL);
        } elseif ($driver === 'sqlite') {
            Schema::create('barangmasuk', function (Blueprint $table) {
                $table->string('IdMasuk', 6)->primary();
                $table->string('username', 20);
                $table->date('tglMasuk')->nullable();
            });

            Schema::create('barangkeluar', function (Blueprint $table) {
                $table->string('IdKeluar', 6)->primary();
                $table->string('username', 20)->nullable();
                $table->date('tglKeluar')->nullable();
            });

            Schema::create('detail_barangmasuk', function (Blueprint $table) {
                $table->string('IdMasuk', 6)->nullable();
                $table->string('IdRoster', 13)->nullable();
                $table->integer('QtyMasuk')->nullable();
                $table->integer('HargaSatuan');
                $table->integer('SubTotal');
                $table->timestamps();
            });

            Schema::create('detail_barangkeluar', function (Blueprint $table) {
                $table->string('IdKeluar', 6)->nullable();
                $table->string('IdRoster', 13)->nullable();
                $table->integer('QtyKeluar')->nullable();
                $table->timestamps();
            });

            DB::unprepared(<<<SQL
                CREATE TRIGGER stokMasuk AFTER INSERT ON detail_barangmasuk BEGIN
                    UPDATE produk SET stock = stock + NEW.QtyMasuk WHERE IdRoster = NEW.IdRoster;
                END;
            SQL);

            DB::unprepared(<<<SQL
                CREATE TRIGGER stokKeluar AFTER INSERT ON detail_barangkeluar BEGIN
                    UPDATE produk SET stock = stock - NEW.QtyKeluar WHERE IdRoster = NEW.IdRoster;
                END;
            SQL);
        }
    }
};
