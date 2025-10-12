@extends('layouts.pegawai')

@section('pegawai-content')
    <div x-data="{view: (localStorage.getItem('pegawai_view') || 'card')}" x-init="$watch('view', v => localStorage.setItem('pegawai_view', v))">
        <div class="flex items-center justify-between mb-4">
            <div class="text-xl font-semibold">Daftar Pegawai</div>
            <div class="flex items-center gap-2">
                <div class="hidden sm:flex bg-white/5 border border-white/10 rounded-lg p-1">
                    <button @click="view='card'" :class="view==='card' ? 'bg-white/10' : ''" class="px-3 py-1 rounded-md">Kartu</button>
                    <button @click="view='table'" :class="view==='table' ? 'bg-white/10' : ''" class="px-3 py-1 rounded-md">Tabel</button>
                </div>
                <a href="{{ route('pegawai.employees.create') }}" class="btn btn-primary">Tambah Pegawai</a>
            </div>
        </div>

        <form method="get" class="mb-3">
            <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama / NIP / jabatan / pangkat gol" class="input w-full">
        </form>

        <!-- Card Grid View -->
        <div x-show="view==='card'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
            @forelse($pegawai as $p)
                <div class="relative group">
                    <!-- glow -->
                    <div aria-hidden="true" class="absolute -inset-px rounded-2xl bg-[conic-gradient(from_180deg,rgba(34,211,238,.25),rgba(168,85,247,.18),rgba(16,185,129,.22),rgba(34,211,238,.25))] blur-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                    <div x-data="{open:false}" class="relative rounded-2xl border border-white/10 bg-gradient-to-b from-white/5 to-white/[0.02] backdrop-blur-xl overflow-hidden shadow-[0_10px_30px_rgba(0,0,0,.35)] transition-transform duration-300 group-hover:-translate-y-[2px]">
                        <!-- top accent -->
                        <div aria-hidden class="pointer-events-none absolute top-0 right-0 translate-x-6 -translate-y-6 w-40 h-40 rounded-full bg-cyan-400/10 blur-2xl"></div>
                        <div aria-hidden class="pointer-events-none absolute -top-8 -left-8 w-24 h-24 bg-fuchsia-500/10 blur-2xl rounded-full"></div>

                        <!-- media -->
                        <div class="pt-3 px-3">
                            <div class="relative mx-auto w-[68%] sm:w-[72%] md:w-[76%] max-w-[240px] aspect-[3/4] bg-slate-900/70 rounded-xl border border-white/10 overflow-hidden shadow-inner">
                                @php($foto = $p->foto_path ? asset('storage/'.$p->foto_path) : asset('images/iconsatudata.PNG'))
                                <img src="{{ $foto }}" alt="Foto {{ $p->nama }}" loading="lazy" decoding="async"
                                     class="w-full h-full object-contain object-top transition-transform duration-300 group-hover:scale-[1.03]">
                                <!-- Action Overlay trigger: click on media area -->
                                <button @click="open=!open" class="absolute inset-0" aria-label="Buka aksi"></button>
                                <div x-cloak x-show="open" @click.outside="open=false" x-transition.opacity x-transition.scale.origin.center class="absolute inset-0 bg-slate-900/60 backdrop-blur-md flex items-center justify-center">
                                    <div class="flex gap-2 sm:gap-3">
                                        <a href="{{ route('pegawai.employees.edit', $p) }}" class="btn" title="Edit">✏️</a>
                                        <a href="{{ route('pegawai.employees.documents.index', $p) }}" class="btn" title="Dokumen">📁</a>
                                        <a href="{{ route('pegawai.employees.surat-tugas.create', $p) }}" class="btn" title="Surat">📝</a>
                                        <form action="{{ route('pegawai.employees.destroy', $p) }}" method="post" onsubmit="return confirm('Hapus data pegawai ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn" title="Hapus">🗑️</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- info -->
                        <div class="px-4 pb-4 pt-3">
                            <div class="font-semibold truncate tracking-tight" title="{{ $p->nama }}">{{ $p->nama }}</div>
                            <div class="text-slate-300 text-xs mt-1">NIP: <span class="text-slate-400">{{ $p->nip ?: '—' }}</span></div>
                            <div class="text-slate-400 text-xs mt-1 truncate">
                                {{ $p->jabatan ?: '—' }} @if($p->unit) • {{ $p->unit }} @endif
                            </div>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                @if($p->profesi)
                                    <span class="px-2 py-0.5 rounded-md text-[11px] bg-emerald-400/10 text-emerald-300 border border-emerald-400/20">{{ $p->profesi }}</span>
                                @endif
                                @if($p->pangkat_gol)
                                    <span class="px-2 py-0.5 rounded-md text-[11px] bg-cyan-400/10 text-cyan-300 border border-cyan-400/20">{{ $p->pangkat_gol }}</span>
                                @endif
                                @if($p->pendidikan_terakhir)
                                    <span class="px-2 py-0.5 rounded-md text-[11px] bg-fuchsia-400/10 text-fuchsia-300 border border-fuchsia-400/20">{{ $p->pendidikan_terakhir }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- bottom lightbar -->
                        <div aria-hidden class="h-[2px] bg-gradient-to-r from-cyan-400/40 via-fuchsia-400/40 to-emerald-400/40"></div>
                    </div>
                </div>
            @empty
                <div class="text-slate-400">Belum ada data pegawai.</div>
            @endforelse
        </div>

        <!-- Pagination (Card View) -->
        <div x-show="view==='card'" x-cloak class="mt-3">{{ $pegawai->links() }}</div>

        <!-- Table View -->
        <div x-show="view==='table'" x-cloak class="card overflow-hidden mt-3">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-white/10">
                        <tr class="text-left">
                            <th class="px-4 py-2">Nama</th>
                            <th class="px-4 py-2">NIP</th>
                            <th class="px-4 py-2">Jabatan</th>
                            <th class="px-4 py-2">Unit</th>
                            <th class="px-4 py-2">Pangkat/Gol</th>
                            <th class="px-4 py-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pegawai as $p)
                        <tr class="border-t border-white/10">
                            <td class="px-4 py-2">{{ $p->nama }}</td>
                            <td class="px-4 py-2">{{ $p->nip }}</td>
                            <td class="px-4 py-2">{{ $p->jabatan }}</td>
                            <td class="px-4 py-2">{{ $p->unit }}</td>
                            <td class="px-4 py-2">{{ $p->pangkat_gol }}</td>
                            <td class="px-4 py-2">
                                <div x-data="{open:false}" class="relative inline-block text-left">
                                    <button @click="open=!open" aria-haspopup="true" :aria-expanded="open" class="btn" title="Aksi">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 6.75a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm0 6a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm0 6a1.5 1.5 0 110-3 1.5 1.5 0 010 3z"/></svg>
                                    </button>
                                    <div x-cloak x-show="open" @click.outside="open=false" x-transition class="absolute right-0 z-20 mt-2 w-44 rounded-md bg-slate-900/95 border border-white/10 shadow-lg">
                                        <a href="{{ route('pegawai.employees.edit', $p) }}" class="flex items-center gap-2 px-3 py-2 hover:bg-white/10">✏️ <span>Edit</span></a>
                                        <a href="{{ route('pegawai.employees.documents.index', $p) }}" class="flex items-center gap-2 px-3 py-2 hover:bg-white/10">📁 <span>Dokumen</span></a>
                                        <a href="{{ route('pegawai.employees.surat-tugas.create', $p) }}" class="flex items-center gap-2 px-3 py-2 hover:bg-white/10">📝 <span>Surat Tugas</span></a>
                                        <form action="{{ route('pegawai.employees.destroy', $p) }}" method="post" onsubmit="return confirm('Hapus data pegawai ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full text-left flex items-center gap-2 px-3 py-2 hover:bg-white/10">🗑️ <span>Hapus</span></button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td class="px-4 py-4 text-slate-400" colspan="6">Belum ada data pegawai.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3">{{ $pegawai->links() }}</div>
        </div>
    </div>
@endsection
