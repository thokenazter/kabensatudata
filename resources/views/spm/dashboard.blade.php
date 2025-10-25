@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
  <div class="max-w-7xl mx-auto px-4 py-8">
    @if(!(auth()->check() && method_exists(auth()->user(), 'hasAnyRole') && auth()->user()->hasAnyRole(['super_admin','pegawai','nakes'])))
      @include('spm._nav')
    @endif
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Dashboard Capaian SPM Kesehatan</h1>
      <p class="text-gray-600 mt-1">Pantau capaian riil vs target Dinkes per indikator</p>
    </div>

    <form method="GET" action="{{ route('spm.dashboard') }}" class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
          <label class="block text-sm text-gray-700 mb-1">Tahun</label>
          <input type="number" name="year" min="2000" max="2100" value="{{ $year }}" class="w-full rounded border-gray-300">
        </div>
        <div>
          <label class="block text-sm text-gray-700 mb-1">Bulan (opsional)</label>
          <select name="month" class="w-full rounded border-gray-300">
            <option value="">Semua Bulan</option>
            @for($m=1; $m<=12; $m++)
              <option value="{{ $m }}" @selected((int)($month ?? 0) === $m)>{{ \Carbon\Carbon::create(null,$m,1)->isoFormat('MMMM') }}</option>
            @endfor
          </select>
        </div>
        <div>
          <label class="block text-sm text-gray-700 mb-1">Desa (opsional)</label>
          <select name="village_id" class="w-full rounded border-gray-300">
            <option value="">Semua Desa</option>
            @foreach($villages as $id=>$name)
              <option value="{{ $id }}" @selected($villageId==$id)>{{ $name }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm text-gray-700 mb-1">Indikator (opsional)</label>
          <select name="indicator_id" class="w-full rounded border-gray-300">
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
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-lg font-semibold text-gray-800">Grafik Capaian per Indikator</h2>
        <div class="text-xs text-gray-500">Menampilkan Sasaran (Dinkes), Capaian (N), dan Sasaran Potensial</div>
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
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                tooltip: {
                  callbacks: {
                    label: function(ctx){
                      const v = ctx.parsed.y;
                      return `${ctx.dataset.label}: ${v ?? '-'}`;
                    }
                  }
                },
                legend: { position: 'bottom' }
              },
              scales: {
                x: { ticks: { autoSkip: false, maxRotation: 45, minRotation: 0 } },
                y: { beginAtZero: true }
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
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-lg font-semibold text-gray-800">Grafik Sub‑Indikator</h2>
        <div class="text-xs text-gray-500">Sasaran (Dinkes), Capaian (N), dan Sasaran Potensial per sub</div>
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
      const potentials = @json($__subPotentials ?? []);
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
              plugins: { legend: { position: 'bottom' } },
              scales: {
                x: { ticks: { autoSkip: false, maxRotation: 45, minRotation: 0 } },
                y: { beginAtZero: true }
              }
            }
          });
        })();
      </script>
    </div>
    @endif

    <div class="space-y-4">
      @foreach ($tree as $ind)
        <details class="bg-white rounded-lg shadow">
          <summary class="cursor-pointer select-none list-none px-4 py-3 border-b text-gray-800 font-semibold flex items-center justify-between">
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
                {{ $code }} — {{ $ind['name'] }}
              @endif
            </span>
            <span class="text-sm text-gray-500">{{ count($ind['subs']) }} sub-indikator</span>
          </summary>
          <div class="p-4 overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="bg-gray-100">
                  <th class="px-3 py-2 text-left">Sub-Indikator</th>
                  <th class="px-3 py-2 text-left">Sasaran</th>
                  <th class="px-3 py-2 text-left">Target %</th>
                  @if($month)
                    <th class="px-3 py-2 text-left">Target Bulan</th>
                  @endif
                  <th class="px-3 py-2 text-left">Capaian (N/D)</th>
                  <th class="px-3 py-2 text-left">% Riil</th>
                  <th class="px-3 py-2 text-left">GAP</th>
                </tr>
              </thead>
              <tbody>
                @forelse($ind['subs'] as $s)
                  <tr class="border-t">
                    <td class="px-3 py-2">
                      <div class="font-medium">{{ $s['name'] }}</div>
                      <div class="text-xs text-gray-500">{{ $s['code'] }}</div>
                      @if(($s['code'] ?? '') === 'SPM_02_KB_AKTIF')
                        <div class="text-[11px] text-gray-500 mt-1">Catatan: Capaian dihitung dari status “menggunakan KB” pada PUS non‑bumil (snapshot akhir periode).</div>
                      @endif
                    </td>
                    <td class="px-3 py-2">{{ $s['denominator_dinkes'] ?? '-' }}</td>
                    <td class="px-3 py-2">{{ $s['target_percentage'] !== null ? number_format($s['target_percentage'], 2) : '-' }}%</td>
                    @if($month)
                      <td class="px-3 py-2">
                        @if(isset($s['monthly_target']))
                          {{ $s['monthly_target'] ?? '-' }}
                          @if(!is_null($s['monthly_achieved']))
                            <span class="ml-2 inline-block px-2 py-0.5 rounded {{ $s['monthly_achieved'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $s['monthly_achieved'] ? 'Tercapai' : 'Belum' }}</span>
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
                          <input type="number" name="value" min="0" class="w-20 rounded border-gray-300 text-sm" placeholder="N" value="{{ $s['numerator_riil'] }}">
                          <button class="px-2 py-1 rounded bg-blue-600 text-white text-xs">Simpan</button>
                        </form>
                        @endif
                      </div>
                      @if(!empty($s['is_overridden']))
                        <div class="text-[11px] text-amber-600">Override aktif</div>
                      @endif
                    </td>
                    <td class="px-3 py-2">{{ number_format($s['percentage_riil'], 2) }}%</td>
                    @php $gap = $s['gap']; @endphp
                    <td class="px-3 py-2 {{ $gap === null ? '' : ($gap >= 0 ? 'text-green-600' : 'text-red-600') }}">{{ $gap === null ? '-' : ($gap >= 0 ? '+'.$gap : $gap) }}</td>
                    <td class="px-3 py-2 text-right"><a href="{{ $s['detail_url'] }}" class="text-blue-600 hover:underline">Lihat detail</a></td>
                  </tr>
                @empty
                  <tr><td colspan="6" class="px-3 py-4 text-center text-gray-500">Tidak ada sub-indikator</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </details>
      @endforeach
    </div>
  </div>
</div>
@endsection
