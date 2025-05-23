<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class GeospatialStatsController extends Controller
{
    /**
     * Obtiene estadísticas geoespaciales generales
     */
    public function getGeospatialStats(): JsonResponse
    {
        try {
            $totalOrganizations = Organization::count();
            $totalServices = Service::count();
            
            // Calcular beneficiarios estimados (ejemplo: cada servicio atiende 10 beneficiarios promedio)
            $totalBeneficiaries = $totalServices * 10;
            
            // Distribución por tipo de servicio
            $serviceTypeDistribution = Service::select('type', DB::raw('count(*) as count'))
                ->groupBy('type')
                ->get()
                ->map(function ($item) {
                    return [
                        'type' => $item->type,
                        'count' => $item->count
                    ];
                });
            
            return response()->json([
                'totalOrganizations' => $totalOrganizations,
                'totalServices' => $totalServices,
                'totalBeneficiaries' => $totalBeneficiaries,
                'serviceTypeDistribution' => $serviceTypeDistribution
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener estadísticas geoespaciales',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtiene los tipos de servicios disponibles
     */
    public function getServiceTypes(): JsonResponse
    {
        try {
            $serviceTypes = Service::distinct('type')
                ->whereNotNull('type')
                ->pluck('type')
                ->filter()
                ->values();
            
            return response()->json([
                'data' => $serviceTypes
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener tipos de servicios',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtiene datos para mapa de calor de densidad de servicios
     */
    public function getServiceDensity(): JsonResponse
    {
        try {
            // Agrupa servicios por proximidad geográfica para crear puntos de densidad
            $densityData = Service::select(
                DB::raw('ROUND(latitude, 3) as lat_rounded'),
                DB::raw('ROUND(longitude, 3) as lng_rounded'),
                DB::raw('COUNT(*) as intensity'),
                DB::raw('AVG(latitude) as latitude'),
                DB::raw('AVG(longitude) as longitude')
            )
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->groupBy('lat_rounded', 'lng_rounded')
            ->havingRaw('COUNT(*) > 0')
            ->get()
            ->map(function ($item) {
                return [
                    'latitude' => floatval($item->latitude),
                    'longitude' => floatval($item->longitude),
                    'intensity' => min(intval($item->intensity), 10) // Limitar intensidad a 10
                ];
            });
            
            return response()->json($densityData);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener datos de densidad',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtiene estadísticas filtradas por tipo de servicio
     */
    public function getServiceTypeStats(string $serviceType): JsonResponse
    {
        try {
            $services = Service::where('type', $serviceType);
            $totalServices = $services->count();
            
            // Obtener organizaciones que proveen este tipo de servicio
            $organizationIds = $services->distinct('organization_id')->pluck('organization_id');
            $totalOrganizations = $organizationIds->count();
            
            // Beneficiarios estimados para este tipo de servicio
            $totalBeneficiaries = $totalServices * 8; // Ajuste específico por tipo
            
            return response()->json([
                'totalOrganizations' => $totalOrganizations,
                'totalServices' => $totalServices,
                'totalBeneficiaries' => $totalBeneficiaries,
                'serviceTypeDistribution' => [
                    [
                        'type' => $serviceType,
                        'count' => $totalServices
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener estadísticas del tipo de servicio',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
