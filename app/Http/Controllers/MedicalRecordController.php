<?php

namespace App\Http\Controllers;

use App\Models\FamilyMember;
use Illuminate\Http\Request;
use App\Models\MedicalRecord;
use Illuminate\Routing\Controller;

class MedicalRecordController extends Controller
{
    /**
     * Show medical records for a family member
     */
    public function index(FamilyMember $familyMember)
    {
        $familyMember->load(['family.building.village']);
        $medicalRecords = $familyMember->medicalRecords()
            ->orderBy('visit_date', 'desc')
            ->paginate(10);

        return view('medical-records.index', [
            'familyMember' => $familyMember,
            'medicalRecords' => $medicalRecords
        ]);
    }

    /**
     * Show the form for creating a new medical record
     */
    public function create(FamilyMember $familyMember)
    {
        return view('medical-records.create', [
            'familyMember' => $familyMember
        ]);
    }

    /**
     * Store a newly created medical record
     */
    public function store(Request $request, FamilyMember $familyMember)
    {
        $validated = $request->validate([
            'visit_date' => 'required|date',
            'chief_complaint' => 'nullable|string|max:255',
            'anamnesis' => 'nullable|string',
            'systolic' => 'nullable|integer|min:60|max:300',
            'diastolic' => 'nullable|integer|min:40|max:200',
            'weight' => 'nullable|numeric|min:0|max:500',
            'height' => 'nullable|numeric|min:0|max:300',
            'heart_rate' => 'nullable|integer|min:30|max:250',
            'body_temperature' => 'nullable|numeric|min:30|max:45',
            'respiratory_rate' => 'nullable|integer|min:5|max:60',
            'diagnosis_code' => 'nullable|string|max:255',
            'diagnosis_name' => 'nullable|string|max:255',
            'therapy' => 'nullable|string',
            'medication' => 'nullable|string',
            'procedure' => 'nullable|string',
        ]);

        // Tambahkan user id yang mencatat
        $validated['created_by'] = auth()->id();

        // Simpan rekam medis
        $familyMember->medicalRecords()->create($validated);

        return redirect()
            ->route('medical-records.index', $familyMember)
            ->with('success', 'Rekam medis berhasil ditambahkan');
    }

    /**
     * Display the specified medical record
     */
    public function show(FamilyMember $familyMember, MedicalRecord $medicalRecord, Request $request)
    {
        // Ensure the medical record belongs to the family member
        if ($medicalRecord->family_member_id !== $familyMember->id) {
            abort(404);
        }

        // Load necessary relationships
        $familyMember->load(['family.building.village']);
        $medicalRecord->load(['creator']);

        // Check if this is an AJAX request for modal content
        if ($request->boolean('partial') || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return view('medical-records._detail_content', [
                'familyMember' => $familyMember,
                'medicalRecord' => $medicalRecord
            ]);
        }

        return view('medical-records.show', [
            'familyMember' => $familyMember,
            'medicalRecord' => $medicalRecord
        ]);
    }

    /**
     * Show the form for editing the specified medical record
     */
    public function edit(FamilyMember $familyMember, MedicalRecord $medicalRecord)
    {
        // Ensure the medical record belongs to the family member
        if ($medicalRecord->family_member_id !== $familyMember->id) {
            abort(404);
        }

        return view('medical-records.edit', [
            'familyMember' => $familyMember,
            'medicalRecord' => $medicalRecord
        ]);
    }

    /**
     * Update the specified medical record
     */
    public function update(Request $request, FamilyMember $familyMember, MedicalRecord $medicalRecord)
    {
        // Ensure the medical record belongs to the family member
        if ($medicalRecord->family_member_id !== $familyMember->id) {
            abort(404);
        }

        $validated = $request->validate([
            'visit_date' => 'required|date',
            'chief_complaint' => 'nullable|string|max:255',
            'anamnesis' => 'nullable|string',
            'systolic' => 'nullable|integer|min:60|max:300',
            'diastolic' => 'nullable|integer|min:40|max:200',
            'weight' => 'nullable|numeric|min:0|max:500',
            'height' => 'nullable|numeric|min:0|max:300',
            'heart_rate' => 'nullable|integer|min:30|max:250',
            'body_temperature' => 'nullable|numeric|min:30|max:45',
            'respiratory_rate' => 'nullable|integer|min:5|max:60',
            'diagnosis_code' => 'nullable|string|max:255',
            'diagnosis_name' => 'nullable|string|max:255',
            'therapy' => 'nullable|string',
            'medication' => 'nullable|string',
            'procedure' => 'nullable|string',
        ]);

        // Update the medical record
        $medicalRecord->update($validated);

        return redirect()
            ->route('medical-records.show', [$familyMember, $medicalRecord])
            ->with('success', 'Rekam medis berhasil diperbarui');
    }

    /**
     * Print prescription for a medical record
     */
    public function printPrescription(FamilyMember $familyMember, MedicalRecord $medicalRecord)
    {
        // Ensure the medical record belongs to the family member
        if ($medicalRecord->family_member_id !== $familyMember->id) {
            abort(404);
        }

        // Load necessary relationships
        $familyMember->load(['family.building.village']);
        
        return view('medical-records.print-prescription', [
            'familyMember' => $familyMember,
            'medicalRecord' => $medicalRecord
        ]);
    }

    // MedicalRecordController.php
    // public function __construct()
    // {
    //     $this->middleware('can:view_any_medical_record')->only(['index']);
    //     $this->middleware('can:view_medical_record')->only(['show']);
    //     $this->middleware('can:create_medical_record')->only(['create', 'store']);
    //     $this->middleware('can:update_medical_record')->only(['edit', 'update']);
    //     $this->middleware('can:delete_medical_record')->only(['destroy']);
    // }
}
