@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
  <div class="max-w-6xl mx-auto px-4 py-8">
    @if(!(auth()->check() && method_exists(auth()->user(), 'hasAnyRole') && auth()->user()->hasAnyRole(['super_admin','pegawai','nakes'])))
      @include('spm._nav')
    @endif
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Target SPM (Dinkes)</h1>
        <p class="text-gray-600">Kelola target tahunan per desa</p>
      </div>
      <div class="space-x-2">
        <a href="{{ route('targets.bulk', ['year' => now()->year]) }}" class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700">Kelola Target (Bulk)</a>
        <a href="{{ route('targets.monthly.bulk', ['year' => now()->year]) }}" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Target Bulanan (Bulk)</a>
        <a href="{{ route('targets.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Buat/Perbarui Target</a>
      </div>
    </div>

    @if(session('success'))
      <div class="mb-4 p-3 bg-green-50 text-green-700 rounded">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-4 py-2 text-left">Tahun</th>
            <th class="px-4 py-2 text-left">Desa</th>
            <th class="px-4 py-2 text-left">Jumlah Indikator</th>
            <th class="px-4 py-2"></th>
          </tr>
        </thead>
        <tbody>
        @forelse($targets as $row)
          <tr class="border-t">
            <td class="px-4 py-2 font-medium">{{ $row->year }}</td>
            <td class="px-4 py-2">{{ $row->village_id ? ($villages[$row->village_id] ?? '-') : 'Semua Desa' }}</td>
            <td class="px-4 py-2">{{ $row->indicators }}</td>
            <td class="px-4 py-2 text-right space-x-2">
              <a class="px-3 py-1 bg-indigo-600 text-white rounded" href="{{ route('targets.create', ['year' => $row->year, 'village_id' => $row->village_id]) }}">Kelola</a>
              <form action="{{ route('targets.destroy', \App\Models\SpmTarget::where('year',$row->year)->where('village_id',$row->village_id)->first() ?? 0) }}" method="POST" class="inline" onsubmit="return confirm('Hapus seluruh target set ini?')">
                @csrf @method('DELETE')
                <button class="px-3 py-1 bg-red-600 text-white rounded">Hapus</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">Belum ada data</td></tr>
        @endforelse
        </tbody>
      </table>
      <div class="p-3">{{ $targets->links() }}</div>
    </div>
  </div>
</div>
@endsection
