<div 
    x-data="{ 
        query: '', 
        results: [], 
        isLoading: false, 
        selectedIndex: -1,
        showResults: false,
        
        init() {
            this.$watch('query', (value) => {
                this.searchAPI();
            });
            
            // Close when clicking outside
            this.$nextTick(() => {
                document.addEventListener('click', (e) => {
                    if (!this.$el.contains(e.target)) {
                        this.showResults = false;
                    }
                });
            });
        },
        
        searchAPI() {
            if (this.query.length < 2) {
                this.results = [];
                this.showResults = false;
                return;
            }
            
            this.isLoading = true;
            this.showResults = true;
            
            fetch(`/api/search?q=${encodeURIComponent(this.query)}`)
                .then(response => response.json())
                .then(data => {
                    this.results = data;
                    this.isLoading = false;
                })
                .catch(error => {
                    console.error('Error performing search:', error);
                    this.isLoading = false;
                });
        },
        
        selectResult(result) {
            window.location.href = result.url;
        },
        
        handleKeydown(event) {
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                this.selectedIndex = Math.min(this.selectedIndex + 1, this.results.length - 1);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
            } else if (event.key === 'Enter' && this.selectedIndex >= 0) {
                event.preventDefault();
                this.selectResult(this.results[this.selectedIndex]);
            } else if (event.key === 'Escape') {
                this.showResults = false;
            }
        }
    }" 
    class="relative max-w-md"
>
    <!-- Search Input -->
    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg x-show="!isLoading" class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
            </svg>
            <svg x-show="isLoading" class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
        <input 
            type="text" 
            x-model.debounce.300ms="query"
            @keydown="handleKeydown"
            @focus="if (query.length >= 2) showResults = true"
            placeholder="Cari warga, keluarga, atau alamat..." 
            class="block w-full bg-white bg-opacity-10 text-white placeholder-gray-300 pl-10 pr-3 py-2 rounded-lg border border-transparent focus:outline-none focus:bg-opacity-20 focus:ring-1 focus:ring-white"
        />
        <button 
            x-show="query.length > 0" 
            @click="query = ''; results = []"
            type="button" 
            class="absolute inset-y-0 right-0 pr-3 flex items-center"
        >
            <svg class="h-5 w-5 text-gray-400 hover:text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>
    
    <!-- Search Results Dropdown -->
    <div 
        x-show="showResults && (isLoading || results.length > 0)" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="absolute mt-2 w-full bg-white dark:bg-gray-800 rounded-md shadow-lg overflow-hidden z-50 max-h-96 overflow-y-auto"
    >
        <div x-show="isLoading && results.length === 0" class="p-4 text-gray-500 text-center">
            <svg class="animate-spin h-5 w-5 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p>Mencari...</p>
        </div>
        
        <div x-show="results.length > 0">
            <div class="text-xs uppercase font-bold text-gray-500 px-4 py-2 border-b border-gray-200 bg-gray-50">
                Hasil Pencarian
            </div>
            <ul class="divide-y divide-gray-200">
                <template x-for="(result, index) in results" :key="index">
                    <li 
                        @click="selectResult(result)"
                        @mouseenter="selectedIndex = index"
                        :class="{'bg-blue-50 dark:bg-blue-900': selectedIndex === index}"
                        class="p-4 hover:bg-blue-50 hover:dark:bg-blue-900 transition duration-150 cursor-pointer"
                    >
                        <div class="flex items-start">
                            <!-- Icon based on result type -->
                            <div class="flex-shrink-0 mt-1">
                                <svg x-show="result.type === 'family'" class="h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                                </svg>
                                <svg x-show="result.type === 'member'" class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                </svg>
                                <svg x-show="result.type === 'building'" class="h-5 w-5 text-yellow-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                                </svg>
                                <svg x-show="result.type === 'village'" class="h-5 w-5 text-purple-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z" />
                                </svg>
                                <svg x-show="result.type === 'medical'" class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 01-1 1h-2a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            
                            <div class="ml-3">
                                <div x-text="result.title" class="text-sm font-medium text-gray-900 dark:text-white"></div>
                                <div x-text="result.subtitle" class="text-xs text-gray-500 dark:text-gray-400"></div>
                                <div x-show="result.details" x-text="result.details" class="text-xs text-gray-500 mt-1"></div>
                            </div>
                        </div>
                    </li>
                </template>
            </ul>
            <div class="p-2 text-center border-t border-gray-200 bg-gray-50 text-xs text-gray-500">
                Tekan <kbd class="px-1.5 py-0.5 text-xs font-semibold bg-gray-100 border border-gray-300 rounded-md">↑</kbd> <kbd class="px-1.5 py-0.5 text-xs font-semibold bg-gray-100 border border-gray-300 rounded-md">↓</kbd> untuk navigasi, <kbd class="px-1.5 py-0.5 text-xs font-semibold bg-gray-100 border border-gray-300 rounded-md">Enter</kbd> untuk memilih
            </div>
        </div>
    </div>
</div>