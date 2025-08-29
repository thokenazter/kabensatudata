<?php

namespace App\Observers;

use App\Models\FamilyMember;
use Carbon\Carbon;

class FamilyMemberObserver
{
    /**
     * Handle the FamilyMember "creating" event.
     */
    public function creating(FamilyMember $familyMember): void
    {
        // Menghitung umur otomatis berdasarkan tanggal lahir
        if ($familyMember->birth_date) {
            $familyMember->age = Carbon::parse($familyMember->birth_date)->age;
        }
    }

    /**
     * Handle the FamilyMember "updating" event.
     */
    public function updating(FamilyMember $familyMember): void
    {
        // Menghitung umur otomatis berdasarkan tanggal lahir
        if ($familyMember->birth_date) {
            $familyMember->age = Carbon::parse($familyMember->birth_date)->age;
        }
    }
}
