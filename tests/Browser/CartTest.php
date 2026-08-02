<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CartTest extends DuskTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        DB::table('roles')->updateOrInsert(['id' => 1], ['name' => 'admin', 'display_name' => 'Admin', 'description' => 'Admin', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('roles')->updateOrInsert(['id' => 2], ['name' => 'user', 'display_name' => 'User', 'description' => 'User', 'created_at' => now(), 'updated_at' => now()]);

        // Seed size
        DB::table('size')->updateOrInsert(['id_ukuran' => 1], ['nama' => 'Standard', 'panjang' => 20, 'lebar' => 20]);
        DB::table('size')->updateOrInsert(['id_ukuran' => 2], ['nama' => 'Besar', 'panjang' => 30, 'lebar' => 30]);

        // Seed classification data
        DB::table('jenisbarang')->updateOrInsert(['IdJenisBarang' => 1], ['JenisBarang' => 'Roster']);
        DB::table('tipe_roster')->updateOrInsert(['IdTipe' => 1], ['namaTipe' => 'Biasa']);
        DB::table('motif_roster')->updateOrInsert(['IdMotif' => 1], ['nama_motif' => 'Classical Brown']);
    }

    /**
     * Test adding item from catalog opens modal, updates cart counter without page reload.
     */
    public function test_add_to_cart_realtime_counter(): void
    {
        // Create user
        $user = new User([
            'f_name' => 'Customer Test',
            'email' => 'customer@example.com',
            'nomor_telepon' => '081234567890',
            'username' => 'customer_test',
            'password' => Hash::make('password123'),
            'user' => 'User',
            'img' => 'default-avatar.png',
        ]);
        $user->tipe_user = 'end_customer';
        $user->status_verifikasi = 'approved';
        $user->save();
        $user->addRole('user');

        // Create product (manually to bypass $fillable on id field)
        $product = new Produk();
        $product->id = 1;
        $product->sku = 'PROD001';
        $product->IdRoster = 'PROD001';
        $product->NamaProduk = 'Super Roster R1';
        $product->id_jenis = 1;
        $product->id_tipe = 1;
        $product->id_motif = 1;
        $product->stock = 100;
        $product->Img = 'test_roster.png';
        $product->deskripsi = 'Super roster test product';
        $product->save();

        // Link product to sizes
        DB::table('produk_size')->updateOrInsert(
            ['produk_id' => 1, 'id_ukuran' => 1],
            ['IdRoster' => 'PROD001', 'harga' => 15000, 'created_at' => now(), 'updated_at' => now()]
        );
        DB::table('produk_size')->updateOrInsert(
            ['produk_id' => 1, 'id_ukuran' => 2],
            ['IdRoster' => 'PROD001', 'harga' => 25000, 'created_at' => now(), 'updated_at' => now()]
        );

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/tokodashboard')
                    ->assertSee('Super Roster R1')
                    ->assertSeeIn('#cart-count', '0')
                    ->click('.pesan-btn') // Click order/pesan button
                    ->waitFor('#pesanModal', 5) // Wait for size selection modal
                    ->assertSeeIn('#pesanModalLabel', 'Pilih Ukuran')
                    ->click('.modal-size-option[data-id="1"]') // Select first size (Standard)
                    ->click('#modalAddToCart') // Click add to cart inside modal
                    ->waitForText('Pesanan berhasil ditambahkan ke keranjang.', 5) // Wait for SweetAlert toast message
                    ->assertSeeIn('#cart-count', '1'); // Cart counter updated dynamically
        });
    }
}
