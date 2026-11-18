<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Services\ExceptionService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ExceptionController extends Controller
{
    public function shipmentExceptions($shipmentId, Request $request)
    {
        $user = $request->user();
        $shipment = Shipment::find($shipmentId);

        if (!$shipment) {
            return response()->json([
                'success' => false,
                'message' => 'Shipment not found.',
            ], 404);
        }

        if ($user->isCustomer() && $shipment->customer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: You do not have permission to view exceptions for this shipment.',
            ], 403);
        }

        $exceptions = $shipment->exceptions()->latest()->get();

        return response()->json([
            'success' => true,
            'data' => [
                'shipment_number' => $shipment->shipment_number,
                'exceptions' => $exceptions,
            ],
        ]);
    }

    public function store($shipmentId, Request $request, ExceptionService $exceptionService)
    {
        $user = $request->user();
        if (!$user->isStaff()) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: Only Logistics Operators and Administrators can report exceptions.',
            ], 403);
        }

        $shipment = Shipment::find($shipmentId);
        if (!$shipment) {
            return response()->json([
                'success' => false,
                'message' => 'Shipment not found.',
            ], 404);
        }

        $validated = $request->validate([
            'type' => 'required|string|in:' . implode(',', ExceptionService::VALID_TYPES),
            'severity' => 'required|string|in:LOW,MEDIUM,HIGH',
            'description' => 'required|string|max:1000',
            'location_id' => 'nullable|exists:locations,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        try {
            $exception = $exceptionService->reportException($shipment, $validated, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Shipment exception reported successfully.',
                'data' => [
                    'exception' => $exception,
                    'shipment_status' => $shipment->fresh()->status,
                ],
            ], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function update($id, Request $request, ExceptionService $exceptionService)
    {
        $user = $request->user();
        if (!$user->isStaff()) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: Only Logistics Operators and Administrators can modify exceptions.',
            ], 403);
        }

        $exception = ShipmentException::find($id);
        if (!$exception) {
            return response()->json([
                'success' => false,
                'message' => 'Shipment exception not found.',
            ], 404);
        }

        $validated = $request->validate([
            'status' => 'required|string|in:OPEN,INVESTIGATING,RESOLVED',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $updated = $exceptionService->updateStatus($exception, $validated['status'], $validated['notes'] ?? null, $user->id);

            return response()->json([
                'success' => true,
                'message' => "Exception updated to status {$validated['status']}.",
                'data' => [
                    'exception' => $updated,
                    'shipment_status' => $exception->shipment->fresh()->status,
                ],
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
