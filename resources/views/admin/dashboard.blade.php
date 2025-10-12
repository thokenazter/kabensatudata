@extends('layouts.admin')

@section('admin-content')
<div class="space-y-8">
    <!-- Hero -->
    <div class="relative overflow-hidden rounded-2xl border border-white/10 bg-gradient-to-r from-cyan-500/20 via-fuchsia-500/20 to-emerald-500/20 p-6">
        <div class="absolute -inset-1 bg-gradient-to-r from-cyan-500/10 via-transparent to-fuchsia-500/10 blur-2xl opacity-60"></div>
        <div class="relative z-10 flex items-center justify-between">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-cyan-300 via-fuchsia-300 to-emerald-300">Selamat Datang di Control Center</h1>
                <p class="mt-1 text-slate-300/80">Ringkasan cepat modul dan tindakan utama</p>
            </div>
            <div class="hidden md:block h-16 w-16 rounded-xl bg-gradient-to-br from-cyan-400 to-fuchsia-500 shadow-[0_0_30px_rgba(168,85,247,0.35)] animate-pulse"></div>
        </div>
    </div>

    <!-- Quick modules -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <a href="{{ route('panel.medicines.index') }}" class="group relative overflow-hidden rounded-xl border border-white/10 bg-white/5 p-5 backdrop-blur hover:-translate-y-0.5 transition-all duration-300">
            <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/10 to-transparent opacity-0 group-hover:opacity-100 transition"></div>
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs uppercase tracking-wide text-slate-400">Manajemen</div>
                    <div class="mt-1 text-lg font-semibold text-slate-100">Obat</div>
                    <div class="mt-1 text-slate-400">Tambah, ubah, kelola stok</div>
                </div>
                <div class="h-12 w-12 rounded-lg bg-gradient-to-br from-cyan-400 to-blue-500 shadow-[0_0_20px_rgba(59,130,246,0.4)]"></div>
            </div>
            <div class="mt-4 h-1 w-full rounded bg-slate-700/60">
                <div class="h-1 w-2/3 rounded bg-gradient-to-r from-cyan-400 to-blue-500 animate-gradient-x"></div>
            </div>
        </a>

        <a href="{{ route('panel.families.index') }}" class="group relative overflow-hidden rounded-xl border border-white/10 bg-white/5 p-5 backdrop-blur hover:-translate-y-0.5 transition-all duration-300">
            <div class="absolute inset-0 bg-gradient-to-r from-fuchsia-500/10 to-transparent opacity-0 group-hover:opacity-100 transition"></div>
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs uppercase tracking-wide text-slate-400">Data</div>
                    <div class="mt-1 text-lg font-semibold text-slate-100">Keluarga</div>
                    <div class="mt-1 text-slate-400">KK, indikator, relasi</div>
                </div>
                <div class="h-12 w-12 rounded-lg bg-gradient-to-br from-fuchsia-400 to-pink-500 shadow-[0_0_20px_rgba(217,70,239,0.35)]"></div>
            </div>
            <div class="mt-4 h-1 w-full rounded bg-slate-700/60">
                <div class="h-1 w-1/2 rounded bg-gradient-to-r from-fuchsia-400 to-pink-500 animate-gradient-x"></div>
            </div>
        </a>

        <a href="{{ route('panel.family-members.index') }}" class="group relative overflow-hidden rounded-xl border border-white/10 bg-white/5 p-5 backdrop-blur hover:-translate-y-0.5 transition-all duration-300">
            <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/10 to-transparent opacity-0 group-hover:opacity-100 transition"></div>
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs uppercase tracking-wide text-slate-400">Data</div>
                    <div class="mt-1 text-lg font-semibold text-slate-100">Anggota</div>
                    <div class="mt-1 text-slate-400">Identitas, kesehatan</div>
                </div>
                <div class="h-12 w-12 rounded-lg bg-gradient-to-br from-emerald-400 to-teal-500 shadow-[0_0_20px_rgba(16,185,129,0.35)]"></div>
            </div>
            <div class="mt-4 h-1 w-full rounded bg-slate-700/60">
                <div class="h-1 w-3/4 rounded bg-gradient-to-r from-emerald-400 to-teal-500 animate-gradient-x"></div>
            </div>
        </a>
    </div>

    <!-- More links -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <a href="{{ route('dashboard') }}" class="relative overflow-hidden rounded-xl border border-white/10 bg-white/5 p-5 backdrop-blur group">
            <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition bg-gradient-to-r from-cyan-500/10 to-fuchsia-500/10"></div>
            <div class="text-sm text-slate-400">Lihat</div>
            <div class="mt-1 text-xl font-semibold text-slate-100">Dashboard Publik</div>
            <div class="mt-2 text-slate-400">Statistik dan analitik keseluruhan</div>
        </a>
        <a href="{{ url('/admin/chatbot') }}" class="relative overflow-hidden rounded-xl border border-white/10 bg-white/5 p-5 backdrop-blur group">
            <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition bg-gradient-to-r from-emerald-500/10 to-cyan-500/10"></div>
            <div class="text-sm text-slate-400">Eksperimen</div>
            <div class="mt-1 text-xl font-semibold text-slate-100">Chatbot</div>
            <div class="mt-2 text-slate-400">Antarmuka chatbot admin</div>
        </a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-1 gap-5">
        <a href="{{ url('/pegawai') }}" class="relative overflow-hidden rounded-xl border border-white/10 bg-white/5 p-5 backdrop-blur group">
            <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition bg-gradient-to-r from-emerald-500/10 to-cyan-500/10"></div>
            <div class="mt-1 text-center text-xl font-semibold text-slate-100">Dashboard Pegawai</div>
        </a>
    </div>

    <style>
        @keyframes gradient-x { 0%,100%{ background-position: 0% 50%; } 50%{ background-position: 100% 50%; } }
        .animate-gradient-x { background-size: 200% 200%; animation: gradient-x 3s ease infinite; }
    </style>
</div>
@endsection
