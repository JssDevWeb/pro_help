<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class StatsController extends Controller
{
    /**
     * Obtener estadísticas generales del sistema
     */
    public function index()
    {
        try {
            $stats = Cache::remember('system_stats', 300, function () {
                return [
                    'organizations' => DB::table('organizations')->count(),
                    'services' => DB::table('services')->count(),
                    'beneficiaries' => DB::table('beneficiaries')->count(),
                    'interventions' => DB::table('interventions')->count(),
                    'users' => DB::table('users')->count(),
                    'active_interventions' => DB::table('interventions')
                        ->whereIn('status', ['scheduled', 'in_progress'])
                        ->count(),
                    'completed_interventions' => DB::table('interventions')
                        ->where('status', 'completed')
                        ->count(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Estadísticas obtenidas correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar el estado de salud del sistema
     */
    public function health()
    {
        try {
            // Verificar conexión a base de datos
            $dbStatus = 'ok';
            try {
                DB::connection()->getPdo();
            } catch (\Exception $e) {
                $dbStatus = 'error: ' . $e->getMessage();
            }

            // Verificar cache
            $cacheStatus = 'ok';
            try {
                Cache::put('health_check', time(), 1);
                Cache::get('health_check');
            } catch (\Exception $e) {
                $cacheStatus = 'error: ' . $e->getMessage();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'status' => 'healthy',
                    'timestamp' => now()->toISOString(),
                    'database' => $dbStatus,
                    'cache' => $cacheStatus,
                    'php_version' => PHP_VERSION,
                    'laravel_version' => app()->version(),
                ],
                'message' => 'Sistema funcionando correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => [
                    'status' => 'unhealthy',
                    'timestamp' => now()->toISOString(),
                    'error' => $e->getMessage()
                ],
                'message' => 'Error en verificación de salud'
            ], 500);
        }
    }

    /**
     * Obtener estadísticas por servicio
     */
    public function serviceStats()
    {
        try {
            $serviceStats = DB::table('services')
                ->leftJoin('interventions', 'services.id', '=', 'interventions.service_id')
                ->select(
                    'services.id',
                    'services.name',
                    'services.type',
                    DB::raw('COUNT(interventions.id) as total_interventions'),
                    DB::raw('COUNT(CASE WHEN interventions.status = "completed" THEN 1 END) as completed_interventions'),
                    DB::raw('COUNT(CASE WHEN interventions.status = "in_progress" THEN 1 END) as active_interventions')
                )
                ->groupBy('services.id', 'services.name', 'services.type')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $serviceStats,
                'message' => 'Estadísticas por servicio obtenidas correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas por servicio: ' . $e->getMessage()
            ], 500);
        }
    }
}
