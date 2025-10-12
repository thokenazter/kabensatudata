<?php

namespace App\Events;

use App\Models\MedicalRecord;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MedicalRecordCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public MedicalRecord $medicalRecord;

    public function __construct(MedicalRecord $medicalRecord)
    {
        $this->medicalRecord = $medicalRecord;
    }
}

