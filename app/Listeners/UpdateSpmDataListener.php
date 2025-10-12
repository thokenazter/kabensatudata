<?php

namespace App\Listeners;

use App\Events\MedicalRecordCreated;

class UpdateSpmDataListener
{
    public function handle(MedicalRecordCreated $event): void
    {
        // Dalam arsitektur baru, tidak perlu memperbarui kolom last_* di FamilyMember.
        // Pastikan saja spm_service_type terisi kode sub-indikator yang valid (jika ada).
        $record = $event->medicalRecord;
        if (!$record->spm_service_type) return;
        // Validasi ringan: pastikan kodenya ada di master sub-indikator.
        $exists = \App\Models\SpmSubIndicator::where('code', $record->spm_service_type)->exists();
        if (!$exists) {
            // Jika kode tidak valid, kosongkan agar tidak mengganggu laporan.
            $record->spm_service_type = null;
            $record->save();
        }
    }
}
