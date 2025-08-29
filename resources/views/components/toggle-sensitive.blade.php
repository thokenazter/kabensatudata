{{-- Komponen untuk menampilkan data sensitif dengan toggle Alpine.js --}}
@props(['label', 'value', 'blurredValue' => null])

<div class="space-y-1">
    <label class="text-sm font-medium text-gray-500">{{ $label }}:</label>
    <div>
        @if(auth()->check())
            {{ $value }}
        @else
            <div x-data="{ show: false }" class="relative">
                <div 
                    x-show="!show" 
                    @click="show = true" 
                    class="blur-sm cursor-pointer"
                >
                    {{ $blurredValue ?? blur_text($value) }}
                </div>
                <div 
                    x-show="show" 
                    @click.away="show = false"
                    x-transition.opacity
                >
                    {{ $blurredValue ?? blur_text($value) }}
                </div>
                <div x-show="!show" class="text-xs text-gray-500 mt-1">(Klik untuk melihat)</div>
            </div>
        @endif
    </div>
</div>