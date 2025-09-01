<?php

namespace App\Console\Commands;

use App\Models\MedicalRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncExistingMedicalRecordsPatientData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'medical-records:sync-patient-data 
                            {--dry-run : Show what would be updated without making changes}
                            {--batch-size=100 : Number of records to process in each batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync patient identity data for existing medical records from FamilyMember data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $batchSize = (int) $this->option('batch-size');

        $this->info('Starting sync of patient data for existing medical records...');
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        // Get medical records that need patient data sync
        $recordsQuery = MedicalRecord::whereNull('patient_name')
            ->orWhereNull('patient_address')
            ->orWhereNull('patient_gender')
            ->with(['familyMember.family.building.village']);

        $totalRecords = $recordsQuery->count();
        
        if ($totalRecords === 0) {
            $this->info('No medical records need patient data sync.');
            return Command::SUCCESS;
        }

        $this->info("Found {$totalRecords} medical records that need patient data sync.");

        $progressBar = $this->output->createProgressBar($totalRecords);
        $progressBar->start();

        $processedCount = 0;
        $errorCount = 0;
        $skippedCount = 0;

        // Process records in batches
        $recordsQuery->chunk($batchSize, function ($records) use ($dryRun, &$processedCount, &$errorCount, &$skippedCount, $progressBar) {
            foreach ($records as $record) {
                try {
                    if (!$record->familyMember) {
                        $this->warn("\nSkipping record ID {$record->id}: No associated family member found");
                        $skippedCount++;
                        $progressBar->advance();
                        continue;
                    }

                    // Store original data for comparison
                    $originalData = [
                        'patient_name' => $record->patient_name,
                        'patient_address' => $record->patient_address,
                        'patient_gender' => $record->patient_gender,
                        'patient_nik' => $record->patient_nik,
                        'patient_rm_number' => $record->patient_rm_number,
                        'patient_birth_date' => $record->patient_birth_date,
                        'patient_age' => $record->patient_age,
                    ];

                    // Sync patient data
                    $record->syncPatientData();

                    // Check if there are changes
                    $hasChanges = false;
                    foreach ($originalData as $field => $originalValue) {
                        if ($record->$field != $originalValue) {
                            $hasChanges = true;
                            break;
                        }
                    }

                    if (!$hasChanges) {
                        $skippedCount++;
                        $progressBar->advance();
                        continue;
                    }

                    if (!$dryRun) {
                        // Save without triggering model events to avoid infinite loop
                        $record->saveQuietly();
                    }

                    $processedCount++;

                    if ($this->output->isVerbose()) {
                        $this->line("\nUpdated record ID {$record->id}:");
                        $this->line("  Patient: {$record->patient_name}");
                        $this->line("  Address: {$record->patient_address}");
                    }

                } catch (\Exception $e) {
                    $this->error("\nError processing record ID {$record->id}: " . $e->getMessage());
                    $errorCount++;
                }

                $progressBar->advance();
            }
        });

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info("Sync completed!");
        $this->table(
            ['Status', 'Count'],
            [
                ['Processed', $processedCount],
                ['Skipped', $skippedCount],
                ['Errors', $errorCount],
                ['Total', $totalRecords],
            ]
        );

        if ($dryRun && $processedCount > 0) {
            $this->warn("This was a dry run. Run without --dry-run to apply changes.");
        }

        return $errorCount > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
