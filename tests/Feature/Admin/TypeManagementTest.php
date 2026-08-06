<?php

namespace Tests\Feature\Admin;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TypeManagementTest extends TestCase
{
    public function test_admin_can_search_type_items_by_name(): void
    {
        $admin = $this->createAdminUser([
            'email' => 'type-search-admin@example.test',
        ]);

        DB::table('jenisbarang')->insert([
            ['IdJenisBarang' => 10, 'JenisBarang' => 'Roster Premium'],
            ['IdJenisBarang' => 11, 'JenisBarang' => 'Bovenlis Standard'],
        ]);

        $response = $this->actingAs($admin)->get('/admin/all-type/search?search=Premium');

        $response->assertOk();
        $response->assertSee('Roster Premium');
        $response->assertDontSee('Bovenlis Standard');
    }

    public function test_admin_can_update_type_item_using_validated_original_id(): void
    {
        $admin = $this->createAdminUser([
            'email' => 'type-update-admin@example.test',
        ]);

        DB::table('jenisbarang')->insert([
            ['IdJenisBarang' => 20, 'JenisBarang' => 'Roster Lama'],
        ]);

        $response = $this->actingAs($admin)->post('/admin/update-type', [
            'original_id' => 20,
            'JenisBarang' => 'Roster Baru',
            'IdJenisBarang' => 999,
        ]);

        $response->assertRedirect(route('alltype'));

        $this->assertDatabaseHas('jenisbarang', [
            'IdJenisBarang' => 20,
            'JenisBarang' => 'Roster Baru',
        ]);

        $this->assertDatabaseMissing('jenisbarang', [
            'IdJenisBarang' => 999,
        ]);
    }
}