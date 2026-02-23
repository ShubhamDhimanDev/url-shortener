<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@platform.test'],
            [
                'ulid'              => (string) Str::ulid(),
                'name'              => 'Super Admin',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
                'timezone'          => 'Asia/Kolkata',
                'locale'            => 'en',
                'is_active'         => true,
            ]
        );

        $admin->assignRole('super_admin');

        $this->command->info("Admin user ready: {$admin->email}");
    }
}
