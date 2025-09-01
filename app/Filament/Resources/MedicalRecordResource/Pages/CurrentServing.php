<?php
// file: app/Filament/Resources/MedicalRecordResource/Pages/CurrentServing.php

namespace App\Filament\Resources\MedicalRecordResource\Pages;

use App\Filament\Resources\MedicalRecordResource;
use App\Models\MedicalRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class CurrentServing extends Page
{
    protected static string $resource = MedicalRecordResource::class;
    protected static string $view = 'filament.resources.medical-record-resource.pages.current-serving';
    protected static ?string $title = 'Sedang Dilayani';

    // Remove navigation dari sidebar
    protected static bool $shouldRegisterNavigation = false;

    public string $role = '';

    public function mount(string $role): void
    {
        $this->role = $role;
    }

    public function getTitle(): string | Htmlable
    {
        $roleNames = [
            'nurse' => 'Perawat',
            'doctor' => 'Dokter', 
            'pharmacy' => 'Apotek'
        ];
        
        $roleName = $roleNames[$this->role] ?? ucfirst($this->role);
        return "Sedang Dilayani - {$roleName}";
    }

    protected function getViewData(): array
    {
        $today = now()->format('Y-m-d');
        
        // Get current serving patient for the specific role
        $statusMap = [
            'nurse' => 'pending_nurse',
            'doctor' => 'pending_doctor',
            'pharmacy' => 'pending_pharmacy'
        ];

        $status = $statusMap[$this->role] ?? 'pending_nurse';
        
        $currentServing = MedicalRecord::where('workflow_status', $status)
            ->whereNotNull('current_role_handler')
            ->whereDate('visit_date', $today)
            ->with(['currentHandler', 'familyMember'])
            ->first();

        // Get next patients in queue
        $nextQueue = MedicalRecord::where('workflow_status', $status)
            ->whereNull('current_role_handler')
            ->whereDate('visit_date', $today)
            ->orderBy('priority_level', 'desc')
            ->orderBy('queue_number')
            ->limit(5)
            ->with('familyMember')
            ->get();

        return [
            'role' => $this->role,
            'currentServing' => $currentServing,
            'nextQueue' => $nextQueue,
            'currentTime' => now(),
        ];
    }
}