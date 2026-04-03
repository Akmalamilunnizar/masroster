<?php

namespace Tests\Feature\Admin;

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
            ])
            ->assertRedirect(route('detailharga.index'));

        $this->assertDatabaseHas('detail_harga', [
            'id_roster' => $product->IdRoster,
            'id_user' => $customer->id,
            'id_ukuran' => 1,
            'harga' => 63000,
        ]);

        $this->actingAs($admin)
            ->put('/admin/detail-harga/' . $product->IdRoster . '/' . $customer->id . '/1', [
                'harga' => '70.000',
            ])
            ->assertRedirect(route('detailharga.index'));

        $this->assertDatabaseHas('detail_harga', [
            'id_roster' => $product->IdRoster,
            'id_user' => $customer->id,
            'id_ukuran' => 1,
            'harga' => 70000,
        ]);
    }
}