{{-- file: resources/views/filament/resources/medical-record-resource/pages/queue-display.blade.php --}}

<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Antrian - {{ now()->format('d F Y') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta http-equiv="refresh" content="10"> {{-- Auto refresh every 10 seconds --}}
    <style>
        .blink {
            animation: blink 1s linear infinite;
        }
        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0; }
        }
        .slide-up {
            animation: slideUp 0.5s ease-out;
        }
        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .glow {
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.5);
        }
    </style>
</head>
<body class="h-full bg-gradient-to-br from-blue-50 to-indigo-100 font-sans">
    <div class="min-h-full p-6">
        {{-- Header --}}
        <div class="text-center mb-6 slide-up">
            <h1 class="text-5xl font-bold text-gray-800 mb-2">🏥 ANTRIAN KLINIK</h1>
            <div class="text-2xl text-gray-600">
                {{ now()->format('l, d F Y') }} • {{ now()->format('H:i:s') }}
            </div>
        </div>

        {{-- Priority Alerts Banner --}}
        @if($priorityAlerts->count() > 0)
            <div class="bg-red-500 text-white p-4 rounded-lg mb-6 blink">
                <div class="text-center">
                    <div class="text-xl font-bold">🚨 PERHATIAN - PASIEN PRIORITAS 🚨</div>
                    <div class="flex justify-center space-x-6 mt-2">
                        @foreach($priorityAlerts->take(3) as $alert)
                            <span class="bg-white text-red-600 px-3 py-1 rounded font-bold">
                                {{ $alert->queue_number }} - {{ $alert->patient_name }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Currently Serving Section --}}
        <div class="grid grid-cols-3 gap-8 mb-8">
            {{-- Perawat --}}
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-8 border-green-500">
                <div class="text-center">
                    <div class="text-green-600 text-3xl mb-3">👩‍⚕️</div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">PERAWAT</h2>
                    
                    @if($currentServing['nurse'])
                        <div class="bg-green-50 p-6 rounded-lg glow">
                            <div class="text-4xl font-bold text-green-800 mb-2">
                                🎯 {{ $currentServing['nurse']->queue_number }}
                            </div>
                            <div class="text-xl text-green-700 truncate">
                                {{ $currentServing['nurse']->patient_name }}
                            </div>
                            <div class="text-sm text-green-600 mt-2">
                                Petugas: {{ $currentServing['nurse']->currentHandler->name ?? '' }}
                            </div>
                        </div>
                    @else
                        <div class="bg-gray-100 p-6 rounded-lg">
                            <div class="text-4xl text-gray-400 mb-2">😴</div>
                            <div class="text-xl text-gray-500">Tidak Ada</div>
                        </div>
                    @endif

                    {{-- Queue Count & Wait Time --}}
                    <div class="mt-4 flex justify-between bg-green-100 p-3 rounded">
                        <div>
                            <div class="text-lg font-bold text-green-800">{{ $queueStats['pending_nurse'] }}</div>
                            <div class="text-xs text-green-600">Antrian</div>
                        </div>
                        <div>
                            <div class="text-lg font-bold text-green-800">~{{ $estimatedWaits['nurse'] }}</div>
                            <div class="text-xs text-green-600">Menit</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Dokter --}}
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-8 border-yellow-500">
                <div class="text-center">
                    <div class="text-yellow-600 text-3xl mb-3">👨‍⚕️</div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">DOKTER</h2>
                    
                    @if($currentServing['doctor'])
                        <div class="bg-yellow-50 p-6 rounded-lg glow">
                            <div class="text-4xl font-bold text-yellow-800 mb-2">
                                🎯 {{ $currentServing['doctor']->queue_number }}
                            </div>
                            <div class="text-xl text-yellow-700 truncate">
                                {{ $currentServing['doctor']->patient_name }}
                            </div>
                            <div class="text-sm text-yellow-600 mt-2">
                                Petugas: {{ $currentServing['doctor']->currentHandler->name ?? '' }}
                            </div>
                        </div>
                    @else
                        <div class="bg-gray-100 p-6 rounded-lg">
                            <div class="text-4xl text-gray-400 mb-2">😴</div>
                            <div class="text-xl text-gray-500">Tidak Ada</div>
                        </div>
                    @endif

                    <div class="mt-4 flex justify-between bg-yellow-100 p-3 rounded">
                        <div>
                            <div class="text-lg font-bold text-yellow-800">{{ $queueStats['pending_doctor'] }}</div>
                            <div class="text-xs text-yellow-600">Antrian</div>
                        </div>
                        <div>
                            <div class="text-lg font-bold text-yellow-800">~{{ $estimatedWaits['doctor'] }}</div>
                            <div class="text-xs text-yellow-600">Menit</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Apoteker --}}
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-8 border-purple-500">
                <div class="text-center">
                    <div class="text-purple-600 text-3xl mb-3">💊</div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">APOTEKER</h2>
                    
                    @if($currentServing['pharmacy'])
                        <div class="bg-purple-50 p-6 rounded-lg glow">
                            <div class="text-4xl font-bold text-purple-800 mb-2">
                                🎯 {{ $currentServing['pharmacy']->queue_number }}
                            </div>
                            <div class="text-xl text-purple-700 truncate">
                                {{ $currentServing['pharmacy']->patient_name }}
                            </div>
                            <div class="text-sm text-purple-600 mt-2">
                                Petugas: {{ $currentServing['pharmacy']->currentHandler->name ?? '' }}
                            </div>
                        </div>
                    @else
                        <div class="bg-gray-100 p-6 rounded-lg">
                            <div class="text-4xl text-gray-400 mb-2">😴</div>
                            <div class="text-xl text-gray-500">Tidak Ada</div>
                        </div>
                    @endif

                    <div class="mt-4 flex justify-between bg-purple-100 p-3 rounded">
                        <div>
                            <div class="text-lg font-bold text-purple-800">{{ $queueStats['pending_pharmacy'] }}</div>
                            <div class="text-xs text-purple-600">Antrian</div>
                        </div>
                        <div>
                            <div class="text-lg font-bold text-purple-800">~{{ $estimatedWaits['pharmacy'] }}</div>
                            <div class="text-xs text-purple-600">Menit</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Next in Queue Section --}}
        <div class="grid grid-cols-3 gap-8">
            {{-- Next for Nurse --}}
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-lg font-bold text-green-700 mb-3 text-center">
                    👩‍⚕️ Antrian Selanjutnya
                </h3>
                <div class="space-y-2">
                    @forelse($nextQueue['nurse']->take(5) as $index => $record)
                        <div class="flex items-center justify-between p-2 bg-green-50 rounded {{ $index === 0 ? 'ring-2 ring-green-300' : '' }}">
                            <div class="font-mono font-bold text-green-800">
                                {{ $record->queue_number }}
                            </div>
                            <div class="text-sm text-green-700 flex-1 mx-2 truncate">
                                {{ $record->patient_name }}
                            </div>
                            @if($record->priority_level !== 'normal')
                                <div class="text-xs">
                                    {{ $record->priority_level === 'emergency' ? '🔴' : '🟡' }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center text-gray-500 py-4">
                            <div class="text-2xl">✅</div>
                            <div>Tidak ada antrian</div>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Next for Doctor --}}
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-lg font-bold text-yellow-700 mb-3 text-center">
                    👨‍⚕️ Antrian Selanjutnya
                </h3>
                <div class="space-y-2">
                    @forelse($nextQueue['doctor']->take(5) as $index => $record)
                        <div class="flex items-center justify-between p-2 bg-yellow-50 rounded {{ $index === 0 ? 'ring-2 ring-yellow-300' : '' }}">
                            <div class="font-mono font-bold text-yellow-800">
                                {{ $record->queue_number }}
                            </div>
                            <div class="text-sm text-yellow-700 flex-1 mx-2 truncate">
                                {{ $record->patient_name }}
                            </div>
                            @if($record->priority_level !== 'normal')
                                <div class="text-xs">
                                    {{ $record->priority_level === 'emergency' ? '🔴' : '🟡' }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center text-gray-500 py-4">
                            <div class="text-2xl">✅</div>
                            <div>Tidak ada antrian</div>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Next for Pharmacy --}}
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-lg font-bold text-purple-700 mb-3 text-center">
                    💊 Antrian Selanjutnya
                </h3>
                <div class="space-y-2">
                    @forelse($nextQueue['pharmacy']->take(5) as $index => $record)
                        <div class="flex items-center justify-between p-2 bg-purple-50 rounded {{ $index === 0 ? 'ring-2 ring-purple-300' : '' }}">
                            <div class="font-mono font-bold text-purple-800">
                                {{ $record->queue_number }}
                            </div>
                            <div class="text-sm text-purple-700 flex-1 mx-2 truncate">
                                {{ $record->patient_name }}
                            </div>
                            @if($record->priority_level !== 'normal')
                                <div class="text-xs">
                                    {{ $record->priority_level === 'emergency' ? '🔴' : '🟡' }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center text-gray-500 py-4">
                            <div class="text-2xl">✅</div>
                            <div>Tidak ada antrian</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Footer Stats --}}
        <div class="mt-6 bg-white rounded-lg shadow p-4">
            <div class="grid grid-cols-4 gap-4 text-center">
                <div>
                    <div class="text-2xl font-bold text-blue-600">{{ $queueStats['completed'] }}</div>
                    <div class="text-sm text-gray-600">Selesai Hari Ini</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-green-600">{{ $queueStats['pending_nurse'] }}</div>
                    <div class="text-sm text-gray-600">Antri Perawat</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-yellow-600">{{ $queueStats['pending_doctor'] }}</div>
                    <div class="text-sm text-gray-600">Antri Dokter</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-purple-600">{{ $queueStats['pending_pharmacy'] }}</div>
                    <div class="text-sm text-gray-600">Antri Apoteker</div>
                </div>
            </div>
        </div>

        {{-- Auto refresh indicator --}}
        <div class="fixed bottom-4 right-4 bg-black bg-opacity-50 text-white px-3 py-1 rounded-full text-xs">
            Auto refresh setiap 10 detik
        </div>
    </div>

    {{-- Sound notification for priority patients --}}
    @if($priorityAlerts->count() > 0)
        <audio autoplay>
            <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmUbBjGG0ey/dSMFIXHA7d+USQ0PVq3n77FOGAo+ltryv2QaBD+BzvLZiToIGGS57duQQAoRVqjd7rNQFwo+ltryv2QaBDl+y+/eizEIGGG35N2VRwsOUKXh8LJbGgY2jdT0xnkqBSd+yO7dlEELDlOq5O+uWBsOUKbh8LBfGgU9k9n0tW0gBTC80PLFeC4Gf+v/+0+8kQEGgQL+3k+8kQEGgQL+3k+8kQEGgQL+3k+8kQEGgQL+3k+8kQEGgQL+3k+8kQEGgQL+3k+8kQEGgQL+3k+8kQEGgQL+3k+8kQEGgQL+3k+8kQEGgQL+3k+8kQEGgQL+3k+8kQEGgQL+3k+8kQEGgQL+3k+8kQEGgQL+3k+8kQEGgQL+3k+8kQEGgQL+3k+8kQEGgQL+3k+8kQEGgQL+3k+8kQEGgQL+3k+8kQEGgQL+3k+8kQEGgQL+3k+8kQEGgQL+3k+8kQEGgQL+3k+8kQEGgQL+3k+8kQEGgQL+3k+8kQEGgQL+3g==" type="audio/wav">
        </audio>
    @endif

    <script>
        // Auto scroll untuk long names
        setInterval(function() {
            document.querySelectorAll('.truncate').forEach(function(el) {
                if (el.scrollWidth > el.clientWidth) {
                    el.style.animation = 'none';
                    setTimeout(() => {
                        el.style.animation = 'scroll 3s linear infinite';
                    }, 10);
                }
            });
        }, 5000);
    </script>
</body>
</html>