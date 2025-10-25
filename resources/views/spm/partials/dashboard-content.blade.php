@php
  $isPanel = auth()->check() && method_exists(auth()->user(), 'hasAnyRole') && auth()->user()->hasAnyRole(['super_admin','pegawai','nakes']);
  $cardCls = $isPanel ? 'rounded-lg border border-white/10 bg-white/5 shadow' : 'bg-white rounded-lg shadow';
  $labelCls = $isPanel ? 'block text-sm text-slate-300 mb-1' : 'block text-sm text-gray-700 mb-1';
  $inputCls = $isPanel ? 'w-full rounded bg-white/10 border border-white/10 text-slate-200' : 'w-full rounded border-gray-300';
  $titleCls = $isPanel ? 'text-2xl font-bold text-slate-100' : 'text-2xl font-bold text-gray-800';
  $subtitleCls = $isPanel ? 'text-slate-300' : 'text-gray-600';
  $theadCls = $isPanel ? 'bg-white/10 text-slate-300' : 'bg-gray-100';
  $tdBorder = $isPanel ? 'border-white/10' : 'border-gray-200';
  $tableText = $isPanel ? 'text-slate-200' : 'text-gray-800';
@endphp

<div class="mb-6">
  <h1 class="{{ $titleCls }}">Dashboard Capaian SPM Kesehatan</h1>
  <p class="{{ $subtitleCls }} mt-1">Pantau capaian riil vs target Dinkes per indikator</p>
 </div>

<form method="GET" action="{{ route('spm.dashboard') }}" class="{{ $cardCls }} p-4 mb-6">
  <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
    <div>
      <label class="{{ $labelCls }}">Tahun</label>
      <input type="number" name="year" min="2000" max="2100" value="{{ $year }}" class="{{ $inputCls }}">
    </div>
    <div>
      <label class="{{ $labelCls }}">Bulan (opsional)</label>
      <select name="month" class="{{ $inputCls }}">
        <option value="">Semua Bulan</option>
        @for($m=1; $m<=12; $m++)
          <option value="{{ $m }}" @selected((int)($month ?? 0) === $m)>{{ \Carbon\Carbon::create(null,$m,1)->isoFormat('MMMM') }}</option>
        @endfor
      </select>
    </div>
    <div>
      <label class="{{ $labelCls }}">Desa (opsional)</label>
      <select name="village_id" class="{{ $inputCls }}">
        <option value="">Semua Desa</option>
        @foreach($villages as $id=>$name)
          <option value="{{ $id }}" @selected($villageId==$id)>{{ $name }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="{{ $labelCls }}">Indikator (opsional)</label>
      <select name="indicator_id" class="{{ $inputCls }}">
        <option value="">Semua Indikator</option>
        @foreach(($indicatorOptions ?? []) as $id=>$name)
          <option value="{{ $id }}" @selected(($indicatorId ?? null)==$id)>{{ $name }}</option>
        @endforeach
      </select>
    </div>
    <div class="flex items-end">
      <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Terapkan</button>
    </div>
  </div>
</form>

@if(isset($overview) && count($overview['labels']) > 0)
<div class="{{ $cardCls }} p-4 mb-6">
  <div class="flex items-center justify-between mb-3">
    <h2 class="text-lg font-semibold {{ $isPanel ? 'text-slate-100' : 'text-gray-800' }}">Grafik Capaian per Indikator</h2>
    <div class="text-xs {{ $subtitleCls }}">Sasaran (Dinkes), Capaian (N), dan Sasaran Potensial</div>
  </div>
  <div class="h-72">
    <canvas id="spmOverviewChart"></canvas>
  </div>
  <script>
    (function(){
      const el = document.getElementById('spmOverviewChart');
      if (!el) return;
      const labels = @json($overview['labels']);
      const actuals = @json($overview['actuals']);
      const potentials = @json($overview['potentials'] ?? []);
      const denoms  = @json($overview['denoms'] ?? []);
      const data = {
        labels,
        datasets: [
          { type: 'bar', label: 'Sasaran (Dinkes)',    data: denoms,     backgroundColor: 'rgba(59,130,246,0.5)',  borderColor: 'rgba(59,130,246,1)',  borderWidth: 1 },
          { type: 'bar', label: 'Sasaran Potensial',   data: potentials, backgroundColor: 'rgba(245,158,11,0.6)', borderColor: 'rgba(245,158,11,1)', borderWidth: 1 },
          { type: 'bar', label: 'Capaian (N)',        data: actuals,    backgroundColor: 'rgba(16,185,129,0.6)', borderColor: 'rgba(16,185,129,1)', borderWidth: 1 },
        ]
      };
      new Chart(el.getContext('2d'), {
        type: 'bar',
        data,
        options: { 
          responsive: true, maintainAspectRatio: false, 
          color: '{{ $isPanel ? 'rgba(226,232,240,0.9)' : '#111827' }}',
          plugins: { legend: { position: 'bottom' } },
          scales: { 
            x: { ticks: { autoSkip: false, maxRotation: 45, color: '{{ $isPanel ? 'rgba(226,232,240,0.8)' : '#374151' }}' }, grid: { color: '{{ $isPanel ? 'rgba(148,163,184,0.15)' : 'rgba(0,0,0,0.05)' }}' } },
            y: { beginAtZero: true, ticks: { color: '{{ $isPanel ? 'rgba(226,232,240,0.8)' : '#374151' }}' }, grid: { color: '{{ $isPanel ? 'rgba(148,163,184,0.15)' : 'rgba(0,0,0,0.05)' }}' } }
          }
        }
      });
    })();
  </script>
</div>
@endif

@if(($indicatorId ?? null) && isset($tree) && count($tree) === 1)
@php
  $__subs = $tree[0]['subs'] ?? [];
  $__subLabels = array_map(fn($s) => $s['name'], $__subs);
  $__subActuals = array_map(fn($s) => $s['numerator_riil'], $__subs);
  $__subDenoms  = array_map(fn($s) => $s['denominator_dinkes'], $__subs);
  $__subPotentials = array_map(fn($s) => $s['denominator_riil'], $__subs);
@endphp
<div class="{{ $cardCls }} p-4 mb-6">
  <div class="flex items-center justify-between mb-3">
    <h2 class="text-lg font-semibold {{ $isPanel ? 'text-slate-100' : 'text-gray-800' }}">Grafik Sub‑Indikator</h2>
    <div class="text-xs {{ $subtitleCls }}">Sasaran (Dinkes), Capaian (N), Target absolut per sub</div>
  </div>
  <div class="h-80">
    <canvas id="spmSubChart"></canvas>
  </div>
  <script>
    (function(){
      const el = document.getElementById('spmSubChart');
      if (!el) return;
      const labels = @json($__subLabels);
      const actuals = @json($__subActuals);
      const potentials = @json($__subPotentials);
      const denoms  = @json($__subDenoms);
      const data = {
        labels,
        datasets: [
          { type: 'bar', label: 'Sasaran (Dinkes)',  data: denoms,     backgroundColor: 'rgba(59,130,246,0.5)',  borderColor: 'rgba(59,130,246,1)',  borderWidth: 1 },
          { type: 'bar', label: 'Sasaran Potensial', data: potentials, backgroundColor: 'rgba(245,158,11,0.6)', borderColor: 'rgba(245,158,11,1)', borderWidth: 1 },
          { type: 'bar', label: 'Capaian (N)',       data: actuals,    backgroundColor: 'rgba(16,185,129,0.6)', borderColor: 'rgba(16,185,129,1)', borderWidth: 1 },
        ]
      };
      new Chart(el.getContext('2d'), {
        type: 'bar',
        data,
        options: {
          responsive: true,
          maintainAspectRatio: false,
          color: '{{ $isPanel ? 'rgba(226,232,240,0.9)' : '#111827' }}',
          plugins: { legend: { position: 'bottom' } },
          scales: {
            x: { ticks: { autoSkip: false, maxRotation: 45, color: '{{ $isPanel ? 'rgba(226,232,240,0.8)' : '#374151' }}' }, grid: { color: '{{ $isPanel ? 'rgba(148,163,184,0.15)' : 'rgba(0,0,0,0.05)' }}' } },
            y: { beginAtZero: true, ticks: { color: '{{ $isPanel ? 'rgba(226,232,240,0.8)' : '#374151' }}' }, grid: { color: '{{ $isPanel ? 'rgba(148,163,184,0.15)' : 'rgba(0,0,0,0.05)' }}' } }
          }
        }
      });
    })();
  </script>
</div>
@endif

<div class="space-y-4">
  @foreach ($tree as $ind)
    <details class="{{ $cardCls }}">
      <summary class="cursor-pointer select-none list-none px-4 py-3 border-b {{ $isPanel ? 'border-white/10 text-slate-100' : 'text-gray-800' }} font-semibold flex items-center justify-between">
        @php
          $code = $ind['code'] ?? '';
          $num = null;
          if (preg_match('/SPM_(\d+)/', $code, $m)) {
            $num = (int)($m[1] ?? null);
          }
        @endphp
        <span>
          @if(!is_null($num))
            {{ $num }}. {{ $ind['name'] }}
          @else
            {{ $ind['code'] }} — {{ $ind['name'] }}
          @endif
        </span>
        <span class="text-sm {{ $subtitleCls }}">{{ count($ind['subs']) }} sub-indikator</span>
      </summary>
      <div class="p-4 overflow-x-auto {{ $tableText }}">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="{{ $theadCls }}">
              <th class="px-3 py-2 text-left">Sub-Indikator</th>
              <th class="px-3 py-2 text-left">Sasaran</th>
              <th class="px-3 py-2 text-left">Target %</th>
              @if($month)
                <th class="px-3 py-2 text-left">Target Bulan</th>
              @endif
              <th class="px-3 py-2 text-left">Capaian (N/D)</th>
              <th class="px-3 py-2 text-left">% Riil</th>
              <th class="px-3 py-2 text-left">GAP</th>
              <th class="px-3 py-2"></th>
            </tr>
          </thead>
          <tbody>
            @forelse($ind['subs'] as $s)
              <tr class="border-t {{ $tdBorder }}">
                <td class="px-3 py-2">
                  <div class="font-medium">{{ $s['name'] }}</div>
                  <div class="text-xs {{ $subtitleCls }}">{{ $s['code'] }}</div>
                  @if(($s['code'] ?? '') === 'SPM_02_KB_AKTIF')
                    <div class="text-[11px] {{ $subtitleCls }} mt-1">Catatan: Capaian dihitung dari status “menggunakan KB” pada PUS non‑bumil (snapshot akhir periode).</div>
                  @endif
                </td>
                <td class="px-3 py-2">{{ $s['denominator_dinkes'] ?? '-' }}</td>
                <td class="px-3 py-2">{{ $s['target_percentage'] !== null ? number_format($s['target_percentage'], 2) : '-' }}%</td>
                @if($month)
                  <td class="px-3 py-2">
                    @if(isset($s['monthly_target']))
                      {{ $s['monthly_target'] ?? '-' }}
                      @if(!is_null($s['monthly_achieved']))
                        <span class="ml-2 inline-block px-2 py-0.5 rounded {{ $s['monthly_achieved'] ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-400/20' : 'bg-rose-500/15 text-rose-300 border border-rose-400/20' }}">{{ $s['monthly_achieved'] ? 'Tercapai' : 'Belum' }}</span>
                      @endif
                    @else
                      -
                    @endif
                  </td>
                @endif
                <td class="px-3 py-2">
                  <div class="flex items-center gap-2">
                    <span>{{ $s['numerator_riil'] }}</span>
                    <span>/ {{ $s['denominator_riil'] }}</span>
                    @if(auth()->check() && method_exists(auth()->user(), 'hasAnyRole') && auth()->user()->hasAnyRole(['super_admin']))
                    <form action="{{ route('spm.achievements.override') }}" method="POST" class="inline-flex items-center gap-1 ml-2">
                      @csrf
                      <input type="hidden" name="spm_sub_indicator_id" value="{{ $s['id'] ?? '' }}">
                      <input type="hidden" name="year" value="{{ $year }}">
                      <input type="hidden" name="month" value="{{ $month }}">
                      <input type="hidden" name="village_id" value="{{ $villageId }}">
                      <input type="hidden" name="redirect" value="{{ request()->fullUrl() }}">
                      <input type="number" name="value" min="0" class="w-20 rounded {{ $isPanel ? 'bg-white/10 border-white/10 text-slate-200' : 'border-gray-300' }} text-sm" placeholder="N" value="{{ $s['numerator_riil'] }}">
                      <button class="px-2 py-1 rounded bg-blue-600 text-white text-xs">Simpan</button>
                    </form>
                    @endif
                  </div>
                  @if(!empty($s['is_overridden']))
                    <div class="text-[11px] {{ $isPanel ? 'text-amber-300' : 'text-amber-600' }}">Override aktif</div>
                  @endif
                </td>
                <td class="px-3 py-2">{{ number_format($s['percentage_riil'], 2) }}%</td>
                @php $gap = $s['gap']; @endphp
                <td class="px-3 py-2 {{ $gap === null ? '' : ($gap >= 0 ? 'text-green-400' : 'text-rose-400') }}">{{ $gap === null ? '-' : ($gap >= 0 ? '+'.$gap : $gap) }}</td>
                <td class="px-3 py-2 text-right"><a href="{{ $s['detail_url'] }}" class="text-blue-500 hover:underline">Lihat detail</a></td>
              </tr>
            @empty
              <tr><td colspan="7" class="px-3 py-4 text-center {{ $subtitleCls }}">Tidak ada sub-indikator</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </details>
  @endforeach
</div>
