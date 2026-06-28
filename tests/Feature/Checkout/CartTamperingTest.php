<?php

namespace Tests\Feature\Checkout;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CartTamperingTest extends TestCase
{
    public function test_client_submitted_price_is_ignored_when_db_price_exists(): void
    {
        $customer = $this->createCustomerUser([
            'email' => 'tamper-customer@example.test',
            'password' => 'password',
        ]);

        $product = $this->createMasrosterProduct([
            'IdRoster' => 'MASSAFE1',
            'NamaProduk' => 'Roster Price Source Test',
            'stock' => 100,
        ]);

        // attach a size and authoritative price in pivot
        DB::table('produk_size')->insert([
            'produk_id' => null,
            'IdRoster' => $product->IdRoster,
            'id_ukuran' => 1,
            'harga' => 50000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($customer)
            ->post('/cart/add', [
                'id' => $product->IdRoster,
                'quantity' => 1,
                'nama' => $product->NamaProduk,
                'harga' => 999999, // tampered price
                'img' => $product->Img,
                'ukuran' => 1,
                'ukuran_label' => 'Standard',
                'subtotal' => 999999,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $cart = session('cart');
        $this->assertArrayHasKey($product->IdRoster.'|1', $cart);
        $item = $cart[$product->IdRoster.'|1'];

        // price should be authoritative pivot value, not client-supplied
        $this->assertEquals(50000, $item['harga']);
        $this->assertEquals(50000 * 1, $item['subtotal']);
    }

    public function test_client_submitted_price_used_when_no_db_price(): void
    {
        $customer = $this->createCustomerUser([
            'email' => 'tamper2-customer@example.test',
            'password' => 'password',
        ]);

        $product = $this->createMasrosterProduct([
            'IdRoster' => 'MASSAFE2',
            'NamaProduk' => 'Roster Price Fallback Test',
            'stock' => 100,
        ]);

        // Do NOT insert produk_size row; fallback to client price
        $this->actingAs($customer)
            ->post('/cart/add', [
                'id' => $product->IdRoster,
                'quantity' => 2,
                'nama' => $product->NamaProduk,
                'harga' => 75000, // client price should be used as fallback
                'img' => $product->Img,
                'ukuran' => 1,
                'ukuran_label' => 'Standard',
                'subtotal' => 150000,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $cart = session('cart');
        $this->assertArrayHasKey($product->IdRoster.'|1', $cart);
        $item = $cart[$product->IdRoster.'|1'];

        $this->assertEquals(75000, $item['harga']);
        $this->assertEquals(75000 * 2, $item['subtotal']);
    }

    public function test_cart_update_recalculates_subtotal_server_side(): void
    {
        $customer = $this->createCustomerUser([
            'email' => 'cart-update-customer@example.test',
            'password' => 'password',
        ]);

        $product = $this->createMasrosterProduct([
            'IdRoster' => 'MASSAFE3',
            'NamaProduk' => 'Roster Update Test',
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

        $this->actingAs($customer)
            ->post('/cart/add', [
                'id' => $product->IdRoster,
                'quantity' => 1,
                'nama' => $product->NamaProduk,
                'harga' => 999999,
                'img' => $product->Img,
                'ukuran' => 1,
                'ukuran_label' => 'Standard',
                'subtotal' => 999999,
            ])
            ->assertOk();

        $this->actingAs($customer)
            ->post('/cart/update/'.$product->IdRoster.'|1', [
                'type' => 'set',
                'quantity' => 4,
                'subtotal' => 1,
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('cartCount', 4);

        $cart = session('cart');
        $item = $cart[$product->IdRoster.'|1'];

        $this->assertSame(4, $item['quantity']);
        $this->assertSame(42000 * 4, $item['subtotal']);
    }
}
