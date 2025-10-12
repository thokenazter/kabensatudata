@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
  <div class="max-w-7xl mx-auto px-4 py-8">
    @if(!(auth()->check() && method_exists(auth()->user(), 'hasAnyRole') && auth()->user()->hasAnyRole(['super_admin','pegawai','nakes'])))
      @include('spm._nav')
    @endif
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Kelola Target Bulanan</h1>
        <p class="text-gray-600">Tetapkan target absolut per bulan untuk tiap sub‑indikator</p>
      </div>
      <a class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300" href="{{ route('targets.index') }}">Kembali</a>
    </div>

    @if(session('success'))
      <div class="mb-4 p-3 bg-green-50 text-green-700 rounded">{{ session('success') }}</div>
    @endif
    @if($errors->any())
      <div class="mb-4 p-3 bg-red-50 text-red-700 rounded">
        <ul class="list-disc list-inside">
          @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
      </div>
    @endif

    <form method="GET" action="{{ route('targets.monthly.bulk') }}" class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm text-gray-700 mb-1">Tahun</label>
          <input type="number" name="year" min="2000" max="2100" value="{{ $year }}" class="w-full rounded border-gray-300">
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
        <div class="flex items-end"><button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Terapkan</button></div>
      </div>
    </form>

    <form method="POST" action="{{ route('targets.monthly.bulk.update') }}" class="bg-white rounded-lg shadow p-4">
      @csrf
      <input type="hidden" name="year" value="{{ $year }}">
      <input type="hidden" name="village_id" value="{{ $villageId }}">

      <div class="overflow-x-auto">
        <div class="flex items-center justify-between mb-3">
          <div class="text-sm text-gray-600">Gunakan tombol di kanan untuk membagi otomatis target tahunan menjadi target bulanan.</div>
          <div class="space-x-2">
            <button type="button" id="autoEqualBtn" class="px-3 py-1.5 rounded bg-indigo-600 text-white">Bagi Merata (Semua Sub)</button>
            <button type="button" id="autoPotentialBtn" class="px-3 py-1.5 rounded bg-emerald-600 text-white">Bagi Proporsional (Potensial)</button>
          </div>
        </div>
        <table class="min-w-full text-sm">
          <thead>
            <tr class="bg-gray-100">
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
              <tr class="border-t">
                <td class="px-3 py-2 font-mono text-xs">{{ $si->code }}</td>
                <td class="px-3 py-2">{{ $si->name }}</td>
                <td class="px-3 py-2 text-gray-600">{{ $si->indicator?->name }}</td>
                @for($m=1;$m<=12;$m++)
                  @php $mt = $rows[$m] ?? null; @endphp
                  <td class="px-3 py-2 text-center">
                    <input type="number" class="w-20 rounded border-gray-300 text-center" name="targets[{{ $si->id }}][{{ $m }}]" value="{{ old('targets.'.$si->id.'.'.$m, $mt?->target_absolute ?? '') }}">
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
  </div>
</div>
@endsection
