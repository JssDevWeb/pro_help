<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InterventionSeeder extends Seeder
{
    public function run(): void
    {
        $interventions = [
            [
                'beneficiary_id' => 1, // Juan Pérez
                'service_id' => 1, // Comedor Social Madrid Centro
                'status' => 'active',
                'start_date' => '2024-05-22',
                'end_date' => '2024-06-22',
                'notes' => 'Necesita apoyo alimentario diario',
                'type' => 'food_assistance',
            ],
            [
                'beneficiary_id' => 2, // María García
                'service_id' => 2, // Albergue Barcelona
                'status' => 'pending',
                'start_date' => '2024-05-23',
                'end_date' => '2024-06-23',
                'notes' => 'Solicitud de alojamiento temporal',
                'type' => 'temporary_shelter',
            ],
        ];

        foreach ($interventions as $intervention) {
            DB::table('interventions')->insert([
                'beneficiary_id' => $intervention['beneficiary_id'],
                'service_id' => $intervention['service_id'],
                'status' => $intervention['status'],
                'start_date' => $intervention['start_date'],
                'end_date' => $intervention['end_date'],
                'notes' => $intervention['notes'],
                'type' => $intervention['type'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}