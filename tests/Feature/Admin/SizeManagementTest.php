<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class SizeManagementTest extends TestCase
{
    public function test_admin_can_create_update_and_delete_size_without_mass_assignment(): void
    {
        $admin = $this->createAdminUser([
            'email' => 'size-admin@example.test',
        ]);

        $createResponse = $this->actingAs($admin)->post('/admin/store-ukuran', [
            'nama' => 'XL',
            'panjang' => 30,
            'lebar' => 40,
            'id_satuan' => 999,
        ]);

        $createResponse->assertRedirect(route('allukuran'));

        $size = \App\Models\Size::where('nama', 'XL')->firstOrFail();
        $this->assertSame(30, $size->panjang);
        $this->assertSame(40, $size->lebar);
        $this->assertObjectNotHasProperty('id_satuan', $size);

        $this->actingAs($admin)->put('/admin/update-ukuran/'.$size->id_ukuran, [
            'nama' => 'XXL',
            'panjang' => 35,
            'lebar' => 45,
            'id_satuan' => 777,
        ])->assertRedirect(route('allukuran'));

        $this->assertDatabaseHas('size', [
            'id_ukuran' => $size->id_ukuran,
            'nama' => 'XXL',
            'panjang' => 35,
            'lebar' => 45,
        ]);

        $this->actingAs($admin)
            ->delete('/admin/delete-ukuran/'.$size->id_ukuran)
            ->assertRedirect(route('allukuran'));

        $this->assertDatabaseMissing('size', [
            'id_ukuran' => $size->id_ukuran,
        ]);
    }
}
