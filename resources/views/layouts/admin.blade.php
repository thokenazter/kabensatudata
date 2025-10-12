@extends('layouts.app')

@section('content')
<div x-data="{ open: true, mobileOpen: false, navMode: (localStorage.getItem('panel.navMode') || 'side') }" x-init="$watch('navMode', v => localStorage.setItem('panel.navMode', v))" @keydown.window.escape="mobileOpen=false" class="relative min-h-screen overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
    <!-- Animated background grid -->
    <div aria-hidden="true" class="pointer-events-none absolute inset-0 opacity-30">
        <div class="absolute -inset-40 bg-[radial-gradient(circle_500px_at_20%_20%,rgba(59,130,246,0.15),transparent),radial-gradient(circle_400px_at_80%_30%,rgba(236,72,153,0.12),transparent),radial-gradient(circle_600px_at_50%_80%,rgba(16,185,129,0.12),transparent)] animate-slow-pan"></div>
        <div class="absolute inset-0 bg-[linear-gradient(transparent,transparent_98%,rgba(255,255,255,0.06)_98%),linear-gradient(90deg,transparent,transparent_98%,rgba(255,255,255,0.06)_98%)] bg-[length:40px_40px]"></div>
    </div>

    <div class="relative z-10 flex">
        <!-- Sidebar -->
        <aside x-cloak x-show="navMode==='side'" :class="open ? 'w-72' : 'w-20'" class="hidden md:block transition-all duration-300 ease-out h-screen sticky top-0 backdrop-blur-xl border-r border-white/10 bg-white/5">
            <div class="px-4 py-5 border-b border-white/10 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <img src="{{ asset('images/iconsatudata.PNG') }}" alt="Kaben Satu Data" class="h-9 w-9 rounded-lg object-cover shadow-[0_0_10px_rgba(59,130,246,0.45)] animate-spin" style="animation-duration: 8s;">
                    <div class="text-slate-100 font-semibold" x-show="open" x-transition.opacity>Control Center</div>
                </div>
                <button @click="open=!open" class="text-slate-300 hover:text-white transition-colors" title="Toggle menu">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
            <nav class="p-3 space-y-1 text-slate-200">
                <a href="{{ route('panel.dashboard') }}" class="nav-link {{ request()->routeIs('panel.dashboard') ? 'active' : '' }}">
                    <span class="icon">🏠</span>
                    <span x-show="open" x-transition.opacity>Dashboard</span>
                </a>
                <!-- Master Data group -->
                <div x-data="{ openMaster: {{ (
                        request()->routeIs('panel.medicines.*') ||
                        request()->routeIs('panel.medical-records.*') ||
                        request()->routeIs('panel.users.*') ||
                        request()->routeIs('panel.villages.*') ||
                        request()->routeIs('panel.buildings.*') ||
                        request()->routeIs('panel.families.*') ||
                        request()->routeIs('panel.family-members.*') ||
                        request()->is('admin/chatbot*')
                    ) ? 'true' : 'false' }} }" class="space-y-0.5">
                    <button type="button" @click="openMaster = !openMaster" class="nav-link w-full {{ (
                        request()->routeIs('panel.medicines.*') ||
                        request()->routeIs('panel.medical-records.*') ||
                        request()->routeIs('panel.users.*') ||
                        request()->routeIs('panel.villages.*') ||
                        request()->routeIs('panel.buildings.*') ||
                        request()->routeIs('panel.families.*') ||
                        request()->routeIs('panel.family-members.*') ||
                        request()->is('admin/chatbot*')
                    ) ? 'active' : '' }}">
                        <span class="icon">🗂️</span>
                        <span x-show="open" x-transition.opacity class="flex-1 text-left">Master Data</span>
                        <svg x-show="open" :class="openMaster ? 'rotate-180' : ''" class="h-4 w-4 text-slate-300 transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.354a.75.75 0 111.02 1.1l-4.2 3.8a.75.75 0 01-1.02 0l-4.2-3.8a.75.75 0 01-.02-1.06z" clip-rule="evenodd"/></svg>
                    </button>

                    <div x-show="openMaster" x-transition.opacity class="pl-6 space-y-0.5">
                        @can('view_any_medicine')
                            <a href="{{ route('panel.medicines.index') }}" class="nav-link ml-6 {{ request()->routeIs('panel.medicines.*') ? 'active' : '' }}">
                                <span class="icon">💊</span>
                                <span x-show="open" x-transition.opacity>Obat</span>
                            </a>
                        @endcan

                        <a href="{{ route('panel.medical-records.index') }}" class="nav-link ml-6 {{ request()->routeIs('panel.medical-records.*') ? 'active' : '' }}">
                            <span class="icon">📋</span>
                            <span x-show="open" x-transition.opacity>Rekam Medis</span>
                        </a>

                        @can('view_any_user')
                            <a href="{{ route('panel.users.index') }}" class="nav-link ml-6 {{ request()->routeIs('panel.users.*') ? 'active' : '' }}">
                                <span class="icon">🧑‍💼</span>
                                <span x-show="open" x-transition.opacity>Pengguna</span>
                            </a>
                        @endcan

                        @can('view_any_village')
                            <a href="{{ route('panel.villages.index') }}" class="nav-link ml-6 {{ request()->routeIs('panel.villages.*') ? 'active' : '' }}">
                                <span class="icon">🏘️</span>
                                <span x-show="open" x-transition.opacity>Desa</span>
                            </a>
                        @endcan

                        @can('view_any_building')
                            <a href="{{ route('panel.buildings.index') }}" class="nav-link ml-6 {{ request()->routeIs('panel.buildings.*') ? 'active' : '' }}">
                                <span class="icon">🏚️</span>
                                <span x-show="open" x-transition.opacity>Bangunan</span>
                            </a>
                        @endcan

                        @can('view_any_family')
                            <a href="{{ route('panel.families.index') }}" class="nav-link ml-6 {{ request()->routeIs('panel.families.*') ? 'active' : '' }}">
                                <span class="icon">👪</span>
                                <span x-show="open" x-transition.opacity>Keluarga</span>
                            </a>
                        @endcan

                        @can('view_any_family::member')
                            <a href="{{ route('panel.family-members.index') }}" class="nav-link ml-6 {{ request()->routeIs('panel.family-members.*') ? 'active' : '' }}">
                                <span class="icon">🧍</span>
                                <span x-show="open" x-transition.opacity>Anggota</span>
                            </a>
                        @endcan

                        <a href="{{ url('/admin/chatbot') }}" class="nav-link ml-6 {{ request()->is('admin/chatbot*') ? 'active' : '' }}">
                            <span class="icon">🤖</span>
                            <span x-show="open" x-transition.opacity>Chatbot</span>
                        </a>
                    </div>
                </div>
                <a href="{{ route('spm.dashboard') }}" class="nav-link {{ request()->routeIs('spm.dashboard') ? 'active' : '' }}">
                    <span class="icon">📊</span>
                    <span x-show="open" x-transition.opacity>SPM Dashboard</span>
                </a>
                <a href="{{ route('targets.bulk', ['year' => now()->year]) }}" class="nav-link {{ request()->routeIs('targets.bulk') ? 'active' : '' }}">
                    <span class="icon">🎯</span>
                    <span x-show="open" x-transition.opacity>Kelola Target (Bulk)</span>
                </a>
                
                
                <a href="{{ route('dashboard') }}" class="nav-link">
                    <span class="icon">📊</span>
                    <span x-show="open" x-transition.opacity>Public Dashboard</span>
                </a>
                
            </nav>
            <div class="p-4 border-t border-white/10 mt-4">
                <form action="{{ route('filament.admin.auth.logout') }}" method="POST">
                    @csrf
                    <button class="w-full text-left px-3 py-2 rounded bg-red-500/10 text-red-300 hover:bg-red-500/20 transition-colors">Keluar</button>
                </form>
            </div>
        </aside>

        <!-- Main -->
        <div class="flex-1 min-w-0">
            <!-- Topbar -->
            <div class="backdrop-blur-xl bg-white/5 border-b border-white/10 px-4 py-3 flex items-center justify-between sticky top-0 z-20">
                <div class="flex items-center space-x-2 text-slate-200">
                    <!-- Mobile menu button hidden (only bottom nav used) -->
                    <button class="hidden md:hidden mr-2 text-slate-300 hover:text-white transition" aria-label="Open menu"></button>
                    <span class="font-semibold hidden md:inline">Admin Panel</span>
                    <span class="text-slate-400 hidden md:inline">/</span>
                    @php $rname = request()->route()?->getName(); @endphp
                    <span class="text-slate-400 capitalize">{{ $rname ? str_replace('.', ' › ', preg_replace('/^panel\./','', $rname)) : '' }}</span>
                </div>
                <div class="flex items-center space-x-3">
                    <!-- Desktop nav mode toggle -->
                    <button class="hidden md:inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-white/10 border border-white/10 text-slate-200 hover:bg-white/15 transition" @click="navMode = navMode==='side' ? 'top' : 'side'" x-text="navMode==='side' ? 'Top Nav' : 'Sidebar'"></button>
                    <div class="relative hidden md:block">
                        <input class="bg-white/10 border border-white/10 rounded-lg pl-9 pr-3 py-2 text-sm text-slate-200 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-cyan-400/40 focus:border-cyan-400/40 w-72" placeholder="Search…">
                        <svg class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/></svg>
                    </div>
                    <div class="h-8 w-8 rounded-full bg-gradient-to-br from-cyan-400 to-fuchsia-500 shadow-[0_0_12px_rgba(168,85,247,0.5)]"></div>
                </div>
            </div>

            <!-- Desktop Top Navigation (futuristic tabs) -->
            <div x-cloak x-show="navMode==='top'" class="hidden md:block border-b border-white/10 bg-white/5 backdrop-blur">
                <div id="desktopTopNav" class="relative flex gap-2 overflow-x-auto no-scrollbar px-4 py-2 items-center">
                    <span id="desktopTopIndicator" class="absolute bottom-0 h-0.5 bg-fuchsia-400 rounded transition-all duration-300" style="left:0;width:0"></span>

                    <a href="{{ route('panel.dashboard') }}" class="dtab-item {{ request()->routeIs('panel.dashboard') ? 'active' : '' }}"><span class="icon">🏠</span><span class="label">Dashboard</span></a>

                    <div x-data="{ openMT: {{ (
                        request()->routeIs('panel.medicines.*') ||
                        request()->routeIs('panel.medical-records.*') ||
                        request()->routeIs('panel.users.*') ||
                        request()->routeIs('panel.villages.*') ||
                        request()->routeIs('panel.buildings.*') ||
                        request()->routeIs('panel.families.*') ||
                        request()->routeIs('panel.family-members.*') ||
                        request()->is('admin/chatbot*')
                    ) ? 'true' : 'false' }} }" class="relative">
                        <button type="button" @click="openMT = !openMT" class="dtab-item {{ (
                            request()->routeIs('panel.medicines.*') ||
                            request()->routeIs('panel.medical-records.*') ||
                            request()->routeIs('panel.users.*') ||
                            request()->routeIs('panel.villages.*') ||
                            request()->routeIs('panel.buildings.*') ||
                            request()->routeIs('panel.families.*') ||
                            request()->routeIs('panel.family-members.*') ||
                            request()->is('admin/chatbot*')
                        ) ? 'active' : '' }}">
                            <span class="icon">🗂️</span>
                            <span class="label">Master Data</span>
                            <svg class="h-3 w-3 ml-1 opacity-70" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.354a.75.75 0 111.02 1.1l-4.2 3.8a.75.75 0 01-1.02 0l-4.2-3.8a.75.75 0 01-.02-1.06z" clip-rule="evenodd"/></svg>
                        </button>
                        <div x-show="openMT" x-transition @click.outside="openMT=false" class="absolute top-full left-0 mt-1 bg-white/10 border border-white/10 backdrop-blur rounded-lg shadow-xl p-1 z-10 min-w-[14rem]">
                            <div class="py-1">
                                @can('view_any_medicine')
                                <a href="{{ route('panel.medicines.index') }}" class="flex items-center gap-2 px-3 py-1.5 rounded hover:bg-white/15 text-slate-200 {{ request()->routeIs('panel.medicines.*') ? 'bg-white/10' : '' }}"><span>💊</span><span>Obat</span></a>
                                @endcan
                                <a href="{{ route('panel.medical-records.index') }}" class="flex items-center gap-2 px-3 py-1.5 rounded hover:bg-white/15 text-slate-200 {{ request()->routeIs('panel.medical-records.*') ? 'bg-white/10' : '' }}"><span>📋</span><span>Rekam Medis</span></a>
                                @can('view_any_user')
                                <a href="{{ route('panel.users.index') }}" class="flex items-center gap-2 px-3 py-1.5 rounded hover:bg-white/15 text-slate-200 {{ request()->routeIs('panel.users.*') ? 'bg-white/10' : '' }}"><span>🧑‍💼</span><span>Pengguna</span></a>
                                @endcan
                                @can('view_any_village')
                                <a href="{{ route('panel.villages.index') }}" class="flex items-center gap-2 px-3 py-1.5 rounded hover:bg-white/15 text-slate-200 {{ request()->routeIs('panel.villages.*') ? 'bg-white/10' : '' }}"><span>🏘️</span><span>Desa</span></a>
                                @endcan
                                @can('view_any_building')
                                <a href="{{ route('panel.buildings.index') }}" class="flex items-center gap-2 px-3 py-1.5 rounded hover:bg-white/15 text-slate-200 {{ request()->routeIs('panel.buildings.*') ? 'bg-white/10' : '' }}"><span>🏚️</span><span>Bangunan</span></a>
                                @endcan
                                @can('view_any_family')
                                <a href="{{ route('panel.families.index') }}" class="flex items-center gap-2 px-3 py-1.5 rounded hover:bg-white/15 text-slate-200 {{ request()->routeIs('panel.families.*') ? 'bg-white/10' : '' }}"><span>👪</span><span>Keluarga</span></a>
                                @endcan
                                @can('view_any_family::member')
                                <a href="{{ route('panel.family-members.index') }}" class="flex items-center gap-2 px-3 py-1.5 rounded hover:bg-white/15 text-slate-200 {{ request()->routeIs('panel.family-members.*') ? 'bg-white/10' : '' }}"><span>🧍</span><span>Anggota</span></a>
                                @endcan
                                <a href="{{ url('/admin/chatbot') }}" class="flex items-center gap-2 px-3 py-1.5 rounded hover:bg-white/15 text-slate-200 {{ request()->is('admin/chatbot*') ? 'bg-white/10' : '' }}"><span>🤖</span><span>Chatbot</span></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Scrollable Tabs removed in favor of Bottom Navigation -->

            <main class="p-4 md:p-6 animate-fade-in pb-24 md:pb-6">
                @if (session('success'))
                    <div class="mb-4 p-3 rounded border border-emerald-400/30 text-emerald-200 bg-emerald-500/10 backdrop-blur">
                        {{ session('success') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 p-3 rounded border border-red-400/30 text-red-200 bg-red-500/10 backdrop-blur">
                        <div class="font-semibold mb-1">Terjadi kesalahan:</div>
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('admin-content')
            </main>
        </div>
    </div>


    <style>
        /* Mobile Tabs */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        @keyframes slow-pan { 0% { transform: translateY(0) scale(1); } 100% { transform: translateY(-10%) scale(1.05);} }
        .animate-slow-pan { animation: slow-pan 20s linear infinite alternate; }
        @keyframes fade-in { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0);} }
        .animate-fade-in { animation: fade-in .5s ease both; }
        .nav-link { display:flex; align-items:center; gap:.75rem; padding:.6rem .75rem; border-radius:.5rem; transition: all .2s ease; position: relative; }
        .nav-link .icon { width: 1.25rem; text-align:center; }
        .nav-link:hover { background: rgba(255,255,255,.06); box-shadow: inset 0 0 0 1px rgba(255,255,255,.08); }
        .nav-link.active { background: linear-gradient(90deg, rgba(34,211,238,0.15), rgba(168,85,247,0.12)); box-shadow: 0 0 0 1px rgba(34,211,238,0.25) inset, 0 0 30px rgba(168,85,247,.2); }
        /* Desktop top nav */
        .dtab-item { position: relative; display:flex; align-items:center; gap:.5rem; color: #cbd5e1; padding:.5rem .75rem; border-radius:.5rem; transition: background-color .2s ease, color .2s ease; }
        .dtab-item .icon { width: 1.2rem; text-align:center; }
        .dtab-item:hover { background: rgba(255,255,255,.06); color: #e2e8f0; }
        .dtab-item.active { color:#e2e8f0; }
    </style>

    <script>
        // Desktop top nav indicator placement
        (function(){
            const bar = document.getElementById('desktopTopNav');
            const ind = document.getElementById('desktopTopIndicator');
            if (!bar || !ind) return;
            function place(){
                const active = bar.querySelector('.dtab-item.active');
                if(!active) return;
                const b = bar.getBoundingClientRect();
                const t = active.getBoundingClientRect();
                const left = t.left - b.left + bar.scrollLeft + 8;
                ind.style.left = left + 'px';
                ind.style.width = Math.max(24, t.width - 16) + 'px';
            }
            window.addEventListener('resize', place);
            document.addEventListener('DOMContentLoaded', place);
            setTimeout(place, 60);
            bar.addEventListener('click', e => { const a=e.target.closest('.dtab-item'); if(!a) return; bar.querySelectorAll('.dtab-item.active').forEach(x=>x.classList.remove('active')); a.classList.add('active'); setTimeout(place, 10); });
        })();
    </script>

    <!-- Mobile Bottom Navigation -->
    <nav class="md:hidden fixed bottom-0 inset-x-0 z-40">
        <div class="relative bg-white/10 backdrop-blur-xl border-t border-white/10" x-data="{ openMasterBottom: false }">
            <!-- Master Data dropdown for bottom nav -->
            <div x-show="openMasterBottom" x-transition @click.outside="openMasterBottom=false" class="absolute bottom-[3.5rem] left-2 right-2 bg-white/10 backdrop-blur-xl border border-white/10 rounded-xl shadow-xl p-2 z-50">
                <div class="grid grid-cols-2 gap-1">
                    @can('view_any_medicine')
                    <a href="{{ route('panel.medicines.index') }}" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-white/15 text-slate-200 {{ request()->routeIs('panel.medicines.*') ? 'bg-white/10' : '' }}"><span>💊</span><span>Obat</span></a>
                    @endcan
                    <a href="{{ route('panel.medical-records.index') }}" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-white/15 text-slate-200 {{ request()->routeIs('panel.medical-records.*') ? 'bg-white/10' : '' }}"><span>📋</span><span>Rekam Medis</span></a>
                    @can('view_any_user')
                    <a href="{{ route('panel.users.index') }}" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-white/15 text-slate-200 {{ request()->routeIs('panel.users.*') ? 'bg-white/10' : '' }}"><span>🧑‍💼</span><span>Pengguna</span></a>
                    @endcan
                    @can('view_any_village')
                    <a href="{{ route('panel.villages.index') }}" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-white/15 text-slate-200 {{ request()->routeIs('panel.villages.*') ? 'bg-white/10' : '' }}"><span>🏘️</span><span>Desa</span></a>
                    @endcan
                    @can('view_any_building')
                    <a href="{{ route('panel.buildings.index') }}" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-white/15 text-slate-200 {{ request()->routeIs('panel.buildings.*') ? 'bg-white/10' : '' }}"><span>🏚️</span><span>Bangunan</span></a>
                    @endcan
                    @can('view_any_family')
                    <a href="{{ route('panel.families.index') }}" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-white/15 text-slate-200 {{ request()->routeIs('panel.families.*') ? 'bg-white/10' : '' }}"><span>👪</span><span>Keluarga</span></a>
                    @endcan
                    @can('view_any_family::member')
                    <a href="{{ route('panel.family-members.index') }}" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-white/15 text-slate-200 {{ request()->routeIs('panel.family-members.*') ? 'bg-white/10' : '' }}"><span>🧍</span><span>Anggota</span></a>
                    @endcan
                    <a href="{{ url('/admin/chatbot') }}" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-white/15 text-slate-200 {{ request()->is('admin/chatbot*') ? 'bg-white/10' : '' }}"><span>🤖</span><span>Chatbot</span></a>
                </div>
            </div>
            <div id="bottomNav" class="flex gap-2 overflow-x-auto no-scrollbar px-2 py-2 items-end">
                <!-- active halo -->
                <span id="bottomHalo" class="pointer-events-none absolute -top-3 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full bg-cyan-400/20 blur-xl opacity-0 transition-opacity"></span>

                <a href="{{ route('panel.dashboard') }}" class="nav-btn {{ request()->routeIs('panel.dashboard') ? 'active' : '' }}">
                    <span class="icon">🏠</span>
                    <span class="label">Home</span>
                </a>

                <a href="#" @click.prevent="openMasterBottom = !openMasterBottom" class="nav-btn {{ (
                    request()->routeIs('panel.medicines.*') ||
                    request()->routeIs('panel.medical-records.*') ||
                    request()->routeIs('panel.users.*') ||
                    request()->routeIs('panel.villages.*') ||
                    request()->routeIs('panel.buildings.*') ||
                    request()->routeIs('panel.families.*') ||
                    request()->routeIs('panel.family-members.*') ||
                    request()->is('admin/chatbot*')
                ) ? 'active' : '' }}">
                    <span class="icon">🗂️</span>
                    <span class="label">Master Data</span>
                </a>

                <a href="{{ route('spm.dashboard') }}" class="nav-btn {{ request()->routeIs('spm.dashboard') ? 'active' : '' }}">
                    <span class="icon">📊</span>
                    <span class="label">SPM</span>
                </a>
                <a href="{{ route('targets.bulk', ['year' => now()->year]) }}" class="nav-btn {{ request()->routeIs('targets.bulk') ? 'active' : '' }}">
                    <span class="icon">🎯</span>
                    <span class="label">Bulk</span>
                </a>

                
            </div>
        </div>
        <style>
            .nav-btn { position: relative; flex: 0 0 auto; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:.2rem; min-width:3.8rem; padding:.45rem .6rem; border-radius:1rem; color:#cbd5e1; transition: transform .2s ease, background-color .2s ease, color .2s ease; }
            .nav-btn .icon { display:grid; place-items:center; width:1.6rem; height:1.6rem; }
            .nav-btn .label { font-size:.7rem; line-height:1; letter-spacing:.01em; }
            .nav-btn.active { color:#e2e8f0; background: linear-gradient(180deg, rgba(34,211,238,.18), rgba(168,85,247,.12)); box-shadow: inset 0 0 0 1px rgba(255,255,255,.12); transform: translateY(-6px); }
        </style>
        <script>
            (function() {
                const bar = document.getElementById('bottomNav');
                const halo = document.getElementById('bottomHalo');
                if (!bar || !halo) return;
                function centerActive() {
                    const active = bar.querySelector('.nav-btn.active');
                    if (!active) return;
                    const b = bar.getBoundingClientRect();
                    const t = active.getBoundingClientRect();
                    const offset = (t.left + t.width/2) - (b.left + b.width/2);
                    bar.scrollTo({ left: bar.scrollLeft + offset, behavior: 'smooth' });
                    // position halo centered above active
                    const left = t.left - b.left + bar.scrollLeft + t.width/2;
                    halo.style.left = left + 'px';
                    halo.style.opacity = 1;
                    halo.style.transform = 'translateX(-50%)';
                }
                window.addEventListener('resize', centerActive);
                document.addEventListener('DOMContentLoaded', centerActive);
                setTimeout(centerActive, 60);
                bar.addEventListener('click', (e) => {
                    const btn = e.target.closest('.nav-btn');
                    if (!btn) return;
                    bar.querySelectorAll('.nav-btn.active').forEach(el => el.classList.remove('active'));
                    btn.classList.add('active');
                    setTimeout(centerActive, 10);
                });
            })();
        </script>
    </nav>
</div>
@endsection
