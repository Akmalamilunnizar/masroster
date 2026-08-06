<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure 'admin' role exists in roles table
        $roleId = DB::table('roles')->where('name', 'admin')->value('id');
        if (!$roleId) {
            $roleId = DB::table('roles')->insertGetId([
                'name' => 'admin',
                'display_name' => 'Admin',
                'description' => 'Administrator with full permissions',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Create or update admin user
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@masroster.com'],
            [
                'f_name' => 'Admin Masroster',
                'nomor_telepon' => '081234567890',
                'username' => 'admin',
                'password' => Hash::make('password123'),
                'user' => 'Admin',
                'img' => 'default-avatar.png',
                'alamat' => 'Alamat Admin',
                'tipe_user' => 'end_customer',
                'status_verifikasi' => 'approved',
            ]
        );

        // 3. Attach role to user in role_user table
        DB::table('role_user')->updateOrInsert([
            'user_id' => $adminUser->id,
            'role_id' => $roleId,
            'user_type' => User::class,
        ], []);
    }
}
