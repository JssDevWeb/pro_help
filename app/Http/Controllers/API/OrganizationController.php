<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrganizationController extends Controller
{
    /**
     * Obtiene lista de organizaciones
     */
    public function index(Request $request)
    {
        $organizations = Organization::with('services')
            ->when($request->has('search'), function ($query) use ($request) {
                $query->where('name', 'ILIKE', '%' . $request->search . '%')
                      ->orWhere('description', 'ILIKE', '%' . $request->search . '%');
            })
            ->paginate(10);

        return response()->json([
            'data' => $organizations->items(),
            'meta' => [
                'current_page' => $organizations->currentPage(),
                'total' => $organizations->total(),
                'per_page' => $organizations->perPage(),
                'last_page' => $organizations->lastPage(),
            ]
        ]);
    }

    /**
     * Obtiene detalles de una organización específica
     */
    public function show($id)
    {
        $organization = Organization::with(['services', 'users'])
            ->findOrFail($id);

        return response()->json([
            'id' => $organization->id,
            'name' => $organization->name,
            'description' => $organization->description,
            'email' => $organization->email,
            'phone' => $organization->phone,
            'address' => $organization->address,
            'location' => $organization->location,
            'services' => $organization->services->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'type' => $service->type,
                    'is_active' => $service->is_active,
                ];
            }),
            'users_count' => $organization->users->count(),
        ]);
    }

    /**
     * Crea una nueva organización
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'email' => 'required|email|unique:organizations',
            'phone' => 'nullable|string|max:20',
            'address' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $organization = Organization::create($validated);

        return response()->json($organization, 201);
    }

    /**
     * Actualiza una organización
     */
    public function update(Request $request, $id)
    {
        $organization = Organization::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'email' => 'sometimes|required|email|unique:organizations,email,' . $organization->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'sometimes|required|string',
            'latitude' => 'sometimes|required|numeric|between:-90,90',
            'longitude' => 'sometimes|required|numeric|between:-180,180',
        ]);

        $organization->update($validated);

        return response()->json($organization);
    }

    /**
     * Elimina una organización
     */
    public function destroy($id)
    {
        $organization = Organization::findOrFail($id);
        $organization->delete();

        return response()->json(['message' => 'Organization deleted successfully']);
    }
}
