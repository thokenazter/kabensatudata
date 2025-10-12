<?php

namespace App\Http\Controllers;

use App\Models\SpmMonthlyTarget;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SpmMonthlyTargetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function bulkEdit(Request $request)
    {
        $year = (int)($request->input('year', now()->year));
        $villageId = $request->input('village_id');
        $villages = Village::orderBy('name')->pluck('name', 'id');

        $subs = \App\Models\SpmSubIndicator::with('indicator')
            ->orderBy('spm_indicator_id')
            ->orderBy('code')
            ->get();

        $monthly = SpmMonthlyTarget::where('year', $year)
            ->when($villageId, fn($q)=>$q->where('village_id', $villageId))
            ->get()
            ->groupBy('spm_sub_indicator_id');

        $view = (auth()->check() && method_exists(auth()->user(), 'hasAnyRole') && auth()->user()->hasAnyRole(['super_admin','pegawai','nakes']))
            ? 'spm.targets.monthly-bulk-admin'
            : 'spm.targets.monthly-bulk';
        return view($view, compact('year','villageId','villages','subs','monthly'));
    }

    public function bulkUpdate(Request $request)
    {
        $data = $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'village_id' => 'nullable|exists:villages,id',
            'targets' => 'array',
            'targets.*.*' => 'nullable|integer|min:0', // targets[subId][month]=int
        ]);

        $year = (int)$data['year'];
        $villageId = $data['village_id'] ?? null;

        foreach (($data['targets'] ?? []) as $subId => $months) {
            foreach (($months ?? []) as $month => $val) {
                if ($val === null || $val === '') continue;
                SpmMonthlyTarget::updateOrCreate([
                    'year' => $year,
                    'month' => (int)$month,
                    'spm_sub_indicator_id' => (int)$subId,
                    'village_id' => $villageId,
                ], [
                    'target_absolute' => (int)$val,
                ]);
            }
        }

        return back()->with('success', 'Target bulanan berhasil disimpan.');
    }

    public function autoDistribute(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'village_id' => 'nullable|exists:villages,id',
            'method' => 'required|in:equal,potential',
            'sub_ids' => 'array',
            'sub_ids.*' => 'integer|exists:spm_sub_indicators,id',
        ]);

        $year = (int)$validated['year'];
        $villageId = $validated['village_id'] ?? null;
        $method = $validated['method'];
        $subIds = $validated['sub_ids'] ?? null;

        $subs = \App\Models\SpmSubIndicator::query()
            ->when($subIds, fn($q)=>$q->whereIn('id', $subIds))
            ->orderBy('spm_indicator_id')->orderBy('code')->get();

        $baseMembers = \App\Models\FamilyMember::query()
            ->when($villageId, function ($q) use ($villageId) {
                $q->whereHas('family.building', function ($b) use ($villageId) {
                    $b->where('village_id', $villageId);
                });
            });

        $denService = app(\App\Services\SpmDenominatorService::class);

        $suggestions = [];
        foreach ($subs as $sub) {
            // Annual target absolute from SpmTarget
            $t = \App\Models\SpmTarget::query()
                ->where('year', $year)
                ->where('spm_sub_indicator_id', $sub->id)
                ->when($villageId, fn($q)=>$q->where('village_id', $villageId))
                ->first()
                ?: \App\Models\SpmTarget::query()->where('year', $year)->whereNull('village_id')->where('spm_sub_indicator_id', $sub->id)->first();

            if (!$t) { continue; }
            $annualTarget = ($t->denominator_dinkes && $t->target_percentage !== null)
                ? (int) round($t->denominator_dinkes * ($t->target_percentage / 100))
                : null;
            if ($annualTarget === null) { continue; }

            // Weights
            $monthlyPotentials = [];
            $weights = [];
            $totalPotential = 0;
            if ($method === 'potential') {
                for ($m=1; $m<=12; $m++) {
                    $periodStart = \Carbon\Carbon::create($year, $m, 1)->startOfDay();
                    $periodEnd = (clone $periodStart)->endOfMonth();
                    $count = $denService->countForSubIndicator($sub->code, $baseMembers, $periodStart, $periodEnd);
                    $monthlyPotentials[$m] = $count;
                    $totalPotential += $count;
                }
                if ($totalPotential > 0) {
                    for ($m=1; $m<=12; $m++) {
                        $weights[$m] = $monthlyPotentials[$m] / $totalPotential;
                    }
                }
            }

            // Distribution
            $alloc = array_fill(1, 12, 0);
            if ($method === 'equal' || $totalPotential === 0) {
                $base = intdiv($annualTarget, 12);
                $rem = $annualTarget - ($base * 12);
                for ($m=1; $m<=12; $m++) { $alloc[$m] = $base; }
                // Distribute remainder to earliest months
                for ($m=1; $m<=12 && $rem>0; $m++, $rem--) { $alloc[$m]++ ; }
            } else {
                // Largest remainder method
                $floors = [];
                $fracs = [];
                $sum = 0;
                for ($m=1; $m<=12; $m++) {
                    $val = $annualTarget * $weights[$m];
                    $f = (int) floor($val);
                    $floors[$m] = $f;
                    $fracs[$m] = $val - $f;
                    $sum += $f;
                }
                $alloc = $floors;
                $rem = $annualTarget - $sum;
                // Sort months by fractional remainder desc, then potential desc, then month asc
                $order = range(1,12);
                usort($order, function($a,$b) use ($fracs,$monthlyPotentials){
                    if ($fracs[$a] === $fracs[$b]) {
                        // tie-break by potential
                        if (($monthlyPotentials[$a] ?? 0) === ($monthlyPotentials[$b] ?? 0)) return $a <=> $b;
                        return ($monthlyPotentials[$b] ?? 0) <=> ($monthlyPotentials[$a] ?? 0);
                    }
                    return ($fracs[$b] <=> $fracs[$a]);
                });
                for ($i=0; $i<count($order) && $rem>0; $i++, $rem--) {
                    $m = $order[$i];
                    $alloc[$m] += 1;
                }
            }

            $suggestions[$sub->id] = $alloc;
        }

        return response()->json([
            'ok' => true,
            'year' => $year,
            'village_id' => $villageId,
            'method' => $method,
            'suggestions' => $suggestions,
        ]);
    }
}
