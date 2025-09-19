<?php

namespace App\Http\Controllers\Admin;

use App\Models\Building;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BuildingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:view_any_building'])->only(['index']);
        $this->middleware(['permission:create_building'])->only(['create', 'store']);
        $this->middleware(['permission:update_building'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_building'])->only(['destroy']);
    }

    public function index(Request $request)
    {
        $q = Building::with(['village'])->withCount('families');

        if ($s = $request->input('q')) {
            $q->where(function ($w) use ($s) {
                $w->where('building_number', 'like', "%{$s}%")
                  ->orWhere('address', 'like', "%{$s}%")
                  ->orWhere('notes', 'like', "%{$s}%");
            });
        }
        if ($villageId = $request->input('village_id')) {
            $q->where('village_id', $villageId);
        }

        $buildings = $q->orderBy('building_number')->paginate(15)->withQueryString();
        $villages = Village::orderBy('name')->pluck('name', 'id');
        return view('admin.buildings.index', compact('buildings', 'villages'));
    }

    public function create()
    {
        $villages = Village::orderBy('name')->pluck('name', 'id');
        return view('admin.buildings.create', compact('villages'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'building_number' => ['required','string','max:50', 'unique:buildings,building_number,NULL,id,village_id,' . $request->input('village_id')],
            'village_id' => 'required|exists:villages,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|max:50',
        ], [
            'building_number.unique' => 'No Urut Bangunan tersebut sudah digunakan pada desa yang dipilih.',
        ]);

        // Validasi koordinat jika keduanya terisi
        if (!is_null($data['latitude'] ?? null) && !is_null($data['longitude'] ?? null)) {
            if (!Building::validateCoordinates($data['latitude'], $data['longitude'])) {
                return back()->withErrors(['latitude' => 'Koordinat tidak valid'])->withInput();
            }
        }

        Building::create($data);
        return redirect()->route('panel.buildings.index')->with('success', 'Bangunan berhasil ditambahkan');
    }

    public function edit(Building $building)
    {
        $villages = Village::orderBy('name')->pluck('name', 'id');
        return view('admin.buildings.edit', compact('building', 'villages'));
    }

    public function update(Request $request, Building $building)
    {
        $data = $request->validate([
            'building_number' => ['required','string','max:50', 'unique:buildings,building_number,' . $building->id . ',id,village_id,' . $request->input('village_id')],
            'village_id' => 'required|exists:villages,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|max:50',
        ], [
            'building_number.unique' => 'No Urut Bangunan tersebut sudah digunakan pada desa yang dipilih.',
        ]);

        if (!is_null($data['latitude'] ?? null) && !is_null($data['longitude'] ?? null)) {
            if (!Building::validateCoordinates($data['latitude'], $data['longitude'])) {
                return back()->withErrors(['latitude' => 'Koordinat tidak valid'])->withInput();
            }
        }

        $building->update($data);
        return redirect()->route('panel.buildings.index')->with('success', 'Bangunan berhasil diperbarui');
    }

    public function destroy(Building $building)
    {
        $building->delete();
        return redirect()->route('panel.buildings.index')->with('success', 'Bangunan berhasil dihapus');
    }
}
