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
}