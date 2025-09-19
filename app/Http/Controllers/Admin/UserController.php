<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:view_any_user'])->only(['index']);
        $this->middleware(['permission:create_user'])->only(['create', 'store']);
        $this->middleware(['permission:update_user'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_user'])->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = User::query();
        if ($q = $request->input('q')) {
            $query->where(function ($qb) use ($q) {
                $qb->where('name', 'like', "%{$q}%")
                   ->orWhere('email', 'like', "%{$q}%");
            });
        }
        $users = $query->orderBy('name')->paginate(15)->withQueryString();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->pluck('name', 'id');
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        if (!empty($data['roles'])) {
            $user->syncRoles(Role::whereIn('id', $data['roles'])->pluck('name')->toArray());
        }

        return redirect()->route('panel.users.index')->with('success', 'User berhasil dibuat');
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->pluck('name', 'id');
        $userRoles = $user->roles()->pluck('id')->toArray();
        return view('admin.users.edit', compact('user', 'roles', 'userRoles'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        $update = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];
        if (!empty($data['password'])) {
            $update['password'] = bcrypt($data['password']);
        }
        $user->update($update);

        $user->syncRoles(Role::whereIn('id', $data['roles'] ?? [])->pluck('name')->toArray());

        return redirect()->route('panel.users.index')->with('success', 'User berhasil diperbarui');
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->withErrors(['Anda tidak dapat menghapus akun Anda sendiri.']);
        }
        $user->delete();
        return redirect()->route('panel.users.index')->with('success', 'User berhasil dihapus');
    }
}

