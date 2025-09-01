{{-- Komponen untuk menampilkan data sensitif --}}
@props(['label', 'value', 'blurredValue' => null])

<div class="space-y-1">
    <label class="text-sm font-medium text-gray-500">{{ $label }}:</label>
    <div>
        @if(auth()->check())
            {{ $value }}
        @else
            <span class="blur-sm hover:blur-none transition-all cursor-pointer">
                {{ $blurredValue ?? '*************-'.substr($value, -4) }}
            </span>
        @endif
    </div>
</div>