<?php
// file: app/Filament/Resources/MedicalRecordResource/Pages/QueueDisplay.php

namespace App\Filament\Resources\MedicalRecordResource\Pages;

use App\Filament\Resources\MedicalRecordResource;
use App\Models\MedicalRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class QueueDisplay extends Page
{
    protected static string $resource = MedicalRecordResource::class;
    protected static string $view = 'filament.resources.medical-record-resource.pages.queue-display';
    protected static ?string $title = 'Display Antrian TV';

    // Remove navigation dari sidebar
    protected static bool $shouldRegisterNavigation = false;

    public function getTitle(): string | Htmlable
    {
        return 'Antrian Klinik - ' . now()->format('d F Y');
    }

    protected function getViewData(): array
    {
        $today = now()->format('Y-m-d');

        // Current serving patients
        $currentServing = [
            'nurse' => MedicalRecord::where('workflow_status', 'pending_nurse')
                ->whereNotNull('current_role_handler')
                ->whereDate('visit_date', $today)
                ->with('currentHandler')
                ->orderBy('nurse_start_time')
                ->first(),
            'doctor' => MedicalRecord::where('workflow_status', 'pending_doctor')
                ->whereNotNull('current_role_handler')
                ->whereDate('visit_date', $today)
                ->with('currentHandler')
                ->orderBy('doctor_start_time')
                ->first(),
            'pharmacy' => MedicalRecord::where('workflow_status', 'pending_pharmacy')
                ->whereNotNull('current_role_handler')
                ->whereDate('visit_date', $today)
                ->with('currentHandler')
                ->orderBy('pharmacy_start_time')
                ->first(),
        ];

        // Next 5 patients in queue for each role
        $nextQueue = [
            'nurse' => MedicalRecord::where('workflow_status', 'pending_nurse')
                ->whereNull('current_role_handler')
                ->whereDate('visit_date', $today)
                ->orderBy('priority_level', 'desc') // Emergency first
                ->orderBy('queue_number')
                ->limit(5)
                ->get(),
            'doctor' => MedicalRecord::where('workflow_status', 'pending_doctor')
                ->whereNull('current_role_handler')
                ->whereDate('visit_date', $today)
                ->orderBy('priority_level', 'desc')
                ->orderBy('queue_number')
                ->limit(5)
                ->get(),
            'pharmacy' => MedicalRecord::where('workflow_status', 'pending_pharmacy')
                ->whereNull('current_role_handler')
                ->whereDate('visit_date', $today)
                ->orderBy('priority_level', 'desc')
                ->orderBy('queue_number')
                ->limit(5)
                ->get(),
        ];

        // Queue statistics
        $queueStats = [
            'pending_nurse' => MedicalRecord::whereDate('visit_date', $today)
                ->where('workflow_status', 'pending_nurse')
                ->count(),
            'pending_doctor' => MedicalRecord::whereDate('visit_date', $today)
                ->where('workflow_status', 'pending_doctor')
                ->count(),
            'pending_pharmacy' => MedicalRecord::whereDate('visit_date', $today)
                ->where('workflow_status', 'pending_pharmacy')
                ->count(),
            'completed' => MedicalRecord::whereDate('visit_date', $today)
                ->where('workflow_status', 'completed')
                ->count(),
        ];

        // Priority alerts
        $priorityAlerts = MedicalRecord::whereDate('visit_date', $today)
            ->whereIn('priority_level', ['urgent', 'emergency'])
            ->whereNot('workflow_status', 'completed')
            ->orderBy('priority_level', 'desc')
            ->orderBy('queue_number')
            ->limit(10)
            ->get();

        // Estimated wait times
        $avgServiceTimes = [
            'nurse' => 15,
            'doctor' => 20,
            'pharmacy' => 10
        ];

        $estimatedWaits = [
            'nurse' => $queueStats['pending_nurse'] * $avgServiceTimes['nurse'],
            'doctor' => $queueStats['pending_doctor'] * $avgServiceTimes['doctor'],
            'pharmacy' => $queueStats['pending_pharmacy'] * $avgServiceTimes['pharmacy'],
        ];

        return [
            'currentServing' => $currentServing,
            'nextQueue' => $nextQueue,
            'queueStats' => $queueStats,
            'priorityAlerts' => $priorityAlerts,
            'estimatedWaits' => $estimatedWaits,
            'currentTime' => now(),
        ];
    }
}
