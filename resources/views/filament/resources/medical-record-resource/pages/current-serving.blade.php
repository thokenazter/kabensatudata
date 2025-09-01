<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white p-6 rounded-lg shadow-lg">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold">
                        @php
                            $roleNames = [
                                'nurse' => 'Perawat',
                                'doctor' => 'Dokter', 
                                'pharmacy' => 'Apotek'
                            ];
                            $roleName = $roleNames[$role] ?? ucfirst($role);
                        @endphp
                        Sedang Dilayani - {{ $roleName }}
                    </h1>
                    <p class="text-blue-100 mt-2">{{ $currentTime->format('d F Y, H:i:s') }}</p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-blue-100">Status</div>
                    <div class="text-2xl font-bold">{{ ucfirst($role) }}</div>
                </div>
            </div>
        </div>

        <!-- Current Serving Patient -->
        <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-green-500">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                Sedang Dilayani
            </h2>
            
            @if($currentServing)
                <div class="bg-green-50 p-4 rounded-lg">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <div class="text-sm text-gray-600">Nomor Antrian</div>
                            <div class="text-3xl font-bold text-green-600">{{ $currentServing->queue_number }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600">Nama Pasien</div>
                            <div class="text-lg font-semibold text-gray-800">{{ $currentServing->familyMember->name ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600">Petugas</div>
                            <div class="text-lg font-semibold text-gray-800">{{ $currentServing->currentHandler->name ?? 'N/A' }}</div>
                        </div>
                    </div>
                    
                    @if($currentServing->priority_level && in_array($currentServing->priority_level, ['urgent', 'emergency']))
                        <div class="mt-3 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            {{ ucfirst($currentServing->priority_level) }}
                        </div>
                    @endif
                </div>
            @else
                <div class="bg-gray-50 p-8 rounded-lg text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2M4 13h2m13-8V4a1 1 0 00-1-1H7a1 1 0 00-1 1v1m8 0V4.5"></path>
                    </svg>
                    <p class="text-gray-500 text-lg">Tidak ada pasien yang sedang dilayani</p>
                </div>
            @endif
        </div>

        <!-- Next in Queue -->
        <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-blue-500">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Antrian Selanjutnya
            </h2>
            
            @if($nextQueue->count() > 0)
                <div class="space-y-3">
                    @foreach($nextQueue as $index => $record)
                        <div class="bg-blue-50 p-4 rounded-lg flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="bg-blue-500 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold">
                                    {{ $index + 1 }}
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-800">{{ $record->queue_number }}</div>
                                    <div class="text-sm text-gray-600">{{ $record->familyMember->name ?? 'N/A' }}</div>
                                </div>
                            </div>
                            
                            @if($record->priority_level && in_array($record->priority_level, ['urgent', 'emergency']))
                                <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    {{ ucfirst($record->priority_level) }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-gray-50 p-8 rounded-lg text-center">
                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <p class="text-gray-500">Tidak ada antrian</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Auto refresh script -->
    <script>
        // Auto refresh every 30 seconds
        setTimeout(function() {
            window.location.reload();
        }, 30000);
    </script>
</x-filament-panels::page>