<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminProfileTest extends TestCase
{
    public function test_guest_is_redirected_from_admin_profile_routes(): void
    {
        $this->get(route('profile'))->assertRedirect(route('login'));
    }

    public function test_admin_can_update_own_profile_and_password(): void
    {
        $admin = $this->createAdminUser([
            'email' => 'admin@example.test',
            'password' => Hash::make('password'),
        ]);

        $response = $this->actingAs($admin)->post(route('storeprofile'), [
            'f_name' => 'Admin Updated',
            'email' => 'admin.updated@example.com',
            'currentPassword' => 'password',
            'newPassword' => 'new-password',
            'newPassword_confirmation' => 'new-password',
        ]);

        $response->assertRedirect(route('profile'));

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'f_name' => 'Admin Updated',
            'email' => 'admin.updated@example.com',
        ]);

        $this->assertTrue(Hash::check('new-password', $admin->fresh()->password));
    }

    public function test_admin_must_provide_current_password_when_updating_password(): void
    {
        $admin = $this->createAdminUser([
            'email' => 'admin2@example.test',
            'password' => Hash::make('password'),
        ]);

        $response = $this->actingAs($admin)->post(route('storeprofile'), [
            'f_name' => 'Admin Updated',
            'email' => 'admin.updated2@example.com',
            'newPassword' => 'new-password',
            'newPassword_confirmation' => 'new-password',
        ]);

        $response->assertSessionHasErrors(['currentPassword']);
        $this->assertFalse(Hash::check('new-password', $admin->fresh()->password));
    }
}