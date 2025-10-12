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
      <h1 class="{{ $titleCls }}">Kelola Target (Bulk)</h1>
      <p class="{{ $subtitleCls }}">Perbarui target tahunan per sub‑indikator sekaligus</p>
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

  <form method="GET" action="{{ route('targets.bulk') }}" class="{{ $cardCls }} p-4 mb-6">
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

  <form method="POST" action="{{ route('targets.bulk.update') }}" class="{{ $cardCls }} p-4">
    @csrf
    <input type="hidden" name="year" value="{{ $year }}">
    <input type="hidden" name="village_id" value="{{ $villageId }}">

    <div class="overflow-x-auto {{ $tableText }}">
      <table class="min-w-full text-sm">
        <thead class="{{ $theadCls }}">
          <tr>
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
            <tr class="border-t {{ $tdBorder }}">
              <td class="px-3 py-2 font-mono text-xs">{{ $si->code }}</td>
              <td class="px-3 py-2">{{ $si->name }}</td>
              <td class="px-3 py-2 {{ $subtitleCls }}">{{ $si->indicator?->name }}</td>
              <td class="px-3 py-2">
                <input type="number" class="w-32 {{ $inputCls }}" name="targets[{{ $si->id }}][denominator_dinkes]" value="{{ old('targets.'.$si->id.'.denominator_dinkes', $t->denominator_dinkes ?? '') }}">
              </td>
              <td class="px-3 py-2">
                <div class="flex items-center gap-2">
                  <input type="number" step="0.01" min="0" max="100" class="w-24 {{ $inputCls }}" name="targets[{{ $si->id }}][target_percentage]" value="{{ old('targets.'.$si->id.'.target_percentage', $t->target_percentage ?? '') }}">
                  <span class="{{ $subtitleCls }}">%</span>
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
@endsection

