<?php

namespace App\Http\Controllers\Admin;

use App\Models\FamilyMember;
use App\Models\Family;
use App\Models\Building;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class FamilyMemberController extends Controller
{
    public function __construct()
    {
        // Perhatikan: permission names mengikuti Shield untuk FamilyMember
        $this->middleware(['permission:view_any_family::member'])->only(['index']);
        $this->middleware(['permission:create_family::member'])->only(['create', 'store']);
        $this->middleware(['permission:update_family::member'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_family::member'])->only(['destroy']);
    }

    public function index(Request $request)
    {
        $q = FamilyMember::with(['family.building.village']);

        if ($s = $request->input('q')) {
            $q->where(function ($w) use ($s) {
                $w->where('name', 'like', "%{$s}%")
                  ->orWhere('nik', 'like', "%{$s}%")
                  ->orWhere('rm_number', 'like', "%{$s}%");
            });
        }
        if ($villageId = $request->input('village_id')) {
            $q->whereHas('family.building', fn($b) => $b->where('village_id', $villageId));
        }
        if ($buildingId = $request->input('building_id')) {
            $q->whereHas('family', fn($f) => $f->where('building_id', $buildingId));
        }
        if ($familyId = $request->input('family_id')) {
            $q->where('family_id', $familyId);
        }

        $members = $q->orderBy('name')->paginate(15)->withQueryString();
        $villages = Village::orderBy('name')->pluck('name', 'id');
        $buildings = Building::orderBy('building_number')->pluck('building_number', 'id');
        $families = Family::orderBy('family_number')->pluck('family_number', 'id');
        return view('admin.family-members.index', compact('members', 'villages', 'buildings', 'families'));
    }

    public function create()
    {
        $villages = Village::orderBy('name')->pluck('name', 'id');
        $buildings = Building::orderBy('building_number')->pluck('building_number', 'id');
        $families = Family::with('building.village')->get()
            ->sortBy('family_number')
            ->mapWithKeys(fn($f) => [
                $f->id => (($f->building->village->name ?? '-') . ' - No.' . ($f->building->building_number ?? '-') . ' - ' . $f->family_number . ' - ' . ($f->head_name ?? '-'))
            ]);
        return view('admin.family-members.create', compact('villages', 'buildings', 'families'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'family_id' => 'required|exists:families,id',
            'name' => 'required|string|max:255',
            'nik' => 'nullable|digits:16',
            'rm_number' => 'nullable|string|max:255',
            'sequence_number_in_family' => 'nullable|integer|min:0',
            'relationship' => 'required|string|max:255',
            'birth_place' => 'nullable|string|max:255',
            'birth_date' => 'required|date|before_or_equal:today',
            'gender' => 'required|string|max:50',
            'religion' => 'nullable|string|max:50',
            'education' => 'nullable|string|max:100',
            'marital_status' => 'nullable|string|max:50',
            'occupation' => 'nullable|string|max:100',
            'is_pregnant' => 'boolean',
            'has_jkn' => 'boolean',
            'is_smoker' => 'boolean',
            'use_water' => 'nullable|string|max:100',
            'use_toilet' => 'boolean',
            'has_tuberculosis' => 'boolean',
            'takes_tb_medication_regularly' => 'boolean',
            'has_chronic_cough' => 'boolean',
            'has_hypertension' => 'boolean',
            'takes_hypertension_medication_regularly' => 'boolean',
            'uses_contraception' => 'boolean',
            'gave_birth_in_health_facility' => 'boolean',
            'exclusive_breastfeeding' => 'boolean',
            'complete_immunization' => 'boolean',
            'growth_monitoring' => 'boolean',
        ]);

        foreach (['is_pregnant','has_jkn','is_smoker','use_toilet','has_tuberculosis','takes_tb_medication_regularly','has_chronic_cough','has_hypertension','takes_hypertension_medication_regularly','uses_contraception','gave_birth_in_health_facility','exclusive_breastfeeding','complete_immunization','growth_monitoring'] as $f) {
            $data[$f] = $request->boolean($f);
        }

        FamilyMember::create($data);
        return redirect()->route('panel.family-members.index')->with('success', 'Anggota keluarga berhasil ditambahkan');
    }

    public function edit(FamilyMember $familyMember)
    {
        $villages = Village::orderBy('name')->pluck('name', 'id');
        $buildings = Building::orderBy('building_number')->pluck('building_number', 'id');
        $families = Family::with('building.village')->get()
            ->sortBy('family_number')
            ->mapWithKeys(fn($f) => [
                $f->id => (($f->building->village->name ?? '-') . ' - No.' . ($f->building->building_number ?? '-') . ' - ' . $f->family_number . ' - ' . ($f->head_name ?? '-'))
            ]);
        return view('admin.family-members.edit', compact('familyMember', 'villages', 'buildings', 'families'));
    }

    public function update(Request $request, FamilyMember $familyMember)
    {
        $data = $request->validate([
            'family_id' => 'required|exists:families,id',
            'name' => 'required|string|max:255',
            'nik' => 'nullable|digits:16',
            'rm_number' => 'nullable|string|max:255',
            'sequence_number_in_family' => 'nullable|integer|min:0',
            'relationship' => 'required|string|max:255',
            'birth_place' => 'nullable|string|max:255',
            'birth_date' => 'required|date|before_or_equal:today',
            'gender' => 'required|string|max:50',
            'religion' => 'nullable|string|max:50',
            'education' => 'nullable|string|max:100',
            'marital_status' => 'nullable|string|max:50',
            'occupation' => 'nullable|string|max:100',
            'is_pregnant' => 'boolean',
            'has_jkn' => 'boolean',
            'is_smoker' => 'boolean',
            'use_water' => 'nullable|string|max:100',
            'use_toilet' => 'boolean',
            'has_tuberculosis' => 'boolean',
            'takes_tb_medication_regularly' => 'boolean',
            'has_chronic_cough' => 'boolean',
            'has_hypertension' => 'boolean',
            'takes_hypertension_medication_regularly' => 'boolean',
            'uses_contraception' => 'boolean',
            'gave_birth_in_health_facility' => 'boolean',
            'exclusive_breastfeeding' => 'boolean',
            'complete_immunization' => 'boolean',
            'growth_monitoring' => 'boolean',
        ]);

        foreach (['is_pregnant','has_jkn','is_smoker','use_toilet','has_tuberculosis','takes_tb_medication_regularly','has_chronic_cough','has_hypertension','takes_hypertension_medication_regularly','uses_contraception','gave_birth_in_health_facility','exclusive_breastfeeding','complete_immunization','growth_monitoring'] as $f) {
            $data[$f] = $request->boolean($f);
        }

        $familyMember->update($data);
        return redirect()->route('panel.family-members.index')->with('success', 'Anggota keluarga berhasil diperbarui');
    }

    public function destroy(FamilyMember $familyMember)
    {
        $familyMember->delete();
        return redirect()->route('panel.family-members.index')->with('success', 'Anggota keluarga berhasil dihapus');
    }
}
