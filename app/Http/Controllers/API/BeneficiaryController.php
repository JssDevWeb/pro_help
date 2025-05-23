<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Beneficiary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BeneficiaryController extends Controller
{
    /**
     * Obtiene lista de beneficiarios
     */
    public function index(Request $request)
    {
        $beneficiaries = Beneficiary::with('interventions.service')
            ->when($request->has('search'), function ($query) use ($request) {
                $query->where('name', 'ILIKE', '%' . $request->search . '%')
                      ->orWhere('identification', 'ILIKE', '%' . $request->search . '%');
            })
            ->when($request->has('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->has('gender'), function ($query) use ($request) {
                $query->where('gender', $request->gender);
            })
            ->paginate(10);

        return response()->json([
            'data' => $beneficiaries->map(function ($beneficiary) {
                return [
                    'id' => $beneficiary->id,
                    'name' => $beneficiary->name,
                    'identification' => $beneficiary->identification,
                    'birth_date' => $beneficiary->birth_date,
                    'gender' => $beneficiary->gender,
                    'phone' => $beneficiary->phone,
                    'email' => $beneficiary->email,
                    'needs' => $beneficiary->needs,
                    'status' => $beneficiary->status,
                    'active_interventions_count' => $beneficiary->interventions->where('status', 'active')->count(),
                ];
            }),
            'meta' => [
                'current_page' => $beneficiaries->currentPage(),
                'total' => $beneficiaries->total(),
                'per_page' => $beneficiaries->perPage(),
                'last_page' => $beneficiaries->lastPage(),
            ]
        ]);
    }

    /**
     * Obtiene detalles de un beneficiario específico
     */
    public function show($id)
    {
        $beneficiary = Beneficiary::with(['interventions.service.organization'])
            ->findOrFail($id);

        return response()->json([
            'id' => $beneficiary->id,
            'name' => $beneficiary->name,
            'identification' => $beneficiary->identification,
            'birth_date' => $beneficiary->birth_date,
            'gender' => $beneficiary->gender,
            'phone' => $beneficiary->phone,
            'email' => $beneficiary->email,
            'address' => $beneficiary->address,
            'location' => [
                'lat' => $beneficiary->latitude,
                'lng' => $beneficiary->longitude
            ],
            'needs' => $beneficiary->needs,
            'status' => $beneficiary->status,
            'notes' => $beneficiary->notes,
            'interventions' => $beneficiary->interventions->map(function ($intervention) {
                return [
                    'id' => $intervention->id,
                    'service' => [
                        'id' => $intervention->service->id,
                        'name' => $intervention->service->name,
                        'organization' => $intervention->service->organization->name,
                    ],
                    'start_date' => $intervention->start_date,
                    'end_date' => $intervention->end_date,
                    'status' => $intervention->status,
                    'type' => $intervention->type,
                    'notes' => $intervention->notes,
                ];
            }),
        ]);
    }

    /**
     * Crea un nuevo beneficiario
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'identification' => 'required|string|unique:beneficiaries',
            'birth_date' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'address' => 'required|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'needs' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $beneficiary = Beneficiary::create($validated);

        return response()->json($beneficiary, 201);
    }

    /**
     * Actualiza un beneficiario
     */
    public function update(Request $request, $id)
    {
        $beneficiary = Beneficiary::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'identification' => 'sometimes|required|string|unique:beneficiaries,identification,' . $beneficiary->id,
            'birth_date' => 'sometimes|required|date',
            'gender' => 'sometimes|required|in:male,female,other',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'address' => 'sometimes|required|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'needs' => 'nullable|array',
            'status' => 'sometimes|in:active,inactive,archived',
            'notes' => 'nullable|string',
        ]);

        $beneficiary->update($validated);

        return response()->json($beneficiary);
    }

    /**
     * Elimina un beneficiario
     */
    public function destroy($id)
    {
        $beneficiary = Beneficiary::findOrFail($id);
        $beneficiary->delete();

        return response()->json(['message' => 'Beneficiary deleted successfully']);
    }
}
