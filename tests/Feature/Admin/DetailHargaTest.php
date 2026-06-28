<?php

namespace Tests\Feature\Admin;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DetailHargaTest extends TestCase
{
    public function test_admin_can_store_and_update_detail_harga(): void
    {
        $admin = $this->createAdminUser([
            'email' => 'harga-admin@example.test',
            'password' => 'password',
        ]);
        $customer = $this->createCustomerUser([
            'email' => 'harga-customer@example.test',
            'password' => 'password',
        ]);
        $product = $this->createMasrosterProduct([
            'IdRoster' => 'MASH01',
            'NamaProduk' => 'Roster Harga',
            'stock' => 10,
            'id_jenis' => 1,
            'id_tipe' => 1,
            'id_motif' => 1,
        ]);

        $this->actingAs($admin)
            ->post('/admin/detail-harga', [
                'id_roster' => $product->IdRoster,
                'id_user' => $customer->id,
                'id_ukuran' => 1,
                'harga' => '63.000',
                'produk_id' => 999999,
            ])
            ->assertRedirect(route('detailharga.index'));

        $this->assertDatabaseHas('detail_harga', [
            'id_roster' => $product->IdRoster,
            'id_user' => $customer->id,
            'id_ukuran' => 1,
            'harga' => 63000,
            'produk_id' => null,
        ]);

        $this->actingAs($admin)
            ->put('/admin/detail-harga/'.$product->IdRoster.'/'.$customer->id.'/1', [
                'harga' => '70.000',
                'produk_id' => 777777,
            ])
            ->assertRedirect(route('detailharga.index'));

        $this->assertDatabaseHas('detail_harga', [
            'id_roster' => $product->IdRoster,
            'id_user' => $customer->id,
            'id_ukuran' => 1,
            'harga' => 70000,
        ]);
    }

    public function test_admin_can_filter_roster_prices_by_size_and_motif(): void
    {
        $admin = $this->createAdminUser([
            'email' => 'harga-filter-admin@example.test',
            'password' => 'password',
        ]);

        $product = $this->createMasrosterProduct([
            'IdRoster' => 'MASH02',
            'NamaProduk' => 'Roster Filter',
            'stock' => 10,
            'id_jenis' => 1,
            'id_tipe' => 1,
            'id_motif' => 1,
        ]);
        DB::table('produk_size')->insert([
            'IdRoster' => $product->IdRoster,
            'id_ukuran' => 1,
            'harga' => 55000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('produk_size')->insert([
            'IdRoster' => $product->IdRoster,
            'id_ukuran' => 2,
            'harga' => 65000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin/detail-harga/roster-prices?jenis_id=1&size_id=1&motif_id=1');

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonPath('0.IdRoster', $product->IdRoster);
    }
}
