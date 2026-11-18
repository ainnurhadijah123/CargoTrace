<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use Illuminate\Http\Request;

class CarrierController extends Controller
{
    // Menampilkan daftar carrier
    public function index()
    {
        $carriers = Carrier::latest()->paginate(10);
        return view('admin.carriers.index', compact('carriers'));
    }

    // Menampilkan form tambah carrier
    public function create()
    {
        return view('admin.carriers.create');
    }

    // Menyimpan data carrier baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:carriers,code',
            'contact_info' => 'nullable|string|max:255',
        ]);

        Carrier::create([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'contact_info' => $request->contact_info,
            'is_active' => $request->has('is_active'), // Checkbox boolean
        ]);

        return redirect()->route('admin.carriers.index')->with('success', 'Data armada berhasil ditambahkan.');
    }

    // Menampilkan form edit carrier
    public function edit(Carrier $carrier)
    {
        return view('admin.carriers.edit', compact('carrier'));
    }

    // Menyimpan perubahan data
    public function update(Request $request, Carrier $carrier)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:carriers,code,' . $carrier->id,
            'contact_info' => 'nullable|string|max:255',
        ]);

        $carrier->update([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'contact_info' => $request->contact_info,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.carriers.index')->with('success', 'Data armada berhasil diperbarui.');
    } // <--- KURUNG KURAWAL INI SUDAH DITAMBAHKAN KEMBALI

    // Menghapus data
    public function destroy(Carrier $carrier)
    {
        $carrier->delete();
        
        // PERUBAHAN: Menambahkan 'admin.' pada redirect route
        return redirect()->route('admin.carriers.index')->with('success', 'Armada berhasil dihapus.');
    }
}