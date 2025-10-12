<?php
// Quick local test for SPM module using SQLite database (sub‑indicator architecture)

define('LARAVEL_START', microtime(true));

putenv('APP_ENV=local');
putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=database/database.sqlite');
putenv('DB_FOREIGN_KEYS=true');

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\{Village, Building, Family, FamilyMember, MedicalRecord, SpmTarget, SpmIndicator, SpmSubIndicator};
use App\Events\MedicalRecordCreated;
use Illuminate\Support\Facades\DB;

function out($label, $value) { echo str_pad($label . ':', 30) . (is_scalar($value) ? $value : json_encode($value)) . "\n"; }

DB::statement('PRAGMA foreign_keys = ON');

// Seed minimal master data
$village = Village::firstOrCreate(['name' => 'Desa Uji', 'code' => 'V001'], ['district' => 'Kec Uji', 'regency' => 'Kab Uji', 'province' => 'Prov Uji']);
if (!$village->sequence_number) { $village->sequence_number = ((int) (Village::max('sequence_number') ?? 0)) + 1; $village->save(); }
$building = Building::firstOrCreate(['village_id' => $village->id, 'building_number' => 'B-001'], ['latitude' => 0, 'longitude' => 0]);
$family = Family::firstOrCreate(['building_id' => $building->id, 'family_number' => 'F-001'], ['head_name' => 'Kepala Keluarga', 'sequence_number_in_building' => 1]);
$member = FamilyMember::firstOrCreate(['family_id' => $family->id, 'name' => 'Ibu SPM'], ['relationship' => 'Istri', 'birth_place' => 'Uji', 'birth_date' => now()->subYears(28)->toDateString(), 'gender' => 'Perempuan', 'is_pregnant' => true]);

// Target SPM: SPM_01_K1
$year = (int) date('Y');
$main = SpmIndicator::firstOrCreate(['code' => 'SPM_01'], ['name' => 'Pelayanan Kesehatan Ibu Hamil']);
$sub = SpmSubIndicator::firstOrCreate(['code' => 'SPM_01_K1'], ['spm_indicator_id' => $main->id, 'name' => 'Cakupan kunjungan ibu hamil K1']);
SpmTarget::updateOrCreate(['year' => $year, 'village_id' => $village->id, 'spm_sub_indicator_id' => $sub->id], ['denominator_dinkes' => 10, 'target_percentage' => 80.0]);

// Rekam medis bertag sub‑indikator
$record = MedicalRecord::create(['family_member_id' => $member->id, 'visit_date' => now()->toDateString(), 'diagnosis_name' => 'Kunjungan ANC', 'spm_service_type' => 'SPM_01_K1']);
MedicalRecordCreated::dispatch($record);

// Hitung capaian
$base = FamilyMember::query()->whereHas('family.building', fn($q) => $q->where('village_id', $village->id));
$denom = (clone $base)->where('is_pregnant', true)->count();
$numer = MedicalRecord::where('spm_service_type', 'SPM_01_K1')->whereBetween('visit_date', [now()->startOfYear(), now()->endOfYear()])->whereHas('familyMember.family.building', fn($q) => $q->where('village_id', $village->id))->count();

out('denominator_riil (SPM_01_K1)', $denom);
out('numerator_riil (SPM_01_K1)', $numer);

$target = SpmTarget::where('year', $year)->where('village_id', $village->id)->where('spm_sub_indicator_id', $sub->id)->first();
$targetAbs = round($target->denominator_dinkes * ($target->target_percentage / 100));
out('target_abs (SPM_01_K1)', $targetAbs);
out('gap (SPM_01_K1)', $numer - $targetAbs);

echo "\nSelesai.\n";
