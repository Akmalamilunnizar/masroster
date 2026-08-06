<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class SupplierManagementTest extends TestCase
{
    public function test_guest_is_redirected_from_supplier_management(): void
    {
        $this->get(route('allsuppliers'))->assertRedirect(route('login'));
    }

    public function test_admin_can_search_suppliers_by_name(): void
    {
        $admin = $this->createAdminUser([
            'email' => 'supplier-admin@example.test',
        ]);

        $supplier = $this->createCustomerUser([
            'f_name' => 'Supplier Alpha',
            'email' => 'supplier-alpha@example.test',
            'username' => 'supplier-alpha',
            'nomor_telepon' => '081234567890',
        ]);

        $response = $this->actingAs($admin)->get(route('searchsupplier', ['search' => 'Alpha']));

        $response->assertOk();
        $response->assertSee('Supplier Alpha');
        $this->assertSame('User', $supplier->fresh()->user);
    }
}