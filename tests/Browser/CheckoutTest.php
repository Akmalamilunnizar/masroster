<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Produk;
use App\Models\Address;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CheckoutTest extends DuskTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        DB::table('roles')->updateOrInsert(['id' => 1], ['name' => 'admin', 'display_name' => 'Admin', 'description' => 'Admin', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('roles')->updateOrInsert(['id' => 2], ['name' => 'user', 'display_name' => 'User', 'description' => 'User', 'created_at' => now(), 'updated_at' => now()]);

        // Seed size
        DB::table('size')->updateOrInsert(['id_ukuran' => 1], ['nama' => 'Standard', 'panjang' => 20, 'lebar' => 20]);

        // Seed classification data
        DB::table('jenisbarang')->updateOrInsert(['IdJenisBarang' => 1], ['JenisBarang' => 'Roster']);
        DB::table('tipe_roster')->updateOrInsert(['IdTipe' => 1], ['namaTipe' => 'Biasa']);
        DB::table('motif_roster')->updateOrInsert(['IdMotif' => 1], ['nama_motif' => 'Classical Brown']);
    }

    /**
     * Test B2C customer checkout using national fallback price.
     */
    public function test_b2c_checkout_flow(): void
    {
        // 1. Create end customer user
        $user = new User([
            'f_name' => 'End Customer B2C',
            'email' => 'b2c_customer@example.com',
            'nomor_telepon' => '081234567890',
            'username' => 'b2c_customer',
            'password' => Hash::make('password123'),
            'user' => 'User',
            'img' => 'default-avatar.png',
        ]);
        $user->tipe_user = 'end_customer';
        $user->status_verifikasi = 'approved';
        $user->save();
        $user->addRole('user');

        // Create address
        $address = Address::create([
            'user_id' => $user->id,
            'label' => 'Rumah B2C',
            'recipient_name' => $user->f_name,
            'phone_number' => $user->nomor_telepon,
            'city' => 'Surabaya',
            'postal_code' => '60111',
            'full_address' => 'Jl. Juanda No. 10, Surabaya',
            'is_default' => true,
        ]);

        // 2. Create product (manually to bypass $fillable on id field)
        $product = new Produk();
        $product->id = 1;
        $product->sku = 'PROD001';
        $product->IdRoster = 'PROD001';
        $product->NamaProduk = 'B2C Roster';
        $product->id_jenis = 1;
        $product->id_tipe = 1;
        $product->id_motif = 1;
        $product->stock = 100;
        $product->Img = 'test_roster.png';
        $product->deskripsi = 'B2C testing product';
        $product->save();

        // Link product to sizes
        DB::table('produk_size')->updateOrInsert(
            ['produk_id' => 1, 'id_ukuran' => 1],
            ['IdRoster' => 'PROD001', 'harga' => 18000, 'created_at' => now(), 'updated_at' => now()]
        );

        // Seed national fallback default price (id_user = 0, address_id = null)
        DB::table('detail_harga')->updateOrInsert(
            ['produk_id' => 1, 'id_ukuran' => 1, 'id_user' => 0, 'address_id' => null],
            ['id_roster' => 'PROD001', 'harga' => 15000]
        );

        $this->browse(function (Browser $browser) use ($user, $address) {
            $browser->loginAs($user)
                    ->visit('/tokodashboard')
                    ->assertSee('B2C Roster')
                    ->click('.pesan-btn')
                    ->waitFor('#pesanModal', 5)
                    ->click('.modal-size-option[data-id="1"]')
                    ->click('#modalAddToCart')
                    ->waitForText('Pesanan berhasil ditambahkan ke keranjang.', 5)
                    ->visit('/cart')
                    ->assertSee('Rp 18.000') // Initially shows standard product price on cart
                    ->press('Proses Checkout') // Submits form to details page
                    ->assertPathIs('/details')
                    ->click('.select-address[data-address-id="' . $address->id . '"]') // Pilih Alamat Ini triggers ajax set-selected-address and redirects
                    ->waitForLocation('/shipping', 5)
                    ->click('.shipping-method[data-method="kurir"]')
                    ->click('.shipping-option[data-cost="20000"]')
                    ->click('#proceedButton')
                    ->waitForLocation('/payment', 5)
                    ->click('#cod') // Choose COD payment method
                    ->click('#pay-button') // Redirects to review page
                    ->waitForLocation('/review', 5)
                    ->assertSee('Rp 15.000') // Subtotal shows fallback price
                    ->assertSee('Rp 35.000') // Grand Total shows fallback price + shipping (15000 + 20000)
                    ->press('Konfirmasi Pesanan')
                    ->waitForLocation('/tokodashboard', 10);

            // Assert transaction created with "Menunggu Pembayaran"
            $transaction = Transaksi::where('id_customer', $user->id)->first();
            $this->assertNotNull($transaction);
            $this->assertEquals('Menunggu Pembayaran', $transaction->workflow_status);
        });
    }

    /**
     * Test B2B approved retailer checkout using special location-scoped price.
     */
    public function test_b2b_checkout_flow(): void
    {
        // 1. Create retailer user (approved)
        $user = new User([
            'f_name' => 'Retailer B2B',
            'email' => 'b2b_retailer@example.com',
            'nomor_telepon' => '081234567892',
            'username' => 'b2b_retailer',
            'password' => Hash::make('password123'),
            'user' => 'User',
            'img' => 'default-avatar.png',
        ]);
        $user->tipe_user = 'retailer';
        $user->status_verifikasi = 'approved';
        $user->save();
        $user->addRole('user');

        // Create address
        $address = Address::create([
            'user_id' => $user->id,
            'label' => 'Toko B2B',
            'recipient_name' => $user->f_name,
            'phone_number' => $user->nomor_telepon,
            'city' => 'Jakarta',
            'postal_code' => '10110',
            'full_address' => 'Jl. Sudirman No. 22, Jakarta',
            'is_default' => true,
        ]);

        // 2. Create product (manually to bypass $fillable on id field)
        $product = new Produk();
        $product->id = 2;
        $product->sku = 'PROD002';
        $product->IdRoster = 'PROD002';
        $product->NamaProduk = 'B2B Roster';
        $product->id_jenis = 1;
        $product->id_tipe = 1;
        $product->id_motif = 1;
        $product->stock = 100;
        $product->Img = 'test_roster2.png';
        $product->deskripsi = 'B2B testing product';
        $product->save();

        // Link product to sizes
        DB::table('produk_size')->updateOrInsert(
            ['produk_id' => 2, 'id_ukuran' => 1],
            ['IdRoster' => 'PROD002', 'harga' => 18000, 'created_at' => now(), 'updated_at' => now()]
        );

        // Seed special location-scoped B2B price (id_user = user_id, address_id = address_id)
        DB::table('detail_harga')->updateOrInsert(
            ['produk_id' => 2, 'id_ukuran' => 1, 'id_user' => $user->id, 'address_id' => $address->id],
            ['id_roster' => 'PROD002', 'harga' => 12000]
        );

        $this->browse(function (Browser $browser) use ($user, $address) {
            $browser->loginAs($user)
                    ->visit('/tokodashboard')
                    ->assertSee('B2B Roster')
                    ->click('.pesan-btn')
                    ->waitFor('#pesanModal', 5)
                    ->click('.modal-size-option[data-id="1"]')
                    ->click('#modalAddToCart')
                    ->waitForText('Pesanan berhasil ditambahkan ke keranjang.', 5)
                    // Set selected address session so the cart resolves prices with the correct address context
                    ->visit('/details')
                    ->click('.select-address[data-address-id="' . $address->id . '"]')
                    ->waitForLocation('/shipping', 5)
                    ->visit('/cart')
                    ->assertSee('Rp 18.000') // Shows standard product price on cart
                    ->press('Proses Checkout')
                    ->assertPathIs('/details')
                    ->click('.select-address[data-address-id="' . $address->id . '"]')
                    ->waitForLocation('/shipping', 5)
                    ->click('.shipping-method[data-method="kurir"]')
                    ->click('.shipping-option[data-cost="20000"]')
                    ->click('#proceedButton')
                    ->waitForLocation('/payment', 5)
                    ->click('#cod')
                    ->click('#pay-button')
                    ->waitForLocation('/review', 5)
                    ->assertSee('Rp 12.000') // Subtotal shows location-scoped B2B price on review
                    ->assertSee('Rp 32.000') // Grand Total shows B2B price + shipping (12000 + 20000)
                    ->press('Konfirmasi Pesanan')
                    ->waitForLocation('/tokodashboard', 10);

            // Assert transaction created with custom B2B location-scoped price
            $transaction = Transaksi::where('id_customer', $user->id)->first();
            $this->assertNotNull($transaction);
            $detail = DB::table('detail_transaksi')->where('IdTransaksi', $transaction->IdTransaksi)->first();
            $this->assertNotNull($detail);
            $this->assertEquals(12000, $detail->harga_satuan);
        });
    }
}
