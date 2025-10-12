<?php

namespace App\Http\Controllers;

use App\Models\FamilyMember;
use App\Models\SpmTarget;
use App\Models\Village;
use App\Models\SpmIndicator;
use App\Services\SpmDenominatorService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SpmController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dashboard(Request $request)
    {
        $year = (int) ($request->input('year', now()->year));
        $villageId = $request->input('village_id');
        $month = $request->input('month'); // 1..12 opsional
        $indicatorId = $request->input('indicator_id'); // opsional: filter indikator

        // Period filter: per bulan (jika ada), jika tidak seluruh tahun
        if ($month && (int)$month >= 1 && (int)$month <= 12) {
            $periodStart = Carbon::create($year, (int)$month, 1)->startOfDay();
            $periodEnd = (clone $periodStart)->endOfMonth();
        } else {
            $periodStart = Carbon::create($year, 1, 1)->startOfDay();
            $periodEnd = Carbon::create($year, 12, 31)->endOfDay();
            $month = null;
        }

        $base = FamilyMember::query()
            ->when($villageId, function ($q) use ($villageId) {
                $q->whereHas('family.building', function ($b) use ($villageId) {
                    $b->where('village_id', $villageId);
                });
            });

        $denominator = app(SpmDenominatorService::class);
        $indicatorOptions = SpmIndicator::orderBy('code')->pluck('name', 'id');
        $indicatorsQuery = SpmIndicator::with('subIndicators')->orderBy('code');
        if ($indicatorId) { $indicatorsQuery->where('id', $indicatorId); }
        $indicators = $indicatorsQuery->get();
        $results = [];
        $overviewLabels = [];
        $overviewActuals = [];
        $overviewTargets = [];
        $overviewDenoms = [];
        $overviewPotentials = [];

        foreach ($indicators as $indicator) {
            $subs = [];
            $sumNumerator = 0;
            $sumTargetAbs = 0;
            $hasAnyTarget = false;
            $sumDenomDinkes = 0;
            $sumDenomRiil = 0;
            $hasAnyDenom = false;
            $hasAnyDenomRiil = false;
            foreach ($indicator->subIndicators as $sub) {
                // Numerator: rekam medis pada tahun berjalan yang bertaut ke sub‑indikator (pivot)
                // Backward-compat: juga hitung data lama yang masih memakai kolom spm_service_type == code
                $numerator = \App\Models\MedicalRecord::query()
                    ->when($villageId, function ($q) use ($villageId) {
                        $q->whereHas('familyMember.family.building', function ($b) use ($villageId) {
                            $b->where('village_id', $villageId);
                        });
                    })
                    ->whereBetween('visit_date', [$periodStart, $periodEnd])
                    ->where(function($q) use ($sub){
                        $q->whereHas('spmSubIndicators', function($w) use ($sub){
                            $w->where('spm_sub_indicators.id', $sub->id);
                        })->orWhere('spm_service_type', $sub->code);
                    })
                    ->count();

                // Override numerator jika ada (admin)
                $isOverridden = false;
                if ($month) {
                    // Bulanan: pakai override bulan jika ada
                    $overrideMonthly = \App\Models\SpmAchievementOverride::query()
                        ->where('spm_sub_indicator_id', $sub->id)
                        ->where('year', $year)
                        ->where('month', (int)$month)
                        ->when($villageId, fn($q) => $q->where('village_id', $villageId))
                        ->first();
                    if ($overrideMonthly) {
                        $numerator = (int) $overrideMonthly->value;
                        $isOverridden = true;
                    }
                } else {
                    // Tahunan: prioritas override tahunan, jika tidak ada gunakan penjumlahan override bulanan (jika tersedia), jika tidak ada pakai angka otomatis
                    $overrideYearly = \App\Models\SpmAchievementOverride::query()
                        ->where('spm_sub_indicator_id', $sub->id)
                        ->where('year', $year)
                        ->whereNull('month')
                        ->when($villageId, fn($q) => $q->where('village_id', $villageId))
                        ->first();
                    if ($overrideYearly) {
                        $numerator = (int) $overrideYearly->value;
                        $isOverridden = true;
                    } else {
                        $monthlyQ = \App\Models\SpmAchievementOverride::query()
                            ->where('spm_sub_indicator_id', $sub->id)
                            ->where('year', $year)
                            ->whereNotNull('month')
                            ->when($villageId, fn($q) => $q->where('village_id', $villageId));
                        $monthlyCount = (clone $monthlyQ)->count();
                        if ($monthlyCount > 0) {
                            $monthlySum = (clone $monthlyQ)->sum('value');
                            $numerator = (int) $monthlySum;
                            $isOverridden = true;
                        }
                    }
                }

                // Denominator riil tergantung sub-indikator
                $denomCount = $denominator->countForSubIndicator($sub->code, $base, $periodStart, $periodEnd);
                
                // Khusus SPM_02_KB_AKTIF: gunakan status uses_contraception sebagai capaian (snapshot),
                // kecuali jika sudah dioverride oleh admin.
                if ($sub->code === 'SPM_02_KB_AKTIF' && !$isOverridden) {
                    $kbDenomQuery = $denominator->queryForSubIndicator($sub->code, $base, $periodStart, $periodEnd);
                    $numerator = (clone $kbDenomQuery)->where('uses_contraception', true)->count();
                }

                // Selaraskan numerator dengan populasi denominator untuk sub tertentu (menghindari mismatch)
                if (!$isOverridden && in_array($sub->code, ['SPM_04_ASI_EKS','SPM_04_IDL'])) {
                    $denomIds = $denominator
                        ->queryForSubIndicator($sub->code, $base, $periodStart, $periodEnd)
                        ->pluck('id');

                    $numerator = \App\Models\MedicalRecord::query()
                        ->when($villageId, function ($q) use ($villageId) {
                            $q->whereHas('familyMember.family.building', function ($b) use ($villageId) {
                                $b->where('village_id', $villageId);
                            });
                        })
                        ->whereBetween('visit_date', [$periodStart, $periodEnd])
                        ->where(function($q) use ($sub){
                            $q->whereHas('spmSubIndicators', function($w) use ($sub){
                                $w->where('spm_sub_indicators.id', $sub->id);
                            })->orWhere('spm_service_type', $sub->code);
                        })
                        ->whereIn('family_member_id', $denomIds)
                        ->count();
                }

                $percentage = $denomCount > 0 ? round(($numerator / $denomCount) * 100, 2) : 0.0;

                // Target dari tabel spm_targets berdasarkan sub-indicator id
                $target = SpmTarget::query()
                    ->where('year', $year)
                    ->where('spm_sub_indicator_id', $sub->id)
                    ->when($villageId, fn($q) => $q->where('village_id', $villageId))
                    ->first();
                if (!$target) {
                    $target = SpmTarget::query()->where('year', $year)->whereNull('village_id')->where('spm_sub_indicator_id', $sub->id)->first();
                }

                $denominatorDinkes = $target?->denominator_dinkes;
                $targetPercentage = $target?->target_percentage;
                $targetAbsolute = ($denominatorDinkes && $targetPercentage !== null) ? round($denominatorDinkes * ($targetPercentage / 100)) : null;

                // Target bulanan eksplisit (jika filter bulan aktif), override perhitungan tahunan
                $monthlyTarget = null;
                if ($month) {
                    $monthlyTarget = \App\Models\SpmMonthlyTarget::query()
                        ->where('year', $year)
                        ->where('month', (int)$month)
                        ->where('spm_sub_indicator_id', $sub->id)
                        ->when($villageId, fn($q) => $q->where('village_id', $villageId))
                        ->first()
                        ?: \App\Models\SpmMonthlyTarget::query()
                            ->where('year', $year)
                            ->where('month', (int)$month)
                            ->where('spm_sub_indicator_id', $sub->id)
                            ->whereNull('village_id')
                            ->first();
                    if ($monthlyTarget) {
                        $targetAbsolute = (int)$monthlyTarget->target_absolute;
                    }
                }
                $gap = ($targetAbsolute !== null) ? ($numerator - $targetAbsolute) : null;

                $subs[] = [
                    'code' => $sub->code,
                    'name' => $sub->name,
                    'id'   => $sub->id,
                    'numerator_riil' => $numerator,
                    'denominator_riil' => $denomCount,
                    'percentage_riil' => $percentage,
                    'denominator_dinkes' => $denominatorDinkes,
                    'target_percentage' => $targetPercentage,
                    'target_absolute' => $targetAbsolute,
                    'monthly_target' => $month ? ($monthlyTarget?->target_absolute) : null,
                    'monthly_achieved' => ($month && $monthlyTarget) ? ($numerator >= (int)$monthlyTarget->target_absolute) : null,
                    'gap' => $gap,
                    'is_overridden' => $isOverridden,
                    'detail_url' => route('spm.sub.detail', ['sub' => $sub->id, 'year' => $year, 'month' => $month, 'village_id' => $villageId]),
                ];

                // Aggregate for overview chart
                $sumNumerator += $numerator;
                if ($targetAbsolute !== null) {
                    $sumTargetAbs += $targetAbsolute;
                    $hasAnyTarget = true;
                }
                if ($denominatorDinkes !== null) {
                    $sumDenomDinkes += $denominatorDinkes;
                    $hasAnyDenom = true;
                }
                // Sum denominator riil (potensial) untuk indikator ini
                $sumDenomRiil += $denomCount;
                $hasAnyDenomRiil = true;
            }

            $results[] = [
                'code' => $indicator->code,
                'name' => $indicator->name,
                'subs' => $subs,
            ];

            $overviewLabels[] = $indicator->name;
            $overviewActuals[] = $sumNumerator;
            $overviewTargets[] = $hasAnyTarget ? $sumTargetAbs : null;
            $overviewDenoms[] = $hasAnyDenom ? $sumDenomDinkes : null;
            $overviewPotentials[] = $hasAnyDenomRiil ? $sumDenomRiil : null;
        }

        $villages = Village::orderBy('name')->pluck('name', 'id');

        $view = (auth()->check() && method_exists(auth()->user(), 'hasAnyRole') && auth()->user()->hasAnyRole(['super_admin','pegawai','nakes']))
            ? 'spm.dashboard-admin'
            : 'spm.dashboard';

        return view($view, [
            'year' => $year,
            'month' => $month,
            'villageId' => $villageId,
            'villages' => $villages,
            'indicatorOptions' => $indicatorOptions,
            'indicatorId' => $indicatorId,
            'tree' => $results,
            'overview' => [
                'labels' => $overviewLabels,
                'actuals' => $overviewActuals,
                'targets' => $overviewTargets, // kept for backward compatibility (not shown in chart)
                'denoms'  => $overviewDenoms,  // Sasaran (Dinkes)
                'potentials' => $overviewPotentials, // Sasaran Potensial (denominator riil)
            ],
        ]);
    }

    public function subDetail(Request $request, \App\Models\SpmSubIndicator $sub)
    {
        $year = (int) ($request->input('year', now()->year));
        $villageId = $request->input('village_id');
        $month = $request->input('month');

        if ($month && (int)$month >= 1 && (int)$month <= 12) {
            $periodStart = Carbon::create($year, (int)$month, 1)->startOfDay();
            $periodEnd = (clone $periodStart)->endOfMonth();
        } else {
            $periodStart = Carbon::create($year, 1, 1)->startOfDay();
            $periodEnd = Carbon::create($year, 12, 31)->endOfDay();
            $month = null;
        }

        // Rekam medis yang match sub‑indikator (pivot atau fallback code)
        $mrQuery = \App\Models\MedicalRecord::query()
            ->when($villageId, function ($q) use ($villageId) {
                $q->whereHas('familyMember.family.building', function ($b) use ($villageId) {
                    $b->where('village_id', $villageId);
                });
            })
            ->whereBetween('visit_date', [$periodStart, $periodEnd])
            ->where(function($q) use ($sub){
                $q->whereHas('spmSubIndicators', function($w) use ($sub){
                    $w->where('spm_sub_indicators.id', $sub->id);
                })->orWhere('spm_service_type', $sub->code);
            })
            ->with(['familyMember.family.building.village']);

        // Ambil distinct pasien dan kunjungan terakhir untuk sub ini pada periode
        // Khusus KB aktif, gunakan status uses_contraception sebagai dasar capaian
        if ($sub->code === 'SPM_02_KB_AKTIF') {
            $baseMembers = \App\Models\FamilyMember::query()
                ->when($villageId, function ($q) use ($villageId) {
                    $q->whereHas('family.building', function ($b) use ($villageId) {
                        $b->where('village_id', $villageId);
                    });
                });
            $denominator = app(\App\Services\SpmDenominatorService::class);
            $kbDenomQuery = $denominator->queryForSubIndicator($sub->code, $baseMembers, $periodStart, $periodEnd);
            $memberIds = (clone $kbDenomQuery)->where('uses_contraception', true)->pluck('id')->unique()->values();
        } else {
            // Untuk sub anak tertentu, selaraskan daftar capaian dengan populasi denominator
            if (in_array($sub->code, ['SPM_04_ASI_EKS','SPM_04_IDL'])) {
                $baseMembers = \App\Models\FamilyMember::query()
                    ->when($villageId, function ($q) use ($villageId) {
                        $q->whereHas('family.building', function ($b) use ($villageId) {
                            $b->where('village_id', $villageId);
                        });
                    });
                $denominator = app(\App\Services\SpmDenominatorService::class);
                $denomQuery = $denominator->queryForSubIndicator($sub->code, $baseMembers, $periodStart, $periodEnd);
                $denomIds = (clone $denomQuery)->pluck('id');
                $memberIds = (clone $mrQuery)->whereIn('family_member_id', $denomIds)->pluck('family_member_id')->unique()->values();
            } else {
                $memberIds = (clone $mrQuery)->pluck('family_member_id')->unique()->values();
            }
        }
        $members = \App\Models\FamilyMember::with(['family.building.village'])
            ->whereIn('id', $memberIds)
            ->get()
            ->keyBy('id');

        $lastByMember = ($sub->code === 'SPM_02_KB_AKTIF')
            ? collect()
            : (clone $mrQuery)
                ->selectRaw('family_member_id, MAX(visit_date) as last_visit')
                ->groupBy('family_member_id')
                ->pluck('last_visit', 'family_member_id');

        $rows = $memberIds->map(function($id) use ($members, $lastByMember){
            $m = $members->get($id);
            $lv = $lastByMember->get($id);
            return [
                'id' => $id,
                'slug' => $m?->slug,
                'name' => $m?->name,
                'rm_number' => $m?->rm_number,
                'gender' => $m?->gender,
                'age' => $m?->age,
                'village' => $m?->family?->building?->village?->name,
                'last_visit' => $lv ? \Carbon\Carbon::parse($lv)->format('Y-m-d') : null,
            ];
        })->filter();

        $villages = \App\Models\Village::orderBy('name')->pluck('name', 'id');

        // Populasi sasaran (potensial) sesuai denominator
        $baseMembers = \App\Models\FamilyMember::query()
            ->when($villageId, function ($q) use ($villageId) {
                $q->whereHas('family.building', function ($b) use ($villageId) {
                    $b->where('village_id', $villageId);
                });
            });
        $denominator = app(\App\Services\SpmDenominatorService::class);
        $denomQuery = $denominator->queryForSubIndicator($sub->code, $baseMembers, $periodStart, $periodEnd);
        $potentialMembers = $denomQuery->with(['family.building.village'])->get();
        $achievedSet = collect($memberIds)->flip();
        $potentialRows = $potentialMembers->map(function($m) use ($achievedSet) {
            return [
                'id' => $m->id,
                'slug' => $m->slug,
                'name' => $m->name,
                'rm_number' => $m->rm_number,
                'gender' => $m->gender,
                'age' => $m->age,
                'village' => optional($m->family->building->village)->name,
                'status' => $achievedSet->has($m->id) ? 'Tercatat' : 'Belum tercatat',
            ];
        });

        // Pra‑sasaran potensial (Bumil) untuk KN1/KN2/KN3: informasi perencanaan, tidak mempengaruhi perhitungan
        $prePotentialRows = collect();
        if (in_array($sub->code, ['SPM_03_KN1','SPM_03_KN2','SPM_03_KN3'])) {
            $pregnantQ = \App\Models\FamilyMember::query()
                ->where('gender', 'Perempuan')
                ->where('is_pregnant', true)
                ->when($villageId, function ($q) use ($villageId) {
                    $q->whereHas('family.building', function ($b) use ($villageId) {
                        $b->where('village_id', $villageId);
                    });
                })
                ->with(['family.building.village']);
            $prePotentialRows = $pregnantQ->get()->map(function($m){
                return [
                    'id' => $m->id,
                    'slug' => $m->slug,
                    'name' => $m->name,
                    'rm_number' => $m->rm_number,
                    'gender' => $m->gender,
                    'age' => $m->age,
                    'village' => optional($m->family->building->village)->name,
                ];
            });
        }

        // Daftar keluarga dengan ODGJ (level keluarga) — khusus untuk SPM_10
        $familiesRows = collect();
        if (str_starts_with($sub->code, 'SPM_10')) {
            $famQuery = \App\Models\Family::where('has_mental_illness', true)
                ->when($villageId, function ($q) use ($villageId) {
                    $q->whereHas('building', function ($b) use ($villageId) {
                        $b->where('village_id', $villageId);
                    });
                })
                ->with(['building.village', 'members']);

            $familiesRows = $famQuery->get()->map(function($f) {
                $odgjCount = $f->members->where('has_mental_disorder', true)->count();
                // Prefer member whose name matches head_name, else first member
                $head = $f->members->firstWhere('name', $f->head_name) ?: $f->members->first();
                $cardUrl = $head ? route('families.card.member', $head) : null;
                return [
                    'id' => $f->id,
                    'family_number' => $f->family_number,
                    'head_name' => $f->head_name,
                    'village' => optional($f->building->village)->name,
                    'odgj_count' => $odgjCount,
                    'card_url' => $cardUrl,
                ];
            });
        }

        $view = (auth()->check() && method_exists(auth()->user(), 'hasAnyRole') && auth()->user()->hasAnyRole(['super_admin','pegawai','nakes']))
            ? 'spm.sub-detail-admin'
            : 'spm.sub-detail';

        return view($view, [
            'sub' => $sub,
            'year' => $year,
            'month' => $month,
            'villageId' => $villageId,
            'villages' => $villages,
            'rows' => $rows,
            'potential' => $potentialRows,
            'prePotential' => $prePotentialRows,
            'families' => $familiesRows,
        ]);
    }
}
