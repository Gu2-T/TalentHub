<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        
        // ✅ Create an Admin User
        $adminUser = User::firstOrCreate(
            ['email' => 'gutu@gmail.com'],
            [
                'name' => 'admin',
                'password' => Hash::make('123456987'),
                'role' => 1,
                'is_verified' => true, // ✅ Set as verified
                'email_verified_at' => now(), // ✅ Mark email as verified
                'remember_token' => Hash::make(Str::random(60)), // ✅ Generate a remember token
            ]
        );

        // If you are using Spatie Roles
        if (method_exists($adminUser, 'assignRole')) {
            $adminUser->assignRole('admin');
        }
    }
}
