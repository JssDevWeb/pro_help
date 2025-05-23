<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BeneficiarySeeder extends Seeder
{
    public function run(): void
    {        $beneficiaries = [
            [
                'name' => 'Juan Pérez',
                'identification' => 'A1234567B',
                'birthdate' => '1985-06-15',
                'gender' => 'male',
                'nationality' => 'Española',
                'spoken_languages' => json_encode(['español', 'inglés']),
                'health_status' => 'Buena salud general',
                'needs' => json_encode(['food', 'shelter']),
                'phone' => '+34600123456',
                'email' => 'juan.perez@mail.com',
                'contact_preference' => 'phone',
                'vulnerability_status' => 'medium',
                'notes' => 'Necesita ayuda con alojamiento temporal',
                'is_active' => true,
            ],
            [
                'name' => 'María García',
                'identification' => 'X8765432Y',
                'birthdate' => '1990-03-21',
                'gender' => 'female',
                'nationality' => 'Colombiana',
                'spoken_languages' => json_encode(['español']),
                'health_status' => 'Requiere medicación regular',
                'needs' => json_encode(['medical', 'job_training']),
                'phone' => '+34600789012',
                'email' => 'maria.garcia@mail.com',
                'contact_preference' => 'email',
                'vulnerability_status' => 'high',
                'notes' => 'Busca formación laboral y asistencia médica',
                'is_active' => true,
            ],
        ];        foreach ($beneficiaries as $beneficiary) {
            $mainFields = [
                'name' => $beneficiary['name'],
                'identification' => $beneficiary['identification'],
                'birthdate' => $beneficiary['birthdate'],
                'gender' => $beneficiary['gender'],
                'nationality' => $beneficiary['nationality'],
                'spoken_languages' => $beneficiary['spoken_languages'],
                'health_status' => $beneficiary['health_status'],
                'needs' => $beneficiary['needs'],
                'phone' => $beneficiary['phone'],
                'email' => $beneficiary['email'],
                'contact_preference' => $beneficiary['contact_preference'],
                'vulnerability_status' => $beneficiary['vulnerability_status'],
                'notes' => $beneficiary['notes'],
                'is_active' => $beneficiary['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Insertar los campos principales
            $beneficiaryId = DB::table('beneficiaries')->insertGetId($mainFields);
            
            // No hay columna last_known_location en la tabla beneficiaries según la migración
            // por lo que eliminamos la parte de actualización de ubicación
        }
    }
}
