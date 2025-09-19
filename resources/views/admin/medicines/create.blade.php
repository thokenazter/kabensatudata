@extends('layouts.admin')

@section('admin-content')
<div class="max-w-3xl">
    <h1 class="text-xl font-semibold mb-4">Tambah Obat</h1>
    <form method="POST" action="{{ route('panel.medicines.store') }}" class="space-y-4">
        @csrf
        @include('admin.medicines._form')
        <div class="flex items-center space-x-2">
            <button class="px-4 py-2 bg-blue-600 text-white rounded">Simpan</button>
            <a href="{{ route('panel.medicines.index') }}" class="px-4 py-2 bg-gray-100 rounded">Batal</a>
        </div>
    </form>
    
</div>
@endsection
