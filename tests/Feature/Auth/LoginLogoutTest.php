<?php

namespace Tests\Feature\Auth;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LoginLogoutTest extends TestCase
{
    public function test_customer_login_redirects_to_tokodashboard(): void
    {
        $user = $this->createCustomerUser([
            'email' => 'customer@example.test',
            'password' => 'password',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/tokodashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_login_redirects_to_admin_dashboard(): void
    {
        $user = $this->createAdminUser([
            'email' => 'admin@example.test',
            'password' => 'password',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_with_wrong_password_is_rejected(): void
    {
        $user = $this->createCustomerUser([
            'email' => 'fail-login@example.test',
            'password' => 'password',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_register_creates_user_and_assigns_user_role(): void
    {
        $response = $this->post('/register', [
            'name' => 'New Customer',
            'email' => 'new-customer@example.test',
            'nomor_telepon' => '081234567891',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/tokodashboard');

        $this->assertDatabaseHas('users', [
            'email' => 'new-customer@example.test',
            'f_name' => 'New Customer',
            'user' => 'User',
        ]);

        $this->assertDatabaseHas('role_user', [
            'role_id' => 2,
            'user_type' => \App\Models\User::class,
        ]);
    }

    public function test_retailer_register_requires_photo_and_sets_retailer_fields(): void
    {
        Storage::fake('public');

        $response = $this->post('/register', [
            'name' => 'New Retailer',
            'email' => 'new-retailer@example.test',
            'nomor_telepon' => '081234567892',
            'tipe_user' => 'retailer',
            'foto_toko' => UploadedFile::fake()->createWithContent(
                'foto-toko.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO7+Q1kAAAAASUVORK5CYII=')
            ),
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/tokodashboard');

        $user = \App\Models\User::where('email', 'new-retailer@example.test')->firstOrFail();

        $this->assertSame('retailer', $user->tipe_user);
        $this->assertSame('pending', $user->status_verifikasi);
        $this->assertNotEmpty($user->foto_toko);
        $this->assertTrue(Storage::disk('public')->exists($user->foto_toko));

        $this->assertDatabaseHas('role_user', [
            'role_id' => 2,
            'user_type' => \App\Models\User::class,
        ]);
    }

    public function test_logout_invalidates_the_session(): void
    {
        $user = $this->createCustomerUser([
            'email' => 'logout@example.test',
            'password' => 'password',
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }
}
