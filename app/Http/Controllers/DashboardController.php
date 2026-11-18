<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\ShipmentException;
use App\Services\TrackingService;
use App\Services\EtaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class DashboardController extends Controller
{
    public function admin(Request $request, EtaService $etaService)
    {
        $totalShipments = Shipment::count();
        $inTransit = Shipment::where('status', 'IN_TRANSIT')->count();
        $delivered = Shipment::where('status', 'DELIVERED')->count();
        
        $delayed = Shipment::where('status', '!=', 'DELIVERED')
            ->whereNotNull('expected_arrival_at')
            ->where('expected_arrival_at', '<', now())
            ->count();

        $openExceptions = DB::table('shipment_exceptions')
            ->whereIn('status', ['OPEN', 'INVESTIGATING'])
            ->count();

        $delayRate = $totalShipments > 0 ? round(($delayed / $totalShipments) * 100, 1) : 0;
        $exceptionRate = $totalShipments > 0 ? round(($openExceptions / $totalShipments) * 100, 1) : 0;

        // Breakdown by status for Chart.js
        $statusBreakdown = [
            'CREATED' => Shipment::where('status', 'CREATED')->count(),
            'BOOKED' => Shipment::where('status', 'BOOKED')->count(),
            'PICKED_UP' => Shipment::where('status', 'PICKED_UP')->count(),
            'IN_TRANSIT' => $inTransit,
            'DELIVERED' => $delivered,
            'EXCEPTION' => Shipment::where('status', 'EXCEPTION')->count(),
        ];

        // Breakdown by carrier for Chart.js
        $carrierBreakdown = Carrier::withCount('shipments')->get()->map(function ($carrier) {
            return [
                'name' => $carrier->name,
                'count' => $carrier->shipments_count,
            ];
        });

        // Active shipments for the Live Dashboard Map
        $activeShipments = Shipment::with(['origin', 'destination', 'trackingEvents' => function($q) {
                $q->latest('event_time');
            }, 'trackingEvents.location'])
            ->whereIn('status', ['IN_TRANSIT', 'EXCEPTION', 'PICKED_UP', 'BOOKED', 'CREATED'])
            ->get()
            ->map(function ($shipment) {
                $latestLocationEvent = $shipment->trackingEvents->firstWhere('location_id', '!=', null);
                
                $lat = $latestLocationEvent && $latestLocationEvent->location ? $latestLocationEvent->location->latitude : ($shipment->origin->latitude ?? 1.2644);
                $lng = $latestLocationEvent && $latestLocationEvent->location ? $latestLocationEvent->location->longitude : ($shipment->origin->longitude ?? 103.8403);
                $locName = $latestLocationEvent && $latestLocationEvent->location ? $latestLocationEvent->location->name : ($shipment->origin->name ?? 'Origin Hub');

                return [
                    'resi' => $shipment->shipment_number,
                    'status' => $shipment->status,
                    'lat' => (float) $lat,
                    'lng' => (float) $lng,
                    'lokasi' => $locName,
                    'origin' => $shipment->origin?->city ?? 'Origin',
                    'destination' => $shipment->destination?->city ?? 'Destination',
                ];
            });

        // Recent open exceptions for quick priority action
        $recentExceptions = ShipmentException::with('shipment')
            ->whereIn('status', ['OPEN', 'INVESTIGATING'])
            ->latest('detected_at')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalShipments',
            'inTransit',
            'delivered',
            'delayed',
            'openExceptions',
            'delayRate',
            'exceptionRate',
            'statusBreakdown',
            'carrierBreakdown',
            'activeShipments',
            'recentExceptions'
        ));
    }

    public function operator(Request $request)
    {
        $query = Shipment::with(['customer', 'carrier', 'origin', 'destination', 'exceptions']);

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $query->where('shipment_number', 'like', "%{$request->search}%");
        }

        $shipments = $query->latest()->paginate(10);
        return view('operator.dashboard', compact('shipments'));
    }

    public function showShipmentOperator($shipment_number, EtaService $etaService)
    {
        $shipment = Shipment::with([
            'customer',
            'carrier',
            'origin',
            'destination',
            'items',
            'chronologicalEvents.location',
            'chronologicalEvents.creator',
            'exceptions',
        ])->where('shipment_number', $shipment_number)->firstOrFail();

        $delay = $etaService->calculateDelay($shipment);

        return view('operator.show_shipment', compact('shipment', 'delay'));
    }

    public function editStatus($shipment_number, TrackingService $trackingService)
    {
        $shipment = Shipment::where('shipment_number', $shipment_number)->firstOrFail();
        $allowedStatuses = $trackingService->getNextPossibleStatuses($shipment);
        $locations = Location::all();

        return view('operator.edit_status', compact('shipment', 'allowedStatuses', 'locations'));
    }

    public function updateStatus(Request $request, $shipment_number, TrackingService $trackingService)
    {
        $shipment = Shipment::where('shipment_number', $shipment_number)->firstOrFail();

        $request->validate([
            'status' => 'required|string',
            'location_id' => 'nullable|exists:locations,id',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $trackingService->recordEvent($shipment, [
                'status' => $request->status,
                'location_id' => $request->location_id,
                'description' => $request->description ?? "Status updated by operator " . Auth::user()->name,
            ], Auth::id());

            return redirect()->route('operator.shipments.show', $shipment_number)
                ->with('success', 'Status pengiriman dan tracking event berhasil diperbarui.');
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function customer(Request $request)
    {
        $shipments = Shipment::with(['origin', 'destination', 'carrier', 'exceptions'])
            ->where('customer_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('customer.dashboard', compact('shipments'));
    }

    public function showShipment($shipment_number, EtaService $etaService)
    {
        $shipment = Shipment::with([
            'customer',
            'carrier',
            'origin',
            'destination',
            'items',
            'chronologicalEvents.location',
            'exceptions',
        ])
        ->where('shipment_number', $shipment_number)
        ->where('customer_id', Auth::id())
        ->firstOrFail();

        $delay = $etaService->calculateDelay($shipment);

        return view('customer.show_shipment', compact('shipment', 'delay'));
    }
}
