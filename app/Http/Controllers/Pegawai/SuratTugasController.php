<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\PegawaiDokumen;
use App\Models\SuratArchive;
use App\Services\Surat\SuratTugasGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SuratTugasController extends Controller
{
    public function create(Pegawai $employee)
    {
        $this->authorizePegawai($employee);
        // get last number for SURAT_TUGAS
        $lastNomor = SuratArchive::where('jenis', 'SURAT_TUGAS')
            ->whereNotNull('nomor_surat')
            ->latest('id')
            ->value('nomor_surat');
        $suggestNomor = $this->suggestNextNomor($lastNomor, now()->year);
        $defaults = [
            'nomor_surat' => $suggestNomor,
            'dasar1' => '',
            'maksud_tugas' => '',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDay()->toDateString(),
            'kota_terbit' => 'Dobo',
            'tanggal_surat' => now()->toDateString(),
        ];
        return view('pegawai.surat-tugas.create', [
            'pegawai' => $employee,
            'defaults' => $defaults,
            'lastNomor' => $lastNomor,
            'suggestNomor' => $suggestNomor,
        ]);
    }

    public function store(Request $request, Pegawai $employee, SuratTugasGenerator $generator)
    {
        $this->authorizePegawai($employee);
        $data = $request->validate([
            'nomor_surat' => ['nullable', 'string', 'max:120'],
            'dasar1' => ['nullable', 'string', 'max:2000'],
            'maksud_tugas' => ['required', 'string', 'max:2000'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'kota_terbit' => ['required', 'string', 'max:120'],
            'tanggal_surat' => ['required', 'date'],
        ]);

        $relativePath = $generator->generate($employee, $data); // e.g., surat/SuratTugas-...

        // Simpan ke dokumen pegawai
        $doc = $employee->dokumen()->create([
            'jenis' => 'SURAT_TUGAS',
            'judul' => 'Surat Tugas ' . ($data['nomor_surat'] ? ('No. ' . $data['nomor_surat']) : ''),
            'file_path' => $relativePath,
            'issued_at' => $data['tanggal_surat'],
            'keterangan' => $data['maksud_tugas'] ?? null,
        ]);

        // Simpan ke arsip surat (E-Arsip)
        SuratArchive::create([
            'jenis' => 'SURAT_TUGAS',
            'nomor_surat' => $data['nomor_surat'] ?? null,
            'pegawai_id' => $employee->id,
            'perihal' => $data['maksud_tugas'] ?? null,
            'issued_at' => $data['tanggal_surat'],
            'file_path' => $relativePath,
            'created_by' => Auth::id(),
            'meta' => [
                'kota_terbit' => $data['kota_terbit'] ?? null,
                'dasar1' => $data['dasar1'] ?? null,
                'tanggal_mulai' => $data['tanggal_mulai'] ?? null,
                'tanggal_selesai' => $data['tanggal_selesai'] ?? null,
            ],
        ]);

        return redirect()->route('pegawai.employees.documents.index', $employee)
            ->with('status', 'Surat Tugas berhasil dibuat.');
    }

    protected function authorizePegawai(Pegawai $pegawai): void
    {
        $user = Auth::user();
        if ($user->hasRole('super_admin')) return;
        if ($pegawai->user_id === $user->id) return;
        abort(403);
    }

    protected function suggestNextNomor(?string $last, int $year): ?string
    {
        if (!$last) return null;
        // Ambil pola nomor: prefix/SEQ/YEAR (contoh 800.1.11.1/004/2025)
        if (preg_match('#^(.*?)/(\d+)/(\d{4})(.*)$#', $last, $m)) {
            [$all, $prefix, $seq, $yr, $tail] = $m;
            $len = strlen($seq);
            $next = (int)$seq + 1;
            $nextSeq = str_pad((string)$next, $len, '0', STR_PAD_LEFT);
            $nextYear = (string)$year;
            return $prefix . '/' . $nextSeq . '/' . $nextYear . $tail;
        }
        return $last; // fallback: tampilkan terakhir saja
    }
}
