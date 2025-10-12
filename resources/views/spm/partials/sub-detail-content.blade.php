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
  $linkCls = $isPanel ? 'text-blue-400 hover:text-blue-300 hover:underline' : 'text-blue-600 hover:underline';
@endphp

<div class="mb-4">
  <h1 class="{{ $titleCls }}">Detail Sub‑Indikator: {{ $sub->code }} — {{ $sub->name }}</h1>
  <p class="{{ $subtitleCls }}">Daftar pasien yang tercatat pada periode terpilih</p>
</div>

<form method="GET" action="{{ route('spm.sub.detail', $sub) }}" class="{{ $cardCls }} p-4 mb-6">
  <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
    <div class="flex items-end">
      <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Terapkan</button>
    </div>
  </div>
</form>

<div class="{{ $cardCls }} overflow-x-auto">
  <table class="min-w-full text-sm {{ $tableText }}">
    <thead class="{{ $theadCls }}">
      <tr>
        <th class="px-3 py-2 text-left">Pasien</th>
        <th class="px-3 py-2 text-left">RM</th>
        <th class="px-3 py-2 text-left">JK</th>
        <th class="px-3 py-2 text-left">Umur</th>
        <th class="px-3 py-2 text-left">Desa</th>
        <th class="px-3 py-2 text-left">Kunjungan Terakhir</th>
        <th class="px-3 py-2"></th>
      </tr>
    </thead>
    <tbody>
      @forelse($rows as $r)
        <tr class="border-t {{ $tdBorder }}">
          <td class="px-3 py-2">{{ $r['name'] }}</td>
          <td class="px-3 py-2">{{ $r['rm_number'] }}</td>
          <td class="px-3 py-2">{{ $r['gender'] }}</td>
          <td class="px-3 py-2">{{ $r['age'] }}</td>
          <td class="px-3 py-2">{{ $r['village'] ?? '-' }}</td>
          <td class="px-3 py-2">{{ $r['last_visit'] ?? '-' }}</td>
          <td class="px-3 py-2 text-right">
            <a href="{{ route('family-members.show', $r['slug']) }}" class="{{ $linkCls }}">Lihat profil</a>
          </td>
        </tr>
      @empty
        <tr><td colspan="7" class="px-3 py-6 text-center {{ $subtitleCls }}">Belum ada data pada periode ini</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="mt-8">
  <h2 class="text-xl font-semibold {{ $isPanel ? 'text-slate-100' : 'text-gray-800' }} mb-3">Populasi Sasaran (Potensial)</h2>
  <div class="{{ $cardCls }} overflow-x-auto">
    <table class="min-w-full text-sm {{ $tableText }}">
      <thead class="{{ $theadCls }}">
        <tr>
          <th class="px-3 py-2 text-left">Pasien</th>
          <th class="px-3 py-2 text-left">RM</th>
          <th class="px-3 py-2 text-left">JK</th>
          <th class="px-3 py-2 text-left">Umur</th>
          <th class="px-3 py-2 text-left">Desa</th>
          <th class="px-3 py-2 text-left">Status</th>
          <th class="px-3 py-2"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($potential as $r)
          <tr class="border-t {{ $tdBorder }}">
            <td class="px-3 py-2">{{ $r['name'] }}</td>
            <td class="px-3 py-2">{{ $r['rm_number'] }}</td>
            <td class="px-3 py-2">{{ $r['gender'] }}</td>
            <td class="px-3 py-2">{{ $r['age'] }}</td>
            <td class="px-3 py-2">{{ $r['village'] ?? '-' }}</td>
            <td class="px-3 py-2">
              @if($r['status'] === 'Tercatat')
                <span class="inline-block px-2 py-0.5 rounded {{ $isPanel ? 'bg-green-500/15 text-emerald-300 border border-emerald-400/20' : 'bg-green-100 text-green-700' }}">Tercatat</span>
              @else
                <span class="inline-block px-2 py-0.5 rounded {{ $isPanel ? 'bg-yellow-500/15 text-yellow-200 border border-yellow-400/20' : 'bg-yellow-100 text-yellow-800' }}">Belum tercatat</span>
              @endif
            </td>
            <td class="px-3 py-2 text-right"><a href="{{ route('family-members.show', $r['slug']) }}" class="{{ $linkCls }}">Lihat profil</a></td>
          </tr>
        @empty
          <tr><td colspan="7" class="px-3 py-6 text-center {{ $subtitleCls }}">Tidak ada populasi sasaran pada periode/area ini</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@if(in_array($sub->code, ['SPM_03_KN1','SPM_03_KN2','SPM_03_KN3']))
<div class="mt-8">
  <h2 class="text-xl font-semibold {{ $isPanel ? 'text-slate-100' : 'text-gray-800' }} mb-1">Pra‑Sasaran Potensial (Bumil)</h2>
  <p class="text-sm {{ $subtitleCls }} mb-3">Daftar ibu hamil pada area/periode ini sebagai calon sasaran kelahiran (informasi perencanaan, tidak mempengaruhi capaian).</p>
  <div class="{{ $cardCls }} overflow-x-auto">
    <table class="min-w-full text-sm {{ $tableText }}">
      <thead class="{{ $theadCls }}">
        <tr>
          <th class="px-3 py-2 text-left">Nama</th>
          <th class="px-3 py-2 text-left">RM</th>
          <th class="px-3 py-2 text-left">Umur</th>
          <th class="px-3 py-2 text-left">Desa</th>
          <th class="px-3 py-2"></th>
        </tr>
      </thead>
      <tbody>
        @forelse(($prePotential ?? collect()) as $r)
          <tr class="border-t {{ $tdBorder }}">
            <td class="px-3 py-2">{{ $r['name'] }}</td>
            <td class="px-3 py-2">{{ $r['rm_number'] }}</td>
            <td class="px-3 py-2">{{ $r['age'] }}</td>
            <td class="px-3 py-2">{{ $r['village'] ?? '-' }}</td>
            <td class="px-3 py-2 text-right"><a href="{{ route('family-members.show', $r['slug']) }}" class="{{ $linkCls }}">Lihat profil</a></td>
          </tr>
        @empty
          <tr><td colspan="5" class="px-3 py-6 text-center {{ $subtitleCls }}">Tidak ada data bumil</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endif

@if(str_starts_with($sub->code, 'SPM_10'))
<div class="mt-8">
  <h2 class="text-xl font-semibold {{ $isPanel ? 'text-slate-100' : 'text-gray-800' }} mb-3">Keluarga dengan ODGJ Berat (Level Keluarga)</h2>
  <p class="text-sm {{ $subtitleCls }} mb-2">Bagian ini menampilkan keluarga yang ditandai memiliki anggota dengan gangguan jiwa berat (sumber data: field keluarga).</p>
  <div class="{{ $cardCls }} overflow-x-auto">
    <table class="min-w-full text-sm {{ $tableText }}">
      <thead class="{{ $theadCls }}">
        <tr>
          <th class="px-3 py-2 text-left">No. Keluarga</th>
          <th class="px-3 py-2 text-left">Kepala Keluarga</th>
          <th class="px-3 py-2 text-left">Desa</th>
          <th class="px-3 py-2 text-left">ODGJ (Individu)</th>
          <th class="px-3 py-2"></th>
        </tr>
      </thead>
      <tbody>
        @forelse(($families ?? collect()) as $f)
          <tr class="border-t {{ $tdBorder }}">
            <td class="px-3 py-2">{{ $f['family_number'] }}</td>
            <td class="px-3 py-2">{{ $f['head_name'] }}</td>
            <td class="px-3 py-2">{{ $f['village'] ?? '-' }}</td>
            <td class="px-3 py-2">{{ $f['odgj_count'] }}</td>
            <td class="px-3 py-2 text-right"><a href="{{ $f['card_url'] }}" class="{{ $linkCls }}">Lihat Kartu Keluarga</a></td>
          </tr>
        @empty
          <tr><td colspan="5" class="px-3 py-6 text-center {{ $subtitleCls }}">Tidak ada keluarga yang tercatat</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endif
