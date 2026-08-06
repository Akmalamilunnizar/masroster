<?php

namespace Tests\Feature\Payment;

use Mockery;
use Tests\TestCase;

class PaymentCreateSnapTokenTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_create_snap_token_recomputes_gross_amount_from_price_and_quantity(): void
    {
        $customer = $this->createCustomerUser([
            'f_name' => 'Payment',
            'username' => 'payment_customer',
            'email' => 'payment_customer@example.com',
            'nomor_telepon' => '081234567899',
        ]);

        config([
            'midtrans.server_key' => 'test-server-key',
            'midtrans.is_production' => false,
        ]);

        $snap = Mockery::mock('alias:Midtrans\\Snap');
        $snap->shouldReceive('getSnapToken')
            ->once()
            ->withArgs(function (array $params): bool {
                if (($params['transaction_details']['gross_amount'] ?? null) !== 10000) {
                    return false;
                }

                if (($params['item_details'][0]['price'] ?? null) !== 5000) {
                    return false;
                }

                if (($params['item_details'][0]['quantity'] ?? null) !== 2) {
                    return false;
                }

                return true;
            })
            ->andReturn('test-snap-token');

        $response = $this->actingAs($customer)->withSession([
            'cart' => [
                'SKU-001|1' => [
                    'id' => 'SKU-001',
                    'quantity' => 2,
                    'nama' => 'Roster Test',
                    'harga' => 5000,
                    'subtotal' => 1,
                    'img' => null,
                    'ukuran' => 1,
                    'ukuran_label' => 'Standar',
                ],
            ],
            'shipping_cost' => 0,
        ])->postJson('/payment/create-snap-token');

        $response->assertOk()->assertJson([
            'snap_token' => 'test-snap-token',
        ]);
    }
}