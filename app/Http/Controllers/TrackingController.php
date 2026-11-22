<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Services\EtaService;
use App\Services\External\WeatherService;

class TrackingController extends Controller
{
    // Menampilkan landing page publik dengan 3D Globe & real-time telemetry
    public function index()
    {
        $allShipments = Shipment::with(['origin', 'destination', 'carrier', 'trackingEvents.location'])
            ->latest()
            ->get();

        $stats = [
            'total' => Shipment::count(),
            'in_transit' => Shipment::where('status', 'IN_TRANSIT')->count(),
            'delivered' => Shipment::where('status', 'DELIVERED')->count(),
            'exceptions' => ShipmentException::whereIn('status', ['OPEN', 'INVESTIGATING'])->count(),
        ];

        $globeRoutes = $this->formatGlobeRoutes($allShipments);

        return view('track.index', compact('stats', 'globeRoutes', 'allShipments'));
    }

    // Memproses pencarian resi
    public function search(Request $request, EtaService $etaService, WeatherService $weatherService)
    {
        $request->validate([
            'tracking_number' => 'required|string|min:4'
        ]);

        $searchNumber = trim($request->tracking_number);

        $shipment = Shipment::with([
            'origin',
            'destination',
            'carrier',
            'customer',
            'items',
            'chronologicalEvents.location',
            'chronologicalEvents.creator',
            'exceptions',
        ])
        ->where('shipment_number', $searchNumber)
        ->first();

        if (!$shipment) {
            return back()->with('error', "Shipment number '{$searchNumber}' not found. Please verify your tracking ID and try again.")
                ->withInput();
        }

        $allShipments = Shipment::with(['origin', 'destination', 'carrier', 'trackingEvents.location'])
            ->latest()
            ->get();

        $stats = [
            'total' => Shipment::count(),
            'in_transit' => Shipment::where('status', 'IN_TRANSIT')->count(),
            'delivered' => Shipment::where('status', 'DELIVERED')->count(),
            'exceptions' => ShipmentException::whereIn('status', ['OPEN', 'INVESTIGATING'])->count(),
        ];

        $globeRoutes = $this->formatGlobeRoutes($allShipments);

        // Hitung estimasi keterlambatan deterministik
        $delayStatus = $etaService->calculateDelay($shipment);

        // Fetch live weather at current checkpoint or origin
        $latestEvent = $shipment->trackingEvents->first();
        $lat = $latestEvent?->latitude ?? $shipment->origin?->latitude ?? 1.2644;
        $lng = $latestEvent?->longitude ?? $shipment->origin?->longitude ?? 103.8403;
        $weather = $weatherService->getWeather((float) $lat, (float) $lng);

        return view('track.index', compact('shipment', 'delayStatus', 'weather', 'stats', 'globeRoutes', 'allShipments'));
    }

    private function formatGlobeRoutes($shipments)
    {
        return $shipments->map(function ($s) {
            $latestEvent = $s->trackingEvents->first();
            return [
                'resi' => $s->shipment_number,
                'cargo' => $s->cargo_type,
                'carrier' => $s->carrier?->name ?? 'Global Carrier',
                'status' => $s->status,
                'origin' => [
                    'code' => $s->origin?->code ?? 'ORG',
                    'name' => $s->origin?->name ?? 'Origin Hub',
                    'city' => $s->origin?->city ?? '',
                    'lat' => (float) ($s->origin?->latitude ?? 0),
                    'lng' => (float) ($s->origin?->longitude ?? 0),
                ],
                'destination' => [
                    'code' => $s->destination?->code ?? 'DST',
                    'name' => $s->destination?->name ?? 'Destination Hub',
                    'city' => $s->destination?->city ?? '',
                    'lat' => (float) ($s->destination?->latitude ?? 0),
                    'lng' => (float) ($s->destination?->longitude ?? 0),
                ],
                'current' => [
                    'lat' => (float) ($latestEvent?->latitude ?? $s->origin?->latitude ?? 0),
                    'lng' => (float) ($latestEvent?->longitude ?? $s->origin?->longitude ?? 0),
                    'location' => $latestEvent?->location?->name ?? ($s->origin?->name ?? 'Checkpoint'),
                ],
            ];
        })->values();
    }
}
