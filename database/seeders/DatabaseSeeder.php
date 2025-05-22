<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            OrganizationSeeder::class,
            UserSeeder::class,
            ServiceSeeder::class,
            BeneficiarySeeder::class,
            InterventionSeeder::class,
        ]);
    }
}
