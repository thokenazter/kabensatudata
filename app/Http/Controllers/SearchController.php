<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\MedicalRecord;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    /**
     * Handle real-time search API requests
     */
    public function search(Request $request)
    {
        // Get search query
        $query = $request->input('q');

        if (empty($query) || strlen($query) < 2) {
            return response()->json([]);
        }

        // Collect search results
        $results = [];

        // Limit results
        $limit = 8;

        // Search family members
        $members = FamilyMember::where('name', 'like', "%{$query}%")
            ->orWhere('nik', 'like', "%{$query}%")
            ->with(['family.building.village'])
            ->limit($limit)
            ->get();

        foreach ($members as $member) {
            $village = optional(optional(optional($member->family)->building)->village)->name ?? 'Unknown';
            $buildingNumber = optional(optional($member->family)->building)->building_number ?? '';

            $results[] = [
                'type' => 'member',
                'id' => $member->id,
                'title' => $member->name,
                'subtitle' => 'NIK: ' . ($member->nik ?? 'N/A') . ' | ' . $member->relationship,
                'details' => "{$village}, Bangunan No.{$buildingNumber}",
                'url' => route('family-members.show', $member)
            ];
        }

        // Search families
        $families = Family::where('head_name', 'like', "%{$query}%")
            ->orWhere('family_number', 'like', "%{$query}%")
            ->with(['building.village'])
            ->limit($limit)
            ->get();

        foreach ($families as $family) {
            $village = optional(optional($family->building)->village)->name ?? 'Unknown';
            $buildingNumber = optional($family->building)->building_number ?? '';

            $results[] = [
                'type' => 'family',
                'id' => $family->id,
                'title' => "Keluarga " . $family->head_name,
                'subtitle' => "No. {$family->family_number}",
                'details' => "{$village}, Bangunan No.{$buildingNumber}",
                'url' => route('families.card', $family)
            ];
        }

        // Search villages
        $villages = Village::where('name', 'like', "%{$query}%")
            ->limit($limit)
            ->get();

        foreach ($villages as $village) {
            $familyCount = $village->families()->count();

            $results[] = [
                'type' => 'village',
                'id' => $village->id,
                'title' => "Desa " . $village->name,
                'subtitle' => "{$village->district}, {$village->regency}",
                'details' => "Total {$familyCount} keluarga",
                'url' => "/admin/resources/villages/{$village->id}"
            ];
        }

        // Search buildings
        $buildings = Building::where('building_number', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->with(['village'])
            ->limit($limit)
            ->get();

        foreach ($buildings as $building) {
            $familyCount = $building->families()->count();
            $village = optional($building->village)->name ?? 'Unknown';

            $results[] = [
                'type' => 'building',
                'id' => $building->id,
                'title' => "Bangunan No. " . $building->building_number,
                'subtitle' => "Desa {$village}",
                'details' => "Total {$familyCount} keluarga",
                'url' => "/admin/resources/buildings/{$building->id}"
            ];
        }

        // Search medical records (if we have permission)
        if (Auth::check() && Auth::user()->can('view_any_medical_record')) {
            $medicalRecords = MedicalRecord::where('chief_complaint', 'like', "%{$query}%")
                ->orWhere('diagnosis_name', 'like', "%{$query}%")
                ->orWhere('diagnosis_code', 'like', "%{$query}%")
                ->with(['familyMember.family.building.village'])
                ->limit($limit)
                ->get();

            foreach ($medicalRecords as $record) {
                $memberName = optional($record->familyMember)->name ?? 'Unknown';
                $visitDate = $record->visit_date->format('d/m/Y');

                $results[] = [
                    'type' => 'medical',
                    'id' => $record->id,
                    'title' => "Rekam Medis: " . $memberName,
                    'subtitle' => "Kunjungan: {$visitDate}",
                    'details' => $record->diagnosis_name ?? $record->chief_complaint ?? 'No details',
                    'url' => route('medical-records.show', [$record->familyMember, $record])
                ];
            }
        }

        // Limit results to prevent overwhelming the UI
        $results = array_slice($results, 0, 10);

        return response()->json($results);
    }
}
