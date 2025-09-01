{{-- Komponen untuk menampilkan indikator kesehatan --}}
@props([
    'title',
    'status',
    'positiveLabel' => 'Ya',
    'negativeLabel' => 'Tidak',
    'positiveColor' => 'text-red-600',
    'negativeColor' => 'text-green-600',
    'subInfo' => null
])

<div class="bg-white shadow rounded-lg p-6 health-card">
    <h4 class="text-sm font-medium text-gray-500">{{ $title }}</h4>
    <p class="mt-2 text-md">
        @if($status)
            <span class="{{ $positiveColor }}">{{ $positiveLabel }}</span>
            @if($subInfo)
                <div>
                    {{ $subInfo }}
                </div>
            @endif
        @else
            <span class="{{ $negativeColor }}">{{ $negativeLabel }}</span>
        @endif
    </p>
    {{ $slot ?? '' }}
</div>