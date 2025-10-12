@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
  <div class="max-w-5xl mx-auto px-4 py-8">
    @if(!(auth()->check() && method_exists(auth()->user(), 'hasAnyRole') && auth()->user()->hasAnyRole(['super_admin','pegawai','nakes'])))
      @include('spm._nav')
    @endif
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Buat/Perbarui Target SPM</h1>
        <p class="text-gray-600">Input 12 indikator untuk tahun dan desa terpilih</p>
      </div>
      <a href="{{ route('targets.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">Kembali</a>
    </div>

    @if ($errors->any())
      <div class="mb-4 p-3 bg-red-50 text-red-700 rounded">
        <ul class="list-disc list-inside">
          @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('targets.store') }}" method="POST" class="bg-white rounded-lg shadow p-4">
      @csrf
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div>
          <label class="block text-sm text-gray-700 mb-1">Tahun</label>
          <input type="number" min="2000" max="2100" name="year" value="{{ old('year', $year) }}" class="w-full rounded border-gray-300" required>
        </div>
        <div>
          <label class="block text-sm text-gray-700 mb-1">Desa (opsional)</label>
          <select name="village_id" class="w-full rounded border-gray-300">
            <option value="">Semua Desa</option>
            @foreach($villages as $id=>$name)
              <option value="{{ $id }}" @selected(old('village_id', $villageId)==$id)>{{ $name }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm text-gray-700 mb-1">Sub-Indikator</label>
          <select name="spm_sub_indicator_id" class="w-full rounded border-gray-300" required>
            <option value="">Pilih Sub-Indikator</option>
            @foreach($subIndicators as $si)
              <option value="{{ $si->id }}" @selected(old('spm_sub_indicator_id')==$si->id)>{{ $si->code }} — {{ $si->name }} ({{ $si->indicator?->name }})</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm text-gray-700 mb-1">Sasaran (Denominator Dinkes)</label>
          <input type="number" min="0" name="denominator_dinkes" value="{{ old('denominator_dinkes', 0) }}" class="w-full rounded border-gray-300" required>
        </div>
        <div>
          <label class="block text-sm text-gray-700 mb-1">Target (%)</label>
          <div class="flex items-center gap-2">
            <input type="number" step="0.01" min="0" max="100" name="target_percentage" value="{{ old('target_percentage', 0) }}" class="w-full rounded border-gray-300" required>
            <span>%</span>
          </div>
        </div>
      </div>

      <div class="mt-6 text-right">
        <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Simpan Target</button>
      </div>
    </form>
  </div>
</div>
@endsection
