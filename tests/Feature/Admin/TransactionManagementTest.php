<?php

namespace Tests\Feature\Admin;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TransactionManagementTest extends TestCase
{
    public function test_admin_can_accept_and_reject_transactions(): void
    {
        $admin = $this->createAdminUser([
            'email' => 'trx-admin@example.test',
        ]);

        $customer = $this->createCustomerUser([
            'email' => 'trx-customer@example.test',
        ]);

        $acceptTransaction = $this->createMasrosterTransaction([
            'IdTransaksi' => 'TX100001',
            'id_admin' => $admin->id,
            'id_customer' => $customer->id,
            'StatusPesanan' => 'Menunggu Konfirmasi',
        ]);

        $rejectTransaction = $this->createMasrosterTransaction([
            'IdTransaksi' => 'TX100002',
            'id_admin' => $admin->id,
            'id_customer' => $customer->id,
            'StatusPesanan' => 'Menunggu Konfirmasi',
        ]);

        $this->actingAs($admin)
            ->post('/admin/all-transaksi/'.$acceptTransaction->IdTransaksi.'/terima')
            ->assertRedirect(route('alltransaksi'));

        $this->assertDatabaseHas('transaksi', [
            'IdTransaksi' => $acceptTransaction->IdTransaksi,
            'StatusPesanan' => 'Diterima',
        ]);

        $this->actingAs($admin)
            ->post('/admin/all-transaksi/'.$rejectTransaction->IdTransaksi.'/tolak')
            ->assertRedirect(route('alltransaksi'));

        $this->assertDatabaseHas('transaksi', [
            'IdTransaksi' => $rejectTransaction->IdTransaksi,
            'StatusPesanan' => 'Ditolak',
        ]);
    }

    public function test_non_admin_cannot_accept_transaction(): void
    {
        $customer = $this->createCustomerUser([
            'email' => 'trx-non-admin@example.test',
        ]);

        $transaction = $this->createMasrosterTransaction([
            'IdTransaksi' => 'TX100003',
            'id_customer' => $customer->id,
            'StatusPesanan' => 'Menunggu Konfirmasi',
        ]);

        $this->actingAs($customer)
            ->post('/admin/all-transaksi/'.$transaction->IdTransaksi.'/terima')
            ->assertForbidden();

        $this->assertDatabaseHas('transaksi', [
            'IdTransaksi' => $transaction->IdTransaksi,
            'StatusPesanan' => 'Menunggu Konfirmasi',
        ]);
    }

    public function test_admin_can_update_invoice_and_view_print_invoice(): void
    {
        $admin = $this->createAdminUser([
            'email' => 'invoice-admin@example.test',
        ]);

        $customer = $this->createCustomerUser([
            'email' => 'invoice-customer@example.test',
        ]);

        $address = $this->createMasrosterAddress($customer, [
            'is_default' => true,
        ]);

        $product = $this->createMasrosterProduct([
            'IdRoster' => 'MASINV01',
            'NamaProduk' => 'Roster Invoice',
        ]);

        $transaction = $this->createMasrosterTransaction([
            'IdTransaksi' => 'TX100004',
            'id_admin' => $admin->id,
            'id_customer' => $customer->id,
            'address_id' => $address->id,
            'GrandTotal' => 126000,
            'Bayar' => 126000,
            'StatusPembayaran' => 'Lunas',
            'StatusPesanan' => 'Diterima',
        ]);

        DB::table('detail_transaksi')->insert([
            'IdTransaksi' => $transaction->IdTransaksi,
            'IdRoster' => $product->IdRoster,
            'id_ukuran' => 1,
            'QtyProduk' => 2,
            'SubTotal' => 126000,
            'data_type' => 'Eceran',
        ]);

        $this->actingAs($admin)
            ->post('/admin/all-transaksi/'.$transaction->IdTransaksi.'/update-invoice', [
                'invoice_number' => 'INV-2026-0001',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('transaksi', [
            'IdTransaksi' => $transaction->IdTransaksi,
            'invoice_number' => 'INV-2026-0001',
        ]);

        $this->actingAs($admin)
            ->get('/admin/print-invoice/'.$transaction->IdTransaksi)
            ->assertOk()
            ->assertSee($transaction->IdTransaksi);
    }

    public function test_admin_can_store_manual_transaction_with_extra_payload_ignored(): void
    {
        $admin = $this->createAdminUser([
            'email' => 'trx-store-admin@example.test',
        ]);

        $customer = $this->createCustomerUser([
            'email' => 'trx-store-customer@example.test',
        ]);

        $address = $this->createMasrosterAddress($customer, [
            'is_default' => true,
        ]);

        $product = $this->createMasrosterProduct([
            'IdRoster' => 'TXS001',
            'NamaProduk' => 'Roster Store Test',
        ]);

        $this->actingAs($admin)
            ->post('/admin/transaksi', [
                'IdTransaksi' => 'TX200010',
                'id_customer' => $customer->id,
                'address_id' => $address->id,
                'Bayar' => '141.000',
                'GrandTotal' => '141.000',
                'StatusPembayaran' => 'Transfer',
                'StatusPesanan' => 'MENUNGGU KONFIRMASI',
                'shipping_method' => 'Online',
                'delivery_method' => 'Delivery',
                'shipping_type' => 'Ongkir',
                'ongkir' => '15.000',
                'notes' => 'Manual test order',
                'workflow_status' => 'Paid',
                'id_admin' => 999,
                'products' => [
                    [
                        'product_id' => $product->IdRoster,
                        'size_id' => 1,
                        'qty' => 2,
                        'price' => 63000,
                    ],
                ],
            ])
            ->assertRedirect(route('alltransaksi'));

        $this->assertDatabaseHas('transaksi', [
            'IdTransaksi' => 'TX200010',
            'id_admin' => 1,
            'id_customer' => $customer->id,
            'GrandTotal' => 141000,
            'Bayar' => 141000,
            'workflow_status' => 'Draft',
            'StatusPesanan' => 'MENUNGGU KONFIRMASI',
        ]);

        $this->assertDatabaseHas('detail_transaksi', [
            'IdTransaksi' => 'TX200010',
            'IdRoster' => $product->IdRoster,
            'id_ukuran' => 1,
            'QtyProduk' => 2,
            'SubTotal' => 126000,
            'data_type' => 'Eceran',
        ]);
    }
}
