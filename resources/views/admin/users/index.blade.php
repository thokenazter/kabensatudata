@extends('layouts.admin')

@section('admin-content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold text-slate-100">Pengguna</h1>
    @can('create_user')
        <a href="{{ route('panel.users.create') }}" class="px-3 py-2 rounded-lg text-white bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 transition">Tambah User</a>
    @endcan
</div>

<form method="GET" class="mb-4 flex items-center gap-2 p-3 rounded-xl border border-white/10 bg-white/5 backdrop-blur">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama/email" class="bg-white/10 border border-white/10 rounded-lg px-3 py-2 text-slate-200 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-cyan-400/40" />
    <button class="px-3 py-2 rounded-lg text-slate-200 bg-white/10 border border-white/10 hover:bg-white/15 transition">Cari</button>
</form>

<div class="relative overflow-x-auto rounded-xl border border-white/10 bg-white/5 backdrop-blur">
    <table class="min-w-full text-sm text-slate-200">
        <thead class="bg-white/5 text-slate-300">
            <tr>
                <th class="text-left px-3 py-2">Nama</th>
                <th class="text-left px-3 py-2">Email</th>
                <th class="text-left px-3 py-2">Roles</th>
                <th class="px-3 py-2">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr class="border-t border-white/10">
                    <td class="px-3 py-2 text-slate-100">{{ $user->name }}</td>
                    <td class="px-3 py-2">{{ $user->email }}</td>
                    <td class="px-3 py-2 space-x-1">
                        @foreach($user->roles as $r)
                            <span class="px-2 py-1 text-xs rounded bg-blue-500/15 text-blue-300">{{ $r->name }}</span>
                        @endforeach
                    </td>
                    <td class="px-3 py-2 space-x-2 whitespace-nowrap">
                        @can('update_user')
                            <a href="{{ route('panel.users.edit', $user) }}" class="px-2 py-1 rounded text-white bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 transition">Ubah</a>
                        @endcan
                        @can('delete_user')
                            <form action="{{ route('panel.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Hapus pengguna ini?')">
                                @csrf @method('DELETE')
                                <button class="px-2 py-1 rounded bg-red-500/20 text-red-200 border border-red-400/20 hover:bg-red-500/30 transition">Hapus</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-3 py-6 text-center text-slate-400">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4 text-slate-300">
    {{ $users->links() }}
</div>
@endsection
