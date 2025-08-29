<nav 
    x-data="{ scrolled: false }" 
    x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 10 })" 
    :class="{ 'bg-white/70 backdrop-blur-lg border-transparent shadow-lg': scrolled, 'bg-white border-gray-100': !scrolled }"
    class="fixed w-full top-0 z-50 border-b transition-all duration-300"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Logo and Brand Name -->
            <div class="flex items-center">
                <div class="flex-shrink-0 flex items-center">
                    <!-- Logo (jika ada) -->
                    {{-- <img class="h-10 w-auto" src="/path-to-your-logo.svg" alt="Logo"> --}}
                    <!-- Brand Name dengan efek gradient yang lebih halus -->
                    <a href="/dashboard">
                        <h1 class="ml-3 text-xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent transition-all duration-300 hover:from-indigo-600 hover:to-blue-600">
                            Core|<span class="text-orange-500">Track</span>
                        </h1>
                    </a>
                </div>
            </div>

            <!-- Navigation Links - Hidden on Mobile -->
            <div class="hidden sm:flex sm:items-center sm:space-x-6">
                <a href="/" class="text-gray-600 hover:text-blue-600 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-300 hover:bg-blue-50/80">
                    Home
                </a>
                <a href="/analysis" class="text-gray-600 hover:text-blue-600 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-300 hover:bg-blue-50/80">
                    Analysis
                </a>
                <a href="/about" class="text-gray-600 hover:text-blue-600 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-300 hover:bg-blue-50/80">
                    About
                </a>
                <a href="/map" class="text-gray-600 hover:text-blue-600 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-300 hover:bg-blue-50/80">
                    Map
                </a>
                
                @auth
                    <!-- Tampilkan ketika user login -->
                    <a href="/admin" target="_blank" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300 shadow-md hover:shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Admin Panel
                    </a>
                @else
                    <!-- Tampilkan ketika user belum login -->
                    <a href="/admin" target="_blank" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300 shadow-md hover:shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        Login
                    </a>
                @endauth
            </div>

            <!-- Mobile Menu Button dengan Alpine.js -->
            <div class="flex items-center sm:hidden">
                <button 
                    type="button" 
                    x-data="{ open: false }"
                    @click="open = !open; $dispatch('toggle-mobile-menu', { open })"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-blue-600 hover:bg-blue-50/80 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500 transition-all duration-200"
                >
                    <span class="sr-only">Buka menu utama</span>
                    <svg 
                        x-bind:class="{'hidden': open, 'block': !open}"
                        class="block h-6 w-6" 
                        xmlns="http://www.w3.org/2000/svg" 
                        fill="none" 
                        viewBox="0 0 24 24" 
                        stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg 
                        x-bind:class="{'block': open, 'hidden': !open}"
                        class="hidden h-6 w-6" 
                        xmlns="http://www.w3.org/2000/svg" 
                        fill="none" 
                        viewBox="0 0 24 24" 
                        stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu dengan Alpine.js -->
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
            <a href="/" class="block px-4 py-2 rounded-lg text-base font-medium text-gray-600 hover:text-blue-600 hover:bg-blue-50/80 transition-all duration-300">
                Home
            </a>
            <a href="/analysis" class="block px-4 py-2 rounded-lg text-base font-medium text-gray-600 hover:text-blue-600 hover:bg-blue-50/80 transition-all duration-300">
                Analysis
            </a>
            <a href="/about" class="block px-4 py-2 rounded-lg text-base font-medium text-gray-600 hover:text-blue-600 hover:bg-blue-50/80 transition-all duration-300">
                About
            </a>
            <a href="/map" class="block px-4 py-2 rounded-lg text-base font-medium text-gray-600 hover:text-blue-600 hover:bg-blue-50/80 transition-all duration-300">
                Map
            </a>
            @auth
                <a href="/admin" target="_blank" class="block mt-4 px-4 py-2 rounded-lg text-base font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 shadow-md transition-all duration-300">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Admin Panel
                    </div>
                </a>
            @else
                <a href="/admin" target="_blank" class="block mt-4 px-4 py-2 rounded-lg text-base font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 shadow-md transition-all duration-300">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        Login
                    </div>
                </a>
            @endauth
        </div>
    </div>
</nav>
<script>
    // Ini hanya untuk mendemonstrasikan efek scroll, bisa dihapus
    document.addEventListener('alpine:init', () => {
        if (window.scrollY > 10) {
            document.querySelector('nav').__x.$data.scrolled = true;
        }
    });
</script>