<?php

namespace Tests\Feature\Address;

use App\Models\Address;
use Tests\TestCase;

class AddressManagementTest extends TestCase
{
    public function test_customer_can_store_set_default_update_and_delete_own_address(): void
    {
        $customer = $this->createCustomerUser([
            'email' => 'address-owner@example.test',
        ]);

        $oldDefault = $this->createMasrosterAddress($customer, [
            'label' => 'Rumah Lama',
            'is_default' => true,
        ]);

        $storeResponse = $this->actingAs($customer)->postJson('/addresses', [
            'label' => 'Kantor',
            'recipient_name' => $customer->f_name,
            'phone_number' => $customer->nomor_telepon,
            'city' => 'Malang',
            'postal_code' => '65111',
            'full_address' => 'Jl. Veteran No. 10',
            'is_default' => false,
        ]);

        $storeResponse->assertOk()->assertJsonPath('success', true);

        $newAddressId = (int) $storeResponse->json('address.id');

        $this->assertDatabaseHas('addresses', [
            'id' => $newAddressId,
            'user_id' => $customer->id,
            'label' => 'Kantor',
            'is_default' => 0,
        ]);

        $this->actingAs($customer)
            ->postJson('/addresses/' . $newAddressId . '/default')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('addresses', [
            'id' => $oldDefault->id,
            'is_default' => 0,
        ]);

        $this->assertDatabaseHas('addresses', [
            'id' => $newAddressId,
            'is_default' => 1,
        ]);

        $this->assertSame($newAddressId, session('selected_address_id'));

        $this->actingAs($customer)
            ->postJson('/addresses/' . $newAddressId, [
                'label' => 'Kantor Pusat',
                'recipient_name' => $customer->f_name,
                'phone_number' => '081234560000',
                'city' => 'Batu',
                'postal_code' => '65311',
                'full_address' => 'Jl. Diponegoro No. 1',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('addresses', [
            'id' => $newAddressId,
            'city' => 'Batu',
            'label' => 'Kantor Pusat',
        ]);

        $this->actingAs($customer)
            ->deleteJson('/addresses/' . $newAddressId)
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('addresses', [
            'id' => $newAddressId,
        ]);
    }

    public function test_customer_cannot_set_default_or_delete_other_users_address(): void
    {
        $owner = $this->createCustomerUser([
            'email' => 'address-real-owner@example.test',
        ]);

        $intruder = $this->createCustomerUser([
            'email' => 'address-intruder@example.test',
        ]);

        $ownerAddress = $this->createMasrosterAddress($owner, [
            'label' => 'Rumah Owner',
            'is_default' => true,
        ]);

        $this->actingAs($intruder)
            ->postJson('/addresses/' . $ownerAddress->id . '/default')
            ->assertForbidden();

        $this->actingAs($intruder)
            ->deleteJson('/addresses/' . $ownerAddress->id)
            ->assertForbidden();

        $this->assertDatabaseHas('addresses', [
            'id' => $ownerAddress->id,
            'user_id' => $owner->id,
        ]);
    }

    public function test_set_selected_address_stores_session_value(): void
    {
        $customer = $this->createCustomerUser([
            'email' => 'selected-address@example.test',
        ]);

        $address = $this->createMasrosterAddress($customer, [
            'is_default' => false,
        ]);

        $response = $this->actingAs($customer)->postJson('/set-selected-address', [
            'address_id' => $address->id,
        ]);

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertSame($address->id, session('selected_address_id'));
    }
}
