<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\FamilyMember;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class FamilyController extends Controller
{
    /**
     * Display the family card for the specified family.
     *
     * @param  \App\Models\Family  $family
     * @return \Illuminate\View\View
     */
    public function showFamilyCard(Family $family)
    {
        // Eager load data yang diperlukan
        $family->load([
            'members',
            'building.village',
            'village',
            'healthIndex'
        ]);

        return view('families.card-full', compact('family'));
    }

    /**
     * Show the family card from a family member.
     * 
     * @param  \App\Models\FamilyMember  $familyMember
     * @return \Illuminate\View\View
     */
    public function showFamilyCardFromMember(FamilyMember $familyMember)
    {
        // Dapatkan keluarga dari anggota dan eager load data yang diperlukan
        $family = $familyMember->family;

        if (!$family) {
            abort(404, 'Keluarga tidak ditemukan');
        }

        $family->load([
            'members',
            'building.village',
            'village',
            'healthIndex'
        ]);

        return view('families.card', compact('family'));
    }
}
