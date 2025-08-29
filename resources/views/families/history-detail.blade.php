<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Detail IKS Keluarga - PKM Kaben Satu Data</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    @include('includes.style')
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    @include('includes.navbar')

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-8">
            <div class="flex justify-between items-center mb-2">
                <a href="{{ route('families.history', $family) }}" class="inline-flex items-center px-4 py-2 mx-4 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </a>
            </div>
    
            <!-- Informasi IKS -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
                <div class="px-6 py-5 bg-gray-50 border-b border-gray-200">
                    <h2 class="text-2xl pb-4 font-semibold text-gray-800">
                        Detail IKS: Keluarga {{ $family->head_name }}
                    </h2>
                    <h3 class="text-lg font-semibold text-gray-900">Perhitungan IKS pada {{ $history->calculated_at->format('d-m-Y H:i') }}</h3>
                </div>
                <div class="p-6 bg-white">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm text-gray-500">Dilakukan oleh: {{ $history->user ? $history->user->name : 'System' }}</p>
                        </div>
                        <div class="text-right">
                            <div class="text-3xl font-bold {{ $history->status_color == 'success' ? 'text-green-600' : ($history->status_color == 'warning' ? 'text-yellow-600' : 'text-red-600') }}">
                                {{ number_format($history->iks_value * 100, 2) }}%
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $history->status_color == 'success' ? 'bg-green-100 text-green-800' : ($history->status_color == 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ $history->health_status }}
                            </span>
                        </div>
                    </div>
                    
                    @if($changes)
                        <div class="mt-6 border-t pt-6">
                            <h4 class="text-md font-medium mb-3">Perubahan dari Perhitungan Sebelumnya</h4>
                            <div class="flex flex-wrap items-center space-x-4">
                                <div class="bg-gray-100 px-4 py-3 rounded-md">
                                    <span class="text-sm text-gray-600">Sebelumnya</span>
                                    <div class="text-lg font-semibold">{{ number_format($changes['previous_iks'] * 100, 2) }}%</div>
                                    <span class="text-xs text-gray-500">{{ $changes['previous_date'] }}</span>
                                </div>
                                <div class="text-2xl">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 {{ $changes['net_change'] >= 0 ? 'text-green-500' : 'text-red-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $changes['net_change'] >= 0 ? 'M13 7l5 5m0 0l-5 5m5-5H6' : 'M11 17l-5-5m0 0l5-5m-5 5h12' }}" />
                                    </svg>
                                </div>
                                <div class="bg-blue-50 px-4 py-3 rounded-md">
                                    <span class="text-sm text-blue-600">Saat ini</span>
                                    <div class="text-lg font-semibold">{{ number_format($history->iks_value * 100, 2) }}%</div>
                                    <span class="text-xs text-gray-500">{{ $history->calculated_at->format('d-m-Y') }}</span>
                                </div>
                                <div class="bg-{{ $changes['net_change'] >= 0 ? 'green' : 'red' }}-50 px-4 py-3 rounded-md ml-auto">
                                    <span class="text-{{ $changes['net_change'] >= 0 ? 'green' : 'red' }}-600 font-medium">
                                        {{ $changes['net_change'] >= 0 ? '+' : '' }}{{ number_format($changes['net_change'] * 100, 2) }}%
                                    </span>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                                <div>
                                    @if(count($changes['improvements']) > 0)
                                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                            <h5 class="text-sm font-medium text-green-700 mb-3 flex items-center">
                                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                                                </svg>
                                                Indikator yang Membaik:
                                            </h5>
                                            <ul class="space-y-2 pl-5 list-disc text-sm text-green-600">
                                                @foreach($changes['improvements'] as $improvement)
                                                    <li>{{ $improvement['name'] }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @else
                                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                            <h5 class="text-sm font-medium text-gray-700 mb-2">Indikator yang Membaik:</h5>
                                            <p class="text-sm text-gray-600">Tidak ada indikator yang membaik</p>
                                        </div>
                                    @endif
                                </div>
                                
                                <div>
                                    @if(count($changes['declines']) > 0)
                                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                            <h5 class="text-sm font-medium text-red-700 mb-3 flex items-center">
                                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                                </svg>
                                                Indikator yang Memburuk:
                                            </h5>
                                            <ul class="space-y-2 pl-5 list-disc text-sm text-red-600">
                                                @foreach($changes['declines'] as $decline)
                                                    <li>{{ $decline['name'] }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @else
                                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                            <h5 class="text-sm font-medium text-gray-700 mb-2">Indikator yang Memburuk:</h5>
                                            <p class="text-sm text-gray-600">Tidak ada indikator yang memburuk</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    @if($history->notes)
                        <div class="mt-6 bg-gray-50 p-4 rounded-md border border-gray-200">
                            <h4 class="text-sm font-medium mb-2">Catatan:</h4>
                            <p class="text-sm text-gray-700">{{ $history->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Detail Indikator -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
                <div class="px-6 py-5 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Detail Indikator</h3>
                </div>
                <div class="p-6 bg-white">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($indicatorDetails as $code => $indicator)
                            @if($indicator['relevant'])
                                <div class="border rounded-lg p-4 {{ $indicator['status'] ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
                                    <div class="flex items-center">
                                        <div class="rounded-full p-2 {{ $indicator['status'] ? 'bg-green-100' : 'bg-red-100' }}">
                                            <svg class="h-6 w-6 {{ $indicator['status'] ? 'text-green-600' : 'text-red-600' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                @if($indicator['status'])
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                @else
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                @endif
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h4 class="text-md font-medium {{ $indicator['status'] ? 'text-green-800' : 'text-red-800' }}">
                                                {{ $indicator['name'] }}
                                            </h4>
                                            <div class="mt-1 text-sm {{ $indicator['status'] ? 'text-green-700' : 'text-red-700' }}">
                                                {{ $indicator['status'] ? 'Terpenuhi' : 'Tidak Terpenuhi' }}
                                            </div>
                                        </div>
                                    </div>
                                    @if($indicator['detail'])
                                        <div class="mt-3 text-sm {{ $indicator['status'] ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $indicator['detail'] }}
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                    
                    <div class="mt-8">
                        <h4 class="text-md font-medium mb-4">Ringkasan Indikator</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="border rounded-lg p-4 bg-blue-50 border-blue-200">
                                <div class="text-sm text-blue-600 font-medium">Total Indikator Relevan</div>
                                <div class="text-2xl font-bold text-blue-700 mt-1">{{ $history->relevant_indicators }}</div>
                            </div>
                            <div class="border rounded-lg p-4 bg-green-50 border-green-200">
                                <div class="text-sm text-green-600 font-medium">Indikator Terpenuhi</div>
                                <div class="text-2xl font-bold text-green-700 mt-1">{{ $history->fulfilled_indicators }}</div>
                            </div>
                            <div class="border rounded-lg p-4 bg-yellow-50 border-yellow-200">
                                <div class="text-sm text-yellow-600 font-medium">Persentase Terpenuhi</div>
                                <div class="text-2xl font-bold text-yellow-700 mt-1">
                                    {{ $history->relevant_indicators > 0 ? number_format(($history->fulfilled_indicators / $history->relevant_indicators) * 100, 2) : 0 }}%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>