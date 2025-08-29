{{-- resources/views/filament/pages/analysis.blade.php --}}

<x-filament-panels::page>
    {{ $this->form }}

    @if($isLoading)
        <div class="my-4 p-6 bg-gray-50 rounded-lg shadow-sm flex items-center justify-center">
            <div class="animate-spin mr-3 h-5 w-5 text-primary-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <span class="text-gray-700">Memproses data...</span>
        </div>
    @endif

    @if($results)
        <div class="mt-8">
            <div class="mb-4 flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-900">Hasil Analisis</h2>
                <div class="flex space-x-3">
                    @if($results['type'] === 'table')
                        <span class="text-gray-600">Total: {{ $results['totalResults'] ?? 0 }} data</span>
                    @endif
                </div>
            </div>

            @if($results['type'] === 'table')
                <div class="overflow-x-auto bg-white shadow-sm rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                @foreach($results['columns'] as $column)
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ $column['label'] }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($results['data'] as $row)
                                <tr>
                                    @foreach($results['columns'] as $column)
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                            @if(isset($row->{$column['field']}))
                                                @if(is_bool($row->{$column['field']}))
                                                    {{ $row->{$column['field']} ? 'Ya' : 'Tidak' }}
                                                @else
                                                    {{ $row->{$column['field']} }}
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($results['columns']) }}" class="px-4 py-3 text-sm text-gray-500 text-center">
                                        Tidak ada data yang ditemukan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @elseif(isset($results['chartData']) && $results['chartData'])
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <canvas id="resultsChart" style="width: 100%; height: 400px;"></canvas>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const ctx = document.getElementById('resultsChart').getContext('2d');
                        
                        const chartData = @json($results['chartData']);
                        
                        new Chart(ctx, {
                            type: chartData.type,
                            data: {
                                labels: chartData.labels,
                                datasets: chartData.datasets.map(dataset => ({
                                    ...dataset,
                                    backgroundColor: [
                                        'rgba(54, 162, 235, 0.6)',
                                        'rgba(255, 99, 132, 0.6)',
                                        'rgba(255, 206, 86, 0.6)',
                                        'rgba(75, 192, 192, 0.6)',
                                        'rgba(153, 102, 255, 0.6)',
                                    ]
                                }))
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'top',
                                    },
                                    title: {
                                        display: true,
                                        text: 'Hasil Analisis'
                                    }
                                }
                            }
                        });
                    });
                </script>
            @endif
        </div>
    @endif
</x-filament-panels::page>