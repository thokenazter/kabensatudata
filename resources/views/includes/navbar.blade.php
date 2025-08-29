<nav 
    x-data="{ 
        scrolled: false, 
        mobileMenuOpen: false 
    }" 
    x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 10 })"
    :class="{ 'bg-white/70 backdrop-blur-lg border-transparent shadow-lg': scrolled, 'bg-white border-gray-100': !scrolled }"
    class="fixed w-full top-0 z-50 border-b transition-all duration-300"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Logo -->
            <div class="flex items-center">
                <div class="flex-shrink-0 flex items-center">
                    <a href="/dashboard">
                        <h1 class="ml-3 text-xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent transition-all duration-300 hover:from-indigo-600 hover:to-blue-600">
                            Kaben|<span class="text-orange-500">SatuData</span>
                        </h1>
                    </a>
                </div>
            </div>

            <!-- Desktop Navigation -->
            <div class="hidden sm:flex sm:items-center sm:space-x-6">
                <a href="{{ route('dashboard') }}" 
                    class="text-gray-600 hover:text-blue-600 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-300 hover:bg-blue-50/80 {{ request()->is('dashboard') ? 'text-blue-600 bg-blue-50/80 font-semibold' : '' }}">
                    Home
                </a>
                <a href="/about" 
                    class="text-gray-600 hover:text-blue-600 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-300 hover:bg-blue-50/80 {{ request()->is('about') ? 'text-blue-600 bg-blue-50/80 font-semibold' : '' }}">
                    About
                </a>
                <a href="{{ route('analysis.index') }}" 
                    class="text-gray-600 hover:text-blue-600 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-300 hover:bg-blue-50/80">
                    Analysis
                </a>
                <a href="{{ route('map.index') }}" 
                    class="text-gray-600 hover:text-blue-600 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-300 hover:bg-blue-50/80">
                    Map
                </a>
                
                <a href="{{ route('admin.chatbot') }}" 
                    class="text-gray-600 hover:text-blue-600 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-300 hover:bg-blue-50/80 {{ request()->is('admin/chatbot') ? 'text-blue-600 bg-blue-50/80 font-semibold' : '' }}">
                    Chatbot Admin
                </a>
                
                @auth
                    <a href="/admin" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300 shadow-md hover:shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Admin Panel
                    </a>
                @else
                    <a href="/admin" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300 shadow-md hover:shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        Login
                    </a>
                @endauth
            </div>

            <!-- Desktop Search -->
            @auth
            <div class="hidden md:flex items-center">
                <div x-data="{ 
                    query: '{{ request('search') }}',
                    results: [],
                    isLoading: false,
                    showResults: false,
                    selectedIndex: -1,

                    init() {
                        if (this.query.length >= 2) this.searchAPI();
                        
                        this.$watch('query', (value) => {
                            if (value.length >= 2) {
                                this.searchAPI();
                            } else {
                                this.results = [];
                                this.showResults = false;
                            }
                        });
                        
                        this.$nextTick(() => {
                            document.addEventListener('click', (e) => {
                                if (!this.$el.contains(e.target)) {
                                    this.showResults = false;
                                }
                            });
                        });
                    },
                    
                    searchAPI() {
                        this.isLoading = true;
                        this.showResults = true;
                        
                        fetch(`/api/search?q=${encodeURIComponent(this.query)}`)
                            .then(response => response.json())
                            .then(data => {
                                this.results = data;
                                this.isLoading = false;
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                this.isLoading = false;
                            });
                    },
                    
                    selectResult(result) {
                        window.location.href = result.url;
                    },
                    
                    submitSearch() {
                        if (this.query.length >= 2) {
                            window.location.href = `{{ route('dashboard') }}?search=${encodeURIComponent(this.query)}`;
                        }
                    },
                    
                    handleKeydown(event) {
                        if (event.key === 'ArrowDown') {
                            event.preventDefault();
                            this.selectedIndex = Math.min(this.selectedIndex + 1, this.results.length - 1);
                        } else if (event.key === 'ArrowUp') {
                            event.preventDefault();
                            this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                        } else if (event.key === 'Enter') {
                            event.preventDefault();
                            if (this.selectedIndex >= 0) {
                                this.selectResult(this.results[this.selectedIndex]);
                            } else {
                                this.submitSearch();
                            }
                        } else if (event.key === 'Escape') {
                            this.showResults = false;
                        }
                    }
                }" class="relative">
                    <div class="relative flex items-center">
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
                            placeholder="Cari NIK atau nama..." 
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-60 sm:w-72 text-sm border-gray-300 rounded-md pl-10 pr-10 py-2 transition-all duration-300"
                        >
                        
                        <div class="absolute inset-y-0 right-0 flex items-center pr-2">
                            <button 
                                x-show="query.length > 0" 
                                @click="query = ''; results = []"
                                type="button" 
                                class="p-1 text-gray-400 hover:text-gray-500 focus:outline-none"
                            >
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            
                            <button 
                                @click="submitSearch"
                                type="button" 
                                class="ml-1 p-1 bg-blue-600 rounded-md text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Results Dropdown -->
                    <div 
                        x-show="showResults && (results.length > 0 || isLoading)" 
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform scale-95"
                        x-transition:enter-end="opacity-100 transform scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 transform scale-100"
                        x-transition:leave-end="opacity-0 transform scale-95"
                        class="absolute right-0 mt-2 w-96 bg-white rounded-md shadow-lg z-50 max-h-[calc(100vh-16rem)] overflow-y-auto"
                    >
                        <template x-if="isLoading">
                            <div class="px-4 py-2 text-center text-gray-500">Mencari...</div>
                        </template>
                        <template x-if="!isLoading && results.length === 0">
                            <div class="px-4 py-2 text-center text-gray-500">Tidak ada hasil</div>
                        </template>
                        <template x-for="(result, index) in results" :key="index">
                            <div 
                                @click="selectResult(result)"
                                :class="{'bg-blue-50': selectedIndex === index}"
                                class="px-4 py-2 cursor-pointer hover:bg-blue-50 transition-colors duration-200 flex items-center"
                            >
                                <div>
                                    <div class="font-semibold text-gray-800" x-text="result.title"></div>
                                    <div class="text-sm text-gray-500" x-text="result.subtitle"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            @endauth

            <!-- Mobile Controls -->
            <div class="flex items-center sm:hidden">
                @auth
                <!-- Mobile Search Toggle -->
                <div class="md:hidden relative mr-2" x-data="{ mobileSearchOpen: false }">
                    <button 
                        @click="mobileSearchOpen = !mobileSearchOpen" 
                        class="p-2 rounded-full text-gray-400 hover:text-blue-600 hover:bg-blue-50/80 focus:outline-none"
                    >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
                
                <!-- Mobile Search Panel -->
                <div 
                    x-show="mobileSearchOpen" 
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 transform -translate-y-2"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 transform translate-y-0"
                    x-transition:leave-end="opacity-0 transform -translate-y-2"
                    class="fixed inset-0 z-40 bg-black bg-opacity-75 p-4"
                >
                    <div class="bg-white rounded-lg p-4 max-w-md mx-auto relative">
                        <button 
                            @click="mobileSearchOpen = false"
                            class="absolute top-3 right-3 text-gray-500 hover:text-gray-700"
                        >
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        
                        <div x-data="{ 
                            mobileQuery: '',
                            mobileResults: [],
                            isSearching: false,

                            init() {
                                this.$watch('mobileQuery', (value) => {
                                    if (value.length >= 2) this.searchMobile();
                                    else this.mobileResults = [];
                                });
                            },

                            searchMobile() {
                                this.isSearching = true;
                                fetch(`/api/search?q=${encodeURIComponent(this.mobileQuery)}`)
                                    .then(response => response.json())
                                    .then(data => {
                                        this.mobileResults = data;
                                        this.isSearching = false;
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        this.isSearching = false;
                                    });
                            },

                            selectMobileResult(result) {
                                window.location.href = result.url;
                                this.mobileSearchOpen = false;
                            },

                            submitMobileSearch() {
                                if (this.mobileQuery.length >= 2) {
                                    window.location.href = `{{ route('dashboard') }}?search=${encodeURIComponent(this.mobileQuery)}`;
                                    this.mobileSearchOpen = false;
                                }
                            }
                        }">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg x-show="!isSearching" class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                    </svg>
                                    <svg x-show="isSearching" class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                                
                                <input 
                                    type="text" 
                                    x-model.debounce.300ms="mobileQuery"
                                    placeholder="Cari NIK atau nama..." 
                                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full text-sm border-gray-300 rounded-md pl-10 pr-10 py-2 transition-all duration-300"
                                >
                                
                                <div class="absolute inset-y-0 right-0 flex items-center pr-2">
                                    <button 
                                        x-show="mobileQuery.length > 0" 
                                        @click="mobileQuery = ''; mobileResults = []"
                                        type="button" 
                                        class="p-1 text-gray-400 hover:text-gray-500 focus:outline-none"
                                    >
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    
                                    <button 
                                        @click="submitMobileSearch"
                                        type="button" 
                                        class="ml-1 p-1 bg-blue-600 rounded-md text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    >
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Mobile Results -->
                            <div class="mt-4 max-h-[50vh] overflow-y-auto">
                                <template x-if="isSearching">
                                    <div class="px-4 py-2 text-center text-gray-500">Mencari...</div>
                                </template>
                                <template x-if="!isSearching && mobileResults.length === 0">
                                    <div class="px-4 py-2 text-center text-gray-500">Tidak ada hasil</div>
                                </template>
                                <template x-for="(result, index) in mobileResults" :key="index">
                                    <div 
                                        @click="selectMobileResult(result)"
                                        class="px-4 py-2 cursor-pointer hover:bg-blue-50 transition-colors duration-200 flex items-center border-b last:border-b-0"
                                    >
                                        <div>
                                            <div class="font-semibold text-gray-800" x-text="result.title"></div>
                                            <div class="text-sm text-gray-500" x-text="result.subtitle"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endauth
            
            <!-- Mobile Menu Button -->
            <button 
                type="button" 
                x-data="{ open: false }"
                @click="open = !open; $dispatch('toggle-mobile-menu', { open })"
                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-blue-600 hover:bg-blue-50/80 focus:outline-none"
            >
                <span class="sr-only">Buka menu utama</span>
                <!-- Hamburger Icon -->
                <svg x-show="!open" class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <!-- Close Icon -->
                <svg x-show="open" class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
</div>

<!-- Mobile Menu -->
<div 
    x-data="{ open: false }"
    @toggle-mobile-menu.window="open = $event.detail.open"
    x-show="open"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 transform scale-95"
    x-transition:enter-end="opacity-100 transform scale-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 transform scale-100"
    x-transition:leave-end="opacity-0 transform scale-95"
    class="sm:hidden"
>
    <div class="px-2 pt-2 pb-3 space-y-1 bg-white/90 backdrop-blur-lg border-b border-gray-100/40 shadow-lg rounded-b-lg">
        <a href="{{ route('dashboard') }}" 
            class="block px-4 py-2 rounded-lg text-base font-medium text-gray-600 hover:text-blue-600 hover:bg-blue-50/80 transition-all duration-300 {{ request()->is('dashboard') ? 'text-blue-600 bg-blue-50/80 font-semibold' : '' }}">
            Home
        </a>
        <a href="/about" 
            class="block px-4 py-2 rounded-lg text-base font-medium text-gray-600 hover:text-blue-600 hover:bg-blue-50/80 transition-all duration-300 {{ request()->is('about') ? 'text-blue-600 bg-blue-50/80 font-semibold' : '' }}">
            About
        </a>
        <a href="{{ route('analysis.index') }}" 
        class="block px-4 py-2 rounded-lg text-base font-medium text-gray-600 hover:text-blue-600 hover:bg-blue-50/80 transition-all duration-300">
        Analysis
    </a>
        <a href="{{ route('map.index') }}" 
            class="block px-4 py-2 rounded-lg text-base font-medium text-gray-600 hover:text-blue-600 hover:bg-blue-50/80 transition-all duration-300">
            Map
        </a>
        
        @auth
            <a href="/admin" class="block mt-4 px-4 py-2 rounded-lg text-base font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 shadow-md transition-all duration-300">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Admin Panel
                </div>
            </a>
        @else
            <a href="/admin" class="block mt-4 px-4 py-2 rounded-lg text-base font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 shadow-md transition-all duration-300">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0v1" />
                        </svg>
                        Login
                    </div>
                </a>
            @endauth
        </div>
    </div>

    <!-- Search Badge -->
    @auth
    @if(request()->has('search') && !empty(request('search')))
    <div class="mt-2 mb-4 text-center">
        <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
            Pencarian: {{ request('search') }}
            <a href="{{ route('dashboard') }}" class="ml-1.5 inline-flex items-center text-blue-600 hover:text-blue-900">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </a>
        </span>
    </div>
    @endif
    @endauth
</nav>

<script>
    // Optional: Close mobile menu when clicking outside
    document.addEventListener('alpine:init', () => {
        Alpine.store('mobileMenu', {
            close() {
                this.mobileMenuOpen = false;
            }
        });
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', (event) => {
            const nav = document.querySelector('nav');
            const mobileMenuButton = nav.querySelector('button');
            const mobileMenu = nav.querySelector('[x-show="mobileMenuOpen"]');
            
            if (mobileMenuButton && mobileMenu) {
                if (!nav.contains(event.target)) {
                    Alpine.store('mobileMenu').close();
                }
            }
        });
    });
</script>