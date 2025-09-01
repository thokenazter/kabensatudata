{{-- file: resources/views/filament/resources/medical-record-resource/pages/queue-dashboard.blade.php --}}

<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Queue Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <x-filament::section class="bg-blue-50">
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600">{{ $queueStats['pending_registration'] }}</div>
                    <div class="text-sm text-blue-500">👩‍💼 Pendaftaran</div>
                </div>
            </x-filament::section>

            <x-filament::section class="bg-green-50">
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600">{{ $queueStats['pending_nurse'] }}</div>
                    <div class="text-sm text-green-500">👩‍⚕️ Perawat</div>
                </div>
            </x-filament::section>

            <x-filament::section class="bg-yellow-50">
                <div class="text-center">
                    <div class="text-3xl font-bold text-yellow-600">{{ $queueStats['pending_doctor'] }}</div>
                    <div class="text-sm text-yellow-500">👨‍⚕️ Dokter</div>
                </div>
            </x-filament::section>

            <x-filament::section class="bg-purple-50">
                <div class="text-center">
                    <div class="text-3xl font-bold text-purple-600">{{ $queueStats['pending_pharmacy'] }}</div>
                    <div class="text-sm text-purple-500">💊 Apoteker</div>
                </div>
            </x-filament::section>

            <x-filament::section class="bg-emerald-50">
                <div class="text-center">
                    <div class="text-3xl font-bold text-emerald-600">{{ $queueStats['completed'] }}</div>
                    <div class="text-sm text-emerald-500">✅ Selesai</div>
                </div>
            </x-filament::section>

            <x-filament::section class="bg-gray-50">
                <div class="text-center">
                    <div class="text-3xl font-bold text-gray-600">{{ $queueStats['total_today'] }}</div>
                    <div class="text-sm text-gray-500">📊 Total Hari Ini</div>
                </div>
            </x-filament::section>
        </div>

        {{-- Current Serving Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Perawat --}}
            <x-filament::section>
                <x-slot name="heading">
                    👩‍⚕️ Sedang Dilayani Perawat
                </x-slot>
                
                @if($currentServing['nurse'])
                    <div class="space-y-3">
                        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                            <div class="text-xl font-bold text-green-800">
                                🎯 {{ $currentServing['nurse']->queue_number }}
                            </div>
                            <div class="text-green-700">{{ $currentServing['nurse']->patient_name }}</div>
                            <div class="text-sm text-green-600">
                                👤 {{ $currentServing['nurse']->currentHandler->name ?? 'Unknown' }}
                            </div>
                            <div class="text-xs text-green-500">
                                ⏱️ {{ $currentServing['nurse']->nurse_start_time ? $currentServing['nurse']->nurse_start_time->diffForHumans() : '' }}
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center text-gray-500 py-8">
                        <div class="text-4xl mb-2">😴</div>
                        <div>Tidak ada yang sedang dilayani</div>
                    </div>
                @endif

                {{-- Next 3 in queue --}}
                <div class="mt-4">
                    <h4 class="font-semibold text-gray-700 mb-2">Antrian Selanjutnya:</h4>
                    @forelse($nextQueue['nurse']->take(3) as $record)
                        <div class="flex items-center justify-between py-2 px-3 bg-gray-50 rounded mb-1">
                            <span class="font-mono font-bold">{{ $record->queue_number }}</span>
                            <span class="text-sm">{{ $record->patient_name }}</span>
                            @if($record->priority_level !== 'normal')
                                <span class="text-xs px-2 py-1 rounded {{ $record->priority_level === 'emergency' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $record->priority_level === 'emergency' ? '🔴' : '🟡' }}
                                </span>
                            @endif
                        </div>
                    @empty
                        <div class="text-sm text-gray-500">Tidak ada antrian</div>
                    @endforelse
                </div>
            </x-filament::section>

            {{-- Dokter --}}
            <x-filament::section>
                <x-slot name="heading">
                    👨‍⚕️ Sedang Dilayani Dokter
                </x-slot>
                
                @if($currentServing['doctor'])
                    <div class="space-y-3">
                        <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                            <div class="text-xl font-bold text-yellow-800">
                                🎯 {{ $currentServing['doctor']->queue_number }}
                            </div>
                            <div class="text-yellow-700">{{ $currentServing['doctor']->patient_name }}</div>
                            <div class="text-sm text-yellow-600">
                                👤 {{ $currentServing['doctor']->currentHandler->name ?? 'Unknown' }}
                            </div>
                            <div class="text-xs text-yellow-500">
                                ⏱️ {{ $currentServing['doctor']->doctor_start_time ? $currentServing['doctor']->doctor_start_time->diffForHumans() : '' }}
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center text-gray-500 py-8">
                        <div class="text-4xl mb-2">😴</div>
                        <div>Tidak ada yang sedang dilayani</div>
                    </div>
                @endif

                <div class="mt-4">
                    <h4 class="font-semibold text-gray-700 mb-2">Antrian Selanjutnya:</h4>
                    @forelse($nextQueue['doctor']->take(3) as $record)
                        <div class="flex items-center justify-between py-2 px-3 bg-gray-50 rounded mb-1">
                            <span class="font-mono font-bold">{{ $record->queue_number }}</span>
                            <span class="text-sm">{{ $record->patient_name }}</span>
                            @if($record->priority_level !== 'normal')
                                <span class="text-xs px-2 py-1 rounded {{ $record->priority_level === 'emergency' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $record->priority_level === 'emergency' ? '🔴' : '🟡' }}
                                </span>
                            @endif
                        </div>
                    @empty
                        <div class="text-sm text-gray-500">Tidak ada antrian</div>
                    @endforelse
                </div>
            </x-filament::section>

            {{-- Apoteker --}}
            <x-filament::section>
                <x-slot name="heading">
                    💊 Sedang Dilayani Apoteker
                </x-slot>
                
                @if($currentServing['pharmacy'])
                    <div class="space-y-3">
                        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                            <div class="text-xl font-bold text-purple-800">
                                🎯 {{ $currentServing['pharmacy']->queue_number }}
                            </div>
                            <div class="text-purple-700">{{ $currentServing['pharmacy']->patient_name }}</div>
                            <div class="text-sm text-purple-600">
                                👤 {{ $currentServing['pharmacy']->currentHandler->name ?? 'Unknown' }}
                            </div>
                            <div class="text-xs text-purple-500">
                                ⏱️ {{ $currentServing['pharmacy']->pharmacy_start_time ? $currentServing['pharmacy']->pharmacy_start_time->diffForHumans() : '' }}
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center text-gray-500 py-8">
                        <div class="text-4xl mb-2">😴</div>
                        <div>Tidak ada yang sedang dilayani</div>
                    </div>
                @endif

                <div class="mt-4">
                    <h4 class="font-semibold text-gray-700 mb-2">Antrian Selanjutnya:</h4>
                    @forelse($nextQueue['pharmacy']->take(3) as $record)
                        <div class="flex items-center justify-between py-2 px-3 bg-gray-50 rounded mb-1">
                            <span class="font-mono font-bold">{{ $record->queue_number }}</span>
                            <span class="text-sm">{{ $record->patient_name }}</span>
                            @if($record->priority_level !== 'normal')
                                <span class="text-xs px-2 py-1 rounded {{ $record->priority_level === 'emergency' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $record->priority_level === 'emergency' ? '🔴' : '🟡' }}
                                </span>
                            @endif
                        </div>
                    @empty
                        <div class="text-sm text-gray-500">Tidak ada antrian</div>
                    @endforelse
                </div>
            </x-filament::section>
        </div>

        {{-- Priority Patients Alert --}}
        @if($priorityPatients->count() > 0)
            <x-filament::section class="border-l-4 border-red-500 bg-red-50">
                <x-slot name="heading">
                    🚨 Pasien Prioritas
                </x-slot>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($priorityPatients as $patient)
                        <div class="bg-white p-3 rounded border {{ $patient->priority_level === 'emergency' ? 'border-red-300' : 'border-yellow-300' }}">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-bold">
                                        {{ $patient->priority_level === 'emergency' ? '🔴' : '🟡' }}
                                        {{ $patient->queue_number }}
                                    </div>
                                    <div class="text-sm">{{ $patient->patient_name }}</div>
                                    <div class="text-xs text-gray-500">
                                        Status: {{ 
                                            match($patient->workflow_status) {
                                                'pending_nurse' => 'Menunggu Perawat',
                                                'pending_doctor' => 'Menunggu Dokter', 
                                                'pending_pharmacy' => 'Menunggu Apoteker',
                                                default => 'Unknown'
                                            }
                                        }}
                                    </div>
                                </div>
                                <div class="text-xs px-2 py-1 rounded {{ $patient->priority_level === 'emergency' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ strtoupper($patient->priority_level) }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        @endif

        {{-- Staff Performance --}}
        @if($staffStats->count() > 0)
            <x-filament::section>
                <x-slot name="heading">
                    📈 Performa Petugas Hari Ini
                </x-slot>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($staffStats as $staff)
                        <div class="bg-gray-50 p-4 rounded">
                            <div class="font-semibold">{{ $staff->name }}</div>
                            <div class="text-sm text-gray-600">
                                ✅ Selesai: {{ $staff->completed_today ?? 0 }}
                            </div>
                            <div class="text-sm text-gray-600">
                                🔄 Sedang Menangani: {{ $staff->active_now ?? 0 }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        @endif
    </div>

    {{-- Auto Refresh Script --}}
    <script>
        // Auto refresh setiap 30 detik
        setTimeout(function() {
            window.location.reload();
        }, 30000);
    </script>
</x-filament-panels::page>