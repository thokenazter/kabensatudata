<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\PegawaiDokumen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PegawaiDokumenController extends Controller
{
    protected function authorizePegawai(Pegawai $pegawai): void
    {
        $user = Auth::user();
        if ($user->hasRole('super_admin')) return;
        if ($pegawai->user_id === $user->id) return;
        abort(403);
    }

    public function index(Pegawai $employee): View
    {
        $this->authorizePegawai($employee);
        $dokumen = $employee->dokumen()->latest()->paginate(20);
        return view('pegawai.documents.index', compact('employee', 'dokumen'));
    }

    public function create(Pegawai $employee): View
    {
        $this->authorizePegawai($employee);
        $document = new PegawaiDokumen();
        return view('pegawai.documents.create', compact('employee', 'document'));
    }

    public function store(Request $request, Pegawai $employee): RedirectResponse
    {
        $this->authorizePegawai($employee);
        $data = $request->validate([
            'jenis' => ['required', 'string', 'max:30'],
            'judul' => ['nullable', 'string', 'max:255'],
            'issued_at' => ['nullable', 'date'],
            'keterangan' => ['nullable', 'string'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:51200'], // 50 MB
        ]);

        $path = $request->file('file')->store("pegawai/{$employee->id}/dokumen", 'public');
        $doc = $employee->dokumen()->create([
            'jenis' => strtoupper($data['jenis']),
            'judul' => $data['judul'] ?? null,
            'issued_at' => $data['issued_at'] ?? null,
            'keterangan' => $data['keterangan'] ?? null,
            'file_path' => $path,
        ]);

        return redirect()->route('pegawai.employees.documents.index', $employee)
            ->with('status', 'Dokumen berhasil diunggah');
    }

    public function edit(Pegawai $employee, PegawaiDokumen $document): View
    {
        $this->authorizePegawai($employee);
        abort_unless($document->pegawai_id === $employee->id, 404);
        return view('pegawai.documents.edit', compact('employee', 'document'));
    }

    public function update(Request $request, Pegawai $employee, PegawaiDokumen $document): RedirectResponse
    {
        $this->authorizePegawai($employee);
        abort_unless($document->pegawai_id === $employee->id, 404);
        $data = $request->validate([
            'jenis' => ['required', 'string', 'max:30'],
            'judul' => ['nullable', 'string', 'max:255'],
            'issued_at' => ['nullable', 'date'],
            'keterangan' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:51200'], // 50 MB
        ]);

        if ($request->hasFile('file')) {
            Storage::disk('public')->delete($document->file_path);
            $document->file_path = $request->file('file')->store("pegawai/{$employee->id}/dokumen", 'public');
        }

        $document->jenis = strtoupper($data['jenis']);
        $document->judul = $data['judul'] ?? null;
        $document->issued_at = $data['issued_at'] ?? null;
        $document->keterangan = $data['keterangan'] ?? null;
        $document->save();

        return back()->with('status', 'Dokumen berhasil diperbarui');
    }

    public function destroy(Pegawai $employee, PegawaiDokumen $document): RedirectResponse
    {
        $this->authorizePegawai($employee);
        abort_unless($document->pegawai_id === $employee->id, 404);
        Storage::disk('public')->delete($document->file_path);
        $document->delete();
        return redirect()->route('pegawai.employees.documents.index', $employee)->with('status', 'Dokumen dihapus');
    }
}
