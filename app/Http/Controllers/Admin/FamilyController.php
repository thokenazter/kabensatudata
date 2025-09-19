<?php

namespace App\Http\Controllers\Admin;

use App\Models\Family;
use App\Models\Building;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class FamilyController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:view_any_family'])->only(['index']);
        $this->middleware(['permission:create_family'])->only(['create', 'store']);
        $this->middleware(['permission:update_family'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_family'])->only(['destroy']);
    }

    public function index(Request $request)
    {
        $q = Family::with(['building.village'])->withCount('members');

        if ($s = $request->input('q')) {
            $q->where(function ($w) use ($s) {
                $w->where('family_number', 'like', "%{$s}%")
                  ->orWhere('head_name', 'like', "%{$s}%");
            });
        }
        if ($villageId = $request->input('village_id')) {
            $q->whereHas('building', fn($b) => $b->where('village_id', $villageId));
        }
        if ($buildingId = $request->input('building_id')) {
            $q->where('building_id', $buildingId);
        }

        $families = $q->orderBy('family_number')->paginate(15)->withQueryString();
        $villages = Village::orderBy('name')->pluck('name', 'id');
        $buildings = Building::orderBy('building_number')->pluck('building_number', 'id');
        return view('admin.families.index', compact('families', 'villages', 'buildings'));
    }

    public function create()
    {
        $villages = Village::orderBy('name')->pluck('name', 'id');
        $buildings = Building::with('village')->get()
            ->sortBy('building_number')
            ->mapWithKeys(fn($b) => [$b->id => (($b->village->name ?? '-') . ' - No. ' . $b->building_number)]);
        return view('admin.families.create', compact('villages', 'buildings'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'building_id' => 'required|exists:buildings,id',
            'family_number' => 'required|string|max:50',
            'sequence_number_in_building' => 'nullable|integer|min:0',
            'head_name' => 'required|string|max:255',
            'has_clean_water' => 'boolean',
            'is_water_protected' => 'boolean',
            'has_toilet' => 'boolean',
            'is_toilet_sanitary' => 'boolean',
            'has_mental_illness' => 'boolean',
            'takes_medication_regularly' => 'boolean',
            'has_restrained_member' => 'boolean',
        ]);

        $flags = ['has_clean_water','is_water_protected','has_toilet','is_toilet_sanitary','has_mental_illness','takes_medication_regularly','has_restrained_member'];
        foreach ($flags as $f) { $data[$f] = $request->boolean($f); }

        Family::create($data);
        return redirect()->route('panel.families.index')->with('success', 'Keluarga berhasil ditambahkan');
    }

    public function edit(Family $family)
    {
        $villages = Village::orderBy('name')->pluck('name', 'id');
        $buildings = Building::with('village')->get()
            ->sortBy('building_number')
            ->mapWithKeys(fn($b) => [$b->id => (($b->village->name ?? '-') . ' - No. ' . $b->building_number)]);
        return view('admin.families.edit', compact('family', 'villages', 'buildings'));
    }

    public function update(Request $request, Family $family)
    {
        $data = $request->validate([
            'building_id' => 'required|exists:buildings,id',
            'family_number' => 'required|string|max:50',
            'sequence_number_in_building' => 'nullable|integer|min:0',
            'head_name' => 'required|string|max:255',
            'has_clean_water' => 'boolean',
            'is_water_protected' => 'boolean',
            'has_toilet' => 'boolean',
            'is_toilet_sanitary' => 'boolean',
            'has_mental_illness' => 'boolean',
            'takes_medication_regularly' => 'boolean',
            'has_restrained_member' => 'boolean',
        ]);

        $flags = ['has_clean_water','is_water_protected','has_toilet','is_toilet_sanitary','has_mental_illness','takes_medication_regularly','has_restrained_member'];
        foreach ($flags as $f) { $data[$f] = $request->boolean($f); }

        $family->update($data);
        return redirect()->route('panel.families.index')->with('success', 'Keluarga berhasil diperbarui');
    }

    public function destroy(Family $family)
    {
        $family->delete();
        return redirect()->route('panel.families.index')->with('success', 'Keluarga berhasil dihapus');
    }
}
