<?php

namespace App\Http\Controllers\API;

use App\Models\FamilyMember;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class FamilyMemberController extends Controller
{
    public function show(FamilyMember $familyMember)
    {
        // Muat data relasi yang diperlukan
        $familyMember->load('family');

        // Tambahkan data tambahan yang diperlukan
        $data = $familyMember->toArray();
        $data['age'] = $familyMember->age;

        return response()->json($data);
    }
}
