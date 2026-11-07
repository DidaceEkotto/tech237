<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::updateOrCreate(
            ['email' => 'admin@admin.test'],
            [
                'name' => 'jeannot gates',
                'password' => Hash::make('000000'),
                'role' => 'super_admin',
                'is_banned' => false,
                'failed_login_attempts' => 0,
                'default_password' => 0,
                'avatar' => null,
                'bio' => 'Super Administrateur Tech237.',
            ]
        );
    }
}
