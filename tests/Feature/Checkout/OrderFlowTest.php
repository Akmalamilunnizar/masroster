<?php

namespace Tests\Feature\Checkout;

use Tests\TestCase;

class OrderFlowTest extends TestCase
{
    public function test_authenticated_customer_can_checkout_and_persist_order(): void
    {
        $customer = $this->createCustomerUser([
            'email' => 'checkout-customer@example.test',
            'password' => 'password',
        ]);

        $product = $this->createMasrosterProduct([
            'IdRoster' => 'MAS801',
            'NamaProduk' => 'Roster Checkout Test',
            'stock' => 100,
        ]);

        $this->actingAs($customer)
            ->post('/cart/add', [
                'id' => $product->IdRoster,
                'quantity' => 2,
                'nama' => $product->NamaProduk,
                'harga' => 63000,
                'img' => $product->Img,
                'ukuran' => 1,
                'ukuran_label' => 'Standard',
                'subtotal' => 126000,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $cart = session('cart');
        $this->assertArrayHasKey('MAS801|1', $cart);

        $this->actingAs($customer)
            ->post('/save-address', [
                'label' => 'Rumah',
                'recipient_name' => $customer->f_name,
                'phone_number' => $customer->nomor_telepon,
                'city' => 'Malang',
                'postal_code' => '65111',
                'full_address' => 'Jl. Testing No. 1, Malang',
                'is_default' => true,
            ])
            ->assertRedirect(route('shipping'));

        $address = $this->createMasrosterAddress($customer, [
            'label' => 'Kantor',
            'is_default' => false,
        ]);

        $this->actingAs($customer)
            ->postJson('/save-shipping', [
                'method' => 'Online',
                'type' => 'Delivery',
                'cost' => 15000,
                'address_id' => $address->id,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $response = $this->actingAs($customer)->postJson('/confirm-order');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('redirect', route('tokodashboard'));

        $transactionId = $response->json('transaction_id');

        $this->assertDatabaseHas('transaksi', [
            'IdTransaksi' => $transactionId,
            'id_customer' => $customer->id,
            'GrandTotal' => 141000,
            'StatusPembayaran' => 'Belum Lunas',
            'StatusPesanan' => 'Menunggu Konfirmasi',
            'ongkir' => 15000,
        ]);

        $this->assertDatabaseHas('detail_transaksi', [
            'IdTransaksi' => $transactionId,
            'IdRoster' => $product->IdRoster,
            'QtyProduk' => 2,
            'SubTotal' => 126000,
            'data_type' => 'Eceran',
        ]);

        $this->assertFalse(session()->has('cart'));
    }

    public function test_checkout_ignores_forged_selected_address_and_uses_customer_default(): void
    {
        $customer = $this->createCustomerUser([
            'email' => 'checkout-forged-address@example.test',
            'password' => 'password',
        ]);

        $intruder = $this->createCustomerUser([
            'email' => 'checkout-forged-address-intruder@example.test',
            'password' => 'password',
        ]);

        $product = $this->createMasrosterProduct([
            'IdRoster' => 'MAS802',
            'NamaProduk' => 'Roster Checkout Forged Address Test',
            'stock' => 100,
        ]);

        $this->actingAs($customer)->post('/cart/add', [
            'id' => $product->IdRoster,
            'quantity' => 1,
            'nama' => $product->NamaProduk,
            'harga' => 63000,
            'img' => $product->Img,
            'ukuran' => 1,
            'ukuran_label' => 'Standard',
            'subtotal' => 63000,
        ])->assertOk();

        $customerDefaultAddress = $this->createMasrosterAddress($customer, [
            'label' => 'Rumah Utama',
            'is_default' => true,
        ]);

        $intruderAddress = $this->createMasrosterAddress($intruder, [
            'label' => 'Alamat Jahat',
            'is_default' => false,
        ]);

        $this->actingAs($customer)->postJson('/save-shipping', [
            'method' => 'Online',
            'type' => 'Delivery',
            'cost' => 15000,
            'address_id' => $intruderAddress->id,
        ])->assertUnprocessable()->assertJsonValidationErrors(['address_id']);

        $this->actingAs($customer)->postJson('/save-shipping', [
            'method' => 'Online',
            'type' => 'Delivery',
            'cost' => 15000,
            'address_id' => $customerDefaultAddress->id,
        ])->assertOk();

        $response = $this->actingAs($customer)->postJson('/confirm-order');

        $response->assertOk()->assertJsonPath('success', true);

        $transactionId = $response->json('transaction_id');

        $this->assertDatabaseHas('transaksi', [
            'IdTransaksi' => $transactionId,
            'id_customer' => $customer->id,
            'address_id' => $customerDefaultAddress->id,
            'GrandTotal' => 78000,
            'ongkir' => 15000,
        ]);

        $this->assertDatabaseMissing('transaksi', [
            'IdTransaksi' => $transactionId,
            'address_id' => $intruderAddress->id,
        ]);
    }

    public function test_confirm_order_skips_colliding_transaction_id(): void
    {
        $customer = $this->createCustomerUser([
            'email' => 'checkout-collision@example.test',
            'password' => 'password',
        ]);

        $product = $this->createMasrosterProduct([
            'IdRoster' => 'MAS803',
            'NamaProduk' => 'Roster Checkout Collision Test',
            'stock' => 100,
        ]);

        $this->actingAs($customer)->post('/cart/add', [
            'id' => $product->IdRoster,
            'quantity' => 1,
            'nama' => $product->NamaProduk,
            'harga' => 63000,
            'img' => $product->Img,
            'ukuran' => 1,
            'ukuran_label' => 'Standard',
            'subtotal' => 63000,
        ])->assertOk();

        $address = $this->createMasrosterAddress($customer, [
            'label' => 'Rumah Collision',
            'is_default' => true,
        ]);

        $this->actingAs($customer)->postJson('/save-shipping', [
            'method' => 'Online',
            'type' => 'Delivery',
            'cost' => 15000,
            'address_id' => $address->id,
        ])->assertOk();

        foreach (['TR0001', 'TR0002', 'TR0003', 'TR0004', 'TR0005', 'TR0006', 'TR0007', 'TR0008', 'TR0010'] as $transactionId) {
            $this->createMasrosterTransaction([
                'IdTransaksi' => $transactionId,
                'id_customer' => $customer->id,
                'StatusPesanan' => 'Menunggu Konfirmasi',
            ]);
        }

        $response = $this->actingAs($customer)->postJson('/confirm-order');

        $response->assertOk()->assertJsonPath('success', true);

        $generatedTransactionId = $response->json('transaction_id');

        $this->assertNotSame('TR0010', $generatedTransactionId);
        $this->assertDatabaseHas('transaksi', [
            'IdTransaksi' => $generatedTransactionId,
            'id_customer' => $customer->id,
        ]);
    }
}
