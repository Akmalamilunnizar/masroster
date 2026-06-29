<?php

namespace Tests\Feature\Checkout;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrderConfirmPricingTest extends TestCase
{
    public function test_confirm_order_ignores_tampered_cart_subtotal_and_uses_database_price(): void
    {
        $customer = $this->createCustomerUser([
            'email' => 'confirm-pricing@example.test',
            'password' => 'password',
        ]);

        $product = $this->createMasrosterProduct([
            'IdRoster' => 'CONFIRM1',
            'NamaProduk' => 'Confirm Price Test',
            'stock' => 100,
        ]);

        DB::table('produk_size')->insert([
            'produk_id' => null,
            'IdRoster' => $product->IdRoster,
            'id_ukuran' => 1,
            'harga' => 42000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($customer)->withSession([
            'cart' => [
                $product->IdRoster.'|1' => [
                    'id' => $product->IdRoster,
                    'quantity' => 3,
                    'nama' => $product->NamaProduk,
                    'harga' => 1,
                    'subtotal' => 1,
                    'img' => $product->Img,
                    'ukuran' => 1,
                    'ukuran_label' => 'Standard',
                ],
            ],
            'shipping_cost' => 5000,
            'shipping_method' => 'Online',
            'shipping_type' => 'Delivery',
            'order_notes' => 'Please handle carefully',
        ]);

        $response = $this->actingAs($customer)->postJson('/confirm-order');

        $response->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('transaksi', [
            'id_customer' => $customer->id,
            'GrandTotal' => (42000 * 3) + 5000,
            'Bayar' => 0,
            'StatusPembayaran' => 'Belum Lunas',
            'ongkir' => 5000,
        ]);

        $this->assertDatabaseHas('detail_transaksi', [
            'IdRoster' => $product->IdRoster,
            'QtyProduk' => 3,
            'SubTotal' => 42000 * 3,
        ]);
    }
}