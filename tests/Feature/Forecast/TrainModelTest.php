<?php

namespace Tests\Feature\Forecast;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TrainModelTest extends TestCase
{
    public function test_admin_can_train_and_promote_new_model_version(): void
    {
        $admin = $this->createAdminUser([
            'email' => 'forecast-admin-train@example.test',
            'password' => 'password',
        ]);

        $product = $this->createMasrosterProduct([
            'IdRoster' => 'MAS930',
            'wmape_score' => null,
        ]);

        for ($i = 6; $i >= 1; $i--) {
            $transaction = $this->createMasrosterTransaction([
                'IdTransaksi' => 'TXT' . $i . '001',
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

        $this->createModelHistory([
            'id_roster' => $product->IdRoster,
            'model_type' => 'prophet',
            'version_id' => 'vold01',
            'wmape_score' => 9.99,
            'mae_score' => 1.00,
            'rmse_score' => 2.00,
            'is_active' => true,
        ]);

        Http::fake(function (\Illuminate\Http\Client\Request $request) {
            $url = $request->url();

            if (str_contains($url, '/health')) {
                return Http::response(['status' => 'ok'], 200);
            }

            if (str_contains($url, '/train/prophet')) {
                return Http::response([
                    'status' => 'success',
                    'model_version' => 'vtrain01',
                    'metrics' => [
                        'mae' => 1.10,
                        'rmse' => 2.20,
                        'wmape' => 3.30,
                    ],
                ], 200);
            }

            return Http::response([], 404);
        });

        $response = $this->actingAs($admin)->postJson('/admin/forecast/train-model', [
            'model' => 'prophet',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('summary.model_version', 'vtrain01')
            ->assertJsonPath('summary.inserted', 1);

        $this->assertDatabaseHas('model_histories', [
            'id_roster' => $product->IdRoster,
            'model_type' => 'prophet',
            'version_id' => 'vtrain01',
            'is_active' => 1,
        ]);

        $this->assertDatabaseHas('model_histories', [
            'id_roster' => $product->IdRoster,
            'model_type' => 'prophet',
            'version_id' => 'vold01',
            'is_active' => 0,
        ]);
    }
}
