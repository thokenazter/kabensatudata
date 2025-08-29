<div class="max-w-7xl mx-auto py-6 pt-24 px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-lg shadow p-6 ">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Filter Data</h3>
        <form action="{{ route('dashboard') }}" method="GET" class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
            <!-- Filter Desa -->
            <div class="sm:col-span-2">
                <label for="village_id" class="block text-sm font-medium text-gray-700">Desa</label>
                <select id="village_id" name="village_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                    <option value="">Semua Desa</option>
                    @foreach($villages as $village)
                        <option value="{{ $village->id }}" {{ request('village_id') == $village->id ? 'selected' : '' }}>
                            {{ $village->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Pendidikan -->
            <div class="sm:col-span-2">
                <label for="education" class="block text-sm font-medium text-gray-700">Pendidikan</label>
                <select id="education" name="education" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                    <option value="">Semua Pendidikan</option>
                    @foreach($educationLevels as $level)
                        <option value="{{ $level }}" {{ request('education') == $level ? 'selected' : '' }}>
                            {{ $level }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Masalah Kesehatan -->
            <div class="sm:col-span-2">
                <label for="health_issue" class="block text-sm font-medium text-gray-700">Masalah Kesehatan</label>
                <select id="health_issue" name="health_issue" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                    <option value="">Semua Masalah Kesehatan</option>
                    <option value="tuberculosis" {{ request('health_issue') == 'tuberculosis' ? 'selected' : '' }}>Pernah TBC</option>
                    <option value="hypertension" {{ request('health_issue') == 'hypertension' ? 'selected' : '' }}>Darah Tinggi</option>
                    <option value="chronic_cough" {{ request('health_issue') == 'chronic_cough' ? 'selected' : '' }}>Batuk Berdahak</option>
                    <option value="mental_illness" {{ request('health_issue') == 'mental_illness' ? 'selected' : '' }}>Gangguan Jiwa</option>
                    <option value="restrained_member" {{ request('health_issue') == 'restrained_member' ? 'selected' : '' }}>Kasus Pasung</option>
                </select>
            </div>

            <!-- Filter Sanitasi -->
            <div class="sm:col-span-2">
                <label for="sanitation_filter" class="block text-sm font-medium text-gray-700">Sanitasi</label>
                <select id="sanitation_filter" name="sanitation_filter" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                    <option value="">Semua Status</option>
                    <option value="clean_water" {{ request('sanitation_filter') == 'clean_water' ? 'selected' : '' }}>Memiliki Air Bersih</option>
                    <option value="protected_water" {{ request('sanitation_filter') == 'protected_water' ? 'selected' : '' }}>Air Bersih Terlindungi</option>
                    <option value="toilet" {{ request('sanitation_filter') == 'toilet' ? 'selected' : '' }}>Memiliki Jamban</option>
                    <option value="sanitary_toilet" {{ request('sanitation_filter') == 'sanitary_toilet' ? 'selected' : '' }}>Jamban Saniter</option>
                    <option value="no_toilet" {{ request('sanitation_filter') == 'no_toilet' ? 'selected' : '' }}>Tidak Memiliki Jamban</option>
                    <option value="no_clean_water" {{ request('sanitation_filter') == 'no_clean_water' ? 'selected' : '' }}>Tidak Memiliki Air Bersih</option>
                </select>
            </div>

            <div class="sm:col-span-6 flex justify-end">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                    </svg>
                    Filter
                </button>
            </div>
        </form>
    </div>
</div>