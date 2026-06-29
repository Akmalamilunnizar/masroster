<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class UserManagementTest extends TestCase
{
    public function test_guest_is_redirected_from_user_management(): void
    {
        $this->get(route('allusers'))->assertRedirect(route('login'));
    }

    public function test_admin_can_search_users_by_name(): void
    {
        $admin = $this->createAdminUser([
            'email' => 'users-admin@example.test',
            'password' => 'password',
        ]);

        $user = $this->createCustomerUser([
            'f_name' => 'User Searchable',
            'email' => 'users-searchable@example.test',
        ]);

        $response = $this->actingAs($admin)->get(route('searchusers', ['search' => 'Searchable']));

        $response->assertOk();
        $response->assertViewHas('users', function ($users) use ($user) {
            return $users->contains('id', $user->id);
        });
    }
}