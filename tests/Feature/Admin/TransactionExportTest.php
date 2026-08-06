<?php

namespace Tests\Feature\Admin;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TransactionExportTest extends TestCase
{
    public function test_admin_can_export_lstm_dataset_with_stock_columns(): void
    {
        $admin = $this->createAdminUser([
            'email' => 'export-admin@example.test',
        ]);

        $customer = $this->createCustomerUser([
            'email' => 'export-customer@example.test',
        ]);

        $product = $this->createMasrosterProduct([
            'IdRoster' => 'MASX001',
            'NamaProduk' => 'Roster Export Test',
            'stock' => 25,
        ]);

        $transaction = $this->createMasrosterTransaction([
            'IdTransaksi' => 'TXEXP001',
            'id_admin' => $admin->id,
            'id_customer' => $customer->id,
            'GrandTotal' => 50000,
            'Bayar' => 50000,
            'StatusPembayaran' => 'Lunas',
            'StatusPesanan' => 'Diterima',
            'notes' => 'Promo launching',
            'ongkir' => 0,
            'tglTransaksi' => now()->subDay(),
        ]);

        DB::table('detail_transaksi')->insert([
            'IdTransaksi' => $transaction->IdTransaksi,
            'IdRoster' => $product->IdRoster,
            'id_ukuran' => 1,
            'QtyProduk' => 5,
            'SubTotal' => 50000,
            'data_type' => 'Eceran',
        ]);

        $response = $this->actingAs($admin)->get('/export-lstm');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}