<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Intervention;
use Illuminate\Http\Request;

class InterventionController extends Controller
{
    /**
     * Obtiene lista de intervenciones
     */
    public function index(Request $request)
    {
        $interventions = Intervention::with(['beneficiary', 'service.organization', 'user'])
            ->when($request->has('beneficiary_id'), function ($query) use ($request) {
                $query->where('beneficiary_id', $request->beneficiary_id);
            })
            ->when($request->has('service_id'), function ($query) use ($request) {
                $query->where('service_id', $request->service_id);
            })
            ->when($request->has('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->has('type'), function ($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'data' => $interventions->map(function ($intervention) {
                return [
                    'id' => $intervention->id,
                    'beneficiary' => [
                        'id' => $intervention->beneficiary->id,
                        'name' => $intervention->beneficiary->name,
                        'identification' => $intervention->beneficiary->identification,
                    ],
                    'service' => [
                        'id' => $intervention->service->id,
                        'name' => $intervention->service->name,
                        'organization' => $intervention->service->organization->name,
                    ],
                    'user' => $intervention->user ? [
                        'id' => $intervention->user->id,
                        'name' => $intervention->user->name,
                    ] : null,
                    'start_date' => $intervention->start_date,
                    'end_date' => $intervention->end_date,
                    'status' => $intervention->status,
                    'type' => $intervention->type,
                    'notes' => $intervention->notes,
                    'created_at' => $intervention->created_at,
                ];
            }),
            'meta' => [
                'current_page' => $interventions->currentPage(),
                'total' => $interventions->total(),
                'per_page' => $interventions->perPage(),
                'last_page' => $interventions->lastPage(),
            ]
        ]);
    }

    /**
     * Obtiene detalles de una intervención específica
     */
    public function show($id)
    {
        $intervention = Intervention::with(['beneficiary', 'service.organization', 'user'])
            ->findOrFail($id);

        return response()->json([
            'id' => $intervention->id,
            'beneficiary' => [
                'id' => $intervention->beneficiary->id,
                'name' => $intervention->beneficiary->name,
                'identification' => $intervention->beneficiary->identification,
                'phone' => $intervention->beneficiary->phone,
                'email' => $intervention->beneficiary->email,
            ],
            'service' => [
                'id' => $intervention->service->id,
                'name' => $intervention->service->name,
                'type' => $intervention->service->type,
                'address' => $intervention->service->address,
                'organization' => [
                    'id' => $intervention->service->organization->id,
                    'name' => $intervention->service->organization->name,
                ],
            ],
            'user' => $intervention->user ? [
                'id' => $intervention->user->id,
                'name' => $intervention->user->name,
                'email' => $intervention->user->email,
            ] : null,
            'start_date' => $intervention->start_date,
            'end_date' => $intervention->end_date,
            'status' => $intervention->status,
            'type' => $intervention->type,
            'notes' => $intervention->notes,
            'created_at' => $intervention->created_at,
            'updated_at' => $intervention->updated_at,
        ]);
    }

    /**
     * Crea una nueva intervención
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'beneficiary_id' => 'required|exists:beneficiaries,id',
            'service_id' => 'required|exists:services,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'type' => 'required|string|max:100',
            'status' => 'sometimes|in:pending,active,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        // Asignar el usuario autenticado
        $validated['user_id'] = auth()->id();
        $validated['status'] = $validated['status'] ?? 'pending';

        $intervention = Intervention::create($validated);

        return response()->json($intervention->load(['beneficiary', 'service', 'user']), 201);
    }

    /**
     * Actualiza una intervención
     */
    public function update(Request $request, $id)
    {
        $intervention = Intervention::findOrFail($id);

        $validated = $request->validate([
            'beneficiary_id' => 'sometimes|required|exists:beneficiaries,id',
            'service_id' => 'sometimes|required|exists:services,id',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'type' => 'sometimes|required|string|max:100',
            'status' => 'sometimes|in:pending,active,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        $intervention->update($validated);

        return response()->json($intervention->load(['beneficiary', 'service', 'user']));
    }

    /**
     * Elimina una intervención
     */
    public function destroy($id)
    {
        $intervention = Intervention::findOrFail($id);
        $intervention->delete();

        return response()->json(['message' => 'Intervention deleted successfully']);
    }
}
