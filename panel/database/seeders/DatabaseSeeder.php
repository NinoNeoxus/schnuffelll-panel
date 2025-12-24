<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Check if admin exists
        if (!User::where('email', 'admin@schnuffelll.com')->exists()) {
            User::create([
                'name' => 'Schnuffelll Admin',
                'email' => 'admin@schnuffelll.com',
                'password' => Hash::make('password'),
                'root_admin' => true,
            ]);
            $this->command->info('Admin user created: admin@schnuffelll.com / password');
        }
    }
}
