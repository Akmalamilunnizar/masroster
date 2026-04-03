<?php

namespace Tests\Feature\Inventory;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StockTriggerTest extends TestCase
{
    public function test_stock_in_and_out_triggers_update_produk_stock(): void
    {
        $admin = $this->createAdminUser([
            'email' => 'stock-admin@example.test',
            'password' => 'password',
        ]);

        $product = $this->createMasrosterProduct([
            'IdRoster' => 'MASSTK1',
            'NamaProduk' => 'Roster Stock Trigger',
            'stock' => 10,
            'id_jenis' => 1,
            'id_tipe' => 1,
            'id_motif' => 1,
        ]);

        DB::table('barangmasuk')->insert([
            'IdMasuk' => 'BM0001',
            'username' => $admin->username,
            'tglMasuk' => now()->toDateString(),
        ]);

        DB::table('detail_barangmasuk')->insert([
            'IdMasuk' => 'BM0001',
            'IdRoster' => $product->IdRoster,
            'QtyMasuk' => 15,
            'HargaSatuan' => 1000,
            'SubTotal' => 15000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseHas('produk', [
            'IdRoster' => $product->IdRoster,
            'stock' => 25,
        ]);

        DB::table('barangkeluar')->insert([
            'IdKeluar' => 'BK0001',
            'username' => $admin->username,
            'tglKeluar' => now()->toDateString(),
        ]);

        DB::table('detail_barangkeluar')->insert([
            'IdKeluar' => 'BK0001',
            'IdRoster' => $product->IdRoster,
            'QtyKeluar' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseHas('produk', [
            'IdRoster' => $product->IdRoster,
            'stock' => 20,
        ]);
    }
}
