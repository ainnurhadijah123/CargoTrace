<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Services\ShipmentService;
use App\Services\TrackingService;
use App\Services\EtaService;
use App\Services\External\WeatherService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ShipmentController extends Controller
{
    public function index(Request $request, EtaService $etaService)
    {
        $user = $request->user();
        $query = Shipment::with(['origin', 'destination', 'carrier', 'customer']);

        // Scope to customer's own shipments
        if ($user->isCustomer()) {
            $query->where('customer_id', $user->id);
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', strtoupper($request->status));
        }

        if ($request->filled('carrier_id')) {
            $query->where('carrier_id', $request->carrier_id);
        }

        if ($request->filled('origin_id')) {
            $query->where('origin_id', $request->origin_id);
        }

        if ($request->filled('destination_id')) {
            $query->where('destination_id', $request->destination_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('shipment_number', 'like', "%{$search}%")
                  ->orWhere('cargo_type', 'like', "%{$search}%");
            });
        }

        $perPage = min((int) ($request->get('per_page', 15)), 100);
        $paginated = $query->latest()->paginate($perPage);

        // Append delay calculation summary
        $items = collect($paginated->items())->map(function ($shipment) use ($etaService) {
            $delay = $etaService->calculateDelay($shipment);
            return [
                'id' => $shipment->id,
                'shipment_number' => $shipment->shipment_number,
                'status' => $shipment->status,
                'progress_percent' => $shipment->progress_percent,
                'customer' => [
                    'id' => $shipment->customer?->id,
                    'name' => $shipment->customer?->name,
                ],
                'carrier' => [
                    'id' => $shipment->carrier?->id,
                    'name' => $shipment->carrier?->name,
                    'code' => $shipment->carrier?->code,
                ],
                'origin' => [
                    'id' => $shipment->origin?->id,
                    'name' => $shipment->origin?->name,
                    'city' => $shipment->origin?->city,
                ],
                'destination' => [
                    'id' => $shipment->destination?->id,
                    'name' => $shipment->destination?->name,
                    'city' => $shipment->destination?->city,
                ],
                'cargo_type' => $shipment->cargo_type,
                'weight_kg' => (float) $shipment->weight_kg,
                'expected_arrival_at' => $shipment->expected_arrival_at?->toIso8601String(),
                'delay_summary' => [
                    'is_delayed' => $delay['is_delayed'],
                    'severity' => $delay['severity'],
                    'delay_hours' => $delay['delay_hours'],
                ],
                'created_at' => $shipment->created_at->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $items,
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
            ],
        ]);
    }

    public function store(Request $request, ShipmentService $service)
    {
        $user = $request->user();
        if (!$user->isStaff()) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: Only Logistics Operators and Administrators can create shipments.',
            ], 403);
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:users,id',
            'carrier_id' => 'required|exists:carriers,id',
            'origin_id' => 'required|exists:locations,id',
            'destination_id' => 'required|exists:locations,id|different:origin_id',
            'cargo_type' => 'nullable|string|max:100',
            'weight_kg' => 'nullable|numeric|min:0.01',
            'expected_departure_at' => 'nullable|date',
            'expected_arrival_at' => 'nullable|date|after_or_equal:expected_departure_at',
            'shipping_cost' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'items' => 'nullable|array',
            'items.*.description' => 'required_with:items|string',
            'items.*.weight_kg' => 'nullable|numeric',
            'items.*.quantity' => 'nullable|integer|min:1',
        ]);

        $shipment = $service->create($validated, $user);

        return response()->json([
            'success' => true,
            'message' => 'Shipment created successfully.',
            'data' => $shipment->load(['origin', 'destination', 'carrier', 'customer', 'items', 'trackingEvents']),
        ], 201);
    }

    public function show($id, Request $request, EtaService $etaService, WeatherService $weatherService)
    {
        $user = $request->user();
        $shipment = Shipment::with([
            'origin',
            'destination',
            'carrier',
            'customer',
            'items',
            'chronologicalEvents.location',
            'chronologicalEvents.creator',
            'exceptions',
        ])->find($id);

        if (!$shipment) {
            return response()->json([
                'success' => false,
                'message' => 'Shipment not found.',
            ], 404);
        }

        // Customer access authorization
        if ($user->isCustomer() && $shipment->customer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: You do not have permission to view this shipment.',
            ], 403);
        }

        $delay = $etaService->calculateDelay($shipment);

        // Fetch weather for latest checkpoint or origin
        $latestEvent = $shipment->trackingEvents->first();
        $lat = $latestEvent?->latitude ?? $shipment->origin?->latitude ?? 1.2644;
        $lng = $latestEvent?->longitude ?? $shipment->origin?->longitude ?? 103.8403;
        $weather = $weatherService->getWeather((float) $lat, (float) $lng);

        return response()->json([
            'success' => true,
            'data' => [
                'shipment' => $shipment,
                'delay_analysis' => $delay,
                'current_weather' => $weather,
            ],
        ]);
    }

    public function update($id, Request $request, ShipmentService $service)
    {
        $user = $request->user();
        if (!$user->isStaff()) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: Only Logistics Operators and Administrators can update shipments.',
            ], 403);
        }

        $shipment = Shipment::find($id);
        if (!$shipment) {
            return response()->json([
                'success' => false,
                'message' => 'Shipment not found.',
            ], 404);
        }

        $validated = $request->validate([
            'carrier_id' => 'sometimes|exists:carriers,id',
            'origin_id' => 'sometimes|exists:locations,id',
            'destination_id' => 'sometimes|exists:locations,id',
            'cargo_type' => 'sometimes|nullable|string',
            'weight_kg' => 'sometimes|nullable|numeric',
            'expected_departure_at' => 'sometimes|nullable|date',
            'expected_arrival_at' => 'sometimes|nullable|date',
            'shipping_cost' => 'sometimes|nullable|numeric',
            'currency' => 'sometimes|nullable|string|size:3',
        ]);

        $updated = $service->update($shipment, $validated, $user);

        return response()->json([
            'success' => true,
            'message' => 'Shipment updated successfully.',
            'data' => $updated,
        ]);
    }

    public function destroy($id, Request $request, ShipmentService $service)
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: Only Administrators can delete shipments.',
            ], 403);
        }

        $shipment = Shipment::find($id);
        if (!$shipment) {
            return response()->json([
                'success' => false,
                'message' => 'Shipment not found.',
            ], 404);
        }

        $service->delete($shipment, $user);

        return response()->json([
            'success' => true,
            'message' => 'Shipment deleted successfully.',
        ]);
    }

    public function tracking($id, Request $request)
    {
        $user = $request->user();
        $shipment = Shipment::find($id);

        if (!$shipment) {
            return response()->json([
                'success' => false,
                'message' => 'Shipment not found.',
            ], 404);
        }

        if ($user->isCustomer() && $shipment->customer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: You do not have permission to view tracking for this shipment.',
            ], 403);
        }

        $events = $shipment->chronologicalEvents()->with(['location', 'creator'])->get();

        return response()->json([
            'success' => true,
            'data' => [
                'shipment_number' => $shipment->shipment_number,
                'current_status' => $shipment->status,
                'events' => $events,
            ],
        ]);
    }

    public function addTracking($id, Request $request, TrackingService $trackingService)
    {
        $user = $request->user();
        if (!$user->isStaff()) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: Only staff members can append tracking events.',
            ], 403);
        }

        $shipment = Shipment::find($id);
        if (!$shipment) {
            return response()->json([
                'success' => false,
                'message' => 'Shipment not found.',
            ], 404);
        }

        $validated = $request->validate([
            'status' => 'required|string',
            'location_id' => 'nullable|exists:locations,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'description' => 'nullable|string',
            'event_time' => 'nullable|date',
        ]);

        try {
            $event = $trackingService->recordEvent($shipment, $validated, $user->id);
            return response()->json([
                'success' => true,
                'message' => 'Tracking event registered successfully.',
                'data' => [
                    'event' => $event,
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
}
