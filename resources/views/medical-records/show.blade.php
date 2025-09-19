<!DOCTYPE html>
<html lang="id" class="h-full scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Detail Rekam Medis - {{ $familyMember->name }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        canvas: '#F8FAFC',
                        ink: '#0F172A'
                    },
                    boxShadow: {
                        sheet: '0 24px 60px -30px rgba(15,23,42,0.35)',
                        card: '0 16px 40px -24px rgba(15,23,42,0.25)'
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-full bg-canvas text-ink antialiased">
    <div class="py-10">
        <div class="mx-auto max-w-6xl px-4 sm:px-8">
            {{-- Header --}}
            <section class="relative mb-10 rounded-3xl bg-white shadow-sheet">
                <div class="absolute inset-x-0 top-0 h-2 rounded-t-3xl bg-gradient-to-r from-sky-400 via-blue-500 to-indigo-600"></div>
                <div class="space-y-8 px-6 pb-8 pt-10 sm:px-10 sm:pb-10">
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                        <div class="space-y-2">
                            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Rekam Medis Pasien</p>
                            <h1 class="text-3xl font-semibold text-slate-900">{{ $familyMember->name }}</h1>
                            <p class="text-sm text-slate-500">Kunjungan {{ $medicalRecord->visit_date?->translatedFormat('d F Y') ?? 'Tanggal tidak tersedia' }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('medical-records.index', $familyMember) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 shadow-sm transition hover:border-slate-300 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/></svg>
                                Kembali ke daftar
                            </a>
                            <a href="{{ route('medical-records.edit', [$familyMember, $medicalRecord]) }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-4 py-2 text-sm font-semibold text-white shadow-card transition hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                                Ubah data
                            </a>
                            <a href="{{ route('medical-records.print-prescription', [$familyMember, $medicalRecord]) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl bg-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow-card transition hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd"/></svg>
                                Cetak resep
                            </a>
                        </div>
                    </div>

                    <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nomor RM</dt>
                            <dd class="mt-1 text-sm font-medium text-slate-900">{{ $familyMember->rm_number ?? '—' }}</dd>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tanggal Lahir</dt>
                            <dd class="mt-1 text-sm font-medium text-slate-900">{{ $familyMember->formatted_birth_date ?? '—' }}</dd>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Jenis Kelamin / Usia</dt>
                            <dd class="mt-1 text-sm font-medium text-slate-900">{{ $familyMember->gender }} • {{ $familyMember->age }} tahun</dd>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Alamat</dt>
                            <dd class="mt-1 text-sm font-medium text-slate-900">Desa {{ $familyMember->family->building->village->name ?? '—' }}</dd>
                        </div>
                    </dl>

                    @if($visitHistory->count() > 1)
                        <div>
                            <h2 class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Riwayat Kunjungan</h2>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach($visitHistory as $history)
                                    @php
                                        $isCurrent = $history->id === $medicalRecord->id;
                                        $visitLabel = optional($history->visit_date)->translatedFormat('d M Y')
                                            ?? optional($history->created_at)->translatedFormat('d M Y')
                                            ?? 'Tanpa tanggal';
                                    @endphp
                                    <a href="{{ route('medical-records.show', [$familyMember, $history]) }}"
                                       @if($isCurrent) aria-current="page" @endif
                                       class="group inline-flex min-w-[7rem] flex-col rounded-lg border px-3 py-2 text-xs transition @if($isCurrent) border-indigo-500 bg-indigo-50 text-indigo-700 font-semibold pointer-events-none @else border-slate-200 bg-white text-slate-600 hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 @endif">
                                        <span>{{ $visitLabel }}</span>
                                        @if(!empty($history->diagnosis_name))
                                            <span class="mt-1 w-full truncate text-[11px] text-slate-400">{{ $history->diagnosis_name }}</span>
                                        @endif
                                        @if($isCurrent)
                                            <span class="mt-2 inline-flex items-center gap-1 self-start rounded-full bg-indigo-500/10 px-2 py-0.5 text-[10px] font-medium text-indigo-700">
                                                • Saat ini
                                            </span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            {{-- Flash message --}}
            @if(session('success'))
                <div class="mb-8 rounded-2xl border-l-4 border-emerald-500 bg-emerald-50 p-4 text-sm text-emerald-800 shadow-card">
                    <div class="flex items-start gap-3">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12 9 18.75 21.75 6" />
                        </svg>
                        <p>{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            {{-- Body --}}
            <div class="grid gap-6 lg:grid-cols-[2fr,1.1fr]">
                {{-- Clinical summary --}}
                <div class="space-y-6">
                    <section class="rounded-2xl bg-white shadow-card">
                        <header class="border-b border-slate-200 px-6 py-4">
                            <h2 class="text-lg font-semibold text-slate-900">Ringkasan Klinis</h2>
                        </header>
                        <div class="px-6 py-5">
                            <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Keluhan Utama</dt>
                                    <dd class="mt-1 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-slate-800 whitespace-pre-line">{{ $medicalRecord->chief_complaint ?? 'Tidak ada catatan.' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Anamnesis</dt>
                                    <dd class="mt-1 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-slate-800 whitespace-pre-line">{{ $medicalRecord->anamnesis ?? 'Tidak ada catatan.' }}</dd>
                                </div>
                            </dl>

                            <div class="mt-6">
                                <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pemeriksaan Fisik</h3>
                                <div class="mt-3 grid grid-cols-1 gap-3 text-sm sm:grid-cols-3">
                                    <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tekanan Darah</p>
                                        @if($medicalRecord->systolic && $medicalRecord->diastolic)
                                            <p class="mt-2 text-lg font-semibold text-slate-900">{{ $medicalRecord->systolic }}/{{ $medicalRecord->diastolic }}<span class="text-sm font-normal text-slate-500"> mmHg</span></p>
                                            <p class="text-xs text-slate-500">{{ $medicalRecord->blood_pressure_category }}</p>
                                        @else
                                            <p class="mt-2 text-slate-400">Belum diisi</p>
                                        @endif
                                    </div>
                                    <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">BB / TB</p>
                                        @if($medicalRecord->weight && $medicalRecord->height)
                                            <p class="mt-2 text-lg font-semibold text-slate-900">{{ $medicalRecord->weight }} <span class="text-sm font-normal text-slate-500">kg</span> / {{ $medicalRecord->height }} <span class="text-sm font-normal text-slate-500">cm</span></p>
                                            <p class="text-xs text-slate-500">BMI: {{ $medicalRecord->bmi }} ({{ $medicalRecord->bmi_category }})</p>
                                        @else
                                            <p class="mt-2 text-slate-400">Belum diisi</p>
                                        @endif
                                    </div>
                                    <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Suhu / Nadi / RR</p>
                                        <div class="mt-2 space-y-1 text-slate-700">
                                            <p>Suhu: <span class="font-medium">{{ $medicalRecord->body_temperature ?? '—' }}</span>°C</p>
                                            <p>Nadi: <span class="font-medium">{{ $medicalRecord->heart_rate ?? '—' }}</span> bpm</p>
                                            <p>RR: <span class="font-medium">{{ $medicalRecord->respiratory_rate ?? '—' }}</span> x/menit</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl bg-white shadow-card">
                        <header class="border-b border-slate-200 px-6 py-4">
                            <h2 class="text-lg font-semibold text-slate-900">Catatan Diagnostik & Rencana</h2>
                        </header>
                        <div class="space-y-5 px-6 py-5 text-sm">
                            <div>
                                <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Diagnosa Utama</h3>
                                <div class="mt-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                    <p class="font-medium text-slate-900">{{ $medicalRecord->diagnosis_name ?? 'Belum dicatat' }}</p>
                                    @if($medicalRecord->diagnosis_code)
                                        <p class="mt-1 text-xs text-slate-500">ICD: {{ $medicalRecord->diagnosis_code }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                                    <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Therapy / Edukasi</h4>
                                    <p class="mt-2 text-slate-700 whitespace-pre-line">{{ $medicalRecord->therapy ?? 'Tidak ada catatan.' }}</p>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                                    <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tindakan Medis</h4>
                                    <p class="mt-2 text-slate-700 whitespace-pre-line">{{ $medicalRecord->procedure ?? 'Tidak ada tindakan' }}</p>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Obat yang Diresepkan</h3>
                                <div class="mt-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-700 whitespace-pre-line">
                                    {{ $medicalRecord->medication ?? 'Tidak ada obat diresepkan.' }}
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                {{-- Sidebar --}}
                <aside class="space-y-6">
                    <section class="rounded-2xl bg-white shadow-card">
                        <header class="border-b border-slate-200 px-6 py-4">
                            <h2 class="text-lg font-semibold text-slate-900">Detail Administratif</h2>
                        </header>
                        <dl class="divide-y divide-slate-200 text-sm">
                            <div class="grid gap-2 px-6 py-4 sm:grid-cols-3">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Dicatat oleh</dt>
                                <dd class="sm:col-span-2 text-slate-800">{{ optional($medicalRecord->creator)->name ?? 'System' }}</dd>
                            </div>
                            <div class="grid gap-2 px-6 py-4 sm:grid-cols-3">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Waktu Input</dt>
                                <dd class="sm:col-span-2 text-slate-800">{{ optional($medicalRecord->created_at)->translatedFormat('d F Y • H:i') ?? '—' }}</dd>
                            </div>
                            <div class="grid gap-2 px-6 py-4 sm:grid-cols-3">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status Alur</dt>
                                <dd class="sm:col-span-2">
                                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-medium text-slate-600">
                                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                                        {{ \Illuminate\Support\Str::of($medicalRecord->workflow_status ?? 'draft')->replace('_', ' ')->upper() }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </section>

                    <section class="rounded-2xl border border-dashed border-slate-300 bg-slate-50/70 px-6 py-5 text-xs text-slate-500">
                        Dokumen ini merupakan bagian dari rekam medis elektronik. Informasi bersifat rahasia dan hanya dapat digunakan oleh tenaga kesehatan berwenang.
                    </section>
                </aside>
            </div>

            <footer class="mt-12 border-t border-slate-200 py-4 text-center text-xs text-slate-500">
                &copy; {{ now()->year }} Sistem Informasi Rekam Medis. Semua hak dilindungi.
            </footer>
        </div>
    </div>
</body>
</html>
