<?php

namespace Tests\Feature\Checkout;

use App\Models\User;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Produk;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LocationPricingAndSnapshotsTest extends TestCase
{
    use DatabaseTransactions;

    private User $customer;
    private Produk $product;
    private int $sizeId = 1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = $this->createCustomerUser([
            'email' => 'location-pricing-customer@example.test',
        ]);

        $this->product = $this->createMasrosterProduct([
            'IdRoster' => 'LOC1',
            'sku' => 'LOC1',
            'NamaProduk' => 'Location Price Test',
            'stock' => 100,
        ]);

        $size = DB::table('size')->where('id_ukuran', $this->sizeId)->first();
        if (!$size) {
            DB::table('size')->insert([
                'id_ukuran' => $this->sizeId,
                'nama' => 'Standard',
                'panjang' => 20,
                'lebar' => 20,
            ]);
        }
    }

    public function test_price_resolves_using_user_specific_and_location_specific_pricing(): void
    {
        $address = $this->createMasrosterAddress($this->customer, ['label' => 'Cabang Malang']);

        DB::table('detail_harga')->insert([
            'produk_id' => $this->product->id,
            'id_roster' => $this->product->sku,
            'id_user' => $this->customer->id,
            'id_ukuran' => $this->sizeId,
            'harga' => 35000,
            'address_id' => $address->id,
        ]);

        $this->actingAs($this->customer)->withSession([
            'cart' => [
                'LOC1|1' => [
                    'id' => $this->product->sku,
                    'quantity' => 2,
                    'ukuran' => $this->sizeId,
                    'harga' => 50000,
                ]
            ],
            'selected_address_id' => $address->id,
        ]);

        $response = $this->postJson('/confirm-order');
        $response->assertOk();

        $this->assertDatabaseHas('transaksi', [
            'id_customer' => $this->customer->id,
            'GrandTotal' => 35000 * 2,
        ]);

        $this->assertDatabaseHas('detail_transaksi', [
            'IdRoster' => 'LOC1',
            'harga_satuan' => 35000,
            'QtyProduk' => 2,
            'SubTotal' => 70000,
        ]);
    }

    public function test_price_falls_back_to_user_specific_national_default_pricing(): void
    {
        $address = $this->createMasrosterAddress($this->customer, ['label' => 'Cabang Surabaya']);

        DB::table('detail_harga')->insert([
            'produk_id' => $this->product->id,
            'id_roster' => $this->product->sku,
            'id_user' => $this->customer->id,
            'id_ukuran' => $this->sizeId,
            'harga' => 38000,
            'address_id' => null, // national default for this user
        ]);

        $this->actingAs($this->customer)->withSession([
            'cart' => [
                'LOC1|1' => [
                    'id' => $this->product->sku,
                    'quantity' => 2,
                    'ukuran' => $this->sizeId,
                    'harga' => 50000,
                ]
            ],
            'selected_address_id' => $address->id,
        ]);

        $response = $this->postJson('/confirm-order');
        $response->assertOk();

        $this->assertDatabaseHas('transaksi', [
            'id_customer' => $this->customer->id,
            'GrandTotal' => 38000 * 2,
        ]);

        $this->assertDatabaseHas('detail_transaksi', [
            'IdRoster' => 'LOC1',
            'harga_satuan' => 38000,
        ]);
    }

    public function test_price_falls_back_to_general_location_specific_pricing(): void
    {
        $address = $this->createMasrosterAddress($this->customer, ['label' => 'Cabang Kediri']);

        DB::table('detail_harga')->insert([
            'produk_id' => $this->product->id,
            'id_roster' => $this->product->sku,
            'id_user' => 0, // general default
            'id_ukuran' => $this->sizeId,
            'harga' => 41000,
            'address_id' => $address->id,
        ]);

        $this->actingAs($this->customer)->withSession([
            'cart' => [
                'LOC1|1' => [
                    'id' => $this->product->sku,
                    'quantity' => 2,
                    'ukuran' => $this->sizeId,
                    'harga' => 50000,
                ]
            ],
            'selected_address_id' => $address->id,
        ]);

        $response = $this->postJson('/confirm-order');
        $response->assertOk();

        $this->assertDatabaseHas('transaksi', [
            'id_customer' => $this->customer->id,
            'GrandTotal' => 41000 * 2,
        ]);
    }

    public function test_price_falls_back_to_general_national_default_pricing(): void
    {
        $address = $this->createMasrosterAddress($this->customer, ['label' => 'Cabang Blitar']);

        DB::table('detail_harga')->insert([
            'produk_id' => $this->product->id,
            'id_roster' => $this->product->sku,
            'id_user' => 0, // general default
            'id_ukuran' => $this->sizeId,
            'harga' => 45000,
            'address_id' => null, // national default
        ]);

        $this->actingAs($this->customer)->withSession([
            'cart' => [
                'LOC1|1' => [
                    'id' => $this->product->sku,
                    'quantity' => 2,
                    'ukuran' => $this->sizeId,
                    'harga' => 50000,
                ]
            ],
            'selected_address_id' => $address->id,
        ]);

        $response = $this->postJson('/confirm-order');
        $response->assertOk();

        $this->assertDatabaseHas('transaksi', [
            'id_customer' => $this->customer->id,
            'GrandTotal' => 45000 * 2,
        ]);
    }

    public function test_price_falls_back_to_variant_baseline_pricing(): void
    {
        $address = $this->createMasrosterAddress($this->customer);

        DB::table('produk_size')->insert([
            'produk_id' => $this->product->id,
            'IdRoster' => $this->product->sku,
            'id_ukuran' => $this->sizeId,
            'harga' => 49000,
        ]);

        $this->actingAs($this->customer)->withSession([
            'cart' => [
                'LOC1|1' => [
                    'id' => $this->product->sku,
                    'quantity' => 2,
                    'ukuran' => $this->sizeId,
                    'harga' => 50000,
                ]
            ],
            'selected_address_id' => $address->id,
        ]);

        $response = $this->postJson('/confirm-order');
        $response->assertOk();

        $this->assertDatabaseHas('transaksi', [
            'id_customer' => $this->customer->id,
            'GrandTotal' => 49000 * 2,
        ]);
    }

    public function test_pending_retailer_without_location_price_triggers_draft_order(): void
    {
        $retailer = $this->createMasrosterUser([
            'tipe_user' => 'retailer',
            'status_verifikasi' => 'pending',
        ]);
        $address = $this->createMasrosterAddress($retailer);

        DB::table('produk_size')->insert([
            'produk_id' => $this->product->id,
            'IdRoster' => $this->product->sku,
            'id_ukuran' => $this->sizeId,
            'harga' => 49000,
        ]);

        $this->actingAs($retailer)->withSession([
            'cart' => [
                'LOC1|1' => [
                    'id' => $this->product->sku,
                    'quantity' => 2,
                    'ukuran' => $this->sizeId,
                    'harga' => 50000,
                ]
            ],
            'selected_address_id' => $address->id,
        ]);

        $response = $this->postJson('/confirm-order');
        $response->assertOk();

        // Transaction should be marked as Draft workflow status
        $this->assertDatabaseHas('transaksi', [
            'id_customer' => $retailer->id,
            'workflow_status' => 'Draft',
        ]);
    }

    public function test_pending_retailer_with_location_price_skips_draft_order(): void
    {
        $retailer = $this->createMasrosterUser([
            'tipe_user' => 'retailer',
            'status_verifikasi' => 'pending',
        ]);
        $address = $this->createMasrosterAddress($retailer);

        DB::table('detail_harga')->insert([
            'produk_id' => $this->product->id,
            'id_roster' => $this->product->sku,
            'id_user' => $retailer->id,
            'id_ukuran' => $this->sizeId,
            'harga' => 30000,
            'address_id' => $address->id,
        ]);

        $this->actingAs($retailer)->withSession([
            'cart' => [
                'LOC1|1' => [
                    'id' => $this->product->sku,
                    'quantity' => 2,
                    'ukuran' => $this->sizeId,
                    'harga' => 50000,
                ]
            ],
            'selected_address_id' => $address->id,
        ]);

        $response = $this->postJson('/confirm-order');
        $response->assertOk();

        // Transaction should be marked as Menunggu Pembayaran directly since location price exists
        $this->assertDatabaseHas('transaksi', [
            'id_customer' => $retailer->id,
            'workflow_status' => 'Menunggu Pembayaran',
        ]);
    }

    public function test_order_line_item_auto_classifies_data_type_based_on_quantity(): void
    {
        $address = $this->createMasrosterAddress($this->customer);

        DB::table('produk_size')->insert([
            'produk_id' => $this->product->id,
            'IdRoster' => $this->product->sku,
            'id_ukuran' => $this->sizeId,
            'harga' => 50000,
        ]);

        $this->actingAs($this->customer)->withSession([
            'cart' => [
                'LOC1|1' => [
                    'id' => $this->product->sku,
                    'quantity' => 150, // Wholesale quantity (>100)
                    'ukuran' => $this->sizeId,
                    'harga' => 50000,
                ]
            ],
            'selected_address_id' => $address->id,
        ]);

        $response = $this->postJson('/confirm-order');
        $response->assertOk();

        $this->assertDatabaseHas('detail_transaksi', [
            'IdRoster' => 'LOC1',
            'QtyProduk' => 150,
            'data_type' => 'Borongan',
        ]);
    }
}
