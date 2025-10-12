@extends('layouts.admin')

@section('admin-content')
  @php
    $isPanel = true;
    $cardCls = 'rounded-lg border border-white/10 bg-white/5 shadow';
    $labelCls = 'block text-sm text-slate-300 mb-1';
    $inputCls = 'w-full rounded bg-white/10 border border-white/10 text-slate-200';
    $titleCls = 'text-2xl font-bold text-slate-100';
    $subtitleCls = 'text-slate-300';
    $theadCls = 'bg-white/10 text-slate-300';
    $tdBorder = 'border-white/10';
    $tableText = 'text-slate-200';
  @endphp

  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="{{ $titleCls }}">Kelola Target Bulanan</h1>
      <p class="{{ $subtitleCls }}">Tetapkan target absolut per bulan untuk tiap sub‑indikator</p>
    </div>
  </div>

  @if(session('success'))
    <div class="mb-4 p-3 bg-emerald-500/10 text-emerald-200 border border-emerald-400/20 rounded">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="mb-4 p-3 bg-rose-500/10 text-rose-200 border border-rose-400/20 rounded">
      <ul class="list-disc list-inside">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  <form method="GET" action="{{ route('targets.monthly.bulk') }}" class="{{ $cardCls }} p-4 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="{{ $labelCls }}">Tahun</label>
        <input type="number" name="year" min="2000" max="2100" value="{{ $year }}" class="{{ $inputCls }}">
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
      <div class="flex items-end"><button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Terapkan</button></div>
    </div>
  </form>

  <form method="POST" action="{{ route('targets.monthly.bulk.update') }}" class="{{ $cardCls }} p-4">
    @csrf
    <input type="hidden" name="year" value="{{ $year }}">
    <input type="hidden" name="village_id" value="{{ $villageId }}">

    <div class="overflow-x-auto {{ $tableText }}">
      <div class="flex items-center justify-between mb-3">
        <div class="text-sm {{ $subtitleCls }}">Gunakan tombol di kanan untuk membagi otomatis target tahunan menjadi target bulanan.</div>
        <div class="space-x-2">
          <button type="button" id="autoEqualBtn" class="px-3 py-1.5 rounded bg-indigo-600 text-white">Bagi Merata (Semua Sub)</button>
          <button type="button" id="autoPotentialBtn" class="px-3 py-1.5 rounded bg-emerald-600 text-white">Bagi Proporsional (Potensial)</button>
        </div>
      </div>
      <table class="min-w-full text-sm">
        <thead class="{{ $theadCls }}">
          <tr>
            <th class="px-3 py-2 text-left">Kode</th>
            <th class="px-3 py-2 text-left">Sub‑Indikator</th>
            <th class="px-3 py-2 text-left">Indikator</th>
            @for($m=1;$m<=12;$m++)
              <th class="px-3 py-2 text-center">{{ \Carbon\Carbon::create(null,$m,1)->isoFormat('MMM') }}</th>
            @endfor
            <th class="px-3 py-2 text-left">Auto</th>
          </tr>
        </thead>
        <tbody>
          @foreach($subs as $si)
            @php $rows = ($monthly[$si->id] ?? collect())->keyBy('month'); @endphp
            <tr class="border-t {{ $tdBorder }}">
              <td class="px-3 py-2 font-mono text-xs">{{ $si->code }}</td>
              <td class="px-3 py-2">{{ $si->name }}</td>
              <td class="px-3 py-2 {{ $subtitleCls }}">{{ $si->indicator?->name }}</td>
              @for($m=1;$m<=12;$m++)
                @php $mt = $rows[$m] ?? null; @endphp
                <td class="px-3 py-2 text-center">
                  <input type="number" class="w-20 {{ $inputCls }} text-center" name="targets[{{ $si->id }}][{{ $m }}]" value="{{ old('targets.'.$si->id.'.'.$m, $mt?->target_absolute ?? '') }}">
                </td>
              @endfor
              <td class="px-3 py-2">
                <div class="flex items-center gap-2">
                  <button type="button" class="px-2 py-1 rounded bg-indigo-600 text-white text-xs" onclick="postAutoRow({{ $si->id }}, 'equal')">Merata</button>
                  <button type="button" class="px-2 py-1 rounded bg-emerald-600 text-white text-xs" onclick="postAutoRow({{ $si->id }}, 'potential')">Proporsional</button>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="mt-6 text-right">
      <button class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700">Simpan Semua Perubahan</button>
    </div>
  </form>

  <script>
    (function(){
      function postAuto(method){
        const form = document.querySelector('form[action*="monthly/bulk"]');
        const year = form.querySelector('input[name="year"]').value;
        const village = form.querySelector('input[name="village_id"]').value;
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || form.querySelector('input[name="_token"]').value;
        fetch('{{ route('targets.monthly.auto') }}', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
          body: JSON.stringify({ year: parseInt(year), village_id: village || null, method })
        }).then(r=>r.json()).then(data => {
          if (!data || !data.ok) return alert('Gagal menghitung saran pembagian');
          const suggestions = data.suggestions || {};
          Object.keys(suggestions).forEach(subId => {
            const months = suggestions[subId];
            Object.keys(months).forEach(m => {
              const inp = document.querySelector(`input[name="targets[${subId}][${m}]"]`);
              if (inp) inp.value = months[m];
            });
          });
        }).catch(()=> alert('Gagal terhubung ke server'));
      }
      window.postAutoRow = function(subId, method){
        const form = document.querySelector('form[action*="monthly/bulk"]');
        const year = form.querySelector('input[name="year"]').value;
        const village = form.querySelector('input[name="village_id"]').value;
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || form.querySelector('input[name="_token"]').value;
        fetch('{{ route('targets.monthly.auto') }}', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
          body: JSON.stringify({ year: parseInt(year), village_id: village || null, method, sub_ids: [subId] })
        }).then(r=>r.json()).then(data => {
          if (!data || !data.ok) return alert('Gagal menghitung saran pembagian');
          const months = (data.suggestions || {})[subId] || {};
          Object.keys(months).forEach(m => {
            const inp = document.querySelector(`input[name="targets[${subId}][${m}]"]`);
            if (inp) inp.value = months[m];
          });
        }).catch(()=> alert('Gagal terhubung ke server'));
      }
      document.getElementById('autoEqualBtn')?.addEventListener('click', ()=> postAuto('equal'));
      document.getElementById('autoPotentialBtn')?.addEventListener('click', ()=> postAuto('potential'));
    })();
  </script>
@endsection
