<?php

namespace App\Http\Controllers;

use App\Models\FamilyMember;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class FamilyMemberController extends Controller
{
    // public function show(FamilyMember $familyMember)
    // {
    //     return view('family-members.show', compact('familyMember'));
    // }

    // public function show($id, $slug = null)
    // {
    //     $familyMember = FamilyMember::findOrFail($id);

    //     // Redirect ke URL dengan slug yang benar jika slug di URL tidak sesuai
    //     $correctSlug = $familyMember->slug;
    //     if ($slug !== $correctSlug) {
    //         return redirect()->route('family-members.show', ['id' => $id, 'slug' => $correctSlug]);
    //     }

    //     return view('family-members.show', compact('familyMember'));
    // }

    public function show(FamilyMember $familyMember)
    {
        return view('family-members.show', compact('familyMember'));
    }
}
