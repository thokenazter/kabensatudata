<?php

namespace App\Http\Controllers;

use App\Models\FamilyMember;
use App\Models\Family;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Services\MemberIndicatorService;
use App\Services\FamilyIndicatorService;

class FamilyMemberController extends Controller
{
    // Tambahkan properti untuk menyimpan service
    protected $memberIndicatorService;
    protected $familyIndicatorService;

    /**
     * Create a new controller instance.
     * 
     * @param \App\Services\MemberIndicatorService $memberIndicatorService
     * @param \App\Services\FamilyIndicatorService $familyIndicatorService
     * @return void
     */
    public function __construct(
        MemberIndicatorService $memberIndicatorService,
        FamilyIndicatorService $familyIndicatorService
    ) {
        $this->memberIndicatorService = $memberIndicatorService;
        $this->familyIndicatorService = $familyIndicatorService;
    }

    /**
     * Display the specified family member.
     * 
     * @param \App\Models\FamilyMember $familyMember
     * @return \Illuminate\View\View
     */
    public function show(FamilyMember $familyMember)
    {
        // Load relasi yang diperlukan
        $familyMember->load([
            'family.healthIndex',
            'family.building.village',
            'family.members' // Penting untuk agregasi indikator keluarga
        ]);

        // Dapatkan indikator yang relevan untuk anggota keluarga
        $relevantIndicators = $this->memberIndicatorService->getRelevantIndicators($familyMember);

        // Dapatkan indikator agregat keluarga
        $familyIndicators = $this->familyIndicatorService->getAggregateIndicators($familyMember->family);

        return view('family-members.show', [
            'familyMember' => $familyMember,
            'relevantIndicators' => $relevantIndicators,
            'familyIndicators' => $familyIndicators
        ]);
    }

    /**
     * Menampilkan hanya indikator kesehatan anggota keluarga.
     * 
     * @param \App\Models\FamilyMember $familyMember
     * @return \Illuminate\View\View
     */
    public function indicators(FamilyMember $familyMember)
    {
        // Load relasi yang diperlukan
        $familyMember->load('family.healthIndex');

        // Dapatkan indikator yang relevan
        $relevantIndicators = $this->memberIndicatorService->getRelevantIndicators($familyMember);

        return view('family-members.indicators', [
            'familyMember' => $familyMember,
            'relevantIndicators' => $relevantIndicators
        ]);
    }

    /**
     * Menampilkan indikator agregat kesehatan seluruh keluarga.
     * 
     * @param \App\Models\FamilyMember $familyMember
     * @return \Illuminate\View\View
     */
    public function familyIndicators(FamilyMember $familyMember)
    {
        // Load relasi yang diperlukan
        $familyMember->load([
            'family.healthIndex',
            'family.members'
        ]);

        // Dapatkan indikator agregat keluarga
        $familyIndicators = $this->familyIndicatorService->getAggregateIndicators($familyMember->family);

        return view('family-members.family-indicators', [
            'familyMember' => $familyMember,
            'familyIndicators' => $familyIndicators
        ]);
    }

    /**
     * Tampilkan dashboard indikator untuk keluarga tertentu.
     * 
     * @param \App\Models\Family $family
     * @return \Illuminate\View\View
     */
    public function familyDashboard(Family $family)
    {
        // Load relasi yang diperlukan
        $family->load([
            'healthIndex',
            'members',
            'building.village'
        ]);

        // Dapatkan indikator agregat keluarga
        $familyIndicators = $this->familyIndicatorService->getAggregateIndicators($family);

        return view('families.dashboard', [
            'family' => $family,
            'familyIndicators' => $familyIndicators
        ]);
    }

    // Method lain yang mungkin sudah ada dalam controller
}
