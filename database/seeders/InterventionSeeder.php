<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InterventionSeeder extends Seeder
{
    public function run(): void
    {        $interventions = [
            [
                'beneficiary_id' => 1, // Juan Pérez
                'service_id' => 1, // Comedor Social Madrid Centro
                'user_id' => 1, // Suponemos que existe un usuario con ID 1
                'status' => 'scheduled',
                'scheduled_date' => '2024-05-22 10:00:00',
                'started_date' => null,
                'completed_date' => null,
                'notes' => 'Necesita apoyo alimentario diario',
                'outcome' => null,
                'follow_up' => 'Seguimiento semanal',
                'follow_up_date' => '2024-06-01 10:00:00',
            ],
            [
                'beneficiary_id' => 2, // María García
                'service_id' => 2, // Albergue Barcelona
                'user_id' => 1, // Mismo usuario
                'status' => 'in_progress',
                'scheduled_date' => '2024-05-23 14:00:00',
                'started_date' => '2024-05-23 14:30:00',
                'completed_date' => null,
                'notes' => 'Solicitud de alojamiento temporal',
                'outcome' => null,
                'follow_up' => 'Revisar situación en una semana',
                'follow_up_date' => '2024-05-30 14:00:00',
            ],
        ];

        foreach ($interventions as $intervention) {
            DB::table('interventions')->insert([
                'beneficiary_id' => $intervention['beneficiary_id'],
                'service_id' => $intervention['service_id'],
                'user_id' => $intervention['user_id'],
                'status' => $intervention['status'],
                'scheduled_date' => $intervention['scheduled_date'],
                'started_date' => $intervention['started_date'],
                'completed_date' => $intervention['completed_date'],
                'notes' => $intervention['notes'],
                'outcome' => $intervention['outcome'],
                'follow_up' => $intervention['follow_up'],
                'follow_up_date' => $intervention['follow_up_date'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}