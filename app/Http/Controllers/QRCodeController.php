<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\FamilyMember;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRCodeController extends Controller
{
    /**
     * Generate QR Code untuk kartu keluarga
     *
     * @param Family $family
     * @return \Illuminate\Http\Response
     */
    public function familyCard(Family $family)
    {
        // Cache QR Code selama 1 bulan (jarang berubah)
        $qrcode = Cache::remember('family.qrcode.' . $family->id, now()->addMonth(), function () use ($family) {
            // Generate URL untuk kartu keluarga
            $url = route('families.card', $family);

            // Generate QR Code dengan logo (opsional)
            return QrCode::format('png')
                ->size(300)
                ->errorCorrection('H')
                ->generate($url);
        });

        // Return sebagai image response
        return response($qrcode)->header('Content-Type', 'image/png');
    }

    /**
     * Generate QR Code untuk halaman anggota keluarga
     *
     * @param FamilyMember $familyMember
     * @return \Illuminate\Http\Response
     */
    public function familyMember(FamilyMember $familyMember)
    {
        // Cache QR Code selama 1 bulan (jarang berubah)
        $qrcode = Cache::remember('member.qrcode.' . $familyMember->id, now()->addMonth(), function () use ($familyMember) {
            // Generate URL untuk halaman anggota keluarga
            $url = route('family-members.show', $familyMember);

            // Generate QR Code
            return QrCode::format('png')
                ->size(300)
                ->errorCorrection('H')
                ->generate($url);
        });

        // Return sebagai image response
        return response($qrcode)->header('Content-Type', 'image/png');
    }

    /**
     * Generate halaman yang menampilkan QR Code untuk keluarga tertentu
     *
     * @param Family $family
     * @return \Illuminate\View\View
     */
    public function showFamilyQrPage(Family $family)
    {
        // Cache QR Code selama 1 bulan (jarang berubah)
        $qrCodeImage = Cache::remember('family.qrcode.base64.' . $family->id, now()->addMonth(), function () use ($family) {
            // Generate URL untuk kartu keluarga
            $url = route('families.card', $family);

            // Generate QR Code untuk ditampilkan di halaman
            return base64_encode(QrCode::format('png')
                ->size(300)
                ->errorCorrection('H')
                ->generate($url));
        });

        // Hindari N+1 Query dengan eager loading
        $family->load(['members', 'building.village']);

        $url = route('families.card', $family);

        return view('families.qrcode', [
            'family' => $family,
            'qrCodeImage' => $qrCodeImage,
            'url' => $url
        ]);
    }

    /**
     * Generate halaman untuk mencetak banyak QR code (batch printing)
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function batchPrintQrCodes(Request $request)
    {
        // Filter berdasarkan desa, bangunan, dll jika disediakan di parameter
        $villageId = $request->input('village_id');
        $buildingId = $request->input('building_id');

        // Buat cache key berdasarkan parameter
        $cacheKey = 'batch.qrcode.' . $villageId . '.' . $buildingId;

        // Cache hasil selama 24 jam
        $families = Cache::remember($cacheKey, now()->addDay(), function () use ($villageId, $buildingId) {
            // Query untuk families berdasarkan filter
            $query = Family::with(['building.village', 'members']);

            if ($villageId) {
                $query->whereHas('building', function ($q) use ($villageId) {
                    $q->where('village_id', $villageId);
                });
            }

            if ($buildingId) {
                $query->where('building_id', $buildingId);
            }

            // Ambil data families dan generate QR code untuk masing-masing
            return $query->get()->map(function ($family) {
                $url = route('families.card', $family);

                return [
                    'family' => $family,
                    'qrCodeImage' => base64_encode(QrCode::format('png')
                        ->size(200)
                        ->errorCorrection('H')
                        ->generate($url)),
                    'url' => $url
                ];
            });
        });

        return view('families.batch-qrcode', [
            'families' => $families
        ]);
    }

    /**
     * Generate QR Code untuk satu keluarga
     *
     * @param Family $family
     * @return \Illuminate\View\View
     */
    public function singleFamilyQrCode(Family $family)
    {
        // Load relasi yang diperlukan dengan eager loading
        $family->load(['members', 'building.village']);

        // Cache QR Code selama 1 bulan (jarang berubah)
        $qrCodeBase64 = Cache::remember('family.qrcode.single.' . $family->id, now()->addMonth(), function () use ($family) {
            // Generate URL untuk kartu keluarga
            $url = route('families.card', $family);

            // Generate QR Code
            $qrCodeImage = QrCode::format('png')
                ->size(300)
                ->errorCorrection('H')
                ->generate($url);

            // Konversi ke base64 untuk tampilan di halaman
            return base64_encode($qrCodeImage);
        });

        $url = route('families.card', $family);

        return view('families.single-qrcode', [
            'family' => $family,
            'qrCodeImage' => $qrCodeBase64,
            'url' => $url
        ]);
    }

    /**
     * Generate QR Code untuk anggota keluarga
     *
     * @param FamilyMember $familyMember
     * @return \Illuminate\View\View
     */
    public function familyMemberQrCode(FamilyMember $familyMember)
    {
        // Load relasi yang diperlukan dengan eager loading
        $familyMember->load(['family.building.village', 'family.members']);

        // Cache QR Code selama 1 bulan (jarang berubah)
        $qrCodeBase64 = Cache::remember('member.qrcode.page.' . $familyMember->id, now()->addMonth(), function () use ($familyMember) {
            // Generate URL untuk halaman anggota keluarga
            $url = route('family-members.show', $familyMember);

            // Generate QR Code
            $qrCodeImage = QrCode::format('png')
                ->size(300)
                ->errorCorrection('H')
                ->generate($url);

            // Konversi ke base64 untuk tampilan di halaman
            return base64_encode($qrCodeImage);
        });

        $url = route('family-members.show', $familyMember);

        return view('families.member-qrcode', [
            'familyMember' => $familyMember,
            'family' => $familyMember->family, // Tambahkan objek family juga
            'qrCodeImage' => $qrCodeBase64,
            'url' => $url
        ]);
    }
}
