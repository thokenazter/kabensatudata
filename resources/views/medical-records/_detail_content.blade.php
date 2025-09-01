{{-- resources/views/medical-records/_detail_content.blade.php --}}
<div class="p-6">
  <div class="mb-4">
    <h3 class="text-base font-semibold text-gray-900">
      {{ $familyMember->name }} • {{ $medicalRecord->visit_date->format('d-m-Y') }}
    </h3>
    <p class="text-sm text-gray-500">
      {{ $familyMember->gender }} • {{ $familyMember->formatted_birth_date ?? '-' }} •
      Desa {{ optional($familyMember->family?->building?->village)->name ?? '-' }}
    </p>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="bg-gray-50 p-4 rounded-lg">
      <div class="text-xs text-gray-500">Tekanan Darah</div>
      @if($medicalRecord->systolic && $medicalRecord->diastolic)
        <div class="mt-1 font-semibold">{{ $medicalRecord->systolic }}/{{ $medicalRecord->diastolic }} mmHg</div>
        <div class="text-xs {{ $medicalRecord->blood_pressure_category === 'Normal' ? 'text-green-600' : 'text-red-600' }}">
          {{ $medicalRecord->blood_pressure_category }}
        </div>
      @else
        <div class="mt-1 text-gray-400">Tidak ada data</div>
      @endif
    </div>

    <div class="bg-gray-50 p-4 rounded-lg">
      <div class="text-xs text-gray-500">BB/TB</div>
      @if($medicalRecord->weight && $medicalRecord->height)
        <div class="mt-1 font-semibold">{{ $medicalRecord->weight }} kg / {{ $medicalRecord->height }} cm</div>
        <div class="text-xs">BMI: {{ $medicalRecord->bmi }} ({{ $medicalRecord->bmi_category }})</div>
      @else
        <div class="mt-1 text-gray-400">Tidak ada data</div>
      @endif
    </div>

    <div class="bg-gray-50 p-4 rounded-lg">
      <div class="text-xs text-gray-500">Suhu / HR / RR</div>
      <div class="mt-1 text-sm">
        @if($medicalRecord->body_temperature) Suhu: <span class="font-medium">{{ $medicalRecord->body_temperature }}°C</span><br>@endif
        @if($medicalRecord->heart_rate) Nadi: <span class="font-medium">{{ $medicalRecord->heart_rate }} bpm</span><br>@endif
        @if($medicalRecord->respiratory_rate) RR: <span class="font-medium">{{ $medicalRecord->respiratory_rate }} rpm</span>@endif
        @if(!$medicalRecord->body_temperature && !$medicalRecord->heart_rate && !$medicalRecord->respiratory_rate)
          <span class="text-gray-400">Tidak ada data</span>
        @endif
      </div>
    </div>
  </div>

  <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="bg-white border p-4 rounded-lg">
      <div class="text-xs text-gray-500">Keluhan Utama</div>
      <div class="mt-1 text-sm">{{ $medicalRecord->chief_complaint ?? '-' }}</div>
    </div>
    <div class="bg-white border p-4 rounded-lg">
      <div class="text-xs text-gray-500">Anamnesa</div>
      <div class="mt-1 text-sm">{{ $medicalRecord->anamnesis ?? '-' }}</div>
    </div>
  </div>

  <div class="mt-4 bg-white border p-4 rounded-lg">
    <div class="text-xs text-gray-500">Diagnosis</div>
    @if($medicalRecord->diagnosis_name)
      <div class="mt-1 text-sm">
        {{ $medicalRecord->diagnosis_name }}
        @if($medicalRecord->diagnosis_code)
          <span class="text-gray-400">({{ $medicalRecord->diagnosis_code }})</span>
        @endif
      </div>
    @else
      <div class="mt-1 text-gray-400 text-sm">Tidak ada diagnosis</div>
    @endif
  </div>

  <div class="mt-4 bg-white border p-4 rounded-lg">
    <div class="text-xs text-gray-500 mb-3">Terapi & Pengobatan</div>
    
    <!-- Obat/Medication -->
    <div class="mb-3">
      <div class="flex items-center gap-2 mb-1">
        <svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
        </svg>
        <span class="text-sm font-medium text-gray-700">Obat-obatan:</span>
      </div>
      @if($medicalRecord->medication)
        <div class="ml-6 bg-green-50 p-3 rounded border-l-4 border-green-400">
          @php
            // Split medication by common separators and clean up
            $medications = preg_split('/[,;\n\r]+/', $medicalRecord->medication);
            $medications = array_filter(array_map('trim', $medications));
          @endphp
          
          @if(count($medications) > 1)
            <ul class="space-y-2">
              @foreach($medications as $medication)
                <li class="flex items-start gap-2 text-sm text-gray-900">
                  <span class="inline-block w-1.5 h-1.5 bg-green-500 rounded-full mt-2 flex-shrink-0"></span>
                  <span>{{ $medication }}</span>
                </li>
              @endforeach
            </ul>
          @else
            <div class="flex items-start gap-2 text-sm text-gray-900">
              <span class="inline-block w-1.5 h-1.5 bg-green-500 rounded-full mt-2 flex-shrink-0"></span>
              <span>{{ $medicalRecord->medication }}</span>
            </div>
          @endif
        </div>
      @else
        <div class="ml-6 text-sm text-gray-400 italic">Tidak ada obat yang diberikan</div>
      @endif
    </div>

    <!-- Terapi/Therapy -->
    @if($medicalRecord->therapy)
    <div class="mb-3">
      <div class="flex items-center gap-2 mb-1">
        <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
        </svg>
        <span class="text-sm font-medium text-gray-700">Terapi:</span>
      </div>
      <div class="ml-6 text-sm text-gray-900 bg-blue-50 p-2 rounded border-l-4 border-blue-400">
        {{ $medicalRecord->therapy }}
      </div>
    </div>
    @endif

    <!-- Tindakan/Procedure -->
    <div>
      <div class="flex items-center gap-2 mb-1">
        <svg class="h-4 w-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <span class="text-sm font-medium text-gray-700">Tindakan Medis:</span>
      </div>
      @if($medicalRecord->procedure)
        <div class="ml-6 text-sm text-gray-900 bg-purple-50 p-2 rounded border-l-4 border-purple-400">
          {{ $medicalRecord->procedure }}
        </div>
      @else
        <div class="ml-6 text-sm text-gray-400 italic">Tidak ada tindakan khusus</div>
      @endif
    </div>
  </div>

  <div class="mt-6 flex flex-wrap gap-2">
    <a href="{{ route('medical-records.edit', [$familyMember, $medicalRecord]) }}"
       class="inline-flex items-center px-3 py-2 rounded-md text-sm text-white bg-green-600 hover:bg-green-700">
      Edit
    </a>
    <a href="{{ route('medical-records.print-prescription', [$familyMember, $medicalRecord]) }}"
       target="_blank" rel="noopener"
       class="inline-flex items-center px-3 py-2 rounded-md text-sm text-white bg-purple-600 hover:bg-purple-700">
      Print Resep
    </a>
  </div>

  <div class="mt-4 text-xs text-gray-500">
    Dicatat oleh: {{ $medicalRecord->creator->name ?? 'System' }} •
    Input: {{ $medicalRecord->created_at->format('d-m-Y H:i') }}
  </div>
</div>
