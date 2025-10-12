<nav class="mb-6">
  <div class="flex flex-wrap items-center gap-2 bg-white rounded-lg shadow p-2">
    @php
      $link = function(string $href, string $label, bool $active=false) {
        $base = 'px-3 py-1.5 rounded-md text-sm';
        $cls = $active
          ? 'bg-blue-600 text-white'
          : 'text-gray-700 hover:bg-gray-100';
        return "<a href=\"{$href}\" class=\"{$base} {$cls}\">{$label}</a>";
      };
    @endphp
    {!! $link(route('spm.dashboard'), 'Dashboard', request()->routeIs('spm.dashboard')) !!}
    {!! $link(route('targets.index'), 'Daftar Target', request()->routeIs('targets.index')) !!}
    {!! $link(route('targets.bulk', ['year' => now()->year]), 'Kelola Target (Bulk)', request()->routeIs('targets.bulk')) !!}
    {!! $link(route('targets.monthly.bulk', ['year' => now()->year]), 'Target Bulanan (Bulk)', request()->routeIs('targets.monthly.bulk')) !!}
    {!! $link(route('targets.create'), 'Tambah Target', request()->routeIs('targets.create')) !!}
  </div>
</nav>
