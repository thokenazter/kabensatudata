@extends('layouts.admin')

@section('admin-content')
<div class="max-w-3xl">
    <h1 class="text-xl font-semibold mb-4">Ubah Desa</h1>
    <form method="POST" action="{{ route('panel.villages.update', $village) }}" class="space-y-4">
        @csrf
        @method('PUT')
        @include('admin.villages._form', ['village' => $village])
        <div class="flex items-center space-x-2">
            <button class="px-4 py-2 bg-blue-600 text-white rounded">Perbarui</button>
            <a href="{{ route('panel.villages.index') }}" class="px-4 py-2 bg-gray-100 rounded">Batal</a>
        </div>
    </form>
</div>
@endsection

