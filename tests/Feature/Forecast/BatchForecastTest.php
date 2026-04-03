<?php

namespace Tests\Feature\Forecast;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BatchForecastTest extends TestCase
{
    public function test_admin_can_run_batch_forecast_with_faked_flask_responses(): void
    {
        $admin = $this->createAdminUser([
            'email' => 'forecast-admin@example.test',
            'password' => 'password',
        ]);

        $product = $this->createMasrosterProduct([
            'IdRoster' => 'MAS900',
            'NamaProduk' => 'Roster Batch Forecast',
            'stock' => 25,
            'forecast_status' => 'safe',
            'forecast_model' => null,
            'forecasted_demand' => null,
            'last_forecast_at' => null,
        ]);

        $transaction = $this->createMasrosterTransaction([
            'IdTransaksi' => 'TX9001',
            'id_admin' => $admin->id,
            'id_customer' => $admin->id,
            'Bayar' => 0,
            'GrandTotal' => 63000,
            'StatusPembayaran' => 'Belum Lunas',
            'StatusPesanan' => 'Menunggu Konfirmasi',
            'shipping_method' => 'Online',
            'shipping_type' => 'Ongkir',
            'ongkir' => 0,
        ]);

        $this->createMasrosterDetailTransaction([
            'IdTransaksi' => $transaction->IdTransaksi,
            'IdRoster' => $product->IdRoster,
            'id_ukuran' => 1,
            'QtyProduk' => 20,
            'SubTotal' => 63000,
            'data_type' => 'Eceran',
        ]);

        Http::fake(function (\Illuminate\Http\Client\Request $request) {
            $url = $request->url();

            if (str_contains($url, '/health')) {
                return Http::response([
                    'status' => 'ok',
                    'registry_loaded' => 2,
                    'models' => ['lstm' => 'ready', 'prophet' => 'ready'],
                    'available_models' => [],
                ], 200);
            }

            if (str_contains($url, '/train/')) {
                return Http::response([
                    'message' => 'trained',
                    'mae' => 1.11,
                    'rmse' => 2.22,
                ], 200);
            }

            if (str_contains($url, '/predict')) {
                return Http::response([
                    'forecast' => [123.45],
                    'mae' => 1.11,
                    'rmse' => 2.22,
                    'model' => 'PROPHET',
                    'model_name' => 'prophet_eceran_tuned',
                    'data_type' => 'Eceran',
                ], 200);
            }

            return Http::response([], 404);
        });

        $response = $this->actingAs($admin)->postJson('/admin/forecast/run-batch', [
            'model' => 'prophet',
            'force' => true,
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success');

        Http::assertSent(fn ($request) => str_contains($request->url(), '/health'));
        Http::assertSent(fn ($request) => str_contains($request->url(), '/train/prophet'));
        Http::assertSent(fn ($request) => str_contains($request->url(), '/predictprophet'));

        $this->assertDatabaseHas('produk', [
            'IdRoster' => $product->IdRoster,
            'forecast_model' => 'prophet',
        ]);
    }
}
