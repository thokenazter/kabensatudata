ROLE:
You are an expert Full-Stack Laravel developer. You will continue working on a Public Health Information System. The foundational SPM module (tracking 12 main indicators and their official targets) has been built.

CONTEXT:
The current system tracks 12 main SPM indicators. However, the user's detailed reporting format requires tracking and reporting on multiple granular sub-indicators for each main indicator. For example, the main indicator "Pelayanan Kesehatan Balita" must be broken down into sub-indicators like "Imunisasi BCG", "Imunisasi Polio 1", "Cakupan Vitamin A", etc., each with its own potential target.

MAIN GOAL:
Refactor the existing SPM module to support a hierarchical structure of Indicators and Sub-Indicators. This will enable granular data capture, calculation, and reporting that precisely matches the user's provided reporting formats.

Please generate the necessary code by following these phases:

Fase 1: Desain Ulang Skema Database untuk Granularitas
Kita akan memperkenalkan dua tabel baru untuk mendefinisikan hierarki SPM secara dinamis dan memodifikasi tabel target yang ada.

1.1. Buat Tabel Induk SPM (spm_indicators):
Ini akan menyimpan 12 indikator utama.

Bash

php artisan make:model SpmIndicator -m
Dalam file migration-nya, definisikan skema:

PHP

Schema::create('spm_indicators', function (Blueprint $table) {
    $table->id();
    $table->string('code')->unique()->comment('e.g., SPM_01, SPM_04');
    $table->string('name');
    $table->text('description')->nullable();
    $table->timestamps();
});
1.2. Buat Tabel Sub-Indikator SPM (spm_sub_indicators):
Ini adalah inti dari fitur baru, berisi rincian kegiatan.

Bash

php artisan make:model SpmSubIndicator -m
Dalam file migration-nya, definisikan skema:

PHP

Schema::create('spm_sub_indicators', function (Blueprint $table) {
    $table->id();
    $table->foreignId('spm_indicator_id')->constrained()->onDelete('cascade');
    $table->string('code')->unique()->comment('e.g., SPM_04_BCG, SPM_01_K4');
    $table->string('name');
    $table->text('definition')->nullable()->comment('Definisi operasional singkat');
    $table->timestamps();
});
Setelah membuat model, definisikan relasi belongsTo di SpmSubIndicator.php ke SpmIndicator dan relasi hasMany di SpmIndicator.php ke SpmSubIndicator.

1.3. Modifikasi Tabel Target (spm_targets):
Kita perlu mengubah tabel ini agar target dapat ditetapkan per sub-indikator.

Bash

php artisan make:migration ModifySpmTargetsTableForSubIndicators --table=spm_targets
Dalam file migration-nya, lakukan perubahan ini:

PHP

// Di dalam method up()
Schema::table('spm_targets', function (Blueprint $table) {
    // Hapus kolom lama yang tidak lagi relevan
    $table->dropUnique('spm_target_unique');
    $table->dropColumn(['spm_indicator_code', 'spm_indicator_name']);

    // Tambahkan foreign key ke sub_indicators
    $table->foreignId('spm_sub_indicator_id')->after('village_id')->constrained()->onDelete('cascade');

    // Buat unique constraint yang baru
    $table->unique(['year', 'village_id', 'spm_sub_indicator_id'], 'spm_target_sub_indicator_unique');
});
Fase 2: Data Seeding untuk Indikator dan Sub-Indikator
Buat sebuah seeder untuk mengisi tabel-tabel baru ini dengan data yang sesuai dari gambar laporan.

Bash

php artisan make:seeder SpmIndicatorsTableSeeder
Dalam file SpmIndicatorsTableSeeder.php, isi method run() dengan data berikut sebagai contoh:

PHP

// Contoh untuk beberapa indikator utama
$indicator1 = SpmIndicator::create(['code' => 'SPM_01', 'name' => 'Pelayanan Kesehatan Ibu Hamil']);
$indicator4 = SpmIndicator::create(['code' => 'SPM_04', 'name' => 'Pelayanan Kesehatan Balita']);
$indicator11 = SpmIndicator::create(['code' => 'SPM_11', 'name' => 'Pelayanan Kesehatan Orang Terduga TB']);

// Sub-indikator untuk Ibu Hamil (SPM_01)
$indicator1->subIndicators()->createMany([
    ['code' => 'SPM_01_K1', 'name' => 'Cakupan kunjungan ibu hamil K1'],
    ['code' => 'SPM_01_K4', 'name' => 'Cakupan kunjungan ibu hamil K4'],
    ['code' => 'SPM_01_FE1', 'name' => 'Cakupan pemberian Fe 1'],
    ['code' => 'SPM_01_FE3', 'name' => 'Cakupan pemberian Fe 3 (90 tablet)'],
]);

// Sub-indikator untuk Balita (SPM_04), sangat detail
$indicator4->subIndicators()->createMany([
    ['code' => 'SPM_04_KUNJUNGAN', 'name' => 'Cakupan kunjungan balita diposyandu (D/S)'],
    ['code' => 'SPM_04_IM_BCG', 'name' => 'BCG'],
    ['code' => 'SPM_04_IM_POLIO1', 'name' => 'Polio 1'],
    ['code' => 'SPM_04_IM_POLIO2', 'name' => 'Polio 2'],
    ['code' => 'SPM_04_IM_POLIO3', 'name' => 'Polio 3'],
    ['code' => 'SPM_04_IM_POLIO4', 'name' => 'Polio 4'],
    ['code' => 'SPM_04_IM_DPT_HB_HIB_1', 'name' => 'DPT-HB-HIB 1'],
    ['code' => 'SPM_04_IM_DPT_HB_HIB_2', 'name' => 'DPT-HB-HIB 2'],
    ['code' => 'SPM_04_IM_DPT_HB_HIB_3', 'name' => 'DPT-HB-HIB 3'],
    ['code' => 'SPM_04_IM_CAMPAK', 'name' => 'Campak/MR'],
    ['code' => 'SPM_04_IDL', 'name' => 'Imunisasi Dasar Lengkap'],
    ['code' => 'SPM_04_ASI_EKS', 'name' => 'Cakupan Bayi yang mendapat ASI Eksklusif'],
    ['code' => 'SPM_04_VIT_A', 'name' => 'Cakupan pemberian vitamin A'],
]);

// Sub-indikator untuk TB (SPM_11)
$indicator11->subIndicators()->createMany([
    ['code' => 'SPM_11_PENEMUAN', 'name' => 'Cakupan penemuan penderita TB'],
    ['code' => 'SPM_11_PENGOBATAN', 'name' => 'Cakupan pengobatan penderita sesuai standar WHO'],
    ['code' => 'SPM_11_KONTAK', 'name' => 'Cakupan pelacakan kasus kontak'],
]);
Tambahkan data untuk 12 indikator utama dan semua sub-indikatornya, lalu jalankan seeder.

Fase 3: Refactoring Logika Aplikasi
Sekarang, perbarui controller dan model untuk menggunakan struktur baru ini.

3.1. Perbarui SpmTargetController dan View-nya:
Formulir untuk membuat target sekarang harus menampilkan dropdown yang berisi daftar SpmSubIndicator, bukan lagi input manual untuk 12 item.

3.2. Refactor Total SpmController.php:
Logika di dalam method dashboard() harus diubah sepenuhnya:

Ambil semua SpmIndicator beserta relasi subIndicators-nya.

Iterasi (loop) melalui setiap SpmIndicator.

Di dalam loop tersebut, iterasi lagi melalui setiap SpmSubIndicator.

Untuk setiap sub-indicator, hitung numerator_riil-nya dengan melakukan query ke tabel medical_records dimana spm_service_type sama dengan spm_sub_indicator.code.

Hitung denominator_riil yang relevan untuk sub-indikator tersebut.

Ambil data targetnya dari tabel spm_targets yang berelasi dengan spm_sub_indicator_id.

Susun data dalam struktur array bertingkat (hierarkis) dan kirimkan ke view.

3.3. Perbarui UpdateSpmDataListener:
Listener ini sekarang tidak lagi mengupdate kolom last_..._date secara langsung. Logikanya adalah mencatat pelayanan. Pastikan spm_service_type yang disimpan di medical_records adalah kode unik dari spm_sub_indicators (misalnya: SPM_04_IM_BCG).

Fase 4: Desain Ulang Tampilan Dashboard
Perbarui spm/dashboard.blade.php untuk menampilkan data granular yang baru.

Tampilan utama tidak lagi 12 kartu. Ubah menjadi 12 bagian (sections) atau panel akordion (collapsible panels), satu untuk setiap Indikator Utama (misal: "Pelayanan Kesehatan Balita").

Di dalam setiap panel, tampilkan sebuah tabel yang merinci capaian untuk setiap sub-indikatornya.

Kolom tabel tersebut harus berisi: Nama Sub-Indikator, Sasaran Dinkes, Target %, Capaian Riil (N/D), % Capaian Riil, dan Kesenjangan (GAP).