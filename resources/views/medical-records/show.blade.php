<!DOCTYPE html>
<html lang="id" class="h-full scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Detail Rekam Medis - {{ $familyMember->name }}</title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          boxShadow: { soft: '0 10px 25px -10px rgba(0,0,0,0.15)' }
        }
      }
    }
  </script>
</head>
<body class="min-h-full bg-gray-50 text-gray-800 antialiased">
    

  <!-- Navigation -->
  {{-- @include('includes.navbar') --}}

  <div class="py-8">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

      <!-- Header -->
      <div class="relative mb-6 overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-700 shadow-soft">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-white/10 via-transparent to-transparent"></div>
        <div class="relative p-6 sm:p-8">
          <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
              <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">Detail Rekam Medis</h1>
              <p class="mt-1 text-sm text-blue-100">{{ $familyMember->name }} • Kunjungan {{ $medicalRecord->visit_date->format('d-m-Y') }}</p>

              <!-- Quick facts -->
              <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-4">
                <div class="rounded-lg bg-white/10 px-4 py-3 backdrop-blur">
                  <dt class="text-xs uppercase tracking-wider text-blue-100">Tanggal Lahir</dt>
                  <dd class="text-sm font-medium text-white">{{ $familyMember->formatted_birth_date ?? '-' }}</dd>
                </div>
                <div class="rounded-lg bg-white/10 px-4 py-3 backdrop-blur">
                  <dt class="text-xs uppercase tracking-wider text-blue-100">Jenis Kelamin</dt>
                  <dd class="text-sm font-medium text-white">{{ $familyMember->gender }}</dd>
                </div>
                <div class="rounded-lg bg-white/10 px-4 py-3 backdrop-blur">
                  <dt class="text-xs uppercase tracking-wider text-blue-100">Umur</dt>
                  <dd class="text-sm font-medium text-white">{{ $familyMember->age }} tahun</dd>
                </div>
                <div class="rounded-lg bg-white/10 px-4 py-3 backdrop-blur">
                  <dt class="text-xs uppercase tracking-wider text-blue-100">Alamat</dt>
                  <dd class="text-sm font-medium text-white">Desa {{ $familyMember->family->building->village->name }}</dd>
                </div>
              </dl>
            </div>

            <!-- Actions -->
            <div class="flex flex-wrap items-center gap-2">
              <a href="{{ route('medical-records.index', $familyMember) }}" class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-3 py-2 text-sm font-medium text-white shadow-sm backdrop-blur transition hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/40">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/></svg>
                Kembali ke Daftar
              </a>
              <a href="{{ route('medical-records.edit', [$familyMember, $medicalRecord]) }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-4 py-2 text-sm font-semibold text-white shadow-soft transition hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                Edit Rekam Medis
              </a>
              <a href="{{ route('medical-records.print-prescription', [$familyMember, $medicalRecord]) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl bg-purple-500 px-4 py-2 text-sm font-semibold text-white shadow-soft transition hover:bg-purple-600 focus:outline-none focus:ring-2 focus:ring-purple-300" title="Print Resep">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd"/></svg>
                Print Resep
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Success Message -->
      @if(session('success'))
      <div class="mb-6 rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-4 text-emerald-800 shadow-sm">
        <div class="flex gap-3">
          <svg class="h-5 w-5 flex-none text-emerald-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
          <p class="text-sm">{{ session('success') }}</p>
        </div>
      </div>
      @endif

      <!-- Content -->
      <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
        <!-- Left: Basic & Vital -->
        <div class="space-y-6 md:col-span-2">
          <!-- Basic Info -->
          <section class="overflow-hidden rounded-2xl bg-white shadow-soft">
            <header class="border-b border-gray-200 px-6 py-4">
              <h2 class="text-lg font-semibold text-gray-900">Informasi Dasar</h2>
            </header>
            <div class="space-y-4 p-6">
              <div>
                <h3 class="text-sm font-medium text-gray-500">Tanggal Kunjungan</h3>
                <p class="mt-1">{{ $medicalRecord->visit_date->format('d F Y') }}</p>
              </div>
              <div>
                <h3 class="text-sm font-medium text-gray-500">Keluhan Utama</h3>
                <p class="mt-1">{{ $medicalRecord->chief_complaint ?? '-' }}</p>
              </div>
              <div>
                <h3 class="text-sm font-medium text-gray-500">Anamnesa</h3>
                <p class="mt-1">{{ $medicalRecord->anamnesis ?? '-' }}</p>
              </div>
            </div>
          </section>

          <!-- Vital Signs -->
          <section class="overflow-hidden rounded-2xl bg-white shadow-soft">
            <header class="border-b border-gray-200 px-6 py-4">
              <h2 class="text-lg font-semibold text-gray-900">Tanda Vital</h2>
            </header>
            <div class="p-6">
              <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
                <div class="rounded-lg bg-gray-50 p-4">
                  <h3 class="text-sm font-medium text-gray-500">Tekanan Darah</h3>
                  @if($medicalRecord->systolic && $medicalRecord->diastolic)
                    <p class="mt-1 text-lg font-semibold">{{ $medicalRecord->systolic }}/{{ $medicalRecord->diastolic }} <span class="text-gray-500">mmHg</span></p>
                    <p class="text-sm {{ $medicalRecord->blood_pressure_category === 'Normal' ? 'text-emerald-600' : 'text-rose-600' }}">{{ $medicalRecord->blood_pressure_category }}</p>
                  @else
                    <p class="mt-1 text-gray-400">Tidak ada data</p>
                  @endif
                </div>
                <div class="rounded-lg bg-gray-50 p-4">
                  <h3 class="text-sm font-medium text-gray-500">BB/TB</h3>
                  @if($medicalRecord->weight && $medicalRecord->height)
                    <p class="mt-1 text-lg font-semibold">{{ $medicalRecord->weight }} <span class="text-gray-500">kg</span> / {{ $medicalRecord->height }} <span class="text-gray-500">cm</span></p>
                    <p class="text-sm">BMI: <span class="font-medium">{{ $medicalRecord->bmi }}</span> <span class="text-gray-500">({{ $medicalRecord->bmi_category }})</span></p>
                  @else
                    <p class="mt-1 text-gray-400">Tidak ada data</p>
                  @endif
                </div>
                <div class="rounded-lg bg-gray-50 p-4">
                  <h3 class="text-sm font-medium text-gray-500">Heart Rate</h3>
                  @if($medicalRecord->heart_rate)
                    <p class="mt-1 text-lg font-semibold">{{ $medicalRecord->heart_rate }} <span class="text-gray-500">bpm</span></p>
                  @else
                    <p class="mt-1 text-gray-400">Tidak ada data</p>
                  @endif
                </div>
                <div class="rounded-lg bg-gray-50 p-4">
                  <h3 class="text-sm font-medium text-gray-500">Suhu Tubuh</h3>
                  @if($medicalRecord->body_temperature)
                    <p class="mt-1 text-lg font-semibold">{{ $medicalRecord->body_temperature }}°C</p>
                  @else
                    <p class="mt-1 text-gray-400">Tidak ada data</p>
                  @endif
                </div>
                <div class="rounded-lg bg-gray-50 p-4">
                  <h3 class="text-sm font-medium text-gray-500">Respiratory Rate</h3>
                  @if($medicalRecord->respiratory_rate)
                    <p class="mt-1 text-lg font-semibold">{{ $medicalRecord->respiratory_rate }} <span class="text-gray-500">rpm</span></p>
                  @else
                    <p class="mt-1 text-gray-400">Tidak ada data</p>
                  @endif
                </div>
              </div>
            </div>
          </section>
        </div>

        <!-- Right: Diagnosis & Treatment -->
        <div class="space-y-6">
          <section class="overflow-hidden rounded-2xl bg-white shadow-soft">
            <header class="border-b border-gray-200 px-6 py-4">
              <h2 class="text-lg font-semibold text-gray-900">Diagnosis</h2>
            </header>
            <div class="p-6">
              @if($medicalRecord->diagnosis_name)
                <div class="rounded-lg bg-gray-50 p-4">
                  <h3 class="text-sm font-medium text-gray-700">{{ $medicalRecord->diagnosis_name }}</h3>
                  @if($medicalRecord->diagnosis_code)
                    <p class="mt-1 text-xs text-gray-500">Kode: {{ $medicalRecord->diagnosis_code }}</p>
                  @endif
                </div>
              @else
                <p class="text-gray-400">Tidak ada diagnosis</p>
              @endif
            </div>
          </section>

          <section class="overflow-hidden rounded-2xl bg-white shadow-soft">
            <header class="border-b border-gray-200 px-6 py-4">
              <h2 class="text-lg font-semibold text-gray-900">Terapi & Pengobatan</h2>
            </header>
            <div class="space-y-4 p-6">
              <div>
                <h3 class="text-sm font-medium text-gray-500">Terapi Obat</h3>
                <p class="mt-1">{{ $medicalRecord->medication ?? '-' }}</p>
              </div>
              <div>
                <h3 class="text-sm font-medium text-gray-500">Tindakan</h3>
                <p class="mt-1">{{ $medicalRecord->procedure ?? '-' }}</p>
              </div>
            </div>
          </section>

          <div class="rounded-2xl bg-gray-50 p-4 text-xs text-gray-600">
            <p>Dicatat oleh: {{ $medicalRecord->creator->name ?? 'System' }}</p>
            <p>Tanggal input: {{ $medicalRecord->created_at->format('d-m-Y H:i') }}</p>
          </div>
        </div>
      </div>

      <!-- Footer note -->
      <p class="mt-6 text-center text-xs text-gray-500">Data bersifat rahasia. Pastikan akses sesuai kewenangan.</p>
    </div>
  </div>
</body>
</html>
