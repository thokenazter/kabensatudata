<?php

namespace App\Http\Controllers;

use App\Models\SpmTarget;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SpmTargetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $targets = SpmTarget::query()
            ->selectRaw('year, village_id, COUNT(*) as indicators')
            ->groupBy('year', 'village_id')
            ->orderBy('year', 'desc')
            ->orderByRaw('COALESCE(village_id, 0) asc')
            ->paginate(15);

        $villages = Village::orderBy('name')->pluck('name', 'id');

        return view('spm.targets.index', compact('targets', 'villages'));
    }

    public function create(Request $request)
    {
        $year = (int)($request->input('year', now()->year));
        $villageId = $request->input('village_id');
        $villages = Village::orderBy('name')->pluck('name', 'id');
        $subIndicators = \App\Models\SpmSubIndicator::with('indicator')->orderBy('code')->get();

        return view('spm.targets.create', compact('year', 'villageId', 'villages', 'subIndicators'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'village_id' => 'nullable|exists:villages,id',
            'spm_sub_indicator_id' => 'required|exists:spm_sub_indicators,id',
            'denominator_dinkes' => 'required|integer|min:0',
            'target_percentage' => 'required|numeric|min:0|max:100',
        ]);

        SpmTarget::updateOrCreate([
            'year' => $validated['year'],
            'village_id' => $validated['village_id'] ?? null,
            'spm_sub_indicator_id' => $validated['spm_sub_indicator_id'],
        ], [
            'denominator_dinkes' => (int) $validated['denominator_dinkes'],
            'target_percentage' => (float) $validated['target_percentage'],
        ]);

        return redirect()->route('targets.index')->with('success', 'Target SPM berhasil disimpan');
    }

    public function edit(Request $request, SpmTarget $spmTarget)
    {
        // Redirect ke halaman create dengan prefill agar konsisten bulk-edit
        return redirect()->route('targets.create', [
            'year' => $spmTarget->year,
            'village_id' => $spmTarget->village_id,
        ]);
    }

    public function update(Request $request, SpmTarget $spmTarget)
    {
        // Tidak digunakan (semua update dilakukan dalam bentuk bulk via store)
        abort(404);
    }

    public function destroy(SpmTarget $spmTarget)
    {
        $year = $spmTarget->year;
        $villageId = $spmTarget->village_id;
        SpmTarget::where('year', $year)
            ->where('village_id', $villageId)
            ->delete();
        return back()->with('success', 'Satu set target tahunan dihapus.');
    }

    // Bulk edit targets for a selected year and optional village
    public function bulkEdit(Request $request)
    {
        $year = (int)($request->input('year', now()->year));
        $villageId = $request->input('village_id');
        $villages = Village::orderBy('name')->pluck('name', 'id');

        // Ambil semua sub-indikator dan target yang cocok dengan filter
        $subs = \App\Models\SpmSubIndicator::with('indicator')
            ->orderBy('spm_indicator_id')
            ->orderBy('code')
            ->get();

        $targets = SpmTarget::where('year', $year)
            ->when($villageId, fn($q)=>$q->where('village_id', $villageId))
            ->get()
            ->keyBy('spm_sub_indicator_id');

        $view = (auth()->check() && method_exists(auth()->user(), 'hasAnyRole') && auth()->user()->hasAnyRole(['super_admin','pegawai','nakes']))
            ? 'spm.targets.bulk-admin'
            : 'spm.targets.bulk';
        return view($view, compact('year','villageId','villages','subs','targets'));
    }

    public function bulkUpdate(Request $request)
    {
        $data = $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'village_id' => 'nullable|exists:villages,id',
            'targets' => 'array',
            'targets.*.denominator_dinkes' => 'nullable|integer|min:0',
            'targets.*.target_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $year = $data['year'];
        $villageId = $data['village_id'] ?? null;

        foreach ($data['targets'] ?? [] as $subId => $vals) {
            $denom = $vals['denominator_dinkes'] ?? null;
            $pct = $vals['target_percentage'] ?? null;
            if ($denom === null && $pct === null) continue; // skip empty row
            SpmTarget::updateOrCreate([
                'year' => $year,
                'village_id' => $villageId,
                'spm_sub_indicator_id' => (int)$subId,
            ], [
                'denominator_dinkes' => (int)($denom ?? 0),
                'target_percentage' => (float)($pct ?? 0),
            ]);
        }

        return back()->with('success', 'Target SPM berhasil disimpan massal.');
    }

    // indicatorMap removed in favor of dynamic sub-indicators
}
