<?php

namespace Tests\Feature\Order;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrderDetailAccessTest extends TestCase
{
    public function test_customer_can_view_own_order_detail(): void
    {
        $customer = $this->createCustomerUser([
            'email' => 'order-owner@example.test',
        ]);

        $product = $this->createMasrosterProduct([
            'IdRoster' => 'MAS901',
            'NamaProduk' => 'Roster Detail Access',
        ]);

        $transaction = $this->createMasrosterTransaction([
            'IdTransaksi' => 'TX200001',
            'id_customer' => $customer->id,
            'GrandTotal' => 63000,
            'StatusPesanan' => 'Menunggu Konfirmasi',
        ]);

        DB::table('detail_transaksi')->insert([
            'IdTransaksi' => $transaction->IdTransaksi,
            'IdRoster' => $product->IdRoster,
            'produk_id' => $product->id,
            'id_ukuran' => 1,
            'QtyProduk' => 1,
            'SubTotal' => 63000,
            'data_type' => 'Eceran',
        ]);

        $response = $this->actingAs($customer)->get('/pesanan/'.$transaction->IdTransaksi);

        $response->assertOk()
            ->assertSee('Detail Pesanan #TX200001')
            ->assertSee('Roster Detail Access');
    }

    public function test_customer_cannot_view_other_customers_order_detail(): void
    {
        $owner = $this->createCustomerUser([
            'email' => 'order-real-owner@example.test',
        ]);

        $intruder = $this->createCustomerUser([
            'email' => 'order-intruder@example.test',
        ]);

        $product = $this->createMasrosterProduct([
            'IdRoster' => 'MAS902',
            'NamaProduk' => 'Roster Hidden Detail',
        ]);

        $transaction = $this->createMasrosterTransaction([
            'IdTransaksi' => 'TX200002',
            'id_customer' => $owner->id,
            'GrandTotal' => 63000,
            'StatusPesanan' => 'Menunggu Konfirmasi',
        ]);

        DB::table('detail_transaksi')->insert([
            'IdTransaksi' => $transaction->IdTransaksi,
            'IdRoster' => $product->IdRoster,
            'produk_id' => $product->id,
            'id_ukuran' => 1,
            'QtyProduk' => 1,
            'SubTotal' => 63000,
            'data_type' => 'Eceran',
        ]);

        $this->actingAs($intruder)
            ->get('/pesanan/'.$transaction->IdTransaksi)
            ->assertNotFound();
    }

    public function test_admin_can_view_any_order_detail(): void
    {
        $admin = $this->createAdminUser([
            'email' => 'order-admin@example.test',
        ]);

        $customer = $this->createCustomerUser([
            'email' => 'order-admin-owned@example.test',
        ]);

        $product = $this->createMasrosterProduct([
            'IdRoster' => 'MAS903',
            'NamaProduk' => 'Roster Admin Detail',
        ]);

        $transaction = $this->createMasrosterTransaction([
            'IdTransaksi' => 'TX200003',
            'id_customer' => $customer->id,
            'GrandTotal' => 63000,
            'StatusPesanan' => 'Menunggu Konfirmasi',
        ]);

        DB::table('detail_transaksi')->insert([
            'IdTransaksi' => $transaction->IdTransaksi,
            'IdRoster' => $product->IdRoster,
            'produk_id' => $product->id,
            'id_ukuran' => 1,
            'QtyProduk' => 1,
            'SubTotal' => 63000,
            'data_type' => 'Eceran',
        ]);

        $this->actingAs($admin)
            ->get('/pesanan/'.$transaction->IdTransaksi)
            ->assertOk()
            ->assertSee('Detail Pesanan #TX200003');
    }
}
