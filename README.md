## Konteks Aplikasi
Saya sedang mengembangkan aplikasi Pendataan PIS-PK (Program Indonesia Sehat - Pendekatan Keluarga) yang terintegrasi dengan sistem rekam medis menggunakan:
- Laravel (PHP Framework)
- Filament (Admin Panel)
- TailwindCSS (Styling)
- Eloquent ORM (Database)

## Struktur Database Existing
- Model `FamilyMember`: Berisi data identitas anggota keluarga
- Model `MedicalRecord`: Berisi data rekam medis yang berelasi ke FamilyMember
- Model `Village`: Berisi data desa/kelurahan

## Requirement yang Dibutuhkan
Saya ingin memodifikasi Model `MedicalRecord` agar dapat menyimpan data identitas pasien secara denormalisasi dari `FamilyMember` untuk keperluan:
1. Performa query yang lebih baik
2. Backup data identitas saat rekam medis dibuat
3. Kemudahan laporan tanpa JOIN kompleks

## Data Identitas yang Perlu Ditambahkan ke MedicalRecord
Dari `FamilyMember`, ambil field berikut:
- `patient_name` (dari field `name`)
- `patient_address` (dari relasi Village melalui Family->Building->Village)
- `patient_gender` (dari field `gender`) 
- `patient_nik` (dari field `nik`)
- `patient_rm_number` (dari field `rm_number`)
- `patient_birth_date` (dari field `birth_date`)
- `patient_age` (calculated dari birth_date saat record dibuat)

## Tasks yang Perlu Dilakukan

### 1. Modifikasi Database Schema
- Buat migration untuk menambahkan kolom identitas pasien ke tabel `medical_records`
- Pastikan kolom `patient_birth_date` menggunakan tipe `date`
- Kolom `patient_age` menggunakan tipe `integer`
- Kolom lainnya menggunakan tipe `string` dengan length yang sesuai

### 2. Update Model MedicalRecord
- Tambahkan field baru ke `$fillable` array
- Tambahkan cast untuk `patient_birth_date` sebagai `date`
- Buat accessor untuk menghitung ulang umur berdasarkan `patient_birth_date`
- Tambahkan method `syncPatientData()` untuk sinkronisasi data dari FamilyMember

### 3. Logic Sinkronisasi Data
Buat method di MedicalRecord untuk auto-fill data identitas:
```php
public function syncPatientData()
{
    if ($this->familyMember) {
        $this->patient_name = $this->familyMember->name;
        $this->patient_gender = $this->familyMember->gender;
        $this->patient_nik = $this->familyMember->nik;
        $this->patient_rm_number = $this->familyMember->rm_number;
        $this->patient_birth_date = $this->familyMember->birth_date;
        $this->patient_age = $this->familyMember->age;
        
        // Get address from family->building->village
        if ($this->familyMember->family && $this->familyMember->family->building) {
            $building = $this->familyMember->family->building;
            $village = $building->village ?? null;
            
            if ($village) {
                $this->patient_address = "{$building->address}, {$village->name}, {$village->district}, {$village->regency}";
            }
        }
    }
}
```

### 4. Event Handler
Implementasikan event handler pada model untuk auto-sync:
- Saat `creating` MedicalRecord baru
- Saat `updating` jika `family_member_id` berubah

### 5. Backward Compatibility
- Pastikan existing records tetap berfungsi
- Buat command artisan untuk mengisi data identitas pada records yang sudah ada

## Output yang Diharapkan
1. Migration file untuk menambah kolom identitas pasien
2. Model MedicalRecord yang sudah diupdate dengan field dan method baru
3. Artisan command untuk migrasi data existing (optional)
4. Test case untuk memastikan fungsi sinkronisasi bekerja dengan baik

## Catatan Penting
- Jangan hapus atau ubah struktur existing yang sudah ada
- Pastikan relasi ke FamilyMember tetap berfungsi
- Data identitas ini bersifat snapshot saat record dibuat, tidak selalu sync dengan FamilyMember
- Handle case ketika FamilyMember atau relasi Village tidak ditemukan

## Coding Standards
- Ikuti PSR-12 coding standard
- Gunakan Laravel naming convention
- Tambahkan docblock untuk method baru
- Handle null values dengan proper validation
