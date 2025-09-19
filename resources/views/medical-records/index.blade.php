<!DOCTYPE html>
<html lang="id" class="h-full scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Riwayat Rekam Medis - {{ $familyMember->name }}</title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Alpine.js -->
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script>
    // Optional: dark mode siap, shadow halus
    tailwind.config = {
      darkMode: 'class',
      theme: { extend: { boxShadow: { soft: '0 10px 25px -10px rgba(0,0,0,0.15)' } } }
    }
  </script>
</head>
<body class="min-h-full bg-gray-50 text-gray-800 antialiased" x-data="searchFilter()">
  <!-- Navigation -->
  {{-- @include('includes.navbar') --}}

  <!-- Page -->
  <div class="py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

      <!-- Header / Hero -->
      <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-700 shadow-soft">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-white/10 via-transparent to-transparent"></div>
        <div class="relative p-6 sm:p-8">
          <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
              <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">Riwayat Rekam Medis</h1>
              <p class="mt-1 text-sm text-blue-100">{{ $familyMember->name }} • {{ $familyMember->nik ?? 'NIK tidak tersedia' }}</p>

              <!-- Quick facts -->
              <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-lg bg-white/10 px-4 py-3 backdrop-blur">
                  <dt class="text-xs uppercase tracking-wider text-blue-100">Tanggal Lahir</dt>
                  <dd class="text-sm font-medium text-white">{{ $familyMember->formatted_birth_date ?? '-' }}</dd>
                </div>
                <div class="rounded-lg bg-white/10 px-4 py-3 backdrop-blur">
                  <dt class="text-xs uppercase tracking-wider text-blue-100">Jenis Kelamin</dt>
                  <dd class="text-sm font-medium text-white">{{ $familyMember->gender }}</dd>
                </div>
                <div class="rounded-lg bg-white/10 px-4 py-3 backdrop-blur">
                  <dt class="text-xs uppercase tracking-wider text-blue-100">Alamat</dt>
                  <dd class="text-sm font-medium text-white">
                    Desa {{ $familyMember->family->building->village->name }}, Bangunan No. {{ $familyMember->family->building->building_number }}
                  </dd>
                </div>
              </dl>
            </div>

            <!-- Actions -->
            <div class="flex flex-wrap items-center gap-2">
              <a href="{{ route('family-members.show', $familyMember) }}"
                 class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-3 py-2 text-sm font-medium text-white shadow-sm backdrop-blur transition hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/40">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                </svg>
                Kembali ke Detail
              </a>
              <a href="{{ route('medical-records.create', $familyMember) }}"
                 class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-4 py-2 text-sm font-semibold text-white shadow-soft transition hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                </svg>
                Tambah Rekam Medis
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Success Message -->
      @if(session('success'))
      <div class="mt-6 rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-4 text-emerald-800 shadow-sm">
        <div class="flex gap-3">
          <svg class="h-5 w-5 flex-none text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
          </svg>
          <p class="text-sm">{{ session('success') }}</p>
        </div>
      </div>
      @endif

      <!-- Search & Filter -->
      <div class="mt-6 rounded-2xl bg-white shadow-soft p-6">
        <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
          <!-- Search Input -->
          <div class="relative flex-1 max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
            </div>
            <input type="text" 
                   x-model="searchQuery"
                   @input="filterRecords()"
                   placeholder="Cari berdasarkan keluhan, diagnosis, atau obat..."
                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
          </div>
          
          <!-- Date Filter -->
          <div class="flex gap-2 items-center">
            <label class="text-sm font-medium text-gray-700">Filter Tanggal:</label>
            <input type="date" 
                   x-model="dateFrom"
                   @change="filterRecords()"
                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <span class="text-gray-500">s/d</span>
            <input type="date" 
                   x-model="dateTo"
                   @change="filterRecords()"
                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
          </div>
          
          <!-- Clear Filter -->
          <button @click="clearFilters()" 
                  class="inline-flex items-center gap-2 px-3 py-2 text-sm text-gray-600 hover:text-gray-800 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Clear
          </button>
        </div>
        
        <!-- Search Results Info -->
        <div x-show="searchQuery || dateFrom || dateTo" class="mt-4 text-sm text-gray-600">
          Menampilkan <span x-text="filteredCount"></span> dari {{ $medicalRecords->total() }} rekam medis
          <span x-show="searchQuery">untuk "<span x-text="searchQuery" class="font-medium"></span>"</span>
        </div>
      </div>

      <!-- Card: Table & Tools -->
      <div class="mt-6 overflow-hidden rounded-2xl bg-white shadow-soft">
        <!-- Toolbar -->
        <div class="flex flex-col gap-3 border-b border-gray-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
          <div class="flex items-center gap-3">
            <h2 class="text-lg font-semibold text-gray-900">Daftar Kunjungan</h2>
            <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
              Total: {{ $medicalRecords->total() }}
            </span>
          </div>

          <!-- Quick Actions -->
          <div class="flex items-center gap-2">
            <button @click="clearFilters()" 
                    x-show="searchQuery || dateFrom || dateTo"
                    class="inline-flex items-center gap-2 px-3 py-2 text-sm text-gray-600 hover:text-gray-800 transition-colors">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
              Reset Filter
            </button>
          </div>
        </div>

        @if($medicalRecords->count() > 0)
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                <th scope="col" class="px-6 py-3">Tgl. Kunjungan</th>
                <th scope="col" class="px-6 py-3">Keluhan Utama</th>
                <th scope="col" class="px-6 py-3">Tekanan Darah</th>
                <th scope="col" class="px-6 py-3">BB/TB</th>
                <th scope="col" class="px-6 py-3">Diagnosa</th>
                <th scope="col" class="px-6 py-3 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white medical-records-tbody">
              @foreach($medicalRecords as $record)
              <tr class="hover:bg-gray-50/70 medical-record-row" 
                  data-search-content="{{ strtolower(($record->chief_complaint ?? '') . ' ' . ($record->diagnosis_name ?? '') . ' ' . ($record->medication ?? '') . ' ' . ($record->anamnesis ?? '')) }}"
                  data-visit-date="{{ $record->visit_date->format('Y-m-d') }}">
                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                  {{ $record->visit_date->format('d-m-Y') }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-700">
                  <p class="line-clamp-2">{{ $record->chief_complaint ?? '-' }}</p>
                </td>
                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                  @if($record->systolic && $record->diastolic)
                    <span class="font-semibold">{{ $record->systolic }}/{{ $record->diastolic }}</span> <span class="text-gray-500">mmHg</span>
                    <span class="mt-1 block text-xs font-medium {{ $record->blood_pressure_category === 'Normal' ? 'text-emerald-600' : 'text-rose-600' }}">
                      {{ $record->blood_pressure_category }}
                    </span>
                  @else
                    -
                  @endif
                </td>
                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                  @if($record->weight && $record->height)
                    {{ $record->weight }}<span class="text-gray-500"> kg</span> / {{ $record->height }}<span class="text-gray-500"> cm</span>
                    <span class="mt-1 block text-xs">BMI: <span class="font-medium">{{ $record->bmi }}</span> <span class="text-gray-500">({{ $record->bmi_category }})</span></span>
                  @else
                    -
                  @endif
                </td>
                <td class="px-6 py-4 text-sm text-gray-700">
                  <span class="block truncate">{{ $record->diagnosis_name ?? '-' }}</span>
                  @if($record->diagnosis_code)
                    <span class="text-xs text-gray-400">({{ $record->diagnosis_code }})</span>
                  @endif
                </td>
                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                  <div class="inline-flex items-center gap-2">
                    <a href="{{ route('medical-records.show', [$familyMember, $record]) }}"
                       class="group inline-flex items-center gap-1 rounded-lg px-2 py-1 text-blue-600 transition hover:bg-blue-50 hover:text-blue-800" 
                       title="Detail">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 3C5.5 3 1.73 6.11.46 10c1.27 3.89 5.04 7 9.54 7s8.27-3.11 9.54-7C18.27 6.11 14.5 3 10 3zm0 10a3 3 0 110-6 3 3 0 010 6z"/>
                      </svg>
                      <span class="hidden sm:inline">Detail</span>
                    </a>
                    <a href="{{ route('medical-records.edit', [$familyMember, $record]) }}"
                       class="group inline-flex items-center gap-1 rounded-lg px-2 py-1 text-emerald-600 transition hover:bg-emerald-50 hover:text-emerald-700" title="Edit">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-8.486 8.486a2 2 0 01-.878.515l-3.18.795a.5.5 0 01-.606-.606l.794-3.18a2 2 0 01.516-.878l8.486-8.486z"/>
                      </svg>
                      <span class="hidden sm:inline">Edit</span>
                    </a>
                    <a href="{{ route('medical-records.print-prescription', [$familyMember, $record]) }}" target="_blank" rel="noopener"
                       class="group inline-flex items-center gap-1 rounded-lg px-2 py-1 text-purple-600 transition hover:bg-purple-50 hover:text-purple-700" title="Print Resep">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd"/>
                      </svg>
                      <span class="hidden sm:inline">Resep</span>
                    </a>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="border-t border-gray-200 bg-gray-50 px-6 py-4">
          {{ $medicalRecords->links() }}
        </div>
        @else
        <div class="px-6 py-14 text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <h3 class="mt-3 text-base font-semibold text-gray-900">Belum ada data rekam medis</h3>
          <p class="mt-1 text-sm text-gray-600">Mulai buat rekam medis baru untuk pasien ini.</p>
          <div class="mt-6">
            <a href="{{ route('medical-records.create', $familyMember) }}"
               class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
              <svg class="-ml-1 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
              </svg>
              Tambah Rekam Medis
            </a>
          </div>
        </div>
        @endif
      </div>

      <!-- Subtle footer note -->
      <p class="mt-6 text-center text-xs text-gray-500">Data bersifat rahasia. Pastikan akses sesuai kewenangan.</p>
    </div>
  </div>

  <!-- Modal removed: detail links now navigate directly to the detail page -->

  <script>
    // Search and Filter functionality
    function searchFilter() {
      return {
        searchQuery: '',
        dateFrom: '',
        dateTo: '',
        filteredCount: {{ $medicalRecords->count() }},
        
        filterRecords() {
          const rows = document.querySelectorAll('.medical-record-row');
          let visibleCount = 0;
          
          rows.forEach(row => {
            let showRow = true;
            
            // Text search filter
            if (this.searchQuery.trim()) {
              const searchContent = row.getAttribute('data-search-content');
              const searchTerms = this.searchQuery.toLowerCase().trim().split(' ');
              const matchesSearch = searchTerms.every(term => 
                searchContent.includes(term)
              );
              if (!matchesSearch) showRow = false;
            }
            
            // Date range filter
            const visitDate = row.getAttribute('data-visit-date');
            if (this.dateFrom && visitDate < this.dateFrom) showRow = false;
            if (this.dateTo && visitDate > this.dateTo) showRow = false;
            
            // Show/hide row
            if (showRow) {
              row.style.display = '';
              visibleCount++;
            } else {
              row.style.display = 'none';
            }
          });
          
          this.filteredCount = visibleCount;
          this.updateEmptyState();
        },
        
        clearFilters() {
          this.searchQuery = '';
          this.dateFrom = '';
          this.dateTo = '';
          this.filterRecords();
        },
        
        updateEmptyState() {
          const tbody = document.querySelector('.medical-records-tbody');
          const emptyState = document.querySelector('.search-empty-state');
          
          if (this.filteredCount === 0 && (this.searchQuery || this.dateFrom || this.dateTo)) {
            // Show search empty state
            if (!emptyState) {
              const emptyRow = document.createElement('tr');
              emptyRow.className = 'search-empty-state';
              emptyRow.innerHTML = `
                <td colspan="6" class="px-6 py-14 text-center">
                  <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                  </svg>
                  <h3 class="mt-3 text-base font-semibold text-gray-900">Tidak ada hasil</h3>
                  <p class="mt-1 text-sm text-gray-600">Coba ubah kata kunci atau filter tanggal.</p>
                  <button type="button" class="mt-4 inline-flex items-center px-3 py-2 text-sm text-blue-600 hover:text-blue-700" onclick="window.dispatchEvent(new CustomEvent('clear-medical-record-filters'))">
                    Hapus Filter
                  </button>
                </td>
              `;
              tbody.appendChild(emptyRow);
            }
          } else {
            // Remove search empty state
            if (emptyState) {
              emptyState.remove();
            }
          }
        }
      }
    }

    window.addEventListener('clear-medical-record-filters', () => {
      if (typeof Alpine !== 'undefined') {
        const scope = document.querySelector('[x-data]')?.__x;
        scope?.$data?.clearFilters?.();
      }
    });
  </script>
</body>
</html>
```
