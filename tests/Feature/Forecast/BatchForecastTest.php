<?php

namespace Tests\Feature\Forecast;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BatchForecastTest extends TestCase
{
    public function test_admin_can_run_batch_forecast_with_partial_success_and_report_failures(): void
    {
        $admin = $this->createAdminUser([
            'email' => 'forecast-admin-partial@example.test',
            'password' => 'password',
        ]);

        $successfulProduct = $this->createMasrosterProduct([
            'IdRoster' => 'MAS900',
            'NamaProduk' => 'Roster Alpha Success',
            'stock' => 25,
            'forecast_status' => 'safe',
            'forecast_model' => null,
            'forecasted_demand' => null,
            'last_forecast_at' => null,
        ]);

        $failedProduct = $this->createMasrosterProduct([
            'IdRoster' => 'MAS901',
            'NamaProduk' => 'Roster Zulu Failure',
            'stock' => 25,
            'forecast_status' => 'safe',
            'forecast_model' => null,
            'forecasted_demand' => null,
            'last_forecast_at' => null,
        ]);

        $this->createModelHistory([
            'id_roster' => $successfulProduct->IdRoster,
            'model_type' => 'prophet',
            'version_id' => 'v900',
            'wmape_score' => 12.5,
            'mae_score' => 1.11,
            'rmse_score' => 2.22,
            'is_active' => true,
        ]);

        $this->createModelHistory([
            'id_roster' => $failedProduct->IdRoster,
            'model_type' => 'prophet',
            'version_id' => 'v900',
            'wmape_score' => 12.5,
            'mae_score' => 1.11,
            'rmse_score' => 2.22,
            'is_active' => true,
        ]);

        for ($i = 6; $i >= 1; $i--) {
            $transaction = $this->createMasrosterTransaction([
                'IdTransaksi' => 'TXS' . $i . '001',
                'id_admin' => $admin->id,
                'id_customer' => $admin->id,
                'Bayar' => 0,
                'GrandTotal' => 63000,
                'StatusPembayaran' => 'Belum Lunas',
                'StatusPesanan' => 'Menunggu Konfirmasi',
                'shipping_method' => 'Online',
                'shipping_type' => 'Ongkir',
                'ongkir' => 0,
                'tglTransaksi' => now()->subMonths($i)->startOfMonth(),
            ]);

            $this->createMasrosterDetailTransaction([
                'IdTransaksi' => $transaction->IdTransaksi,
                'IdRoster' => $successfulProduct->IdRoster,
                'id_ukuran' => 1,
                'QtyProduk' => 10,
                'SubTotal' => 63000,
                'data_type' => 'Eceran',
            ]);
        }

        $failedTransaction = $this->createMasrosterTransaction([
            'IdTransaksi' => 'TX9002',
            'id_admin' => $admin->id,
            'id_customer' => $admin->id,
            'Bayar' => 0,
            'GrandTotal' => 63000,
            'StatusPembayaran' => 'Belum Lunas',
            'StatusPesanan' => 'Menunggu Konfirmasi',
            'shipping_method' => 'Online',
            'shipping_type' => 'Ongkir',
            'ongkir' => 0,
            'tglTransaksi' => now()->subMonth()->startOfMonth(),
        ]);

        $this->createMasrosterDetailTransaction([
            'IdTransaksi' => $failedTransaction->IdTransaksi,
            'IdRoster' => $failedProduct->IdRoster,
            'id_ukuran' => 1,
            'QtyProduk' => 20,
            'SubTotal' => 63000,
            'data_type' => 'Eceran',
        ]);

        $predictCalls = 0;

        Http::fake(function (\Illuminate\Http\Client\Request $request) use (&$predictCalls) {
            $url = $request->url();

            if (str_contains($url, '/health')) {
                return Http::response([
                    'status' => 'ok',
                    'registry_loaded' => 2,
                    'models' => ['lstm' => 'ready', 'prophet' => 'ready'],
                    'available_models' => [],
                ], 200);
            }

            if (str_contains($url, '/predict')) {
                $predictCalls++;

                $body = json_decode($request->body(), true);
                $this->assertSame('v900', $body['model_version'] ?? null);

                if ($predictCalls === 1) {
                    return Http::response([
                        'status' => 'success',
                        'forecast' => [123.45],
                        'metrics' => [
                            'mae' => 1.11,
                            'rmse' => 2.22,
                            'wmape' => 3.33,
                        ],
                        'model' => [
                            'framework' => 'PROPHET',
                            'model_name' => 'prophet_eceran_tuned',
                            'data_type' => 'Eceran',
                        ],
                    ], 200);
                }

                return Http::response([
                    'error' => 'Minimal 3 data diperlukan untuk prediksi Prophet.',
                ], 400);
            }

            return Http::response([], 404);
        });

        $response = $this->actingAs($admin)->postJson('/admin/forecast/run-batch', [
            'model' => 'prophet',
            'force' => true,
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'partial_success')
            ->assertJsonPath('summary.success', 1)
            ->assertJsonPath('summary.failed', 1)
            ->assertJsonPath('summary.total', 2);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/health'));
        Http::assertSent(fn ($request) => str_contains($request->url(), '/predictprophet'));

        $this->assertDatabaseHas('produk', [
            'IdRoster' => $successfulProduct->IdRoster,
            'forecast_model' => 'prophet',
        ]);

        $successfulProduct->refresh();
        $failedProduct->refresh();

        $this->assertNotNull($successfulProduct->forecasted_demand);
        $this->assertNull($failedProduct->forecasted_demand);
        $this->assertNull($failedProduct->last_forecast_at);
    }

    public function test_admin_can_run_batch_forecast_with_faked_flask_responses(): void
    {
        $admin = $this->createAdminUser([
            'email' => 'forecast-admin@example.test',
            'password' => 'password',
        ]);

        $product = $this->createMasrosterProduct([
            'IdRoster' => 'MAS902',
            'NamaProduk' => 'Roster Batch Forecast',
            'stock' => 25,
            'forecast_status' => 'safe',
            'forecast_model' => null,
            'forecasted_demand' => null,
            'last_forecast_at' => null,
        ]);

        $this->createModelHistory([
            'id_roster' => $product->IdRoster,
            'model_type' => 'prophet',
            'version_id' => 'v902',
            'wmape_score' => 11.1,
            'mae_score' => 1.11,
            'rmse_score' => 2.22,
            'is_active' => true,
        ]);

        for ($i = 6; $i >= 1; $i--) {
            $transaction = $this->createMasrosterTransaction([
                'IdTransaksi' => 'TXB' . $i . '001',
                'id_admin' => $admin->id,
                'id_customer' => $admin->id,
                'Bayar' => 0,
                'GrandTotal' => 63000,
                'StatusPembayaran' => 'Belum Lunas',
                'StatusPesanan' => 'Menunggu Konfirmasi',
                'shipping_method' => 'Online',
                'shipping_type' => 'Ongkir',
                'ongkir' => 0,
                'tglTransaksi' => now()->subMonths($i)->startOfMonth(),
            ]);

            $this->createMasrosterDetailTransaction([
                'IdTransaksi' => $transaction->IdTransaksi,
                'IdRoster' => $product->IdRoster,
                'id_ukuran' => 1,
                'QtyProduk' => 10,
                'SubTotal' => 63000,
                'data_type' => 'Eceran',
            ]);
        }

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

            if (str_contains($url, '/predict')) {
                $body = json_decode($request->body(), true);
                $this->assertSame('v902', $body['model_version'] ?? null);

                return Http::response([
                    'status' => 'success',
                    'forecast' => [123.45],
                    'metrics' => [
                        'mae' => 1.11,
                        'rmse' => 2.22,
                        'wmape' => 3.33,
                    ],
                    'model' => [
                        'framework' => 'PROPHET',
                        'model_name' => 'prophet_eceran_tuned',
                        'data_type' => 'Eceran',
                    ],
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
        Http::assertSent(fn ($request) => str_contains($request->url(), '/predictprophet'));

        $this->assertDatabaseHas('produk', [
            'IdRoster' => $product->IdRoster,
            'forecast_model' => 'prophet',
        ]);

        $product->refresh();
        $this->assertNotNull($product->forecasted_demand);
    }
}
