{{-- Komponen untuk menampilkan informasi lokasi dan keluarga --}}
@props(['familyMember'])

<div class="bg-white shadow-lg rounded-lg overflow-hidden">
    <div class="bg-gradient-to-r from-gray-700 to-gray-800 px-6 py-4">
        <h3 class="text-xl font-semibold text-white">Informasi Keluarga & Lokasi</h3>
        <p class="text-gray-300 text-sm">Data tempat tinggal dan keluarga</p>
    </div>
    
    <div class="p-6 space-y-4">
        {{-- Informasi Lokasi --}}
        <div class="border-b border-gray-200 pb-4">
            <h4 class="font-medium text-gray-900 mb-3">Informasi Lokasi</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="space-y-1">
                    <span class="text-sm text-gray-500">Nomor Bangunan:</span>
                    <div class="font-medium">
                        @if(auth()->check())
                            {{ $familyMember->family->building->building_number ?? 'Tidak tersedia' }}
                        @else
                            <span class="blur-sm hover:blur-none transition-all cursor-pointer">
                                *************{{ substr($familyMember->family->building->building_number ?? 'N/A', -3) }}
                            </span>
                        @endif
                    </div>
                </div>
                
                <div class="space-y-1">
                    <span class="text-sm text-gray-500">Desa:</span>
                    <div class="font-medium">
                        @if($familyMember->family && $familyMember->family->building && $familyMember->family->building->village)
                            {{ $familyMember->family->building->village->name ?? 'Tidak tersedia' }}
                        @elseif($familyMember->family && $familyMember->family->village)
                            {{ $familyMember->family->village->name ?? 'Tidak tersedia' }}
                        @else
                            Tidak tersedia
                        @endif
                    </div>
                </div>
                
                <div class="space-y-1">
                    <span class="text-sm text-gray-500">Kecamatan:</span>
                    <div class="font-medium">
                        @if($familyMember->family && $familyMember->family->building && $familyMember->family->building->village)
                            {{ $familyMember->family->building->village->district ?? 'Tidak tersedia' }}
                        @elseif($familyMember->family && $familyMember->family->village)
                            {{ $familyMember->family->village->district ?? 'Tidak tersedia' }}
                        @else
                            Tidak tersedia
                        @endif
                    </div>
                </div>
                
                <div class="space-y-1">
                    <span class="text-sm text-gray-500">Kabupaten:</span>
                    <div class="font-medium">
                        @if($familyMember->family && $familyMember->family->building && $familyMember->family->building->village)
                            {{ $familyMember->family->building->village->regency ?? 'Tidak tersedia' }}
                        @elseif($familyMember->family && $familyMember->family->village)
                            {{ $familyMember->family->village->regency ?? 'Tidak tersedia' }}
                        @else
                            Tidak tersedia
                        @endif
                    </div>
                </div>
                
                <div class="space-y-1">
                    <span class="text-sm text-gray-500">Provinsi:</span>
                    <div class="font-medium">
                        @if($familyMember->family && $familyMember->family->building && $familyMember->family->building->village)
                            {{ $familyMember->family->building->village->province ?? 'Tidak tersedia' }}
                        @elseif($familyMember->family && $familyMember->family->village)
                            {{ $familyMember->family->village->province ?? 'Tidak tersedia' }}
                        @else
                            Tidak tersedia
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Informasi Keluarga --}}
        <div>
            <h4 class="font-medium text-gray-900 mb-3">Informasi Keluarga</h4>
            
            {{-- Kepala Keluarga --}}
            <div class="bg-gray-50 p-4 rounded-lg mb-3">
                <span class="text-sm text-gray-500 block mb-1">Kepala Keluarga:</span>
                <div class="font-medium">
                    @php
                        $headOfFamily = $familyMember->family->members()
                            ->where('relationship', 'Kepala Keluarga')
                            ->first();
                    @endphp
                    
                    @if(auth()->check())
                        {{ $headOfFamily->name ?? $familyMember->family->head_name ?? 'Tidak tersedia' }}
                    @else
                        <span class="blur-sm hover:blur-none transition-all cursor-pointer">
                            @if($headOfFamily)
                                {{ strlen($headOfFamily->name) > 10 ? substr($headOfFamily->name, 0, 2) . '****' . substr($headOfFamily->name, -4) : '****' . substr($headOfFamily->name, -4) }}
                            @elseif($familyMember->family->head_name)
                                {{ strlen($familyMember->family->head_name) > 10 ? substr($familyMember->family->head_name, 0, 2) . '****' . substr($familyMember->family->head_name, -4) : '****' . substr($familyMember->family->head_name, -4) }}
                            @else
                                Tidak tersedia
                            @endif
                        </span>
                    @endif
                </div>
            </div>
            
            {{-- Pasangan (Suami/Istri) jika status pernikahan "Kawin" --}}
            @if($familyMember->marital_status === 'Kawin')
                <div class="bg-gray-50 p-4 rounded-lg">
                    <span class="text-sm text-gray-500 block mb-1">
                        {{ $familyMember->gender === 'Laki-laki' ? 'Istri:' : 'Suami:' }}
                    </span>
                    <div class="font-medium">
                        @php
                            // Cari pasangan berdasarkan gender yang berbeda dan relationship "Istri" atau "Suami"
                            $oppositeGender = $familyMember->gender === 'Laki-laki' ? 'Perempuan' : 'Laki-laki';
                            $spouse = $familyMember->family->members()
                                ->where('gender', $oppositeGender)
                                ->whereIn('relationship', ['Istri', 'Suami'])
                                ->where('id', '!=', $familyMember->id)
                                ->first();
                            
                            // Alternatif, jika tidak ada yang relationship Istri/Suami, mungkin bisa cek dari status kawin
                            if (!$spouse) {
                                $spouse = $familyMember->family->members()
                                    ->where('gender', $oppositeGender)
                                    ->where('marital_status', 'Kawin')
                                    ->where('id', '!=', $familyMember->id)
                                    ->first();
                            }
                        @endphp
                        
                        @if(auth()->check())
                            {{ $spouse->name ?? 'Tidak tersedia' }}
                        @else
                            <span class="blur-sm hover:blur-none transition-all cursor-pointer">
                                {{ $spouse ? (strlen($spouse->name) > 10 ? substr($spouse->name, 0, 2) . '****' . substr($spouse->name, -4) : '****' . substr($spouse->name, -4)) : 'Tidak tersedia' }}
                            </span>
                        @endif
                    </div>
                </div>
            @endif
            
            {{-- Nomor Keluarga --}}
            <div class="bg-gray-50 p-4 rounded-lg mt-3">
                <span class="text-sm text-gray-500 block mb-1">Nomor Keluarga:</span>
                <div class="font-medium">
                    @if(auth()->check())
                        {{ $familyMember->family->family_number ?? 'Tidak tersedia' }}
                    @else
                        <span class="blur-sm hover:blur-none transition-all cursor-pointer">
                            *************{{ substr($familyMember->family->family_number ?? 'N/A', -4) }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>