<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AuthTest extends DuskTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        DB::table('roles')->updateOrInsert(['id' => 1], ['name' => 'admin', 'display_name' => 'Admin', 'description' => 'Admin', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('roles')->updateOrInsert(['id' => 2], ['name' => 'user', 'display_name' => 'User', 'description' => 'User', 'created_at' => now(), 'updated_at' => now()]);
    }

    /**
     * Test end customer registration is immediately approved.
     */
    public function test_end_customer_registration(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                    ->type('name', 'End Customer Test')
                    ->type('email', 'end_customer_test@example.com')
                    ->type('nomor_telepon', '081234567890')
                    ->select('tipe_user', 'end_customer')
                    ->type('password', 'password123')
                    ->type('password_confirmation', 'password123')
                    ->press('Register');

            // Debug if path is not /tokodashboard
            if ($browser->driver->getCurrentURL() !== url('/tokodashboard')) {
                echo "\n--- DEBUG: Registration failed! Page HTML: ---\n";
                echo $browser->driver->getPageSource();
                echo "\n----------------------------------------\n";
            }

            $browser->assertPathIs('/tokodashboard');

            // Verify database
            $user = User::where('email', 'end_customer_test@example.com')->first();
            $this->assertNotNull($user);
            $this->assertEquals('end_customer', $user->tipe_user);
            $this->assertEquals('approved', $user->status_verifikasi);
        });
    }

    /**
     * Test retailer registration requires a foto_toko upload and is set to pending.
     */
    public function test_retailer_registration(): void
    {
        $this->browse(function (Browser $browser) {
            // Use existing project asset for upload
            $filePath = base_path('public/dashboard2/assets/img/icons/logomasroster.png');

            $browser->visit('/register')
                    ->type('name', 'Retailer Test')
                    ->type('email', 'retailer_test@example.com')
                    ->type('nomor_telepon', '081234567891')
                    ->select('tipe_user', 'retailer')
                    ->waitFor('#foto_toko_container', 2)
                    ->attach('foto_toko', $filePath)
                    ->type('password', 'password123')
                    ->type('password_confirmation', 'password123')
                    ->press('Register');

            // Debug if path is not /tokodashboard
            if ($browser->driver->getCurrentURL() !== url('/tokodashboard')) {
                echo "\n--- DEBUG: Retailer Registration failed! Page HTML: ---\n";
                echo $browser->driver->getPageSource();
                echo "\n----------------------------------------\n";
            }

            $browser->assertPathIs('/tokodashboard');

            // Verify database
            $user = User::where('email', 'retailer_test@example.com')->first();
            $this->assertNotNull($user);
            $this->assertEquals('retailer', $user->tipe_user);
            $this->assertEquals('pending', $user->status_verifikasi);
            $this->assertNotNull($user->foto_toko);
        });
    }
}
