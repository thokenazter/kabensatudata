<x-filament::page>
    <div 
        x-data="crosstabAnalysis()"
        x-init="initialize()"
        class="space-y-6"
    >
        <!-- Form settings -->
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Konfigurasi Analisis Crosstab</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Row Variable -->
                <div>
                    <x-filament::input.wrapper>
                        <x-filament::input.label>Variabel Baris</x-filament::input.label>
                        <select 
                            x-model="rowVariable" 
                            class="w-full border-gray-300 rounded-lg shadow-sm"
                        >
                            <option value="">Pilih variabel baris...</option>
                            <template x-for="(group, groupKey) in variables" :key="groupKey">
                                <optgroup :label="group.label">
                                    <template x-for="(variable, key) in group.variables" :key="key">
                                        <option :value="key" x-text="variable.label"></option>
                                    </template>
                                </optgroup>
                            </template>
                        </select>
                    </x-filament::input.wrapper>
                </div>
                
                <!-- Column Variable -->
                <div>
                    <x-filament::input.wrapper>
                        <x-filament::input.label>Variabel Kolom</x-filament::input.label>
                        <select 
                            x-model="columnVariable" 
                            class="w-full border-gray-300 rounded-lg shadow-sm"
                        >
                            <option value="">Pilih variabel kolom...</option>
                            <template x-for="(group, groupKey) in variables" :key="groupKey">
                                <optgroup :label="group.label">
                                    <template x-for="(variable, key) in group.variables" :key="key">
                                        <option :value="key" x-text="variable.label"></option>
                                    </template>
                                </optgroup>
                            </template>
                        </select>
                    </x-filament::input.wrapper>
                </div>
                
                <!-- Aggregation -->
                <div>
                    <x-filament::input.wrapper>
                        <x-filament::input.label>Fungsi Agregasi</x-filament::input.label>
                        <select 
                            x-model="aggregation" 
                            class="w-full border-gray-300 rounded-lg shadow-sm"
                        >
                            <option value="count">Jumlah (Count)</option>
                            <option value="sum">Total (Sum)</option>
                            <option value="avg">Rata-rata (Average)</option>
                            <option value="min">Minimum</option>
                            <option value="max">Maximum</option>
                        </select>
                    </x-filament::input.wrapper>
                    
                    <div x-show="aggregation !== 'count'" class="mt-3">
                        <x-filament::input.wrapper>
                            <x-filament::input.label>Variabel Nilai</x-filament::input.label>
                            <select 
                                x-model="valueField" 
                                class="w-full border-gray-300 rounded-lg shadow-sm"
                            >
                                <option value="">Pilih variabel nilai...</option>
                                <template x-for="(group, groupKey) in variables" :key="groupKey">
                                    <optgroup :label="group.label">
                                        <template x-for="(variable, key) in group.variables" :key="key">
                                            <option 
                                                x-show="variable.type === 'number'" 
                                                :value="key" 
                                                x-text="variable.label"
                                            ></option>
                                        </template>
                                    </optgroup>
                                </template>
                            </select>
                        </x-filament::input.wrapper>
                    </div>
                </div>
            </div>
            
            <!-- Display Options -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <!-- Display Settings -->
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-100 shadow-sm">
                    <h3 class="text-md font-medium text-gray-700 mb-3">Opsi Tampilan</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Tampilkan Persentase
                            </label>
                            <div class="flex flex-col space-y-2">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="percentageType" value="none" checked x-model="percentageType" class="text-primary-600">
                                    <span class="ml-2 text-sm text-gray-700">Tidak ada</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="percentageType" value="row" x-model="percentageType" class="text-primary-600">
                                    <span class="ml-2 text-sm text-gray-700">Baris</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="percentageType" value="column" x-model="percentageType" class="text-primary-600">
                                    <span class="ml-2 text-sm text-gray-700">Kolom</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="percentageType" value="total" x-model="percentageType" class="text-primary-600">
                                    <span class="ml-2 text-sm text-gray-700">Total</span>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Highlight Cell
                            </label>
                            <div class="flex flex-col space-y-2">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="highlightType" value="none" x-model="highlightType" class="text-primary-600">
                                    <span class="ml-2 text-sm text-gray-700">Tidak ada</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="highlightType" value="row" checked x-model="highlightType" class="text-primary-600">
                                    <span class="ml-2 text-sm text-gray-700">Per Baris</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="highlightType" value="column" x-model="highlightType" class="text-primary-600">
                                    <span class="ml-2 text-sm text-gray-700">Per Kolom</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="highlightType" value="all" x-model="highlightType" class="text-primary-600">
                                    <span class="ml-2 text-sm text-gray-700">Global</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Filters -->
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-100 shadow-sm">
                    <h3 class="text-md font-medium text-gray-700 mb-3">Filter Tambahan</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-gray-700">
                                Tambah Filter
                            </label>
                            <button 
                                @click="addFilter" 
                                class="text-sm bg-primary-100 hover:bg-primary-200 text-primary-700 py-1 px-2 rounded"
                            >
                                <span class="fi-btn-icon w-5 h-5 mr-1">+</span>
                                Tambah
                            </button>
                        </div>
                        <div id="filters-container">
                            <template x-for="(filter, index) in filters" :key="index">
                                <div class="flex flex-wrap items-end gap-2 pb-2 mb-2 border-b border-gray-200">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">
                                            Variabel
                                        </label>
                                        <select 
                                            x-model="filter.field" 
                                            class="text-sm rounded-md border-gray-300 shadow-sm"
                                            style="min-width: 160px;"
                                        >
                                            <option value="">Pilih variabel...</option>
                                            <template x-for="(group, groupKey) in variables" :key="groupKey">
                                                <optgroup :label="group.label">
                                                    <template x-for="(variable, key) in group.variables" :key="key">
                                                        <option :value="key" x-text="variable.label"></option>
                                                    </template>
                                                </optgroup>
                                            </template>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">
                                            Operator
                                        </label>
                                        <select 
                                            x-model="filter.operator" 
                                            class="text-sm rounded-md border-gray-300 shadow-sm"
                                        >
                                            <option value="eq">Sama dengan</option>
                                            <option value="neq">Tidak sama dengan</option>
                                            <option value="gt">Lebih besar dari</option>
                                            <option value="lt">Lebih kecil dari</option>
                                            <option value="gte">Lebih besar atau sama dengan</option>
                                            <option value="lte">Lebih kecil atau sama dengan</option>
                                            <option value="contains">Mengandung</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">
                                            Nilai
                                        </label>
                                        <input 
                                            type="text" 
                                            x-model="filter.value" 
                                            class="text-sm rounded-md border-gray-300 shadow-sm" 
                                            placeholder="Nilai filter"
                                        >
                                    </div>
                                    
                                    <div>
                                        <button 
                                            @click="removeFilter(index)" 
                                            class="text-sm bg-red-100 hover:bg-red-200 text-red-700 p-2 rounded-md"
                                        >
                                            <span class="sr-only">Hapus</span>
                                            <span class="fi-btn-icon w-5 h-5">×</span>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 flex flex-wrap gap-2">
                <x-filament::button 
                    color="primary" 
                    @click="generateCrosstab"
                >
                    <span class="mr-1">
                        <i class="fi-btn-icon w-5 h-5">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="w-5 h-5">
                                <path d="M3.75 3a.75.75 0 0 0-.75.75v.5c0 .414.336.75.75.75H4c0-1.19.52-2.253 1.342-2.97l-.592-.592a.75.75 0 0 0-1.06 1.06l.59.59h.882A3 3 0 0 0 4 6.75v.25H3.75A.75.75 0 0 0 3 7.75v.5c0 .414.336.75.75.75H4v3.25H3.75a.75.75 0 0 0-.75.75v.5c0 .414.336.75.75.75H4c0 1.19.52 2.253 1.342 2.97l-.592.592a.75.75 0 1 0 1.06 1.06l.592-.59h.882a3 3 0 0 0 2.968 3H10c.086 0 .17-.01.25-.031V15.5a.75.75 0 0 0-.75-.75H9a1.5 1.5 0 0 1-1.5-1.5V13H15v.25a1.5 1.5 0 0 1-1.5 1.5h-.25a.75.75 0 0 0-.75.75v5.25c.08.021.164.031.25.031h.5a3 3 0 0 0 2.968-3h.882l.592.59a.75.75 0 1 0 1.06-1.06l-.592-.592A2.985 2.985 0 0 0 20 16.75h.25a.75.75 0 0 0 .75-.75v-.5a.75.75 0 0 0-.75-.75H20v-3.25h.25a.75.75 0 0 0 .75-.75v-.5a.75.75 0 0 0-.75-.75H20v-.25a3 3 0 0 0-2.25-2.906V5a.75.75 0a0 0-.75-.75h-.5a.75.75 0 0 0-.75.75v.187a.803.803 0 0 0-.013-.007l-2.661-1.256-2.354 2.876a.75.75 0 0 0 .231 1.042l.54.399v.494c0 .414.336.75.75.75h1.786a1.5 1.5 0 0 1 1.476 1.243l.001.007H13v-.75a.75.75 0 0 0-.75-.75h-.5a.75.75 0 0 0-.75.75v.25H4v-.25a3 3 0 0 0-3-3h.25a.75.75 0 0 0 .75-.75v-.5a.75.75 0 0 0-.75-.75H1a.75.75 0 0 0-.75.75v.5c0 .414.336.75.75.75H1.25c.001 0 .002 0 .003 0h.375a.375.375 0 0 1 .375.375c0 .207-.169.375-.375.375H1.25A.75.75 0 0 0 .5 6v.5c0 .414.336.75.75.75H2a1.5 1.5 0 0 1 1.5 1.5v1.5H1.25a.75.75 0 0 0-.75.75v.5c0 .414.336.75.75.75H3.5V13a1.5 1.5 0 0 1-1.5 1.5H1.25a.75.75 0 0 0-.75.75v.5c0 .414.336.75.75.75H2c-.001 0-.002 0-.003 0h-.372a.375.375 0 0 0-.375.375c0 .207.168.375.375.375h.622c.446 0 .809-.364.809-.813 0-.271-.132-.511-.333-.664"></path>
                            </svg>
                        </i>
                    </span>
                    Buat Tabulasi Silang
                </x-filament::button>
                
                <x-filament::button 
                    color="gray" 
                    @click="resetForm"
                >
                    <span class="mr-1">
                        <i class="fi-btn-icon w-5 h-5">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="w-5 h-5">
                                <path fill-rule="evenodd" d="M10 4C6.68629 4 4 6.68629 4 10C4 13.3137 6.68629 16 10 16C13.3137 16 16 13.3137 16 10C16 6.68629 13.3137 4 10 4ZM2 10C2 5.58172 5.58172 2 10 2C14.4183 2 18 5.58172 18 10C18 14.4183 14.4183 18 10 18C5.58172 18 2 14.4183 2 10Z" clip-rule="evenodd"></path>
                                <path fill-rule="evenodd" d="M13.7071 7.70711C14.0976 7.31658 14.0976 6.68342 13.7071 6.29289C13.3166 5.90237 12.6834 5.90237 12.2929 6.29289L10 8.58579L7.70711 6.29289C7.31658 5.90237 6.68342 5.90237 6.29289 6.29289C5.90237 6.68342 5.90237 7.31658 6.29289 7.70711L8.58579 10L6.29289 12.2929C5.90237 12.6834 5.90237 13.3166 6.29289 13.7071C6.68342 14.0976 7.31658 14.0976 7.70711 13.7071L10 11.4142L12.2929 13.7071C12.6834 14.0976 13.3166 14.0976 13.7071 13.7071C14.0976 13.3166 14.0976 12.6834 13.7071 12.2929L11.4142 10L13.7071 7.70711Z" clip-rule="evenodd"></path>
                            </svg>
                        </i>
                    </span>
                    Reset
                </x-filament::button>
            </div>
        </div>
        
        <!-- Loading indicator -->
        <div x-show="loading" class="bg-white rounded-xl shadow p-6 text-center">
            <div class="inline-flex items-center px-4 py-2">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Menganalisis data...</span>
            </div>
        </div>
        
        <!-- Welcome message -->
        <div x-show="!loading && !crosstabData && !errorMessage" class="bg-white rounded-xl shadow p-6 text-center">
            <div class="mb-4">
                <div class="inline-flex rounded-full bg-primary-100 p-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-primary-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                    </svg>
                </div>
            </div>
            <h2 class="text-xl font-semibold text-gray-800 mb-2">Analisis Tabulasi Silang</h2>
            <p class="text-gray-600 max-w-lg mx-auto mb-4">
                Silakan pilih variabel baris dan kolom pada panel konfigurasi di atas, lalu klik tombol "Buat Tabulasi Silang" untuk mulai menganalisis data.
            </p>
            <div class="space-y-4 max-w-md mx-auto">
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-100 text-left">
                    <h3 class="font-medium text-blue-800 mb-1">Contoh Penggunaan</h3>
                    <p class="text-sm text-blue-700">
                        Anda dapat melihat hubungan antara desa dan kepemilikan JKN, atau status imunisasi dengan kelompok usia, dan sebagainya.
                    </p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg border border-green-100 text-left">
                    <h3 class="font-medium text-green-800 mb-1">Tip</h3>
                    <p class="text-sm text-green-700">
                        Gunakan filter tambahan untuk mempersempit analisis pada kelompok tertentu, misalnya hanya untuk desa tertentu atau kelompok umur tertentu.
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Error message -->
        <div x-show="!loading && errorMessage" class="bg-white rounded-xl shadow p-6">
            <div class="bg-red-50 text-red-800 p-4 rounded-lg border border-red-200">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Terjadi kesalahan</h3>
                        <div class="mt-2 text-sm text-red-700" x-text="errorMessage"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Results Section -->
        <div x-show="!loading && crosstabData" class="bg-white rounded-xl shadow p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4" x-text="'Hasil Tabulasi Silang: ' + getVariableLabel(rowVariable) + ' dan ' + getVariableLabel(columnVariable)"></h2>
            
            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r">
                                <span x-text="getVariableLabel(rowVariable) + ' / ' + getVariableLabel(columnVariable)"></span>
                            </th>
                            <template x-for="column in crosstabData.columns" :key="column">
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-r">
                                    <span x-text="formatLabel(crosstabData.column_labels[column] || column)"></span>
                                </th>
                            </template>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="row in crosstabData.rows" :key="row">
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 border-r">
                                    <span x-text="formatLabel(crosstabData.row_labels[row] || row)"></span>
                                </td>
                                <template x-for="column in crosstabData.columns" :key="column">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-center border-r" :class="getCellClass(row, column)">
                                        <div x-text="formatValue(crosstabData.data[row][column])"></div>
                                        <div x-show="percentageType !== 'none'" class="text-xs text-gray-500" x-text="getPercentage(row, column)"></div>
                                    </td>
                                </template>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 bg-gray-50 text-center">
                                    <span x-text="formatValue(crosstabData.row_totals[row])"></span>
                                </td>
                            </tr>
                        </template>
                        
                        <!-- Total row -->
                        <tr class="bg-gray-100">
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 border-r">
                                Total
                            </td>
                            <template x-for="column in crosstabData.columns" :key="column">
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 text-center border-r">
                                    <span x-text="formatValue(crosstabData.column_totals[column])"></span>
                                </td>
                            </template>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 text-center bg-gray-200">
                                <span x-text="formatValue(crosstabData.grand_total)"></span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Action buttons -->
            <div class="flex flex-wrap gap-2 justify-end mt-4">
                <x-filament::button color="gray" @click="printCrosstab">
                    <svg class="h-5 w-5 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd" />
                    </svg>
                    Print
                </x-filament::button>
                <x-filament::button color="success" @click="exportToExcel">
                   Export Excel
               </x-filament::button>
           </div>
           
           <!-- Visualization Section -->
           <div class="mt-6 border-t pt-4">
               <h3 class="text-lg font-semibold text-gray-800 mb-3">Visualisasi</h3>
               <div class="flex gap-2 mb-3">
                   <x-filament::button 
                       size="sm" 
                       :color="$getColor('primary')" 
                       class="viz-btn active" 
                       @click="changeVisualization('bar')"
                   >
                       Bar Chart
                   </x-filament::button>
                   <x-filament::button 
                       size="sm" 
                       :color="$getColor('gray')" 
                       class="viz-btn" 
                       @click="changeVisualization('heatmap')"
                   >
                       Heatmap
                   </x-filament::button>
                   <x-filament::button 
                       size="sm" 
                       :color="$getColor('gray')" 
                       class="viz-btn" 
                       @click="changeVisualization('stacked')"
                   >
                       Stacked Bar
                   </x-filament::button>
                   <x-filament::button 
                       size="sm" 
                       :color="$getColor('gray')" 
                       class="viz-btn" 
                       @click="changeVisualization('grouped')"
                   >
                       Grouped Bar
                   </x-filament::button>
               </div>
               <div id="crosstabChart" class="h-80 border border-gray-200 rounded-lg bg-gray-50 p-4"></div>
           </div>
           
           <!-- Statistics Section -->
           <div class="mt-6 border-t pt-4">
               <h3 class="text-lg font-semibold text-gray-800 mb-3">Statistik Hasil</h3>
               <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                   <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm">
                       <h4 class="font-medium text-gray-800 mb-2">Ringkasan</h4>
                       <div class="text-sm space-y-1">
                           <p>Jumlah baris: <span class="font-medium" x-text="crosstabData.rows.length"></span></p>
                           <p>Jumlah kolom: <span class="font-medium" x-text="crosstabData.columns.length"></span></p>
                           <p>Total data: <span class="font-medium" x-text="formatValue(crosstabData.grand_total)"></span></p>
                           <p>Nilai tertinggi: <span class="font-medium" x-text="formatValue(getMaxValue())"></span></p>
                       </div>
                   </div>
                   
                   <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm">
                       <h4 class="font-medium text-gray-800 mb-2">Distribusi</h4>
                       <div class="text-sm">
                           <p>Variabel Baris: <span class="font-medium" x-text="getVariableLabel(rowVariable)"></span></p>
                           <p>Variabel Kolom: <span class="font-medium" x-text="getVariableLabel(columnVariable)"></span></p>
                           <template x-if="aggregation !== 'count'">
                               <p>Variabel Nilai: <span class="font-medium" x-text="getVariableLabel(valueField)"></span></p>
                           </template>
                           <p>Metode Agregasi: <span class="font-medium" x-text="getAggregationLabel()"></span></p>
                       </div>
                   </div>
                   
                   <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm">
                       <h4 class="font-medium text-gray-800 mb-2">Tampilan</h4>
                       <div class="text-sm space-y-2">
                           <p class="flex items-center justify-between">
                               Persentase: 
                               <span class="font-medium" x-text="getPercentageLabel()"></span>
                           </p>
                           <p class="flex items-center justify-between">
                               Highlight Cell: 
                               <span class="font-medium" x-text="getHighlightLabel()"></span>
                           </p>
                           
                           <template x-if="filters.length > 0">
                               <div>
                                   <p>Filter yang diterapkan: <span class="font-medium" x-text="filters.length"></span></p>
                               </div>
                           </template>
                       </div>
                   </div>
               </div>
           </div>
       </div>
   </div>
   
   @pushOnce('scripts')
   <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
   <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
   <script>
       document.addEventListener('alpine:init', () => {
           Alpine.data('crosstabAnalysis', () => ({
               variables: {},
               rowVariable: '',
               columnVariable: '',
               aggregation: 'count',
               valueField: '',
               filters: [],
               percentageType: 'none',
               highlightType: 'row',
               crosstabData: null,
               loading: false,
               errorMessage: null,
               chart: null,
               currentChartType: 'bar',
               
               initialize() {
                   this.fetchVariables();
               },
               
               fetchVariables() {
                   this.loading = true;
                   
                   fetch('/api/crosstab/variables')
                       .then(response => response.json())
                       .then(data => {
                           this.variables = data.variables;
                           this.loading = false;
                       })
                       .catch(error => {
                           console.error('Error fetching variables:', error);
                           this.errorMessage = 'Gagal memuat variabel untuk analisis.';
                           this.loading = false;
                       });
               },
               
               generateCrosstab() {
                   if (!this.rowVariable || !this.columnVariable) {
                       alert('Pilih variabel baris dan kolom terlebih dahulu.');
                       return;
                   }
                   
                   if (this.aggregation !== 'count' && !this.valueField) {
                       alert('Pilih variabel nilai untuk agregasi ' + this.getAggregationLabel() + '.');
                       return;
                   }
                   
                   this.loading = true;
                   this.errorMessage = null;
                   
                   const params = {
                       row_variable: this.rowVariable,
                       column_variable: this.columnVariable,
                       aggregation: this.aggregation,
                       value_field: this.valueField,
                       filters: this.filters
                   };
                   
                   fetch('/api/crosstab/data', {
                       method: 'POST',
                       headers: {
                           'Content-Type': 'application/json',
                           'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                       },
                       body: JSON.stringify(params)
                   })
                   .then(response => {
                       if (!response.ok) {
                           throw new Error('Terjadi kesalahan saat mengambil data crosstab.');
                       }
                       return response.json();
                   })
                   .then(data => {
                       this.crosstabData = data;
                       this.loading = false;
                       
                       // Render visualization after data is loaded
                       this.$nextTick(() => {
                           this.renderVisualization(this.currentChartType);
                       });
                   })
                   .catch(error => {
                       console.error('Error generating crosstab:', error);
                       this.errorMessage = error.message || 'Terjadi kesalahan saat menganalisis data.';
                       this.loading = false;
                   });
               },
               
               renderVisualization(chartType) {
                   if (!this.crosstabData || !this.crosstabData.rows || !this.crosstabData.columns) {
                       return;
                   }
                   
                   // Destroy existing chart if any
                   if (this.chart) {
                       this.chart.destroy();
                   }
                   
                   const chartContainer = document.getElementById('crosstabChart');
                   if (!chartContainer) {
                       return;
                   }
                   
                   let options;
                   
                   if (chartType === 'bar') {
                       // Standard bar chart
                       const series = this.crosstabData.columns.map(column => ({
                           name: this.formatLabel(this.crosstabData.column_labels[column] || column),
                           data: this.crosstabData.rows.map(row => this.crosstabData.data[row][column])
                       }));
                       
                       options = {
                           chart: {
                               type: 'bar',
                               height: 320,
                               toolbar: {
                                   show: true
                               }
                           },
                           plotOptions: {
                               bar: {
                                   horizontal: false,
                                   columnWidth: '55%',
                                   endingShape: 'rounded'
                               },
                           },
                           dataLabels: {
                               enabled: false
                           },
                           stroke: {
                               show: true,
                               width: 2,
                               colors: ['transparent']
                           },
                           series: series,
                           xaxis: {
                               categories: this.crosstabData.rows.map(row => this.formatLabel(this.crosstabData.row_labels[row] || row)),
                               title: {
                                   text: this.getVariableLabel(this.rowVariable)
                               }
                           },
                           yaxis: {
                               title: {
                                   text: this.getAggregationLabel()
                               }
                           },
                           fill: {
                               opacity: 1
                           },
                           tooltip: {
                               y: {
                                   formatter: (val) => this.formatValue(val)
                               }
                           },
                           theme: {
                               mode: 'light',
                               palette: 'palette1'
                           }
                       };
                   } else if (chartType === 'heatmap') {
                       // Heatmap chart
                       const series = this.crosstabData.rows.map(row => ({
                           name: this.formatLabel(this.crosstabData.row_labels[row] || row),
                           data: this.crosstabData.columns.map(column => ({
                               x: this.formatLabel(this.crosstabData.column_labels[column] || column),
                               y: this.crosstabData.data[row][column]
                           }))
                       }));
                       
                       options = {
                           chart: {
                               type: 'heatmap',
                               height: 320,
                               toolbar: {
                                   show: true
                               }
                           },
                           dataLabels: {
                               enabled: true,
                               style: {
                                   colors: ['#fff']
                               },
                               formatter: (val) => this.formatValue(val)
                           },
                           colors: ["#008FFB"],
                           series: series,
                           title: {
                               text: `${this.getVariableLabel(this.rowVariable)} vs ${this.getVariableLabel(this.columnVariable)}`,
                               align: 'center'
                           },
                           theme: {
                               mode: 'light',
                               palette: 'palette1'
                           }
                       };
                   } else if (chartType === 'stacked') {
                       // Stacked bar chart
                       const series = this.crosstabData.columns.map(column => ({
                           name: this.formatLabel(this.crosstabData.column_labels[column] || column),
                           data: this.crosstabData.rows.map(row => this.crosstabData.data[row][column])
                       }));
                       
                       options = {
                           chart: {
                               type: 'bar',
                               height: 320,
                               stacked: true,
                               toolbar: {
                                   show: true
                               }
                           },
                           plotOptions: {
                               bar: {
                                   horizontal: false,
                                   columnWidth: '55%',
                                   endingShape: 'rounded'
                               },
                           },
                           dataLabels: {
                               enabled: false
                           },
                           stroke: {
                               show: true,
                               width: 2,
                               colors: ['transparent']
                           },
                           series: series,
                           xaxis: {
                               categories: this.crosstabData.rows.map(row => this.formatLabel(this.crosstabData.row_labels[row] || row)),
                               title: {
                                   text: this.getVariableLabel(this.rowVariable)
                               }
                           },
                           yaxis: {
                               title: {
                                   text: this.getAggregationLabel()
                               }
                           },
                           fill: {
                               opacity: 1
                           },
                           tooltip: {
                               y: {
                                   formatter: (val) => this.formatValue(val)
                               }
                           },
                           theme: {
                               mode: 'light',
                               palette: 'palette1'
                           }
                       };
                   } else if (chartType === 'grouped') {
                       // Grouped horizontal bar chart
                       const series = this.crosstabData.columns.map(column => ({
                           name: this.formatLabel(this.crosstabData.column_labels[column] || column),
                           data: this.crosstabData.rows.map(row => this.crosstabData.data[row][column])
                       }));
                       
                       options = {
                           chart: {
                               type: 'bar',
                               height: 320,
                               toolbar: {
                                   show: true
                               }
                           },
                           plotOptions: {
                               bar: {
                                   horizontal: true,
                                   columnWidth: '55%',
                                   endingShape: 'rounded'
                               },
                           },
                           dataLabels: {
                               enabled: false
                           },
                           stroke: {
                               show: true,
                               width: 2,
                               colors: ['transparent']
                           },
                           series: series,
                           xaxis: {
                               title: {
                                   text: this.getAggregationLabel()
                               }
                           },
                           yaxis: {
                               categories: this.crosstabData.rows.map(row => this.formatLabel(this.crosstabData.row_labels[row] || row)),
                               title: {
                                   text: this.getVariableLabel(this.rowVariable)
                               }
                           },
                           fill: {
                               opacity: 1
                           },
                           tooltip: {
                               y: {
                                   formatter: (val) => this.formatValue(val)
                               }
                           },
                           theme: {
                               mode: 'light',
                               palette: 'palette1'
                           }
                       };
                   }
                   
                   this.chart = new ApexCharts(chartContainer, options);
                   this.chart.render();
               },
               
               changeVisualization(chartType) {
                   // Update active button
                   document.querySelectorAll('.viz-btn').forEach(btn => {
                       btn.classList.remove('bg-primary-500', 'text-white');
                       btn.classList.add('bg-gray-100', 'text-gray-700');
                   });
                   
                   const activeBtn = document.querySelector(`.viz-btn[data-chart-type="${chartType}"]`);
                   if (activeBtn) {
                       activeBtn.classList.remove('bg-gray-100', 'text-gray-700');
                       activeBtn.classList.add('bg-primary-500', 'text-white');
                   }
                   
                   this.currentChartType = chartType;
                   this.renderVisualization(chartType);
               },
               
               getVariableLabel(variable) {
                   if (!variable) return '';
                   
                   const parts = variable.split('.');
                   
                   for (const groupKey in this.variables) {
                       const group = this.variables[groupKey];
                       
                       for (const key in group.variables) {
                           if (key === variable) {
                               return group.variables[key].label;
                           }
                       }
                   }
                   
                   return variable;
               },
               
               formatLabel(value) {
                   if (value === undefined || value === null) return '';
                   if (value === true || value === 1 || value === '1') return 'Ya';
                   if (value === false || value === 0 || value === '0') return 'Tidak';
                   return value.toString();
               },
               
               formatValue(value) {
                   if (value === undefined || value === null) return '0';
                   
                   if (this.aggregation === 'avg') {
                       return parseFloat(value).toFixed(2);
                   }
                   
                   return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
               },
               
               getPercentage(row, column) {
                   if (!this.crosstabData) return '';
                   
                   const value = this.crosstabData.data[row][column];
                   let total = 0;
                   
                   switch (this.percentageType) {
                       case 'row':
                           total = this.crosstabData.row_totals[row];
                           break;
                       case 'column':
                           total = this.crosstabData.column_totals[column];
                           break;
                       case 'total':
                           total = this.crosstabData.grand_total;
                           break;
                       default:
                           return '';
                   }
                   
                   if (total === 0) return '0%';
                   
                   const percentage = (value / total) * 100;
                   return percentage.toFixed(1) + '%';
               },
               
               getCellClass(row, column) {
                   if (!this.crosstabData || this.highlightType === 'none') return '';
                   
                   const value = this.crosstabData.data[row][column];
                   let maxValue = 1;
                   let intensity = 0;
                   
                   if (this.highlightType === 'row') {
                       // Max in row
                       maxValue = Math.max(...this.crosstabData.columns.map(col => this.crosstabData.data[row][col]));
                       intensity = maxValue > 0 ? Math.min(Math.floor((value / maxValue) * 10), 9) : 0;
                   } else if (this.highlightType === 'column') {
                       // Max in column
                       maxValue = Math.max(...this.crosstabData.rows.map(r => this.crosstabData.data[r][column]));
                       intensity = maxValue > 0 ? Math.min(Math.floor((value / maxValue) * 10), 9) : 0;
                   } else if (this.highlightType === 'all') {
                       // Global max
                       maxValue = this.getMaxValue();
                       intensity = maxValue > 0 ? Math.min(Math.floor((value / maxValue) * 10), 9) : 0;
                   }
                   
                   return `bg-blue-${intensity}00`;
               },
               
               getMaxValue() {
                   if (!this.crosstabData) return 0;
                   
                   let maxValue = 0;
                   
                   this.crosstabData.rows.forEach(row => {
                       this.crosstabData.columns.forEach(column => {
                           maxValue = Math.max(maxValue, this.crosstabData.data[row][column]);
                       });
                   });
                   
                   return maxValue;
               },
               
               getAggregationLabel() {
                   const aggLabels = {
                       'count': 'Jumlah (Count)',
                       'sum': 'Total (Sum)',
                       'avg': 'Rata-rata (Average)',
                       'min': 'Minimum',
                       'max': 'Maximum'
                   };
                   
                   let label = aggLabels[this.aggregation] || this.aggregation;
                   
                   if (this.aggregation !== 'count' && this.valueField) {
                       label += ' dari ' + this.getVariableLabel(this.valueField);
                   }
                   
                   return label;
               },
               
               getPercentageLabel() {
                   const labels = {
                       'none': 'Tidak ada',
                       'row': 'Per baris',
                       'column': 'Per kolom',
                       'total': 'Dari total'
                   };
                   
                   return labels[this.percentageType] || this.percentageType;
               },
               
               getHighlightLabel() {
                   const labels = {
                       'none': 'Tidak ada',
                       'row': 'Per baris',
                       'column': 'Per kolom',
                       'all': 'Global'
                   };
                   
                   return labels[this.highlightType] || this.highlightType;
               },
               
               addFilter() {
                   this.filters.push({
                       field: '',
                       operator: 'eq',
                       value: ''
                   });
               },
               
               removeFilter(index) {
                   this.filters.splice(index, 1);
               },
               
               resetForm() {
                   this.rowVariable = '';
                   this.columnVariable = '';
                   this.aggregation = 'count';
                   this.valueField = '';
                   this.filters = [];
                   this.percentageType = 'none';
                   this.highlightType = 'row';
                   this.crosstabData = null;
                   this.errorMessage = null;
                   
                   if (this.chart) {
                       this.chart.destroy();
                       this.chart = null;
                   }
               },
               
               printCrosstab() {
                   window.print();
               },
               
               exportToExcel() {
                   if (!this.crosstabData) return;
                   
                   // Create new workbook
                   const wb = XLSX.utils.book_new();
                   
                   // Create header row for crosstab
                   const header = [
                       `${this.getVariableLabel(this.rowVariable)} / ${this.getVariableLabel(this.columnVariable)}`,
                       ...this.crosstabData.columns.map(col => this.formatLabel(this.crosstabData.column_labels[col] || col)),
                       'Total'
                   ];
                   
                   // Create data rows
                   const rows = this.crosstabData.rows.map(row => {
                       return [
                           this.formatLabel(this.crosstabData.row_labels[row] || row),
                           ...this.crosstabData.columns.map(col => this.crosstabData.data[row][col]),
                           this.crosstabData.row_totals[row]
                       ];
                   });
                   
                   // Add total row
                   const totalRow = [
                       'Total',
                       ...this.crosstabData.columns.map(col => this.crosstabData.column_totals[col]),
                       this.crosstabData.grand_total
                   ];
                   
                   // Combine all rows
                   const wsData = [header, ...rows, totalRow];
                   
                   // Create worksheet
                   const ws = XLSX.utils.aoa_to_sheet(wsData);
                   
                   // Add worksheet to workbook
                   XLSX.utils.book_append_sheet(wb, ws, 'Crosstab Analysis');
                   
                   // Create info sheet with metadata
                   const infoData = [
                       ['Analisis Crosstab PIS-PK', ''],
                       ['', ''],
                       ['Variabel Baris:', this.getVariableLabel(this.rowVariable)],
                       ['Variabel Kolom:', this.getVariableLabel(this.columnVariable)],
                       ['Metode Agregasi:', this.getAggregationLabel()],
                       ['Tanggal Export:', new Date().toLocaleString()],
                       ['', ''],
                       ['Jumlah Baris:', this.crosstabData.rows.length],
                       ['Jumlah Kolom:', this.crosstabData.columns.length],
                       ['Total Data:', this.crosstabData.grand_total],
                   ];
                   
                   // Add filter info if any
                   if (this.filters.length > 0) {
                       infoData.push(['', '']);
                       infoData.push(['Filter yang diterapkan:', '']);
                       
                       this.filters.forEach((filter, index) => {
                           infoData.push([
                               `Filter ${index + 1}:`,
                               `${this.getVariableLabel(filter.field)} ${filter.operator} ${filter.value}`
                           ]);
                       });
                   }
                   
                   const infoWs = XLSX.utils.aoa_to_sheet(infoData);
                   XLSX.utils.book_append_sheet(wb, infoWs, 'Info');
                   
                   // Generate filename
                   const rowName = this.rowVariable.split('.').pop();
                   const colName = this.columnVariable.split('.').pop();
                   const filename = `Crosstab_${rowName}_${colName}_${new Date().toISOString().slice(0, 10)}.xlsx`;
                   
                   // Export file
                   XLSX.writeFile(wb, filename);
               }
           }));
       });
   </script>
   
   <style>
       @media print {
           body * {
               visibility: hidden;
           }
           
           .fi-sidebar, .fi-topbar, form, button {
               display: none !important;
           }
           
           #crosstabTable, #crosstabTable * {
               visibility: visible;
           }
           
           #crosstabTable {
               position: absolute;
               left: 0;
               top: 0;
               width: 100%;
           }
       }
       
       /* Cell highlight coloring */
       .bg-blue-100 { background-color: #dbeafe; }
       .bg-blue-200 { background-color: #bfdbfe; }
       .bg-blue-300 { background-color: #93c5fd; }
       .bg-blue-400 { background-color: #60a5fa; }
       .bg-blue-500 { background-color: #3b82f6; color: white; }
       .bg-blue-600 { background-color: #2563eb; color: white; }
       .bg-blue-700 { background-color: #1d4ed8; color: white; }
       .bg-blue-800 { background-color: #1e40af; color: white; }
       .bg-blue-900 { background-color: #1e3a8a; color: white; }
   </style>
   @endPushOnce
</x-filament::page>