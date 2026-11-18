<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Services\TrackingService;
use Illuminate\Http\Request;
use Exception;

class TrackingEventController extends Controller
{
    protected TrackingService $trackingService;

    public function __construct(TrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }

    public function store(Request $request, Shipment $shipment)
    {
        $validated = $request->validate([
            'status'      => 'required|string',
            'location_id' => 'nullable|exists:locations,id',
            'description' => 'nullable|string|max:255',
            'latitude'    => 'nullable|numeric',
            'longitude'   => 'nullable|numeric',
            'event_time'  => 'nullable|date',
        ]);

        try {
            $this->trackingService->recordEvent($shipment, $validated, $request->user()->id);
            return back()->with('success', 'Tracking event logged and shipment status updated successfully.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }
}
