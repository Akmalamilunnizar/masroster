<?php

namespace Tests\Feature\Forecast;

use App\Http\Controllers\Api\V1\TransaksiController;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExportDatasetTest extends TestCase
{
    public function test_export_helpers_use_authoritative_stock_and_promo_signals(): void
    {
        $admin = $this->createAdminUser([
            'email' => 'export-lstm-admin@example.test',
            'password' => 'password',
        ]);

        $product = $this->createMasrosterProduct([
            'IdRoster' => 'MASEXP1',
            'NamaProduk' => 'Roster Export LSTM',
            'stock' => 7,
        ]);

        $transaction = $this->createMasrosterTransaction([
            'IdTransaksi' => 'TXEXP001',
            'id_admin' => $admin->id,
            'tglTransaksi' => Carbon::parse('2026-06-28'),
            'StatusPembayaran' => 'Lunas',
            'StatusPesanan' => 'Diterima',
        ]);

        DB::table('detail_transaksi')->insert([
            'IdTransaksi' => $transaction->IdTransaksi,
            'IdRoster' => $product->IdRoster,
            'id_ukuran' => 1,
            'QtyProduk' => 3,
            'SubTotal' => 210000,
            'data_type' => 'Eceran',
        ]);

        $controller = new TransaksiController();
        $reflection = new \ReflectionClass($controller);

        $getStokAwal = $reflection->getMethod('getStokAwal');
        $getStokAwal->setAccessible(true);

        $getStokAkhir = $reflection->getMethod('getStokAkhir');
        $getStokAkhir->setAccessible(true);

        $isHariLibur = $reflection->getMethod('isHariLibur');
        $isHariLibur->setAccessible(true);

        $hasPromo = $reflection->getMethod('hasPromo');
        $hasPromo->setAccessible(true);

        $this->assertSame(10, $getStokAwal->invoke($controller, $product->IdRoster));
        $this->assertSame(7, $getStokAkhir->invoke($controller, $product->IdRoster));
        $this->assertTrue($isHariLibur->invoke($controller, Carbon::parse('2026-06-28')));
        $this->assertFalse($isHariLibur->invoke($controller, Carbon::parse('2026-06-29')));

        $promoTransaction = (object) [
            'IdTransaksi' => $transaction->IdTransaksi,
            'ongkir' => 10000,
            'GrandTotal' => 90000,
            'notes' => 'Promo diskon weekend',
        ];

        $this->assertTrue($hasPromo->invoke($controller, $promoTransaction));
    }

    public function test_export_routes_return_csv_headers(): void
    {
        $admin = $this->createAdminUser([
            'email' => 'export-prophet-admin@example.test',
            'password' => 'password',
        ]);

        $product = $this->createMasrosterProduct([
            'IdRoster' => 'MASEXP2',
            'NamaProduk' => 'Roster Export Prophet',
            'stock' => 100,
        ]);

        $transaction = $this->createMasrosterTransaction([
            'IdTransaksi' => 'TXEXP002',
            'id_admin' => $admin->id,
            'tglTransaksi' => Carbon::parse('2026-06-28'),
            'Bayar' => 90000,
            'GrandTotal' => 90000,
            'ongkir' => 10000,
            'notes' => 'Promo diskon weekend',
            'StatusPembayaran' => 'Lunas',
            'StatusPesanan' => 'Diterima',
        ]);

        DB::table('detail_transaksi')->insert([
            'IdTransaksi' => $transaction->IdTransaksi,
            'IdRoster' => $product->IdRoster,
            'id_ukuran' => 1,
            'QtyProduk' => 2,
            'SubTotal' => 100000,
            'data_type' => 'Eceran',
        ]);

        $lstmResponse = $this->actingAs($admin)->get(route('export.lstm'));
        $prophetResponse = $this->actingAs($admin)->get(route('export.prophet'));

        $lstmResponse->assertOk();
        $lstmResponse->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $prophetResponse->assertOk();
        $prophetResponse->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}