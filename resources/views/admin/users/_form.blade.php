@php
    $roles = $roles ?? collect();
    $selected = $userRoles ?? [];
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm mb-1">Nama</label>
        <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" class="w-full border rounded px-3 py-2" required>
    </div>
    <div>
        <label class="block text-sm mb-1">Email</label>
        <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" class="w-full border rounded px-3 py-2" required>
    </div>
    <div>
        <label class="block text-sm mb-1">Password</label>
        <input type="password" name="password" class="w-full border rounded px-3 py-2" @if(!isset($user)) required @endif>
        @isset($user)
            <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah</p>
        @endisset
    </div>
    <div>
        <label class="block text-sm mb-1">Roles</label>
        <select name="roles[]" multiple class="w-full border rounded px-3 py-2 h-32">
            @foreach($roles as $id => $name)
                <option value="{{ $id }}" @selected(in_array($id, old('roles', $selected)))>{{ $name }}</option>
            @endforeach
        </select>
        <p class="text-xs text-gray-500 mt-1">Tahan Ctrl/Cmd untuk memilih banyak</p>
    </div>
</div>

