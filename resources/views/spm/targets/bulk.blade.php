@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
  <div class="max-w-7xl mx-auto px-4 py-8">
    @if(!(auth()->check() && method_exists(auth()->user(), 'hasAnyRole') && auth()->user()->hasAnyRole(['super_admin','pegawai','nakes'])))
      @include('spm._nav')
    @endif
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Kelola Target (Bulk)</h1>
        <p class="text-gray-600">Perbarui target tahunan per sub‑indikator sekaligus</p>
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

    <form method="GET" action="{{ route('targets.bulk') }}" class="bg-white rounded-lg shadow p-4 mb-6">
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

    <form method="POST" action="{{ route('targets.bulk.update') }}" class="bg-white rounded-lg shadow p-4">
      @csrf
      <input type="hidden" name="year" value="{{ $year }}">
      <input type="hidden" name="village_id" value="{{ $villageId }}">

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="bg-gray-100">
              <th class="px-3 py-2 text-left">Kode</th>
              <th class="px-3 py-2 text-left">Sub‑Indikator</th>
              <th class="px-3 py-2 text-left">Indikator Utama</th>
              <th class="px-3 py-2 text-left">Sasaran Dinkes</th>
              <th class="px-3 py-2 text-left">Target %</th>
            </tr>
          </thead>
          <tbody>
            @foreach($subs as $si)
              @php $t = $targets[$si->id] ?? null; @endphp
              <tr class="border-t">
                <td class="px-3 py-2 font-mono text-xs">{{ $si->code }}</td>
                <td class="px-3 py-2">{{ $si->name }}</td>
                <td class="px-3 py-2 text-gray-600">{{ $si->indicator?->name }}</td>
                <td class="px-3 py-2">
                  <input type="number" class="w-32 rounded border-gray-300" name="targets[{{ $si->id }}][denominator_dinkes]" value="{{ old('targets.'.$si->id.'.denominator_dinkes', $t->denominator_dinkes ?? '') }}">
                </td>
                <td class="px-3 py-2">
                  <div class="flex items-center gap-2">
                    <input type="number" step="0.01" min="0" max="100" class="w-24 rounded border-gray-300" name="targets[{{ $si->id }}][target_percentage]" value="{{ old('targets.'.$si->id.'.target_percentage', $t->target_percentage ?? '') }}">
                    <span>%</span>
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
  </div>
</div>
@endsection
