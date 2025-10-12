<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\PegawaiDokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardPegawaiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isSuper = $user->hasRole('super_admin');

        if ($isSuper) {
            $stats = [
                'total_pegawai' => Pegawai::count(),
                'total_dokumen' => PegawaiDokumen::count(),
                'dokumen_sk' => PegawaiDokumen::where('jenis', 'SK')->count(),
                'dokumen_ktp' => PegawaiDokumen::where('jenis', 'KTP')->count(),
            ];

            // Charts: gender, education, profession
            $genderRaw = Pegawai::selectRaw('jenis_kelamin, COUNT(*) as total')
                ->whereNotNull('jenis_kelamin')
                ->groupBy('jenis_kelamin')
                ->pluck('total', 'jenis_kelamin');
            $gender = [
                'L' => (int) ($genderRaw['L'] ?? 0),
                'P' => (int) ($genderRaw['P'] ?? 0),
            ];

            $educationRaw = Pegawai::selectRaw('pendidikan_terakhir, COUNT(*) as total')
                ->whereNotNull('pendidikan_terakhir')
                ->groupBy('pendidikan_terakhir')
                ->pluck('total', 'pendidikan_terakhir');

            $education = [
                'SMA/SMK' => (int)($educationRaw['SMA'] ?? 0) + (int)($educationRaw['SMK'] ?? 0),
                'D3' => (int)($educationRaw['D3'] ?? 0),
                'S1' => (int)($educationRaw['S1'] ?? 0),
                'S2' => (int)($educationRaw['S2'] ?? 0),
                'Profesi' => (int)($educationRaw['Profesi'] ?? 0),
            ];

            $professionRaw = Pegawai::selectRaw('profesi, COUNT(*) as total')
                ->whereNotNull('profesi')
                ->groupBy('profesi')
                ->orderBy('total', 'desc')
                ->pluck('total', 'profesi');
            // Fokus beberapa kategori utama
            $profession = [
                'Kesehatan Masyarakat' => (int)($professionRaw['Kesehatan Masyarakat'] ?? 0),
                'Perawat' => (int)($professionRaw['Perawat'] ?? 0),
                'Bidan' => (int)($professionRaw['Bidan'] ?? 0),
                'Sanitarian' => (int)($professionRaw['Sanitarian'] ?? 0),
            ];
            $others = collect($professionRaw)->except(array_keys($profession))->sum();
            if ($others > 0) {
                $profession['Lainnya'] = (int)$others;
            }

            $charts = compact('gender', 'education', 'profession');
            $myPegawai = null;
        } else {
            $myPegawai = Pegawai::firstWhere('user_id', $user->id);
            $stats = [
                'dokumen_saya' => $myPegawai ? $myPegawai->dokumen()->count() : 0,
            ];
            $charts = null;
        }

        return view('pegawai.dashboard', compact('stats', 'myPegawai', 'isSuper', 'charts'));
    }
}
