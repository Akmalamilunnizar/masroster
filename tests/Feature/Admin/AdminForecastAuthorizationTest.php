<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class AdminForecastAuthorizationTest extends TestCase
{
    public function test_customer_cannot_access_admin_batch_forecast(): void
    {
        $user = $this->createCustomerUser([
            'email' => 'customer-forecast@example.test',
            'password' => 'password',
        ]);

        $response = $this->actingAs($user)->postJson('/admin/forecast/run-batch', [
            'model' => 'prophet',
            'force' => true,
        ]);

        $response->assertForbidden();
    }
}
