Plaintext

ROLE:
You are an expert Full-Stack Laravel developer specializing in Public Health Information Systems (Sistem Informasi Kesehatan). Your primary mission is to implement a new, comprehensive module for monitoring Standar Pelayanan Minimal (SPM) Bidang Kesehatan.

This implementation must be integrated into an existing Laravel application that already has a well-structured database with models like `FamilyMember`, `MedicalRecord`, `Family`, and `Village`.

The final module must fulfill two core objectives:
1.  Calculate SPM achievements in **real-time** based on the application's internal data (`FamilyMember` for denominators, `MedicalRecord` for numerators).
2.  Allow users to input **official annual targets** from the District Health Office (Dinas Kesehatan) and display a comparison between real-time achievement and these official targets.

Please generate all the necessary code, following Laravel best practices. Structure your response into the distinct phases outlined below.

---

### **Fase 1: Database & Model Setup**

This phase establishes the complete database schema required for the module.

**1.1. Create `SpmTarget` Model and Migration:**
Generate a model and migration to store the official annual targets.
```bash
php artisan make:model SpmTarget -m
In the generated migration file for spm_targets, define the schema:

PHP

Schema::create('spm_targets', function (Blueprint $table) {
    $table->id();
    $table->year('year');
    $table->foreignId('village_id')->nullable()->constrained()->onDelete('cascade');
    $table->string('spm_indicator_code')->comment('e.g., SPM_01_IBU_HAMIL');
    $table->string('spm_indicator_name');
    $table->integer('denominator_dinkes')->comment('Official denominator from Dinkes');
    $table->decimal('target_percentage', 5, 2)->comment('e.g., 90.00 for 90%');
    $table->text('notes')->nullable();
    $table->timestamps();
    $table->unique(['year', 'village_id', 'spm_indicator_code'], 'spm_target_unique');
});
1.2. Modify family_members Table:
Generate a migration to add new columns for detailed SPM tracking.

Bash

php artisan make:migration AddSpmFieldsToFamilyMembersTable --table=family_members
In the migration's up() method, add the following columns:

PHP

$table->boolean('has_diabetes_mellitus')->default(false)->after('takes_hypertension_medication_regularly');
$table->boolean('takes_dm_medication_regularly')->default(false)->after('has_diabetes_mellitus');
$table->boolean('has_mental_disorder')->default(false)->after('takes_dm_medication_regularly');
$table->boolean('takes_mental_disorder_medication_regularly')->default(false)->after('has_mental_disorder');
$table->boolean('is_at_risk_for_hiv')->default(false)->after('takes_mental_disorder_medication_regularly');
$table->date('last_anc_visit_date')->nullable();
$table->date('last_maternity_service_date')->nullable();
$table->date('last_neonatal_visit_date')->nullable();
$table->date('last_child_growth_monitoring_date')->nullable();
$table->date('last_school_screening_date')->nullable();
$table->date('last_productive_age_screening_date')->nullable();
$table->date('last_elderly_screening_date')->nullable();
$table->date('last_hiv_screening_date')->nullable();
$table->date('last_tb_screening_date')->nullable();
1.3. Modify medical_records Table:
Generate a migration to add a service type tag.

Bash

php artisan make:migration AddSpmServiceTypeToMedicalRecordsTable --table=medical_records
In the migration's up() method, add the column:

PHP

$table->string('spm_service_type')->nullable()->index()->after('therapy');
Fase 2: Management CRUD for Official Targets
Create the interface for users to manage the annual targets.

2.1. Generate Controller, Routes, and Views:

Bash

php artisan make:controller SpmTargetController --resource
Add the resource route in routes/web.php:

PHP

use App\Http\Controllers\SpmTargetController;
Route::resource('/spm/targets', SpmTargetController::class)->middleware('auth');
Generate Blade views for this resource (index, create, edit). The create.blade.php view must be a form that allows a user to select a year and village (optional), and then provides 12 rows to input the denominator_dinkes and target_percentage for each SPM indicator.

Fase 3: Core SPM Logic & Dashboard
Develop the main controller that calculates and displays SPM achievements.

3.1. Generate SpmController:

Bash

php artisan make:controller SpmController
Add its route in routes/web.php:

PHP

use App\Http\Controllers\SpmController;
Route::get('/spm/dashboard', [SpmController::class, 'dashboard'])->name('spm.dashboard')->middleware('auth');
3.2. Implement dashboard() Method Logic:
Inside SpmController.php, the dashboard() method must perform these steps:

Get filter inputs: year (default to current year) and village_id (optional).

Calculate Real-time Achievement for all 12 indicators by querying FamilyMember (for denominators) and using the last_..._date columns (for numerators).

Fetch Official Targets for the selected filters from the SpmTarget model.

Merge & Enrich Data: For each indicator, create a comprehensive data structure containing:

name: The indicator name.

numerator_riil: Real-time numerator.

denominator_riil: Real-time denominator.

percentage_riil: Real-time achievement percentage.

denominator_dinkes: Official denominator from SpmTarget.

target_percentage: Official target percentage from SpmTarget.

target_absolute: The calculated absolute target (denominator_dinkes * target_percentage / 100).

gap: The calculated gap (numerator_riil - target_absolute).

Pass the final array of 12 enriched indicators to a view named spm.dashboard.

Fase 4: UI/Blade View for Dashboard
Create the main dashboard view using TailwindCSS.

4.1. Create dashboard.blade.php:
In resources/views/spm/dashboard.blade.php, implement the following:

A title: "Dashboard Capaian SPM Kesehatan".

Filter controls for Year and Village.

A responsive grid layout for 12 "Indicator Cards".

Each Indicator Card must clearly display:

Indicator Title.

Capaian Riil (Data Aplikasi): Show numerator_riil / denominator_riil and percentage_riil with a progress bar.

Target Dinkes: Show Sasaran: {denominator_dinkes} and Target: {target_percentage}%.

Analisis Kesenjangan: Show the gap value prominently (e.g., "Kurang: {gap} orang" or "Tercapai: +{gap} orang") with conditional red/green coloring.

Fase 5: Automation via Event & Listener
Automate data updates to ensure data integrity.

5.1. Generate Event and Listener:

Bash

php artisan make:event MedicalRecordCreated
php artisan make:listener UpdateSpmDataListener --event=MedicalRecordCreated
5.2. Configure Event & Listener:

In MedicalRecordCreated.php, accept a MedicalRecord object in the constructor.

In EventServiceProvider.php, register the listener.

In UpdateSpmDataListener.php, implement the handle() method. Use a switch statement on the spm_service_type of the medical record to update the correct last_..._date field on the associated FamilyMember model and save it.

In your MedicalRecordController's store method, after saving a new record, dispatch the event: MedicalRecordCreated::dispatch($medicalRecord);.

Please generate the code for each required file (migrations, controllers, models, event, listener, and Blade view) in separate, clearly marked blocks. Ensure the code is clean, efficient, well-commented, and follows Laravel conventions.