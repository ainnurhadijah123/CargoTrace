<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use App\Services\ShipmentService;
use App\Services\EtaService;
use App\Http\Requests\StoreShipmentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ShipmentController extends Controller
{
    public function __construct(private ShipmentService $shipments)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $query = Shipment::with(['customer', 'carrier', 'origin', 'destination', 'exceptions']);

        // Scoping for customer role
        if ($user->isCustomer()) {
            $query->where('customer_id', $user->id);
        }

        // Search by shipment number
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('shipment_number', 'like', "%{$search}%")
                  ->orWhere('cargo_type', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by carrier
        if ($request->filled('carrier_id')) {
            $query->where('carrier_id', $request->carrier_id);
        }

        // Filter by origin
        if ($request->filled('origin_id')) {
            $query->where('origin_id', $request->origin_id);
        }

        // Filter by destination
        if ($request->filled('destination_id')) {
            $query->where('destination_id', $request->destination_id);
        }

        // Server-side pagination
        $shipments = $query->latest()->paginate(10)->withQueryString();

        $carriers = Carrier::where('is_active', true)->orderBy('name')->get();
        $locations = Location::orderBy('name')->get();

        return view('admin.shipments.index', compact('shipments', 'carriers', 'locations'));
    }

    public function create()
    {
        return view('admin.shipments.form', ['shipment' => null] + $this->formData());
    }

    public function store(StoreShipmentRequest $request)
    {
        $user = $request->user();
        $shipment = $this->shipments->create($request->validated(), $user);

        return redirect()->route('admin.shipments.show', $shipment)->with('success', 'Shipment order created successfully.');
    }

    public function show(Shipment $shipment, EtaService $etaService)
    {
        $shipment->load([
            'customer',
            'carrier',
            'origin',
            'destination',
            'creator',
            'items',
            'chronologicalEvents.location',
            'chronologicalEvents.creator',
            'exceptions',
        ]);

        $delayStatus = $etaService->calculateDelay($shipment);

        return view('admin.shipments.show', compact('shipment', 'delayStatus'));
    }

    public function edit(Shipment $shipment)
    {
        $shipment->load('items');
        return view('admin.shipments.form', compact('shipment') + $this->formData());
    }

    public function update(StoreShipmentRequest $request, Shipment $shipment)
    {
        $this->shipments->update($shipment, $request->validated(), $request->user());

        return redirect()->route('admin.shipments.show', $shipment)->with('success', 'Shipment updated successfully.');
    }

    public function destroy(Shipment $shipment, Request $request)
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            abort(403, 'Only Administrators can delete shipments.');
        }

        if (strtoupper($shipment->status) !== 'CREATED') {
            return back()->with('error', 'Only shipments in CREATED status can be safely deleted.');
        }

        $this->shipments->delete($shipment, $user);

        return redirect()->route('admin.shipments.index')->with('success', 'Shipment deleted successfully.');
    }

    private function formData(): array
    {
        return [
            'customers' => User::where('role', 'CUSTOMER')->orderBy('name')->get(),
            'carriers' => Carrier::where('is_active', true)->orderBy('name')->get(),
            'locations' => Location::orderBy('name')->get(),
        ];
    }

    public function print(Shipment $shipment)
    {
        $shipment->load(['origin', 'destination', 'carrier', 'customer', 'items']);
        return view('admin.shipments.print', compact('shipment'));
    }
}
