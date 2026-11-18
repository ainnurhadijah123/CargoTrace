<?php

namespace App\Http\Controllers;
use App\Models\AuditLog;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::latest()->paginate(10);
        return view('admin.locations.index', compact('locations'));
    }

    public function create()
    {
        return view('admin.locations.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:locations,code|max:50',
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        Location::create($request->all());

        // PERUBAHAN: admin.locations.index
        return redirect()->route('admin.locations.index')->with('success', 'Data lokasi berhasil ditambahkan.');
    }

    public function edit(Location $location)
    {
        return view('admin.locations.edit', compact('location'));
    }

    public function update(Request $request, Location $location)
    {
        $request->validate([
            'code' => 'required|max:50|unique:locations,code,' . $location->id,
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $location->update($request->all());

        // PERUBAHAN: admin.locations.index
        return redirect()->route('admin.locations.index')->with('success', 'Data lokasi berhasil diperbarui.');
    }

    public function destroy(Location $location)
    {
    // Cek apakah lokasi ini sedang dipakai sebagai asal atau tujuan di tabel shipments
    $isUsed = \App\Models\Shipment::where('origin_id', $location->id)
                ->orWhere('destination_id', $location->id)
                ->exists();

    if ($isUsed) {
        return redirect()->route('admin.locations.index')
            ->with('error', 'Lokasi "' . $location->name . '" tidak dapat dihapus karena masih digunakan dalam data pengiriman (shipment)!');
    }
    $locationName = $location->name;
    $location->delete();

    AuditLog::record('DELETED', 'Location', "Lokasi dihapus: $locationName");

    return redirect()->route('admin.locations.index')->with('success', 'Lokasi berhasil dihapus.');
    }
}