<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\PegawaiDokumen;
use App\Models\SuratArchive;
use App\Services\Surat\SuratNumberingService;
use App\Services\Surat\SuratTugasGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuratController extends Controller
{
    public function createTugas(Request $request, SuratNumberingService $numbering)
    {
        $this->authorize('view_dashboard');
        $pegawaiList = Pegawai::orderBy('nama')->limit(200)->get(['id','nama','nip']);
        $last = $numbering->getLastNomor('SURAT_TUGAS');
        $suggest = $numbering->suggestNomor('SURAT_TUGAS');
        $defaults = [
            'nomor_surat' => $suggest,
            'dasar1' => '',
            'maksud_tugas' => '',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDay()->toDateString(),
            'kota_terbit' => 'Dobo',
            'tanggal_surat' => now()->toDateString(),
        ];
        return view('pegawai.surat.create-tugas', compact('pegawaiList', 'defaults', 'last', 'suggest'));
    }

    public function storeTugas(Request $request, SuratNumberingService $numbering, SuratTugasGenerator $generator)
    {
        $data = $request->validate([
            'pegawai_id' => ['required', 'exists:pegawai,id'],
            'nomor_surat' => ['nullable', 'string', 'max:120'],
            'dasar1' => ['nullable', 'string', 'max:2000'],
            'maksud_tugas' => ['required', 'string', 'max:2000'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'kota_terbit' => ['required', 'string', 'max:120'],
            'tanggal_surat' => ['required', 'date'],
        ]);

        $pegawai = Pegawai::findOrFail($data['pegawai_id']);

        // Assign nomor jika kosong
        if (empty($data['nomor_surat'])) {
            $data['nomor_surat'] = $numbering->assignNomor('SURAT_TUGAS', (int)date('Y', strtotime($data['tanggal_surat'])));
        }

        $relativePath = $generator->generate($pegawai, $data);

        // Simpan ke dokumen pegawai
        $pegawai->dokumen()->create([
            'jenis' => 'SURAT_TUGAS',
            'judul' => 'Surat Tugas ' . ($data['nomor_surat'] ? ('No. ' . $data['nomor_surat']) : ''),
            'file_path' => $relativePath,
            'issued_at' => $data['tanggal_surat'],
            'keterangan' => $data['maksud_tugas'] ?? null,
        ]);

        // Simpan arsip
        SuratArchive::create([
            'jenis' => 'SURAT_TUGAS',
            'nomor_surat' => $data['nomor_surat'] ?? null,
            'pegawai_id' => $pegawai->id,
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

        return redirect()->route('pegawai.surat-arsip.index')->with('status', 'Surat Tugas berhasil dibuat & diarsipkan.');
    }
}
