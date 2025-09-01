{{-- resources/views/filament/pages/analysis.blade.php --}}

<x-filament-panels::page>
    <div>
        {{ $this->form }}

        <div class="mt-4">
            <x-filament::button
                wire:click="analyze"
                type="button"
            >
                Analisis Data
            </x-filament::button>
            
            @if($results)
                <x-filament::button
                    wire:click="directExportToExcel"
                    type="button"
                    color="success"
                    class="ml-2"
                >
                    Export Excel
                </x-filament::button>
            @endif
        </div>

        @if($results)
            <div class="mt-8 bg-white rounded-xl shadow">
                <div class="p-4 border-b">
                    <h3 class="text-lg font-medium">Hasil Analisis</h3>
                    <p class="text-sm text-gray-500">
                        Total data: {{ count($results['data']) }}
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left rtl:text-right divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-xs font-medium text-gray-500 uppercase">No</th>
                                @foreach($results['columns'] as $column)
                                    <th class="px-6 py-3 bg-gray-50 text-xs font-medium text-gray-500 uppercase">
                                        {{ $column['label'] }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($results['data'] as $index => $row)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $index + 1 }}
                                    </td>
                                    @foreach($results['columns'] as $column)
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @php
                                                $field = $column['field'];
                                                $value = property_exists($row, $field) ? $row->{$field} : null;
                                                
                                                if (is_bool($value) || in_array($value, [0, 1, '0', '1'])) {
                                                    echo $value == 1 ? 'Ya' : 'Tidak';
                                                } elseif ($value === null || $value === '') {
                                                    echo '-';
                                                } elseif (is_numeric($value)) {
                                                    echo number_format($value, 0);
                                                } else {
                                                    echo $value;
                                                }
                                            @endphp
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>