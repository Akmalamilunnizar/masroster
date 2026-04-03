<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class DashboardUtilityTest extends TestCase
{
    public function test_admin_dashboard_shows_stock_alerts_and_restock_recommendations(): void
    {
        $admin = $this->createAdminUser([
            'email' => 'dashboard-admin@example.test',
        ]);

        $this->createMasrosterProduct([
            'IdRoster' => 'MASD001',
            'NamaProduk' => 'Roster Critical',
            'stock' => 10,
            'forecasted_demand' => 95,
            'safety_stock' => 70,
            'forecast_status' => 'critical',
        ]);

        $this->createMasrosterProduct([
            'IdRoster' => 'MASD002',
            'NamaProduk' => 'Roster Low',
            'stock' => 120,
            'forecasted_demand' => 90,
            'safety_stock' => 70,
            'forecast_status' => 'low',
        ]);

        $this->createMasrosterProduct([
            'IdRoster' => 'MASD003',
            'NamaProduk' => 'Roster Overstock',
            'stock' => 700,
            'forecasted_demand' => 80,
            'safety_stock' => 70,
            'forecast_status' => 'overstock',
        ]);

        $this->createMasrosterProduct([
            'IdRoster' => 'MASD004',
            'NamaProduk' => 'Roster Safe',
            'stock' => 200,
            'forecasted_demand' => 80,
            'safety_stock' => 70,
            'forecast_status' => 'safe',
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee('Stock Alerts');
        $response->assertSee('Actionable Forecasting Recommendations');
        $response->assertSee('Roster Critical');
        $response->assertSee('Roster Low');
        $response->assertSee('Critical');
        $response->assertSee('Low');
    }
}
