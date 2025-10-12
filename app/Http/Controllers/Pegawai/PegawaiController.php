<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PegawaiController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = Auth::user();
        if (!$user->hasRole('super_admin')) {
            $mine = Pegawai::firstWhere('user_id', $user->id);
            if ($mine) {
                return redirect()->route('pegawai.employees.edit', $mine);
            }
            return redirect()->route('pegawai.employees.create');
        }

        $q = trim((string) $request->query('q', ''));
        $pegawai = Pegawai::query()
            ->when($q !== '', function ($w) use ($q) {
                $w->where('nama', 'like', "%{$q}%")
                  ->orWhere('nip', 'like', "%{$q}%")
                  ->orWhere('jabatan', 'like', "%{$q}%")
                  ->orWhere('pangkat_gol', 'like', "%{$q}%");
            })
            ->orderBy('nama')
            ->paginate(15)
            ->withQueryString();

        return view('pegawai.employees.index', compact('pegawai', 'q'));
    }

    public function create(): View
    {
        $user = Auth::user();
        $pegawai = new Pegawai([
            'user_id' => $user->hasRole('super_admin') ? null : $user->id,
        ]);
        $users = [];
        if ($user->hasRole('super_admin')) {
            $users = User::orderBy('name')->limit(500)->get(['id','name','email']);
        }
        return view('pegawai.employees.create', compact('pegawai', 'users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $data = $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
            'nama' => ['required', 'string', 'max:255'],
            'nip' => ['nullable', 'string', 'max:100'],
            'nik' => ['nullable', 'string', 'max:30'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:255'],
            'pangkat_gol' => ['nullable', 'string', 'max:50'],
            'pendidikan_terakhir' => ['required', 'string', 'max:50'],
            'profesi' => ['required', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'alamat' => ['nullable', 'string'],
            'no_hp' => ['nullable', 'string', 'max:30'],
            'foto' => ['nullable', 'image', 'max:5120'], // 5 MB
            'ktp' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'], // 10 MB
        ]);

        if (!$user->hasRole('super_admin')) {
            $data['user_id'] = $user->id;
        }

        $pegawai = Pegawai::create(collect($data)->except(['foto', 'ktp'])->toArray());

        // Handle uploads
        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store("pegawai/{$pegawai->id}", 'public');
            $pegawai->foto_path = $path;
        }
        if ($request->hasFile('ktp')) {
            $path = $request->file('ktp')->store("pegawai/{$pegawai->id}", 'public');
            $pegawai->ktp_path = $path;
        }
        $pegawai->save();

        return redirect()->route('pegawai.employees.edit', $pegawai)
            ->with('status', 'Data pegawai berhasil dibuat');
    }

    public function edit(Pegawai $employee): View
    {
        $this->authorizePegawai($employee);
        $users = [];
        if (Auth::user()->hasRole('super_admin')) {
            $users = User::orderBy('name')->limit(500)->get(['id','name','email']);
        }
        return view('pegawai.employees.edit', ['pegawai' => $employee, 'users' => $users]);
    }

    public function update(Request $request, Pegawai $employee): RedirectResponse
    {
        $this->authorizePegawai($employee);
        $data = $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
            'nama' => ['required', 'string', 'max:255'],
            'nip' => ['nullable', 'string', 'max:100'],
            'nik' => ['nullable', 'string', 'max:30'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:255'],
            'pangkat_gol' => ['nullable', 'string', 'max:50'],
            'pendidikan_terakhir' => ['required', 'string', 'max:50'],
            'profesi' => ['required', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'alamat' => ['nullable', 'string'],
            'no_hp' => ['nullable', 'string', 'max:30'],
            'foto' => ['nullable', 'image', 'max:5120'], // 5 MB
            'ktp' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'], // 10 MB
        ]);

        // Only super_admin can change ownership
        $fill = collect($data)->except(['foto', 'ktp']);
        if (!Auth::user()->hasRole('super_admin')) {
            $fill = $fill->except(['user_id']);
        }
        $employee->fill($fill->toArray());

        if ($request->hasFile('foto')) {
            if ($employee->foto_path) {
                Storage::disk('public')->delete($employee->foto_path);
            }
            $employee->foto_path = $request->file('foto')->store("pegawai/{$employee->id}", 'public');
        }
        if ($request->hasFile('ktp')) {
            if ($employee->ktp_path) {
                Storage::disk('public')->delete($employee->ktp_path);
            }
            $employee->ktp_path = $request->file('ktp')->store("pegawai/{$employee->id}", 'public');
        }

        $employee->save();

        return back()->with('status', 'Data pegawai berhasil diperbarui');
    }

    public function destroy(Pegawai $employee): RedirectResponse
    {
        $this->authorizePegawai($employee, onlyOwner: false);
        $employee->delete();
        return redirect()->route('pegawai.employees.index')->with('status', 'Data pegawai dihapus');
    }

    protected function authorizePegawai(Pegawai $pegawai, bool $onlyOwner = true): void
    {
        $user = Auth::user();
        if ($user->hasRole('super_admin')) {
            return; // allowed by Gate::before too
        }
        if ($onlyOwner && $pegawai->user_id === $user->id) {
            return;
        }
        abort(403);
    }
}
