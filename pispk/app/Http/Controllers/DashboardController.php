<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\Village;
use App\Models\Building;
use App\Models\FamilyMember;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // ini baru bikin
        // Ambil data statistik untuk dashboard
        $totalFamilies = Family::count();
        $totalMembers = FamilyMember::count();
        $totalBuildings = Building::count();

        // Ambil data anggota keluarga untuk ditampilkan
        $recentMembers = FamilyMember::with('family')
            ->latest()
            ->take(10)
            ->get();

        // Anda juga bisa menambahkan statistik lain
        $maleCount = FamilyMember::where('gender', 'Laki-laki')->count();
        $femaleCount = FamilyMember::where('gender', 'Perempuan')->count();

        // Kirim data ke view dashboard
        // Ambil data statistik untuk dashboard
        $totalFamilies = Family::count();
        $totalMembers = FamilyMember::count();
        $totalBuildings = Building::count();

        // Query dasar untuk data anggota keluarga
        $membersQuery = FamilyMember::with('family');

        // Cek apakah ada permintaan pencarian
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $membersQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        // Ambil anggota keluarga terbaru (dengan filter pencarian jika ada)
        $recentMembers = $membersQuery->latest()->take(1)->get();

        // ini baru bikin

        // Get filter parameters
        $villageId = $request->input('village_id');
        $educationLevel = $request->input('education');
        $healthIssue = $request->input('health_issue');
        $sanitationFilter = $request->input('sanitation_filter');

        // Base queries with filters
        $membersQuery = FamilyMember::query()
            ->when($villageId, function ($query) use ($villageId) {
                $query->whereHas('family.building', function ($q) use ($villageId) {
                    $q->where('village_id', $villageId);
                });
            })
            ->when($educationLevel, function ($query) use ($educationLevel) {
                $query->where('education', $educationLevel);
            })
            ->when($healthIssue, function ($query) use ($healthIssue) {
                if (in_array($healthIssue, ['tuberculosis', 'hypertension', 'chronic_cough'])) {
                    $columnName = 'has_' . $healthIssue;
                    $query->where($columnName, true);
                } elseif (in_array($healthIssue, ['mental_illness', 'restrained_member'])) {
                    $columnName = 'has_' . $healthIssue;
                    $query->whereHas('family', function ($q) use ($columnName) {
                        $q->where($columnName, true);
                    });
                }
            })
            ->when($sanitationFilter, function ($query) use ($sanitationFilter) {
                switch ($sanitationFilter) {
                    case 'clean_water':
                        return $query->whereHas('family', function ($q) {
                            $q->where('has_clean_water', true);
                        });
                    case 'protected_water':
                        return $query->whereHas('family', function ($q) {
                            $q->where('has_clean_water', true)
                                ->where('is_water_protected', true);
                        });
                    case 'toilet':
                        return $query->whereHas('family', function ($q) {
                            $q->where('has_toilet', true);
                        });
                    case 'sanitary_toilet':
                        return $query->whereHas('family', function ($q) {
                            $q->where('has_toilet', true)
                                ->where('is_toilet_sanitary', true);
                        });
                    case 'no_toilet':
                        return $query->whereHas('family', function ($q) {
                            $q->where('has_toilet', false);
                        });
                    case 'no_clean_water':
                        return $query->whereHas('family', function ($q) {
                            $q->where('has_clean_water', false);
                        });
                    default:
                        return $query;
                }
            });

        $familiesQuery = Family::query()
            ->when($villageId, function ($query) use ($villageId) {
                $query->whereHas('building', function ($q) use ($villageId) {
                    $q->where('village_id', $villageId);
                });
            })
            ->when($sanitationFilter, function ($query) use ($sanitationFilter) {
                switch ($sanitationFilter) {
                    case 'clean_water':
                        return $query->where('has_clean_water', true);
                    case 'protected_water':
                        return $query->where('has_clean_water', true)
                            ->where('is_water_protected', true);
                    case 'toilet':
                        return $query->where('has_toilet', true);
                    case 'sanitary_toilet':
                        return $query->where('has_toilet', true)
                            ->where('is_toilet_sanitary', true);
                    case 'no_toilet':
                        return $query->where('has_toilet', false);
                    case 'no_clean_water':
                        return $query->where('has_clean_water', false);
                    default:
                        return $query;
                }
            });

        // Get all villages for filter dropdown
        $villages = Village::orderBy('name')->get();

        // Get education levels for filter dropdown
        $educationLevels = [
            'Tidak Pernah Sekolah',
            'Tidak Tamat SD/MI',
            'Tamat SD/MI',
            'Tamat SMP/MTs',
            'Tamat SMA/MA/SMK',
            'Tamat D1/D2/D3',
            'Tamat D4/S1',
            'Tamat S2/S3'
        ];

        // Calculate statistics
        $stats = [
            'total_members' => $membersQuery->count(),
            'total_families' => $familiesQuery->count(),
            'tbc_count' => FamilyMember::query()
                ->when($villageId, function ($query) use ($villageId) {
                    $query->whereHas('family.building', function ($q) use ($villageId) {
                        $q->where('village_id', $villageId);
                    });
                })
                ->where('has_tuberculosis', true)
                ->count(),
            'hypertension_count' => FamilyMember::query()
                ->when($villageId, function ($query) use ($villageId) {
                    $query->whereHas('family.building', function ($q) use ($villageId) {
                        $q->where('village_id', $villageId);
                    });
                })
                ->where('has_hypertension', true)
                ->count(),
            'chronic_cough_count' => FamilyMember::query()
                ->when($villageId, function ($query) use ($villageId) {
                    $query->whereHas('family.building', function ($q) use ($villageId) {
                        $q->where('village_id', $villageId);
                    });
                })
                ->where('has_chronic_cough', true)
                ->count(),
            'mental_illness_count' => Family::query()
                ->when($villageId, function ($query) use ($villageId) {
                    $query->whereHas('building', function ($q) use ($villageId) {
                        $q->where('village_id', $villageId);
                    });
                })
                ->where('has_mental_illness', true)
                ->count(),
            'restrained_count' => Family::query()
                ->when($villageId, function ($query) use ($villageId) {
                    $query->whereHas('building', function ($q) use ($villageId) {
                        $q->where('village_id', $villageId);
                    });
                })
                ->where('has_restrained_member', true)
                ->count(),
        ];

        // Gender distribution statistics
        $genderStats = FamilyMember::query()
            ->when($villageId, function ($query) use ($villageId) {
                $query->whereHas('family.building', function ($q) use ($villageId) {
                    $q->where('village_id', $villageId);
                });
            })
            ->when($educationLevel, function ($query) use ($educationLevel) {
                $query->where('education', $educationLevel);
            })
            ->when($healthIssue, function ($query) use ($healthIssue) {
                if (in_array($healthIssue, ['tuberculosis', 'hypertension', 'chronic_cough'])) {
                    $columnName = 'has_' . $healthIssue;
                    $query->where($columnName, true);
                } elseif (in_array($healthIssue, ['mental_illness', 'restrained_member'])) {
                    $columnName = 'has_' . $healthIssue;
                    $query->whereHas('family', function ($q) use ($columnName) {
                        $q->where($columnName, true);
                    });
                }
            })
            ->select('gender', DB::raw('count(*) as total'))
            ->groupBy('gender')
            ->pluck('total', 'gender')
            ->toArray();

        // Age distribution statistics
        $ageStats = [
            '0-5' => 0,
            '6-12' => 0,
            '13-17' => 0,
            '18-30' => 0,
            '31-50' => 0,
            '>50' => 0
        ];

        // Clone query for each age group to avoid resetting state
        $ageGroupRanges = [
            '0-5' => [0, 5],
            '6-12' => [6, 12],
            '13-17' => [13, 17],
            '18-30' => [18, 30],
            '31-50' => [31, 50],
            '>50' => [51, 150]
        ];

        foreach ($ageGroupRanges as $group => [$min, $max]) {
            $ageStats[$group] = (clone $membersQuery)
                ->whereRaw("TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) >= ?", [$min])
                ->whereRaw("TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) <= ?", [$max])
                ->count();
        }

        // Education distribution statistics
        $educationStats = FamilyMember::query()
            ->when($villageId, function ($query) use ($villageId) {
                $query->whereHas('family.building', function ($q) use ($villageId) {
                    $q->where('village_id', $villageId);
                });
            })
            ->when($educationLevel, function ($query) use ($educationLevel) {
                $query->where('education', $educationLevel);
            })
            ->when($healthIssue, function ($query) use ($healthIssue) {
                if (in_array($healthIssue, ['tuberculosis', 'hypertension', 'chronic_cough'])) {
                    $columnName = 'has_' . $healthIssue;
                    $query->where($columnName, true);
                } elseif (in_array($healthIssue, ['mental_illness', 'restrained_member'])) {
                    $columnName = 'has_' . $healthIssue;
                    $query->whereHas('family', function ($q) use ($columnName) {
                        $q->where($columnName, true);
                    });
                }
            })
            ->whereNotNull('education')
            ->select('education', DB::raw('count(*) as total'))
            ->groupBy('education')
            ->pluck('total', 'education')
            ->toArray();

        // Sanitation statistics - water and toilet
        $sanitationStats = [];

        // Clean water stats
        $cleanWaterCount = Family::query()
            ->when($villageId, function ($query) use ($villageId) {
                $query->whereHas('building', function ($q) use ($villageId) {
                    $q->where('village_id', $villageId);
                });
            })
            ->where('has_clean_water', true)
            ->count();

        $sanitationStats['clean_water_count'] = $cleanWaterCount;
        $sanitationStats['clean_water_percentage'] = $stats['total_families'] > 0
            ? ($cleanWaterCount / $stats['total_families']) * 100
            : 0;

        // Protected water source stats
        $protectedWaterCount = Family::query()
            ->when($villageId, function ($query) use ($villageId) {
                $query->whereHas('building', function ($q) use ($villageId) {
                    $q->where('village_id', $villageId);
                });
            })
            ->where('has_clean_water', true)
            ->where('is_water_protected', true)
            ->count();

        $sanitationStats['protected_water_count'] = $protectedWaterCount;
        $sanitationStats['protected_water_percentage'] = $cleanWaterCount > 0
            ? ($protectedWaterCount / $cleanWaterCount) * 100
            : 0;

        // Toilet stats
        $toiletCount = Family::query()
            ->when($villageId, function ($query) use ($villageId) {
                $query->whereHas('building', function ($q) use ($villageId) {
                    $q->where('village_id', $villageId);
                });
            })
            ->where('has_toilet', true)
            ->count();

        $sanitationStats['toilet_count'] = $toiletCount;
        $sanitationStats['toilet_percentage'] = $stats['total_families'] > 0
            ? ($toiletCount / $stats['total_families']) * 100
            : 0;

        // Sanitary toilet stats
        $sanitaryToiletCount = Family::query()
            ->when($villageId, function ($query) use ($villageId) {
                $query->whereHas('building', function ($q) use ($villageId) {
                    $q->where('village_id', $villageId);
                });
            })
            ->where('has_toilet', true)
            ->where('is_toilet_sanitary', true)
            ->count();

        $sanitationStats['sanitary_toilet_count'] = $sanitaryToiletCount;
        $sanitationStats['sanitary_toilet_percentage'] = $toiletCount > 0
            ? ($sanitaryToiletCount / $toiletCount) * 100
            : 0;

        // Statistik sanitasi per desa
        $sanitationByVillage = Village::with(['buildings.families'])
            ->get()
            ->map(function ($village) {
                $totalFamilies = 0;
                $cleanWaterCount = 0;
                $protectedWaterCount = 0;
                $toiletCount = 0;
                $sanitaryToiletCount = 0;

                foreach ($village->buildings as $building) {
                    foreach ($building->families as $family) {
                        $totalFamilies++;

                        if ($family->has_clean_water) {
                            $cleanWaterCount++;
                            if ($family->is_water_protected) {
                                $protectedWaterCount++;
                            }
                        }

                        if ($family->has_toilet) {
                            $toiletCount++;
                            if ($family->is_toilet_sanitary) {
                                $sanitaryToiletCount++;
                            }
                        }
                    }
                }

                $cleanWaterPercentage = $totalFamilies > 0 ? ($cleanWaterCount / $totalFamilies) * 100 : 0;
                $protectedWaterPercentage = $cleanWaterCount > 0 ? ($protectedWaterCount / $cleanWaterCount) * 100 : 0;
                $toiletPercentage = $totalFamilies > 0 ? ($toiletCount / $totalFamilies) * 100 : 0;
                $sanitaryToiletPercentage = $toiletCount > 0 ? ($sanitaryToiletCount / $toiletCount) * 100 : 0;

                return [
                    'id' => $village->id,
                    'name' => $village->name,
                    'total_families' => $totalFamilies,
                    'clean_water_count' => $cleanWaterCount,
                    'clean_water_percentage' => $cleanWaterPercentage,
                    'protected_water_count' => $protectedWaterCount,
                    'protected_water_percentage' => $protectedWaterPercentage,
                    'toilet_count' => $toiletCount,
                    'toilet_percentage' => $toiletPercentage,
                    'sanitary_toilet_count' => $sanitaryToiletCount,
                    'sanitary_toilet_percentage' => $sanitaryToiletPercentage,
                ];
            });

        // Get the latest family members for table display
        $latestMembers = FamilyMember::with(['family.building.village'])
            ->when($villageId, function ($query) use ($villageId) {
                $query->whereHas('family.building', function ($q) use ($villageId) {
                    $q->where('village_id', $villageId);
                });
            })
            ->when($educationLevel, function ($query) use ($educationLevel) {
                $query->where('education', $educationLevel);
            })
            ->when($healthIssue, function ($query) use ($healthIssue) {
                if (in_array($healthIssue, ['tuberculosis', 'hypertension', 'chronic_cough'])) {
                    $columnName = 'has_' . $healthIssue;
                    $query->where($columnName, true);
                } elseif (in_array($healthIssue, ['mental_illness', 'restrained_member'])) {
                    $columnName = 'has_' . $healthIssue;
                    $query->whereHas('family', function ($q) use ($columnName) {
                        $q->where($columnName, true);
                    });
                }
            })
            ->latest()
            ->take(10)
            ->get();

        $tbcCases = FamilyMember::with(['family.building.village'])
            ->where('has_tuberculosis', true)
            ->when($villageId, function ($query) use ($villageId) {
                $query->whereHas('family.building', function ($q) use ($villageId) {
                    $q->where('village_id', $villageId);
                });
            })
            ->take(10)
            ->get();

        // Dapatkan detail untuk kasus Hipertensi (10 teratas)
        $hypertensionCases = FamilyMember::with(['family.building.village'])
            ->where('has_hypertension', true)
            ->when($villageId, function ($query) use ($villageId) {
                $query->whereHas('family.building', function ($q) use ($villageId) {
                    $q->where('village_id', $villageId);
                });
            })
            ->take(10)
            ->get();

        // Statistik Kesehatan Ibu
        $maternalStats = [];

        // Wanita usia 10-54 tahun, menikah
        $womenQuery = FamilyMember::query()
            ->where('gender', 'Perempuan')
            ->whereRaw("TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 10 AND 54")
            ->whereIn('marital_status', ['Kawin', 'Cerai Hidup', 'Cerai Mati'])
            ->when($villageId, function ($query) use ($villageId) {
                $query->whereHas('family.building', function ($q) use ($villageId) {
                    $q->where('village_id', $villageId);
                });
            })
            ->when($educationLevel, function ($query) use ($educationLevel) {
                $query->where('education', $educationLevel);
            })
            ->when($request->input('occupation'), function ($query, $occupation) {
                return $query->where('occupation', $occupation);
            });

        // Total wanita usia 10-54 tahun, menikah
        $totalWomen = (clone $womenQuery)->count();

        // Menggunakan KB
        $kbCount = (clone $womenQuery)->where('uses_contraception', true)->count();
        $maternalStats['kb_count'] = $kbCount;
        $maternalStats['kb_percentage'] = $totalWomen > 0 ? ($kbCount / $totalWomen) * 100 : 0;

        // Tidak menggunakan KB
        $noKbCount = (clone $womenQuery)->where(function ($query) {
            $query->where('uses_contraception', false)
                ->orWhereNull('uses_contraception');
        })->count();
        $maternalStats['no_kb_count'] = $noKbCount;
        $maternalStats['no_kb_percentage'] = $totalWomen > 0 ? ($noKbCount / $totalWomen) * 100 : 0;

        // Ibu hamil
        $pregnantCount = (clone $womenQuery)->where('is_pregnant', true)->count();
        $maternalStats['pregnant_count'] = $pregnantCount;
        $maternalStats['pregnant_percentage'] = $totalWomen > 0 ? ($pregnantCount / $totalWomen) * 100 : 0;

        // Bersalin di fasilitas kesehatan
        $healthFacilityBirthCount = (clone $womenQuery)
            ->where('gave_birth_in_health_facility', true)
            ->count();
        $maternalStats['health_facility_birth_count'] = $healthFacilityBirthCount;

        // Ambil sampel kasus untuk tooltip
        $kbCases = (clone $womenQuery)
            ->where('uses_contraception', true)
            ->take(10)
            ->get();

        $noKbCases = (clone $womenQuery)
            ->where(function ($query) {
                $query->where('uses_contraception', false)
                    ->orWhereNull('uses_contraception');
            })
            ->take(10)
            ->get();

        $pregnantCases = (clone $womenQuery)
            ->where('is_pregnant', true)
            ->take(10)
            ->get();

        $healthFacilityBirthCases = (clone $womenQuery)
            ->where('gave_birth_in_health_facility', true)
            ->take(10)
            ->get();

        // Statistik Kesehatan Anak
        $childStats = [];

        // Anak usia 7-23 bulan untuk ASI eksklusif
        $childrenQuery7to23 = FamilyMember::query()
            ->whereRaw("TIMESTAMPDIFF(MONTH, birth_date, CURDATE()) BETWEEN 7 AND 23")
            ->when($villageId, function ($query) use ($villageId) {
                $query->whereHas('family.building', function ($q) use ($villageId) {
                    $q->where('village_id', $villageId);
                });
            });

        // ASI eksklusif
        $exclusiveBreastfeedingCount = (clone $childrenQuery7to23)
            ->where('exclusive_breastfeeding', true)
            ->count();
        $childStats['exclusive_breastfeeding_count'] = $exclusiveBreastfeedingCount;
        $total7to23 = (clone $childrenQuery7to23)->count();
        $childStats['exclusive_breastfeeding_percentage'] = $total7to23 > 0 ?
            ($exclusiveBreastfeedingCount / $total7to23) * 100 : 0;

        // Anak usia 12-23 bulan untuk imunisasi
        $childrenQuery12to23 = FamilyMember::query()
            ->whereRaw("TIMESTAMPDIFF(MONTH, birth_date, CURDATE()) BETWEEN 12 AND 23")
            ->when($villageId, function ($query) use ($villageId) {
                $query->whereHas('family.building', function ($q) use ($villageId) {
                    $q->where('village_id', $villageId);
                });
            });

        // Imunisasi lengkap
        $completeImmunizationCount = (clone $childrenQuery12to23)
            ->where('complete_immunization', true)
            ->count();
        $childStats['complete_immunization_count'] = $completeImmunizationCount;
        $total12to23 = (clone $childrenQuery12to23)->count();
        $childStats['complete_immunization_percentage'] = $total12to23 > 0 ?
            ($completeImmunizationCount / $total12to23) * 100 : 0;

        // Anak usia 2-59 bulan untuk pemantauan pertumbuhan
        $childrenQuery2to59 = FamilyMember::query()
            ->whereRaw("TIMESTAMPDIFF(MONTH, birth_date, CURDATE()) BETWEEN 2 AND 59")
            ->when($villageId, function ($query) use ($villageId) {
                $query->whereHas('family.building', function ($q) use ($villageId) {
                    $q->where('village_id', $villageId);
                });
            });

        // Pemantauan pertumbuhan
        $growthMonitoringCount = (clone $childrenQuery2to59)
            ->where('growth_monitoring', true)
            ->count();
        $childStats['growth_monitoring_count'] = $growthMonitoringCount;
        $total2to59 = (clone $childrenQuery2to59)->count();
        $childStats['growth_monitoring_percentage'] = $total2to59 > 0 ?
            ($growthMonitoringCount / $total2to59) * 100 : 0;

        // Ambil sampel kasus anak untuk tooltip
        $exclusiveBreastfeedingCases = (clone $childrenQuery7to23)
            ->where('exclusive_breastfeeding', true)
            ->take(10)
            ->get();

        $completeImmunizationCases = (clone $childrenQuery12to23)
            ->where('complete_immunization', true)
            ->take(10)
            ->get();

        $growthMonitoringCases = (clone $childrenQuery2to59)
            ->where('growth_monitoring', true)
            ->take(10)
            ->get();

        // Dapatkan daftar pekerjaan yang ada
        $occupationList = FamilyMember::whereNotNull('occupation')
            ->select('occupation')
            ->distinct()
            ->pluck('occupation')
            ->toArray();

        // JKN
        $jknStats = [
            'total_members' => FamilyMember::count(),
            'jkn_count' => FamilyMember::where('has_jkn', true)->count(),
            'jkn_percentage' => FamilyMember::count() > 0
                ? (FamilyMember::where('has_jkn', true)->count() / FamilyMember::count()) * 100
                : 0
        ];

        $jknByVillage = Village::with(['buildings.families.members'])
            ->get()
            ->map(function ($village) {
                $members = $village->buildings->flatMap->families->flatMap->members;
                $totalMembers = $members->count();
                $jknCount = $members->where('has_jkn', true)->count();

                return [
                    'name' => $village->name,
                    'total_members' => $totalMembers,
                    'jkn_count' => $jknCount,
                    'jkn_percentage' => $totalMembers > 0 ? ($jknCount / $totalMembers) * 100 : 0
                ];
            });

        // Tambahkan ke variabel untuk view
        return view('dashboard.home', compact(
            'villages',
            'educationLevels',
            'stats',
            'genderStats',
            'ageStats',
            'educationStats',
            'sanitationStats',
            'sanitationByVillage',
            'maternalStats',
            'childStats',
            'kbCases',
            'noKbCases',
            'pregnantCases',
            'healthFacilityBirthCases',
            'exclusiveBreastfeedingCases',
            'completeImmunizationCases',
            'growthMonitoringCases',
            'tbcCases',
            'hypertensionCases',
            'latestMembers',
            'occupationList',
            'jknStats',
            'jknByVillage',
            'totalFamilies',
            'totalMembers',
            'totalBuildings',
            'recentMembers',
            'maleCount',
            'femaleCount'
        ));
    }
}
