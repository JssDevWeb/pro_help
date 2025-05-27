<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@shelterconnect.org',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Organization Manager',
            'email' => 'manager@cruzrojamadrid.es',
            'password' => Hash::make('password'),
            'role' => 'organization_manager',
            'organization_id' => 1,
        ]);

        User::create([
            'name' => 'Service Provider',
            'email' => 'provider@caritasbarcelona.org',
            'password' => Hash::make('password'),
            'role' => 'service_provider',
            'organization_id' => 2,
        ]);
    }
}