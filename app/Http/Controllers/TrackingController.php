<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shipment;
use App\Services\EtaService;
use App\Services\External\WeatherService;

class TrackingController extends Controller
{
    // Menampilkan halaman pencarian resi
    public function index()
    {
        return view('track.index');
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

        // Hitung estimasi keterlambatan deterministik
        $delayStatus = $etaService->calculateDelay($shipment);

        // Fetch live weather at current checkpoint or origin
        $latestEvent = $shipment->trackingEvents->first();
        $lat = $latestEvent?->latitude ?? $shipment->origin?->latitude ?? 1.2644;
        $lng = $latestEvent?->longitude ?? $shipment->origin?->longitude ?? 103.8403;
        $weather = $weatherService->getWeather((float) $lat, (float) $lng);

        return view('track.index', compact('shipment', 'delayStatus', 'weather'));
    }
}
