<?php

namespace App\Http\Controllers;

use App\Models\Building;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MapController extends Controller
{
    public function index()
    {
        // return view('map.index');

        return view('map.index', [
            'isLoggedIn' => auth()->check()
        ]);
    }

    public function getBuildingsData()
    {
        $buildings = Building::with(['village', 'families', 'families.members'])
            ->get()
            ->map(function ($building) {
                return [
                    'id' => $building->id,
                    'building_number' => $building->building_number,
                    'longitude' => $building->longitude,
                    'latitude' => $building->latitude,
                    'village' => $building->village->name,
                    'families_count' => $building->families->count(),
                    'families' => $building->families->map(function ($family) {
                        return [
                            'id' => $family->id,
                            'has_clean_water' => $family->has_clean_water,
                            'has_toilet' => $family->has_toilet,
                            'is_toilet_sanitary' => $family->is_toilet_sanitary,
                            'has_tuberculosis' => $family->has_tuberculosis,
                            'members' => $family->members->map(function ($member) {
                                return [
                                    'has_tuberculosis' => $member->has_tuberculosis,
                                    'has_hypertension' => $member->has_hypertension,
                                    'has_chronic_cough' => $member->has_chronic_cough
                                ];
                            })
                        ];
                    })
                ];
            });

        return response()->json($buildings);
    }

    public function getBuildingDetails($id)
    {
        $building = Building::with([
            'village',
            'families.members' => function ($query) {
                $query->select('id', 'family_id', 'name', 'relationship', 'gender', 'birth_date');
            }
        ])->findOrFail($id);

        return response()->json($building);
    }
}
