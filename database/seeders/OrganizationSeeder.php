<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $organizations = [
            [
                'name' => 'Cruz Roja Madrid',
                'description' => 'Organización humanitaria en Madrid',
                'email' => 'contacto@cruzrojamadrid.es',
                'phone' => '+34911234567',
                'address' => 'Calle del Prado, 21, Madrid',
                'latitude' => 40.4168,
                'longitude' => -3.7038,
            ],
            [
                'name' => 'Cáritas Barcelona',
                'description' => 'Servicios sociales en Barcelona',
                'email' => 'info@caritasbarcelona.org',
                'phone' => '+34932234567',
                'address' => 'Via Laietana, 5, Barcelona',
                'latitude' => 41.3851,
                'longitude' => 2.1734,
            ],
        ];        foreach ($organizations as $org) {            DB::statement("
                INSERT INTO organizations (
                    name, description, email, phone, address, 
                    latitude, longitude, location, created_at, updated_at
                ) VALUES (
                    ?, ?, ?, ?, ?,
                    ?, ?, ST_SetSRID(ST_MakePoint(?, ?), 4326),
                    NOW(), NOW()
                )
            ", [
                $org['name'],
                $org['description'],
                $org['email'],
                $org['phone'],
                $org['address'],
                $org['latitude'],
                $org['longitude'],
                $org['longitude'],  // Longitud primero para Point(X, Y)
                $org['latitude'],   // Latitud después
            ]);
        }
    }
}
