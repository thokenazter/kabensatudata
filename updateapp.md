Bisa. Secara fungsional semuanya dapat dipindah ke panel kustom (Blade/Livewire) tanpa mengubah logika bisnis dan data. Di repo ini, beberapa fitur non‑Filament sudah ada (dashboard, analysis, medical records, map), jadi migrasinya realistis dilakukan bertahap.

Temuan Utama

Panel & routing: app/Providers/Filament/AdminPanelProvider.php:28 menetapkan panel id('admin')->path('admin'), login override, SPA, home /dashboard, plugin Shield.
Konfigurasi: config/filament.php sederhana; assets & cache; provider khusus VoiceInputServiceProvider.
Otorisasi: Spatie Permission aktif (Filament Shield). Kebijakan model tersedia, mis. app/Policies/FamilyPolicy.php:18, app/Policies/VillagePolicy.php, dll. Gate super admin di app/Providers/AuthServiceProvider.php:18.
Resource CRUD (inti): Users, Medicines, Villages, Buildings, Families, Family Members, Family Health Index, Medical Records (termasuk antrian). Contoh: app/Filament/Resources/MedicineResource.php, app/Filament/Resources/MedicalRecordResource.php.
Halaman khusus: IKS Report app/Filament/Pages/IksReport.php (view: resources/views/filament/pages/iks-report.blade.php), Analysis app/Filament/Pages/Analysis.php1 (kemungkinan berkas sisa), serangkaian halaman Antrian Rekam Medis.
Widget: beberapa widget statistik dan chart, mis. app/Filament/Widgets/HealthStatsOverview.php.
View berbasis Filament components masih dipakai: banyak <x-filament*> di resources/views/filament/..., sehingga perlu dikonversi ke Blade standar.
Aset non‑Filament sudah ada: Dashboard, Analysis, Medical Records versi MVC biasa, Map, dll. Lihat routes/web.php dan controller terkait (mis. app/Http/Controllers/MedicalRecordController.php).
Hal yang Perlu Diganti Saat Lepas dari Filament

Login admin: kini lewat app/Filament/Pages/Auth/Login.php. Tanpa Filament, butuh halaman login/middleware setara pada guard web.
Navigasi & layout: Filament mengelola sidebar, topbar, theme. Perlu layout kustom setara (resources/views/layouts/admin.blade.php).
Komponen UI: <x-filament::...> dan <x-filament-panels::page> di Blade harus diganti Tailwind/Blade/Livewire biasa.
Route name kompatibilitas: ada referensi ke route Filament, mis. route('filament.admin.resources.medical-records.queue-display') di QueueDashboard. Perlu alias/redirect sementara atau ubah referensi saat konversi.
Strategi Migrasi Bertahap

Fase 1 – Kerangka Admin Kustom
Buat grup route prefix('admin')->middleware('auth') dan layout kustom dasar untuk sidebar/topbar.
Tambahkan middleware per izin/role spatie untuk tiap menu (padankan dengan Shield), contoh: ->middleware('permission:view_any_medicine').
Tambahkan login minimal (Breeze atau controller/login sederhana) untuk menjaga UX GET /admin/login dan logika redirect setara milik Filament Login.
Fase 2 – Alihkan Resource yang “low‑risk” dulu
Mulai dari Medicines dan Users (pola CRUD standar).
Implementasi: Controller CRUD, Blade views (index/create/edit/show), validasi, pagination, search, sort; aksi “adjust stock” sebagai endpoint khusus (mengganti Tables\Actions\Action).
Fase 3 – Data Master: Villages, Buildings, Families, Family Members
CRUD + filter dasar; gunakan komponen select terhubung (village→buildings) seperti yang sudah ada di route /api/buildings.
Fase 4 – Rekam Medis
Pemetaan antrian/role:
Halaman: dashboard antrian, display TV, registration/nurse/doctor/pharmacy queue, current serving.
Pindahkan helper/form logic dari MedicalRecordResource ke controller/service + Livewire (untuk stateful update yang sebelumnya di-handle “reactive/afterStateUpdated”).
Pertahankan helper model: queue status, kalkulasi, denormalisasi pasien tetap di app/Models/MedicalRecord.php.
Fase 5 – Halaman Khusus
IKS Report: Reuse service App\Services\IksReportService dan konversi Blade dari resources/views/filament/pages/iks-report.blade.php (hapus komponen x-filament).
Analysis & Crosstab: Sudah ada versi non‑Filament (controller + views), pastikan masuk ke navigasi admin kustom.
Fase 6 – Widget/Statistik Dashboard
Pindah widget jadi partial Blade atau Livewire; sumber data tetap (Family/FamilyMember query). Integrasikan ke /dashboard kustom.
Fase 7 – Bersih‑bersih & Penghapusan Filament
Setelah semua menu berfungsi di panel kustom: hapus app/Providers/Filament/..., app/Filament/..., override resources/views/vendor/filament, dependensi composer (filament/*, bezhansalleh/filament-shield) jika tak dipakai lagi.
Ganti/pertahankan rute bernama yang semula digunakan agar tidak memutus tautan internal selama masa transisi.
Usulan Struktur Panel Kustom + Rute

Layout: resources/views/layouts/admin.blade.php (sidebar, user-menu, breadcrumbs).
Rute inti (contoh):
GET /admin → Dashboard (ganti Filament Dashboard)
GET /admin/users, POST /admin/users, dst. (Users + role/permission management)
GET /admin/medicines (+ adjust stock endpoint POST /admin/medicines/{id}/adjust)
GET /admin/villages, GET /admin/buildings, GET /admin/families, GET /admin/family-members
Rekam Medis: GET /admin/medical-records, GET /admin/queues/dashboard, /queues/registration|nurse|doctor|pharmacy, /queues/display, /queues/current-serving
Middleware otorisasi:
Mapping izin Spatie mengikuti Shield, mis. view_any_user, create_medicine, dsb. (lihat kebijakan yang sudah ada).
Pilot Migrasi yang Disarankan

Mulai dari Medicines:
Controller: app/Http/Controllers/Admin/MedicineController.php
Views: resources/views/admin/medicines/*
Fitur: index (search/sort/pagination), create/edit/delete, adjust stock, filter is_active/stock_status.
Lanjut Users:
Controller: CRUD user + assign roles (Spatie). Jaga hashing password.
Risiko & Catatan

Login: Saat ini banyak halaman memakai auth middleware, sementara login Filament menyediakan formnya. Kita perlu menyediakan login /admin/login kustom sebelum menonaktifkan Filament.
Komponen Blade Filament: Semua <x-filament*> harus diganti; yang berat hanya wrapper/section — konten inti umumnya HTML biasa, jadi relatif mudah dipindahkan.
Nama rute: Jika ada hardcode ke rute Filament (contoh pada QueueDashboard), buat alias rute sementara agar tidak memutus tautan selama transisi.
Dependensi Excel/PDF/Chart: Tetap bisa dipakai dari controller (sudah digunakan di luar Filament). Pastikan Maatwebsite Excel digunakan di runtime (saat ini ada di require-dev; jika dipakai produksi, pindahkan ke require).