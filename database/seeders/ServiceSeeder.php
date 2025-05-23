<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'name' => 'Comedor Social Madrid Centro',
                'description' => 'Servicio de comidas diarias',
                'type' => 'food',
                'capacity' => 100,
                'address' => 'Calle Gran Vía 31, Madrid',
                'latitude' => 40.4200,
                'longitude' => -3.7025,
                'organization_id' => 1, // Cruz Roja Madrid
                'schedule' => json_encode([
                    'monday' => '9:00-17:00',
                    'tuesday' => '9:00-17:00',
                    'wednesday' => '9:00-17:00',
                    'thursday' => '9:00-17:00',
                    'friday' => '9:00-17:00',
                ]),
                'is_active' => true,
            ],
            [
                'name' => 'Albergue Barcelona',
                'description' => 'Alojamiento temporal',
                'type' => 'shelter',
                'capacity' => 50,
                'address' => 'Carrer de Balmes 24, Barcelona',
                'latitude' => 41.3870,
                'longitude' => 2.1700,
                'organization_id' => 2, // Cáritas Barcelona
                'schedule' => json_encode([
                    'monday' => '24h',
                    'tuesday' => '24h',
                    'wednesday' => '24h',
                    'thursday' => '24h',
                    'friday' => '24h',
                    'saturday' => '24h',
                    'sunday' => '24h',
                ]),
                'is_active' => true,
            ],
        ];        foreach ($services as $service) {
            $mainFields = [
                'name' => $service['name'],
                'description' => $service['description'],
                'type' => $service['type'],
                'capacity' => $service['capacity'],
                'address' => $service['address'],
                'latitude' => $service['latitude'],
                'longitude' => $service['longitude'],
                'organization_id' => $service['organization_id'],
                'availability' => $service['schedule'] ?? null, // Usando availability en lugar de schedule
                'requirements' => null,
                'is_active' => $service['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Insertar primero los campos principales
            $serviceId = DB::table('services')->insertGetId($mainFields);

            // Luego actualizar la columna de ubicación
            DB::statement("
                UPDATE services 
                SET location = ST_SetSRID(ST_MakePoint(?, ?), 4326)
                WHERE id = ?
            ", [
                $service['longitude'],
                $service['latitude'],
                $serviceId
            ]);
        }
    }
}
