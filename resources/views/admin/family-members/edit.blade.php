@extends('layouts.admin')

@section('admin-content')
<div class="max-w-4xl">
    <h1 class="text-xl font-semibold mb-4">Ubah Anggota Keluarga</h1>
    <form method="POST" action="{{ route('panel.family-members.update', $familyMember) }}" class="space-y-4">
        @csrf
        @method('PUT')
        @include('admin.family-members._form', [
            'familyMember' => $familyMember,
            'families' => $families,
        ])
        <div class="flex items-center space-x-2">
            <button class="px-4 py-2 bg-blue-600 text-white rounded">Perbarui</button>
            <a href="{{ route('panel.family-members.index') }}" class="px-4 py-2 bg-gray-100 rounded">Batal</a>
        </div>
    </form>
</div>
@endsection

