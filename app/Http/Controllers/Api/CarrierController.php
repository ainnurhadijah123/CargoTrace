<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class CarrierController extends Controller
{
    public function index()
    {
        $carriers = Carrier::all();
        return response()->json([
            'success' => true,
            'data' => $carriers,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: Only Administrators can create carriers.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:carriers,code',
            'contact_info' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $carrier = Carrier::create($validated);
        AuditLog::record('CREATED', 'Carrier', "Carrier {$carrier->name} ({$carrier->code}) created by {$user->name}.");

        return response()->json([
            'success' => true,
            'message' => 'Carrier created successfully.',
            'data' => $carrier,
        ], 201);
    }

    public function show($id)
    {
        $carrier = Carrier::find($id);
        if (!$carrier) {
            return response()->json([
                'success' => false,
                'message' => 'Carrier not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $carrier,
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: Only Administrators can update carriers.',
            ], 403);
        }

        $carrier = Carrier::find($id);
        if (!$carrier) {
            return response()->json([
                'success' => false,
                'message' => 'Carrier not found.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:20|unique:carriers,code,' . $carrier->id,
            'contact_info' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $carrier->update($validated);
        AuditLog::record('UPDATED', 'Carrier', "Carrier {$carrier->name} updated by {$user->name}.");

        return response()->json([
            'success' => true,
            'message' => 'Carrier updated successfully.',
            'data' => $carrier,
        ]);
    }
}
