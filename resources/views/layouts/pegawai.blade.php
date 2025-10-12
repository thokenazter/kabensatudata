@extends('layouts.app')

@section('content')
<div x-data="{ sidebar:true, mobileOpen:false }" @keydown.window.escape="mobileOpen=false" class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-slate-100">
    <!-- Subtle animated background -->
    <div aria-hidden="true" class="pointer-events-none fixed inset-0 opacity-30">
        <div class="absolute -inset-40 bg-[radial-gradient(circle_500px_at_20%_20%,rgba(59,130,246,0.15),transparent),radial-gradient(circle_400px_at_80%_30%,rgba(236,72,153,0.12),transparent),radial-gradient(circle_600px_at_50%_80%,rgba(16,185,129,0.12),transparent)] animate-[slowpan_22s_linear_infinite_alternate]"></div>
        <div class="absolute inset-0 bg-[linear-gradient(transparent,transparent_98%,rgba(255,255,255,0.06)_98%),linear-gradient(90deg,transparent,transparent_98%,rgba(255,255,255,0.06)_98%)] bg-[length:40px_40px]"></div>
    </div>

    <div class="relative z-10 flex min-h-screen">
        <!-- Sidebar (Desktop) -->
        <aside class="hidden md:flex w-72 shrink-0 flex-col border-r border-white/10 bg-white/5 backdrop-blur-xl">
            <a href="/dashboard" class="cursor-pointer">
                <div class="flex items-center gap-3 px-4 py-5 border-b border-white/10">
                <img src="{{ asset('images/iconsatudata.PNG') }}" class="h-9 w-9 rounded-lg shadow-[0_0_10px_rgba(59,130,246,.45)]" alt="Logo">
                <div class="font-semibold">Dashboard Puskesmas</div>
            </div>
            </a>
            <nav class="p-3 space-y-1">
                <a href="{{ route('pegawai.dashboard') }}" class="peg-nav {{ request()->routeIs('pegawai.dashboard') ? 'active' : '' }}">
                    <span class="icon">🏠</span>
                    <span>Dashboard</span>
                </a>
                {{-- <a href="{{ route('spm.dashboard') }}" class="peg-nav {{ request()->routeIs('spm.dashboard') ? 'active' : '' }}">
                    <span class="icon">📊</span>
                    <span>SPM Kesehatan</span>
                </a> --}}
                <a href="{{ route('pegawai.employees.index') }}" class="peg-nav {{ request()->routeIs('pegawai.employees.*') ? 'active' : '' }}">
                    <span class="icon">🧑‍💼</span>
                    <span>Data Pegawai</span>
                </a>
                <a href="{{ route('pegawai.surat.tugas.create') }}" class="peg-nav {{ request()->routeIs('pegawai.surat.tugas.*') ? 'active' : '' }}">
                    <span class="icon">📝</span>
                    <span>Buat Surat</span>
                </a>
                <a href="{{ route('pegawai.surat-arsip.index') }}" class="peg-nav {{ request()->routeIs('pegawai.surat-arsip.*') ? 'active' : '' }}">
                    <span class="icon">🗂️</span>
                    <span>E‑Arsip Surat</span>
                </a>
                <a href="/panel" class="peg-nav">
                <span class="icon">🅿️</span>
                    <span>Panel Dashboard</span>
                </a>
                @php($user = auth()->user())
                @if($user)
                    @php($mine = \App\Models\Pegawai::firstWhere('user_id', $user->id))
                    <a href="{{ $mine ? route('pegawai.employees.edit', $mine) : route('pegawai.employees.create') }}" class="peg-nav">
                        <span class="icon">✏️</span>
                        <span>Profil Saya</span>
                    </a>
                    @if($mine)
                        <a href="{{ route('pegawai.employees.documents.index', $mine) }}" class="peg-nav {{ request()->routeIs('pegawai.employees.documents.*') ? 'active' : '' }}">
                            <span class="icon">📁</span>
                            <span>Dokumen Saya</span>
                        </a>
                    @endif
                @endif
            </nav>
        </aside>

        <!-- Mobile Drawer -->
        <div x-cloak x-show="mobileOpen" x-transition.opacity class="fixed inset-0 z-40 bg-black/50 md:hidden" @click="mobileOpen=false" aria-hidden="true"></div>
        <aside x-cloak x-show="mobileOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full opacity-0" x-transition:enter-end="translate-x-0 opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0 opacity-100" x-transition:leave-end="-translate-x-full opacity-0" class="fixed z-50 inset-y-0 left-0 w-72 bg-slate-900/95 backdrop-blur-xl border-r border-white/10 p-3 md:hidden" role="dialog" aria-modal="true">
            <div class="flex items-center justify-between px-1 py-3 border-b border-white/10">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/iconsatudata.PNG') }}" class="h-8 w-8 rounded" alt="Logo">
                    <div class="font-semibold">Menu</div>
                </div>
                <button class="text-slate-300" @click="mobileOpen=false" aria-label="Tutup menu">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <nav class="mt-3 space-y-1">
                <a href="{{ route('pegawai.dashboard') }}" class="peg-nav {{ request()->routeIs('pegawai.dashboard') ? 'active' : '' }}"><span class="icon">🏠</span><span>Dashboard</span></a>
                <a href="{{ route('spm.dashboard') }}" class="peg-nav {{ request()->routeIs('spm.dashboard') ? 'active' : '' }}"><span class="icon">📊</span><span>SPM Kesehatan</span></a>
                <a href="{{ route('pegawai.employees.index') }}" class="peg-nav {{ request()->routeIs('pegawai.employees.*') ? 'active' : '' }}"><span class="icon">🧑‍💼</span><span>Data Pegawai</span></a>
                @php($user = auth()->user())
                @if($user)
                    @php($mine = \App\Models\Pegawai::firstWhere('user_id', $user->id))
                    <a href="{{ $mine ? route('pegawai.employees.edit', $mine) : route('pegawai.employees.create') }}" class="peg-nav"><span class="icon">✏️</span><span>Profil Saya</span></a>
                    @if($mine)
                        <a href="{{ route('pegawai.employees.documents.index', $mine) }}" class="peg-nav {{ request()->routeIs('pegawai.employees.documents.*') ? 'active' : '' }}"><span class="icon">📁</span><span>Dokumen Saya</span></a>
                    @endif
                @endif
            </nav>
        </aside>

        <!-- Main -->
        <main class="flex-1">
            <!-- Topbar -->
            <div class="sticky top-0 z-20 bg-slate-900/60 backdrop-blur-xl border-b border-white/10">
                <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
                    <button class="md:hidden -ml-1 p-2 rounded bg-white/5 border border-white/10" @click="mobileOpen=true" aria-label="Buka menu">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div class="text-slate-300 text-sm sm:text-base">{{ now()->isoFormat('dddd, D MMMM Y') }}</div>
                    <div class="flex items-center gap-2 text-xs sm:text-sm">
                        <span class="px-2 py-1 rounded bg-emerald-400/10 text-emerald-300 border border-emerald-400/20 truncate max-w-[50vw]">{{ auth()->user()->name ?? 'User' }}</span>
                        <a href="{{ route('custom-logout') }}" class="px-3 py-1 rounded bg-white/10 hover:bg-white/15 border border-white/10">Logout</a>
                    </div>
                </div>
            </div>
            <!-- Content -->
            <div class="max-w-7xl mx-auto p-3 sm:p-4">
                @yield('pegawai-content')
            </div>
        </main>
    </div>
    <style>
        @keyframes slowpan { from { transform: translateX(-3%) translateY(-2%);} to { transform: translateX(3%) translateY(2%);} }
        .peg-nav{display:flex;align-items:center;gap:.75rem;padding:.65rem .8rem;border-radius:.6rem;color:#cbd5e1;transition:background .2s ease,box-shadow .2s ease}
        .peg-nav .icon{width:1.25rem;text-align:center}
        .peg-nav:hover{background:rgba(255,255,255,.06);box-shadow:inset 0 0 0 1px rgba(255,255,255,.08)}
        .peg-nav.active{background:linear-gradient(90deg,rgba(34,211,238,.15),rgba(168,85,247,.12));box-shadow:0 0 0 1px rgba(34,211,238,.25) inset, 0 0 30px rgba(168,85,247,.2)}
        .card{background:linear-gradient(180deg,rgba(255,255,255,.06),rgba(255,255,255,.03));border:1px solid rgba(255,255,255,.08);border-radius:14px}
        .btn{display:inline-flex;align-items:center;gap:.5rem;border-radius:.7rem;padding:.6rem .95rem;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.06)}
        .btn:hover{background:rgba(255,255,255,.1)}
        .btn-primary{background:linear-gradient(90deg,#22d3ee,#a855f7);border:none;color:white}
        .input{background:rgba(15,23,42,.7);border:1px solid rgba(255,255,255,.12);border-radius:.6rem;padding:.55rem .75rem}
        .label{font-size:.85rem;color:#9ca3af}
        .no-scrollbar::-webkit-scrollbar{display:none}
        .no-scrollbar{-ms-overflow-style:none;scrollbar-width:none}
        [x-cloak]{display:none !important}
        @media (prefers-reduced-motion: reduce){
            [class*="animate-"], .animate-[slowpan_22s_linear_infinite_alternate]{animation:none !important}
        }
    </style>

    <!-- Mobile Bottom Navigation -->
    <nav class="md:hidden fixed bottom-0 inset-x-0 z-40">
        <div class="relative bg-white/10 backdrop-blur-xl border-t border-white/10">
            <div class="flex items-stretch justify-around px-2 py-2">
                <a href="{{ route('pegawai.dashboard') }}" class="flex flex-col items-center gap-1 px-3 py-2 rounded {{ request()->routeIs('pegawai.dashboard') ? 'bg-white/10' : '' }}">
                    <span>🏠</span>
                    <span class="text-[11px]">Home</span>
                </a>
                <a href="{{ route('pegawai.employees.index') }}" class="flex flex-col items-center gap-1 px-3 py-2 rounded {{ request()->routeIs('pegawai.employees.*') ? 'bg-white/10' : '' }}">
                    <span>🧑‍💼</span>
                    <span class="text-[11px]">Pegawai</span>
                </a>
                @php($user = auth()->user())
                @if($user)
                    @php($mine = \App\Models\Pegawai::firstWhere('user_id', $user->id))
                    <a href="{{ $mine ? route('pegawai.employees.documents.index', $mine) : route('pegawai.employees.create') }}" class="flex flex-col items-center gap-1 px-3 py-2 rounded {{ request()->routeIs('pegawai.employees.documents.*') ? 'bg-white/10' : '' }}">
                        <span>📁</span>
                        <span class="text-[11px]">Dokumen</span>
                    </a>
                    <a href="{{ route('pegawai.surat.tugas.create') }}" class="flex flex-col items-center gap-1 px-3 py-2 rounded {{ request()->routeIs('pegawai.surat.tugas.*') ? 'bg-white/10' : '' }}">
                        <span>📝</span>
                        <span class="text-[11px]">Buat</span>
                    </a>
                    <a href="{{ route('pegawai.surat-arsip.index') }}" class="flex flex-col items-center gap-1 px-3 py-2 rounded {{ request()->routeIs('pegawai.surat-arsip.*') ? 'bg-white/10' : '' }}">
                        <span>🗂️</span>
                        <span class="text-[11px]">Arsip</span>
                    </a>
                @endif
            </div>
        </div>
    </nav>
</div>
@endsection
