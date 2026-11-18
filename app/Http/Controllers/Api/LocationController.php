<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::all();
        return response()->json([
            'success' => true,
            'data' => $locations,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: Only Administrators can create locations.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:locations,code',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $location = Location::create($validated);
        AuditLog::record('CREATED', 'Location', "Location {$location->name} ({$location->code}) created by {$user->name}.");

        return response()->json([
            'success' => true,
            'message' => 'Location created successfully.',
            'data' => $location,
        ], 201);
    }

    public function show($id)
    {
        $location = Location::find($id);
        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'Location not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $location,
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: Only Administrators can update locations.',
            ], 403);
        }

        $location = Location::find($id);
        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'Location not found.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:10|unique:locations,code,' . $location->id,
            'city' => 'sometimes|required|string|max:255',
            'country' => 'sometimes|required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $location->update($validated);
        AuditLog::record('UPDATED', 'Location', "Location {$location->name} updated by {$user->name}.");

        return response()->json([
            'success' => true,
            'message' => 'Location updated successfully.',
            'data' => $location,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: Only Administrators can delete locations.',
            ], 403);
        }

        $location = Location::find($id);
        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'Location not found.',
            ], 404);
        }

        $locationName = $location->name;
        $location->delete();
        AuditLog::record('DELETED', 'Location', "Location {$locationName} deleted by {$user->name}.");

        return response()->json([
            'success' => true,
            'message' => 'Location deleted successfully.',
        ]);
    }
}
