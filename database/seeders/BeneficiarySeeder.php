<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BeneficiarySeeder extends Seeder
{
    public function run(): void
    {
        $beneficiaries = [
            [
                'name' => 'Juan Pérez',
                'identification' => 'A1234567B',
                'birth_date' => '1985-06-15',
                'gender' => 'male',
                'phone' => '+34600123456',
                'email' => 'juan.perez@mail.com',
                'address' => 'Calle Mayor 15, Madrid',
                'latitude' => 40.4165,
                'longitude' => -3.7026,
                'needs' => json_encode(['food', 'shelter']),
                'status' => 'active',
            ],
            [
                'name' => 'María García',
                'identification' => 'X8765432Y',
                'birth_date' => '1990-03-21',
                'gender' => 'female',
                'phone' => '+34600789012',
                'email' => 'maria.garcia@mail.com',
                'address' => 'Carrer de Sants 45, Barcelona',
                'latitude' => 41.3879,
                'longitude' => 2.1699,
                'needs' => json_encode(['medical', 'job_training']),
                'status' => 'active',
            ],
        ];

        foreach ($beneficiaries as $beneficiary) {
            $mainFields = [
                'name' => $beneficiary['name'],
                'identification' => $beneficiary['identification'],
                'birth_date' => $beneficiary['birth_date'],
                'gender' => $beneficiary['gender'],
                'phone' => $beneficiary['phone'],
                'email' => $beneficiary['email'],
                'address' => $beneficiary['address'],
                'needs' => $beneficiary['needs'],
                'status' => $beneficiary['status'],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Insertar primero los campos principales
            $beneficiaryId = DB::table('beneficiaries')->insertGetId($mainFields);            // Luego actualizar la columna de ubicación
            DB::statement("
                UPDATE beneficiaries 
                SET last_known_location = ST_SetSRID(ST_MakePoint(?, ?), 4326)
                WHERE id = ?
            ", [
                $beneficiary['longitude'],
                $beneficiary['latitude'],
                $beneficiaryId
            ]);
        }
    }
}
