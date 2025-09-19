<div class="mb-8 px-4">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8"> 
        <!-- Data Anggota Keluarga Terbaru -->
        {{-- <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900"></h3>
                    
                    <div class="flex items-center">
                        @auth
                            <!-- Form Pencarian - Hanya tampil jika sudah login -->
                            <form method="GET" action="{{ route('dashboard') }}" class="flex mr-4">
                                <input 
                                    type="text" 
                                    name="search" 
                                    placeholder="Cari NIK atau nama..." 
                                    value="{{ request('search') }}"
                                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-60 sm:w-80 text-sm border-gray-300 rounded-md px-4"
                                >
                                <button type="submit" class="ml-2 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Cari
                                </button>
                                @if(request()->has('search') && !empty(request('search')))
                                    <a href="{{ route('dashboard') }}" class="ml-2 text-sm text-gray-600 hover:text-gray-900">
                                        Reset
                                    </a>
                                @endif
                            </form>
                        @endauth
                        
                        @guest
                            <div class="text-sm text-blue-600">
                                <a href="/admin" class="font-medium hover:underline">
                                    Login untuk melihat data lengkap dan menggunakan fitur pencarian
                                </a>
                            </div>
                        @endguest
                    </div>
                </div>
                
                @auth
                    @if(request()->has('search') && !empty(request('search')))
                        <div class="mt-2 mb-4">
                            <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                Pencarian: {{ request('search') }}
                                <a href="{{ route('dashboard') }}" class="ml-1.5 inline-flex items-center text-blue-600 hover:text-blue-900">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </a>
                            </span>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Nama
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            NIK
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            No. Keluarga
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Umur
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Jenis Kelamin
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @if($recentMembers->isEmpty())
                                        <tr>
                                            <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                                <p>Tidak ada data yang ditemukan dengan kata kunci "{{ request('search') }}"</p>
                                                <a href="{{ route('dashboard') }}" class="mt-2 inline-block text-sm text-blue-600 hover:underline">
                                                    Kembali
                                                </a>
                                            </td>
                                        </tr>
                                    @else
                                        @foreach($recentMembers as $member)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if(auth()->check())
                                                        {{ $member->name }}
                                                    @else
                                                        <span class="blur-sm hover:blur-none transition-all cursor-pointer">
                                                            {{ substr($member->name, 0, 3) }}***
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if(auth()->check())
                                                        {{ $member->nik }}
                                                    @else
                                                        <span class="blur-sm hover:blur-none transition-all cursor-pointer">
                                                            XXXX-XXXX-XXXX-{{ substr($member->nik, -4) }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    {{ $member->family->family_number }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    {{ $member->age }} tahun
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    {{ $member->gender }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ route('family-members.show', $member) }}" 
                                                       class="text-blue-600 hover:text-blue-900 inline-flex items-center gap-1">
                                                        Detail
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                        </svg>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-10">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data yang ditampilkan</h3>
                            <p class="mt-1 text-sm text-gray-500">Gunakan pencarian di atas untuk menampilkan data.</p>
                        </div>
                    @endif
                @endauth
                
                @guest
                    <div class="text-center py-10">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Login diperlukan</h3>
                        <p class="mt-1 text-sm text-gray-500">Silakan login untuk melihat data dan menggunakan fitur pencarian.</p>
                    </div>
                @endguest
            </div>
        </div> --}}

         
        <div class="mt-4">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg transition-all duration-300 hover:shadow-2xl">
                    <div class="p-6">
                        <div class="flex flex-col items-center">
                            <!-- Icon Rumah Modern dengan animasi -->
                            <div class="transform transition-transform duration-300 hover:scale-110">
                                <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                            </div>
                            
                            <div class="mt-4">
                                <div class="text-lg font-medium text-gray-900">Jumlah Rumah Keseluruhan</div>
                                <div class="mt-2 text-3xl font-bold text-blue-600 text-center">{{ $totalBuildings }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

