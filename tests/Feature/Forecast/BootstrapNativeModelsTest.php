<?php

namespace Tests\Feature\Forecast;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BootstrapNativeModelsTest extends TestCase
{
    public function test_admin_can_bootstrap_native_models_from_flask_health(): void
    {
        $this->createMasrosterProduct([
            'IdRoster' => 'MAS940',
        ]);

        $this->createMasrosterProduct([
            'IdRoster' => 'MAS941',
        ]);

        Http::fake(function (\Illuminate\Http\Client\Request $request) {
            if (str_contains($request->url(), '/health')) {
                return Http::response([
                    'status' => 'ok',
                    'available_models' => [
                        [
                            'model_name' => 'lstm_eceran_original',
                            'model_version' => 'v_original_lstm_lstm_eceran_original',
                            'framework' => 'LSTM',
                            'data_type' => 'Eceran',
                            'bootstrap_source' => 'legacy',
                            'artifact_ready' => true,
                        ],
                        [
                            'model_name' => 'prophet_eceran_tuned',
                            'model_version' => 'v_legacy_prophet_prophet_eceran_tuned',
                            'framework' => 'PROPHET',
                            'data_type' => 'Eceran',
                            'bootstrap_source' => 'legacy',
                            'artifact_ready' => true,
                        ],
                    ],
                ], 200);
            }

            return Http::response([], 404);
        });

        $this->artisan('app:bootstrap-native-models')
            ->assertExitCode(0);

        $this->assertDatabaseHas('model_histories', [
            'id_roster' => 'MAS940',
            'model_type' => 'lstm',
            'version_id' => 'v_original_lstm_lstm_eceran_original',
            'is_active' => 1,
        ]);

        $this->assertDatabaseHas('model_histories', [
            'id_roster' => 'MAS940',
            'model_type' => 'prophet',
            'version_id' => 'v_legacy_prophet_prophet_eceran_tuned',
            'is_active' => 1,
        ]);

        $countBefore = \App\Models\ModelHistory::query()->count();

        $this->artisan('app:bootstrap-native-models')
            ->assertExitCode(0);

        $this->assertSame($countBefore, \App\Models\ModelHistory::query()->count());
    }
}
