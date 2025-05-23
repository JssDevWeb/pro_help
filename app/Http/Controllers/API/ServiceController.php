<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    /**
     * Obtiene lista de servicios
     */
    public function index(Request $request)
    {
        $services = Service::with('organization')
            ->when($request->has('type'), function ($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->when($request->has('organization_id'), function ($query) use ($request) {
                $query->where('organization_id', $request->organization_id);
            })
            ->where('is_active', true)
            ->paginate(10);

        return response()->json([
            'data' => $services->items(),
            'meta' => [
                'current_page' => $services->currentPage(),
                'total' => $services->total(),
                'per_page' => $services->perPage(),
                'last_page' => $services->lastPage(),
            ]
        ]);
    }

    /**
     * Búsqueda de servicios por proximidad geográfica
     */
    public function nearby(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'radius' => 'sometimes|numeric|min:100|max:50000',
            'type' => 'sometimes|string',
        ]);

        $lat = $request->lat;
        $lng = $request->lng;
        $radius = $request->radius ?? 5000; // 5km por defecto

        $services = Service::with('organization')
            ->selectRaw("
                services.*, 
                organizations.name as organization_name,
                ST_DistanceSphere(services.location, ST_MakePoint(?, ?)::geography) as distance
            ", [$lng, $lat])
            ->join('organizations', 'services.organization_id', '=', 'organizations.id')
            ->whereRaw("ST_DWithin(services.location::geography, ST_MakePoint(?, ?)::geography, ?)", [$lng, $lat, $radius])
            ->when($request->has('type'), function ($query) use ($request) {
                $query->where('services.type', $request->type);
            })
            ->where('services.is_active', true)
            ->orderBy('distance')
            ->get()
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'organization' => $service->organization_name,
                    'type' => $service->type,
                    'description' => $service->description,
                    'address' => $service->address,
                    'location' => [
                        'lat' => $service->latitude,
                        'lng' => $service->longitude
                    ],
                    'schedule' => $service->schedule,
                    'capacity' => $service->capacity,
                    'distance' => round($service->distance, 0), // distancia en metros
                ];
            });

        return response()->json([
            'data' => $services,
            'search_params' => [
                'latitude' => $lat,
                'longitude' => $lng,
                'radius' => $radius,
                'type' => $request->type,
            ]
        ]);
    }

    /**
     * Obtiene detalles de un servicio específico
     */
    public function show($id)
    {
        $service = Service::with(['organization', 'interventions.beneficiary'])
            ->findOrFail($id);

        return response()->json([
            'id' => $service->id,
            'name' => $service->name,
            'organization' => [
                'id' => $service->organization->id,
                'name' => $service->organization->name,
            ],
            'type' => $service->type,
            'description' => $service->description,
            'address' => $service->address,
            'location' => $service->location,
            'schedule' => $service->schedule,
            'capacity' => $service->capacity,
            'is_active' => $service->is_active,
            'interventions_count' => $service->interventions->count(),
            'active_interventions_count' => $service->interventions->where('status', 'active')->count(),
        ]);
    }

    /**
     * Crea un nuevo servicio
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'organization_id' => 'required|exists:organizations,id',
            'type' => 'required|string|max:100',
            'description' => 'required|string',
            'address' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'schedule' => 'nullable|array',
            'capacity' => 'nullable|integer|min:1',
        ]);

        $service = Service::create($validated);

        return response()->json($service, 201);
    }

    /**
     * Actualiza un servicio
     */
    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'organization_id' => 'sometimes|required|exists:organizations,id',
            'type' => 'sometimes|required|string|max:100',
            'description' => 'sometimes|required|string',
            'address' => 'sometimes|required|string',
            'latitude' => 'sometimes|required|numeric|between:-90,90',
            'longitude' => 'sometimes|required|numeric|between:-180,180',
            'schedule' => 'nullable|array',
            'capacity' => 'nullable|integer|min:1',
            'is_active' => 'sometimes|boolean',
        ]);

        $service->update($validated);

        return response()->json($service);
    }

    /**
     * Elimina un servicio
     */
    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();

        return response()->json(['message' => 'Service deleted successfully']);
    }
}
