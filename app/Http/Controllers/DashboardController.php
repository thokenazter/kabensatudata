<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\Village;
use App\Models\Building;
use App\Models\FamilyMember;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Ambil data parameter filter
        $villageId = $request->input('village_id');
        $educationLevel = $request->input('education');
        $healthIssue = $request->input('health_issue');
        $sanitationFilter = $request->input('sanitation_filter');

        // Cache key berdasarkan filter untuk data statistik
        $cacheKey = "dashboard.stats.{$villageId}.{$educationLevel}.{$healthIssue}.{$sanitationFilter}";

        // Ambil data statistik dari cache jika ada, atau hitung jika tidak ada
        $stats = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($villageId, $educationLevel, $healthIssue, $sanitationFilter) {
            // Menggunakan query builder untuk performa yang lebih baik
            $total_members = DB::table('family_members')
                ->when($villageId, function ($query) use ($villageId) {
                    $query->join('families', 'family_members.family_id', '=', 'families.id')
                        ->join('buildings', 'families.building_id', '=', 'buildings.id')
                        ->where('buildings.village_id', $villageId);
                })
                ->count();

            $total_families = DB::table('families')
                ->when($villageId, function ($query) use ($villageId) {
                    $query->join('buildings', 'families.building_id', '=', 'buildings.id')
                        ->where('buildings.village_id', $villageId);
                })
                ->count();

            $total_buildings = DB::table('buildings')
                ->when($villageId, function ($query) use ($villageId) {
                    $query->where('village_id', $villageId);
                })
                ->count();

            // Hitung statistik kesehatan
            $tbc_count = FamilyMember::where('has_tuberculosis', true)
                ->when($villageId, function ($query) use ($villageId) {
                    $query->whereHas('family.building', function ($q) use ($villageId) {
                        $q->where('village_id', $villageId);
                    });
                })
                ->count();

            $hypertension_count = FamilyMember::where('has_hypertension', true)
                ->when($villageId, function ($query) use ($villageId) {
                    $query->whereHas('family.building', function ($q) use ($villageId) {
                        $q->where('village_id', $villageId);
                    });
                })
                ->count();

            $chronic_cough_count = FamilyMember::where('has_chronic_cough', true)
                ->when($villageId, function ($query) use ($villageId) {
                    $query->whereHas('family.building', function ($q) use ($villageId) {
                        $q->where('village_id', $villageId);
                    });
                })
                ->count();

            $mental_illness_count = Family::where('has_mental_illness', true)
                ->when($villageId, function ($query) use ($villageId) {
                    $query->whereHas('building', function ($q) use ($villageId) {
                        $q->where('village_id', $villageId);
                    });
                })
                ->count();

            $restrained_count = Family::where('has_restrained_member', true)
                ->when($villageId, function ($query) use ($villageId) {
                    $query->whereHas('building', function ($q) use ($villageId) {
                        $q->where('village_id', $villageId);
                    });
                })
                ->count();

            return [
                'members' => $total_members,
                'families' => $total_families,
                'buildings' => $total_buildings,
                'villages' => Village::count(),
                'tbc_count' => $tbc_count,
                'hypertension_count' => $hypertension_count,
                'chronic_cough_count' => $chronic_cough_count,
                'mental_illness_count' => $mental_illness_count,
                'restrained_count' => $restrained_count,
            ];
        });

        // Get all villages for filter dropdown
        $villages = Village::orderBy('name')->get();

        // Get education levels for filter dropdown
        $educationLevels = [
            'Tidak Sekolah',
            'SD/MI',
            'SMP/MTs',
            'SMA/MA',
            'D1',
            'D2',
            'D3',
            'D4/S1',
            'S2',
            'S3'
        ];

        // Ambil anggota keluarga terbaru
        $recentMembers = FamilyMember::with(['family', 'family.building', 'family.building.village']);

        // Jika ada parameter pencarian
        if ($request->has('search') && !empty($request->search)) {
            $keyword = $request->search;
            $recentMembers = $recentMembers->where(function ($query) use ($keyword) {
                $query->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('nik', 'like', '%' . $keyword . '%')
                    ->orWhereHas('family', function ($query) use ($keyword) {
                        $query->where('family_number', 'like', '%' . $keyword . '%');
                    });
            });
        }

        // Ambil hasil pencarian
        $recentMembers = $recentMembers->latest()->take(10)->get();

        // Data untuk kasus kesehatan
        $tbcCases = FamilyMember::where('has_tuberculosis', true)
            ->when($villageId, function ($query) use ($villageId) {
                $query->whereHas('family.building', function ($q) use ($villageId) {
                    $q->where('village_id', $villageId);
                });
            })
            ->with(['family.building.village'])
            ->get();

        $hypertensionCases = FamilyMember::where('has_hypertension', true)
            ->when($villageId, function ($query) use ($villageId) {
                $query->whereHas('family.building', function ($q) use ($villageId) {
                    $q->where('village_id', $villageId);
                });
            })
            ->with(['family.building.village'])
            ->get();

        $chronicCoughCases = FamilyMember::where('has_chronic_cough', true)
            ->when($villageId, function ($query) use ($villageId) {
                $query->whereHas('family.building', function ($q) use ($villageId) {
                    $q->where('village_id', $villageId);
                });
            })
            ->get();

        $mentalIllnessCases = Family::where('has_mental_illness', true)
            ->when($villageId, function ($query) use ($villageId) {
                $query->whereHas('building', function ($q) use ($villageId) {
                    $q->where('village_id', $villageId);
                });
            })
            ->get();

        $restrainedMemberCases = Family::where('has_restrained_member', true)
            ->when($villageId, function ($query) use ($villageId) {
                $query->whereHas('building', function ($q) use ($villageId) {
                    $q->where('village_id', $villageId);
                });
            })
            ->get();

        // Data gender
        $genderStats = [
            'male' => FamilyMember::where('gender', 'Laki-laki')
                ->when($villageId, function ($query) use ($villageId) {
                    $query->whereHas('family.building', function ($q) use ($villageId) {
                        $q->where('village_id', $villageId);
                    });
                })
                ->count(),
            'female' => FamilyMember::where('gender', 'Perempuan')
                ->when($villageId, function ($query) use ($villageId) {
                    $query->whereHas('family.building', function ($q) use ($villageId) {
                        $q->where('village_id', $villageId);
                    });
                })
                ->count(),
        ];

        // Data maternal health
        $maternalStats = [
            'kb_count' => FamilyMember::where('gender', 'Perempuan')
                ->where('uses_contraception', true)
                ->when($villageId, function ($query) use ($villageId) {
                    $query->whereHas('family.building', function ($q) use ($villageId) {
                        $q->where('village_id', $villageId);
                    });
                })
                ->count(),
            'no_kb_count' => FamilyMember::where('gender', 'Perempuan')
                ->where('uses_contraception', false)
                ->when($villageId, function ($query) use ($villageId) {
                    $query->whereHas('family.building', function ($q) use ($villageId) {
                        $q->where('village_id', $villageId);
                    });
                })
                ->count(),
            'pregnant_count' => FamilyMember::where('gender', 'Perempuan')
                ->where('is_pregnant', true)
                ->when($villageId, function ($query) use ($villageId) {
                    $query->whereHas('family.building', function ($q) use ($villageId) {
                        $q->where('village_id', $villageId);
                    });
                })
                ->count(),
            'health_facility_birth_count' => FamilyMember::where('gave_birth_in_health_facility', true)
                ->when($villageId, function ($query) use ($villageId) {
                    $query->whereHas('family.building', function ($q) use ($villageId) {
                        $q->where('village_id', $villageId);
                    });
                })
                ->count(),
        ];

        // Data child health
        $childStats = [
            'exclusive_breastfeeding_count' => FamilyMember::where('exclusive_breastfeeding', true)
                ->when($villageId, function ($query) use ($villageId) {
                    $query->whereHas('family.building', function ($q) use ($villageId) {
                        $q->where('village_id', $villageId);
                    });
                })
                ->count(),
            'complete_immunization_count' => FamilyMember::where('complete_immunization', true)
                ->when($villageId, function ($query) use ($villageId) {
                    $query->whereHas('family.building', function ($q) use ($villageId) {
                        $q->where('village_id', $villageId);
                    });
                })
                ->count(),
            'growth_monitoring_count' => FamilyMember::where('growth_monitoring', true)
                ->when($villageId, function ($query) use ($villageId) {
                    $query->whereHas('family.building', function ($q) use ($villageId) {
                        $q->where('village_id', $villageId);
                    });
                })
                ->count(),
        ];

        // Data sanitasi
        $sanitationStats = [
            'clean_water_count' => Family::where('has_clean_water', true)
                ->when($villageId, function ($query) use ($villageId) {
                    $query->whereHas('building', function ($q) use ($villageId) {
                        $q->where('village_id', $villageId);
                    });
                })
                ->count(),
            'protected_water_count' => Family::where('has_clean_water', true)
                ->where('is_water_protected', true)
                ->when($villageId, function ($query) use ($villageId) {
                    $query->whereHas('building', function ($q) use ($villageId) {
                        $q->where('village_id', $villageId);
                    });
                })
                ->count(),
            'toilet_count' => Family::where('has_toilet', true)
                ->when($villageId, function ($query) use ($villageId) {
                    $query->whereHas('building', function ($q) use ($villageId) {
                        $q->where('village_id', $villageId);
                    });
                })
                ->count(),
            'sanitary_toilet_count' => Family::where('has_toilet', true)
                ->where('is_toilet_sanitary', true)
                ->when($villageId, function ($query) use ($villageId) {
                    $query->whereHas('building', function ($q) use ($villageId) {
                        $q->where('village_id', $villageId);
                    });
                })
                ->count(),
        ];

        // Menghitung persentase untuk sanitasi
        $totalFamilies = $stats['families'];
        $sanitationStats['clean_water_percentage'] = $totalFamilies > 0 ? ($sanitationStats['clean_water_count'] / $totalFamilies) * 100 : 0;
        $sanitationStats['protected_water_percentage'] = $sanitationStats['clean_water_count'] > 0 ? ($sanitationStats['protected_water_count'] / $sanitationStats['clean_water_count']) * 100 : 0;
        $sanitationStats['toilet_percentage'] = $totalFamilies > 0 ? ($sanitationStats['toilet_count'] / $totalFamilies) * 100 : 0;
        $sanitationStats['sanitary_toilet_percentage'] = $sanitationStats['toilet_count'] > 0 ? ($sanitationStats['sanitary_toilet_count'] / $sanitationStats['toilet_count']) * 100 : 0;

        // Data kasus KB
        $kbCases = FamilyMember::where('gender', 'Perempuan')
            ->where('uses_contraception', true)
            ->when($villageId, function ($query) use ($villageId) {
                $query->whereHas('family.building', function ($q) use ($villageId) {
                    $q->where('village_id', $villageId);
                });
            })
            ->with(['family.building.village'])
            ->get();

        // Data kasus Tidak KB
        $noKbCases = FamilyMember::where('gender', 'Perempuan')
            ->where('uses_contraception', false)
            ->when($villageId, function ($query) use ($villageId) {
                $query->whereHas('family.building', function ($q) use ($villageId) {
                    $q->where('village_id', $villageId);
                });
            })
            ->with(['family.building.village'])
            ->get();

        // Data kasus Bumil
        $pregnancyCases = FamilyMember::where('gender', 'Perempuan')
            ->where('is_pregnant', true)
            ->when($villageId, function ($query) use ($villageId) {
                $query->whereHas('family.building', function ($q) use ($villageId) {
                    $q->where('village_id', $villageId);
                });
            })
            ->with(['family.building.village'])
            ->get();

        // Data kasus Melahirkan di Fasilitas Kesehatan
        $healthFacilityBirthCases = FamilyMember::where('gave_birth_in_health_facility', true)
            ->when($villageId, function ($query) use ($villageId) {
                $query->whereHas('family.building', function ($q) use ($villageId) {
                    $q->where('village_id', $villageId);
                });
            })
            ->with(['family.building.village'])
            ->get();

        // Data kasus ASI Eksklusif
        $exclusiveBreastfeedingCases = FamilyMember::where('exclusive_breastfeeding', true)
            ->when($villageId, function ($query) use ($villageId) {
                $query->whereHas('family.building', function ($q) use ($villageId) {
                    $q->where('village_id', $villageId);
                });
            })
            ->with(['family.building.village'])
            ->get();

        // Data kasus Imunisasi Lengkap
        $completeImmunizationCases = FamilyMember::where('complete_immunization', true)
            ->when($villageId, function ($query) use ($villageId) {
                $query->whereHas('family.building', function ($q) use ($villageId) {
                    $q->where('village_id', $villageId);
                });
            })
            ->with(['family.building.village'])
            ->get();

        // Data kasus Pemantauan Pertumbuhan
        $growthMonitoringCases = FamilyMember::where('growth_monitoring', true)
            ->when($villageId, function ($query) use ($villageId) {
                $query->whereHas('family.building', function ($q) use ($villageId) {
                    $q->where('village_id', $villageId);
                });
            })
            ->with(['family.building.village'])
            ->get();

        // Data JKN
        $jknCount = FamilyMember::where('has_jkn', true)
            ->when($villageId, function ($query) use ($villageId) {
                $query->whereHas('family.building', function ($q) use ($villageId) {
                    $q->where('village_id', $villageId);
                });
            })
            ->count();

        $totalMembers = DB::table('family_members')
            ->when($villageId, function ($query) use ($villageId) {
                $query->join('families', 'family_members.family_id', '=', 'families.id')
                    ->join('buildings', 'families.building_id', '=', 'buildings.id')
                    ->where('buildings.village_id', $villageId);
            })
            ->count();

        $jknStats = [
            'members' => $totalMembers,
            'jkn_count' => $jknCount,
            'jkn_percentage' => $totalMembers > 0 ? ($jknCount / $totalMembers) * 100 : 0,
        ];

        // Data JKN by Village
        $jknByVillage = Village::with(['families.members'])
            ->get()
            ->map(function ($village) {
                // Hitung total member dan yang memiliki JKN
                $totalMembers = 0;
                $jknCount = 0;

                foreach ($village->families as $family) {
                    $totalMembers += $family->members->count();
                    $jknCount += $family->members->where('has_jkn', true)->count();
                }

                $jknPercentage = $totalMembers > 0 ? ($jknCount / $totalMembers) * 100 : 0;

                return [
                    'id' => $village->id,
                    'name' => $village->name,
                    'members' => $totalMembers,
                    'jkn_count' => $jknCount,
                    'jkn_percentage' => $jknPercentage
                ];
            });

        // Data sanitasi berdasarkan desa
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

        // Dapatkan data untuk IKS (Indeks Keluarga Sehat)
        $iksService = app(\App\Services\IksReportService::class);
        $overallData = $iksService->generateOverallReport();
        $villageData = $iksService->generateVillageReport()->take(5);

        // Hitung jumlah bangunan untuk statistic
        $totalBuildings = $stats['buildings'];

        // Data statistik pendidikan
        $educationQuery = FamilyMember::select('education', DB::raw('count(*) as total'))
            ->whereNotNull('education')
            ->when($villageId, function ($query) use ($villageId) {
                $query->whereHas('family.building', function ($q) use ($villageId) {
                    $q->where('village_id', $villageId);
                });
            })
            ->groupBy('education')
            ->get();

        $educationStats = [];
        foreach ($educationQuery as $item) {
            $educationStats[$item->education] = $item->total;
        }

        // Data statistik umur
        $now = now();
        $ageStats = [
            '0-5' => FamilyMember::where(DB::raw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE())'), '<=', 5)
                ->when($villageId, function ($query) use ($villageId) {
                    $query->whereHas('family.building', function ($q) use ($villageId) {
                        $q->where('village_id', $villageId);
                    });
                })
                ->count(),
            '6-12' => FamilyMember::where(DB::raw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE())'), '>', 5)
                ->where(DB::raw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE())'), '<=', 12)
                ->when($villageId, function ($query) use ($villageId) {
                    $query->whereHas('family.building', function ($q) use ($villageId) {
                        $q->where('village_id', $villageId);
                    });
                })
                ->count(),
            '13-17' => FamilyMember::where(DB::raw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE())'), '>', 12)
                ->where(DB::raw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE())'), '<=', 17)
                ->when($villageId, function ($query) use ($villageId) {
                    $query->whereHas('family.building', function ($q) use ($villageId) {
                        $q->where('village_id', $villageId);
                    });
                })
                ->count(),
            '18-30' => FamilyMember::where(DB::raw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE())'), '>', 17)
                ->where(DB::raw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE())'), '<=', 30)
                ->when($villageId, function ($query) use ($villageId) {
                    $query->whereHas('family.building', function ($q) use ($villageId) {
                        $q->where('village_id', $villageId);
                    });
                })
                ->count(),
            '31-50' => FamilyMember::where(DB::raw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE())'), '>', 30)
                ->where(DB::raw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE())'), '<=', 50)
                ->when($villageId, function ($query) use ($villageId) {
                    $query->whereHas('family.building', function ($q) use ($villageId) {
                        $q->where('village_id', $villageId);
                    });
                })
                ->count(),
            '>50' => FamilyMember::where(DB::raw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE())'), '>', 50)
                ->when($villageId, function ($query) use ($villageId) {
                    $query->whereHas('family.building', function ($q) use ($villageId) {
                        $q->where('village_id', $villageId);
                    });
                })
                ->count(),
        ];

        // Render view dengan semua data yang diperlukan
        return view('dashboard.home', compact(
            'stats',
            'villages',
            'educationLevels',
            'recentMembers',
            'villageId',
            'educationLevel',
            'healthIssue',
            'sanitationFilter',
            'genderStats',
            'maternalStats',
            'childStats',
            'sanitationStats',
            'overallData',
            'villageData',
            'totalBuildings',
            'tbcCases',
            'hypertensionCases',
            'chronicCoughCases',
            'mentalIllnessCases',
            'restrainedMemberCases',
            'kbCases',
            'noKbCases',
            'pregnancyCases',
            'healthFacilityBirthCases',
            'exclusiveBreastfeedingCases',
            'completeImmunizationCases',
            'growthMonitoringCases',
            'jknStats',
            'jknByVillage',
            'sanitationByVillage',
            'educationStats',
            'ageStats'
        ));
    }
}
