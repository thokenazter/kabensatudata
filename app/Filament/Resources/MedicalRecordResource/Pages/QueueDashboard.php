<?php
// file: app/Filament/Resources/MedicalRecordResource/Pages/QueueDashboard.php

namespace App\Filament\Resources\MedicalRecordResource\Pages;

use App\Filament\Resources\MedicalRecordResource;
use App\Models\MedicalRecord;
use App\Models\User;
use Filament\Resources\Pages\Page;
use Filament\Actions\Action;
use Illuminate\Contracts\Support\Htmlable;

class QueueDashboard extends Page
{
    protected static string $resource = MedicalRecordResource::class;
    protected static string $view = 'filament.resources.medical-record-resource.pages.queue-dashboard';
    protected static ?string $title = 'Dashboard Antrian Hari Ini';
    protected static ?string $navigationLabel = 'Dashboard Antrian';
    protected static ?string $navigationIcon = 'heroicon-o-tv';

    public function getTitle(): string | Htmlable
    {
        return 'Dashboard Antrian - ' . now()->format('d F Y');
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->action(fn() => $this->redirect(request()->header('Referer'))),

            Action::make('queue_display')
                ->label('Tampilan TV')
                ->icon('heroicon-o-tv')
                ->color('success')
                ->url(route('filament.admin.resources.medical-records.queue-display'))
                ->openUrlInNewTab(),
        ];
    }

    protected function getViewData(): array
    {
        $today = now()->format('Y-m-d');

        // Queue Statistics
        $queueStats = [
            'pending_registration' => MedicalRecord::whereDate('visit_date', $today)
                ->where('workflow_status', 'pending_registration')
                ->count(),
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
            'total_today' => MedicalRecord::whereDate('visit_date', $today)->count(),
        ];

        // Current Serving
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

        // Next in Queue
        $nextQueue = [
            'nurse' => MedicalRecord::where('workflow_status', 'pending_nurse')
                ->whereNull('current_role_handler')
                ->whereDate('visit_date', $today)
                ->orderBy('queue_number')
                ->limit(5)
                ->get(),
            'doctor' => MedicalRecord::where('workflow_status', 'pending_doctor')
                ->whereNull('current_role_handler')
                ->whereDate('visit_date', $today)
                ->orderBy('queue_number')
                ->limit(5)
                ->get(),
            'pharmacy' => MedicalRecord::where('workflow_status', 'pending_pharmacy')
                ->whereNull('current_role_handler')
                ->whereDate('visit_date', $today)
                ->orderBy('queue_number')
                ->limit(5)
                ->get(),
        ];

        // Staff Performance Today
        $staffStats = User::whereHas('handledRecordsToday', function ($query) use ($today) {
            $query->whereDate('visit_date', $today);
        })
            ->withCount([
                'handledRecordsToday as completed_today' => function ($query) use ($today) {
                    $query->whereDate('visit_date', $today)
                        ->where('workflow_status', 'completed');
                },
                'handledRecordsToday as active_now' => function ($query) use ($today) {
                    $query->whereDate('visit_date', $today)
                        ->where('current_role_handler', auth()->id())
                        ->whereNot('workflow_status', 'completed');
                }
            ])
            ->get();

        // Priority Patients
        $priorityPatients = MedicalRecord::whereDate('visit_date', $today)
            ->whereIn('priority_level', ['urgent', 'emergency'])
            ->whereNot('workflow_status', 'completed')
            ->orderBy('priority_level', 'desc')
            ->orderBy('queue_number')
            ->get();

        return [
            'queueStats' => $queueStats,
            'currentServing' => $currentServing,
            'nextQueue' => $nextQueue,
            'staffStats' => $staffStats,
            'priorityPatients' => $priorityPatients,
            'today' => $today,
        ];
    }
}
