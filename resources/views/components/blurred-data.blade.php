<div>
    <span @class([
        'relative',
        'blur-sm hover:blur-none cursor-pointer transition-all' => $shouldBlur()
    ])>
        {{ $getContent() }}
        
        @if($shouldBlur() && !auth()->check())
            <span class="absolute bottom-0 left-0 right-0 text-xs text-blue-600 text-opacity-0 hover:text-opacity-100 transition-all">
                Login untuk melihat
            </span>
        @endif
    </span>
</div>