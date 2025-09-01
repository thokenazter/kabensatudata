<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class FamilyMember extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'family_id',
        'name',
        'slug',
        'nik',
        'rm_number',
        'sequence_number_in_family',
        'relationship',
        'birth_place',
        'birth_date',
        'gender',
        'is_pregnant',
        'religion',
        'education',
        'marital_status',
        'occupation',
        'has_jkn',
        'is_smoker',
        'use_water',
        'use_toilet',
        'has_tuberculosis',
        'takes_tb_medication_regularly',
        'has_chronic_cough',
        'has_hypertension',
        'takes_hypertension_medication_regularly',
        'uses_contraception',
        'gave_birth_in_health_facility',
        'exclusive_breastfeeding',
        'complete_immunization',
        'growth_monitoring'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'birth_date' => 'date',
        'is_pregnant' => 'boolean',
        'has_jkn' => 'boolean',
        'is_smoker' => 'boolean',
        'use_toilet' => 'boolean',
        'has_tuberculosis' => 'boolean',
        'takes_tb_medication_regularly' => 'boolean',
        'has_chronic_cough' => 'boolean',
        'has_hypertension' => 'boolean',
        'takes_hypertension_medication_regularly' => 'boolean',
        'uses_contraception' => 'boolean',
        'gave_birth_in_health_facility' => 'boolean',
        'exclusive_breastfeeding' => 'boolean',
        'complete_immunization' => 'boolean',
        'growth_monitoring' => 'boolean',
    ];

    /**
     * The relationships that should be eager loaded.
     *
     * @var array<string>
     */
    protected $with = ['family'];

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($member) {
            if (empty($member->slug)) {
                $member->slug = self::generateUniqueSlug($member->name, $member->id ?? 0);
            }
            
            // Generate sequence number and RM number for new members
            $member->generateSequenceNumber();
            $member->generateRmNumber();
        });

        static::updating(function ($member) {
            if ($member->isDirty('name') && !$member->isDirty('slug')) {
                $member->slug = self::generateUniqueSlug($member->name, $member->id);
            }

            // Regenerate RM number if family relationship changes
            if ($member->isDirty('family_id')) {
                $member->generateSequenceNumber();
                $member->generateRmNumber();
            }

            // You could add other business logic here
            // For example, tracking when a member becomes pregnant
            if ($member->isDirty('is_pregnant') && $member->is_pregnant) {
                // event(new MemberBecamePregnant($member));
            }
        });
    }

    /**
     * Generate a unique slug based on the name.
     *
     * @param string $name
     * @param int $id
     * @return string
     */
    protected static function generateUniqueSlug(string $name, int $id): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;

        // If ID is provided, append it to ensure uniqueness
        if ($id > 0) {
            $slug = "{$baseSlug}-{$id}";
        }

        return $slug;
    }

    /**
     * Get the family that owns the family member.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    /**
     * Get the village through the family.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function village()
    {
        return $this->hasOneThrough(Village::class, Family::class, 'id', 'id', 'family_id', 'village_id');
    }

    /**
     * Calculate age based on birth date.
     *
     * @return int|null
     */
    public function getAgeAttribute(): ?int
    {
        if ($this->birth_date) {
            return Carbon::parse($this->birth_date)->age;
        }

        return null;
    }

    /**
     * Determine if family member is a woman of reproductive age (WUS).
     * In Indonesia's health system, WUS is defined as women aged 10-54 years.
     * 
     * @return bool
     */
    public function getIsWomenOfReproductiveAgeAttribute(): bool
    {
        return $this->gender === 'Perempuan' &&
            $this->age >= 10 &&
            $this->age <= 54;
    }

    /**
     * Determine if family member is an under-five child (0-59 months).
     *
     * @return bool
     */
    // public function getIsUnderFiveAttribute(): bool
    // {
    //     return $this->age !== null && $this->age < 5;
    // }

    /**
     * Determine if family member is above 10 years old.
     *
     * @return bool
     */
    public function getIsAboveTenAttribute(): bool
    {
        return $this->age !== null && $this->age > 10;
    }

    /**
     * Determine if family member is above 15 years old.
     *
     * @return bool
     */
    public function getIsAboveFifteenAttribute(): bool
    {
        return $this->age !== null && $this->age > 15;
    }

    /**
     * Determine if family member is an infant (0-11 months).
     *
     * @return bool
     */
    public function getIsInfantAttribute(): bool
    {
        if (!$this->birth_date) {
            return false;
        }

        $ageInMonths = Carbon::parse($this->birth_date)->diffInMonths(Carbon::now());
        return $ageInMonths < 12;
    }

    /**
     * Determine if family member is a toddler (12-23 months).
     *
     * @return bool
     */
    public function getIsToddlerAttribute(): bool
    {
        if (!$this->birth_date) {
            return false;
        }

        $ageInMonths = Carbon::parse($this->birth_date)->diffInMonths(Carbon::now());
        return $ageInMonths >= 12 && $ageInMonths <= 23;
    }

    /**
     * Determine if family member is eligible for exclusive breastfeeding monitoring (7-23 months).
     *
     * @return bool
     */
    public function getIsEligibleForBreastfeedingAttribute(): bool
    {
        if (!$this->birth_date) {
            return false;
        }

        $ageInMonths = Carbon::parse($this->birth_date)->diffInMonths(Carbon::now());
        return $ageInMonths >= 7 && $ageInMonths <= 23;
    }

    /**
     * Determine if family member is an under-five child (0-59 months).
     *
     * @return bool
     */
    public function getIsUnderFiveAttribute(): bool
    {
        if (!$this->birth_date) {
            return false;
        }

        $ageInMonths = Carbon::parse($this->birth_date)->diffInMonths(Carbon::now());
        return $ageInMonths < 60;
    }

    /**
     * Get age in months.
     *
     * @return int|null
     */
    public function getAgeInMonthsAttribute(): ?int
    {
        if (!$this->birth_date) {
            return null;
        }

        return Carbon::parse($this->birth_date)->diffInMonths(Carbon::now());
    }

    /**
     * Get the public version of name (blurred for non-logged in users).
     *
     * @return string
     */
    public function getPublicNameAttribute(): string
    {
        if (function_exists('blur_text') && function_exists('should_blur_data')) {
            return should_blur_data() ? blur_text($this->name) : $this->name;
        }

        // Fallback if helper functions don't exist
        return auth()->check() ? $this->name : '********';
    }

    /**
     * Get the public version of NIK (blurred for non-logged in users).
     *
     * @return string
     */
    public function getPublicNikAttribute(): string
    {
        // Jika NIK adalah null, kembalikan string kosong
        if ($this->nik === null) {
            return '';
        }

        if (function_exists('blur_nik') && function_exists('should_blur_data')) {
            return should_blur_data() ? blur_nik($this->nik) : $this->nik;
        }

        // Fallback if helper functions don't exist
        if (!auth()->check() && $this->nik) {
            $length = strlen($this->nik);
            if ($length > 4) {
                return str_repeat('*', $length - 4) . substr($this->nik, -4);
            }
        }

        return $this->nik ?: '';
    }
    /**
     * Get the public version of birth place (blurred for non-logged in users).
     *
     * @return string
     */
    public function getPublicBirthPlaceAttribute(): string
    {
        if (function_exists('blur_text') && function_exists('should_blur_data')) {
            return should_blur_data() ? blur_text($this->birth_place) : $this->birth_place;
        }

        // Fallback if helper functions don't exist
        return auth()->check() ? ($this->birth_place ?: '') : '********';
    }

    /**
     * Get the public version of birth date (blurred for non-logged in users).
     *
     * @return string|null
     */
    public function getPublicBirthDateAttribute(): ?string
    {
        if (!$this->birth_date) {
            return null;
        }

        if (function_exists('should_blur_data')) {
            return should_blur_data()
                ? $this->birth_date->format('Y')
                : $this->birth_date->format('d-m-Y');
        }

        // Fallback if helper function doesn't exist
        return auth()->check()
            ? $this->birth_date->format('d-m-Y')
            : $this->birth_date->format('Y');
    }

    /**
     * Get formatted birth date.
     *
     * @return string|null
     */
    public function getFormattedBirthDateAttribute(): ?string
    {
        return $this->birth_date ? $this->birth_date->format('d-m-Y') : null;
    }

    /**
     * Get public version of age.
     *
     * @return string
     */
    public function getPublicAgeAttribute(): string
    {
        if (function_exists('should_blur_data')) {
            return should_blur_data() ? '**' : (string)$this->age;
        }

        // Fallback if helper function doesn't exist
        return auth()->check() ? (string)($this->age ?: '') : '**';
    }

    /**
     * Get medication status text for TB.
     *
     * @return string
     */
    public function getTbMedicationStatusTextAttribute(): string
    {
        if (!$this->has_tuberculosis) {
            return '';
        }

        return $this->takes_tb_medication_regularly
            ? '<span class="text-green-600">Minum Obat Secara Teratur</span>'
            : '<span class="text-red-600">Mangkir Obat</span>';
    }

    /**
     * Get medication status text for hypertension.
     *
     * @return string
     */
    public function getHypertensionMedicationStatusTextAttribute(): string
    {
        if (!$this->has_hypertension) {
            return '';
        }

        return $this->takes_hypertension_medication_regularly
            ? '<span class="text-green-600">Minum Obat Secara Teratur</span>'
            : '<span class="text-red-600">Tdk Pernah Konsumsi Obat Darah Tinggi</span>';
    }

    /**
     * Set the NIK attribute with validation.
     *
     * @param string|null $value
     * @return void
     * @throws \InvalidArgumentException When NIK format is invalid
     */
    public function setNikAttribute($value): void
    {
        // Skip validation if empty or null
        if (empty($value)) {
            $this->attributes['nik'] = $value;
            return;
        }

        // Basic validation for NIK (16 digits)
        if (!is_numeric($value) || strlen($value) !== 16) {
            throw new \InvalidArgumentException('NIK harus berupa 16 digit numerik');
        }

        $this->attributes['nik'] = $value;
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the slug attribute.
     *
     * @return string
     */
    public function getSlugAttribute(): string
    {
        // If slug exists in attributes, return it
        if (!empty($this->attributes['slug'])) {
            return $this->attributes['slug'];
        }

        // Otherwise generate a temporary one
        $slug = Str::slug($this->name ?? '');

        // Append ID to ensure uniqueness
        return $slug . '-' . $this->id;
    }

    /**
     * Get the village of this family member.
     *
     * @return \App\Models\Village|null
     */
    public function getVillageAttribute()
    {
        if (!$this->relationLoaded('family') || !$this->family) {
            return null;
        }

        // Try to get village from family.building first, then from family directly
        if ($this->family->building && $this->family->building->relationLoaded('village')) {
            return $this->family->building->village;
        }

        return $this->family->village;
    }

    /**
     * Get the district of this family member.
     *
     * @return \App\Models\District|null
     */
    public function getDistrictAttribute()
    {
        $village = $this->getVillageAttribute();

        if (!$village || !$village->relationLoaded('district')) {
            return null;
        }

        return $village->district;
    }

    /**
     * Get the regency of this family member.
     *
     * @return \App\Models\Regency|null
     */
    public function getRegencyAttribute()
    {
        $district = $this->getDistrictAttribute();

        if (!$district || !$district->relationLoaded('regency')) {
            return null;
        }

        return $district->regency;
    }

    /**
     * Get spouse information if married.
     *
     * @return \App\Models\FamilyMember|null
     */
    public function getSpouseAttribute()
    {
        // Cache the result to avoid repeated queries
        static $cache = [];
        $cacheKey = $this->id;

        if (array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }

        if ($this->marital_status !== 'Kawin') {
            return $cache[$cacheKey] = null;
        }

        if (!$this->relationLoaded('family') || !$this->family) {
            return $cache[$cacheKey] = null;
        }

        $oppositeGender = $this->gender === 'Laki-laki' ? 'Perempuan' : 'Laki-laki';

        // Try to find spouse by relationship
        $spouse = $this->family->members()
            ->where('gender', $oppositeGender)
            ->whereIn('relationship', ['Istri', 'Suami'])
            ->where('id', '!=', $this->id)
            ->first();

        // If not found, try by marital status
        if (!$spouse) {
            $spouse = $this->family->members()
                ->where('gender', $oppositeGender)
                ->where('marital_status', 'Kawin')
                ->where('id', '!=', $this->id)
                ->first();
        }

        return $cache[$cacheKey] = $spouse;
    }

    /**
     * Get head of family.
     *
     * @return \App\Models\FamilyMember|null
     */
    public function getHeadOfFamilyAttribute()
    {
        // Cache the result to avoid repeated queries
        static $cache = [];
        $cacheKey = $this->family_id ?? 0;

        if (array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }

        if (!$this->relationLoaded('family') || !$this->family) {
            return $cache[$cacheKey] = null;
        }

        return $cache[$cacheKey] = $this->family->members()
            ->where('relationship', 'Kepala Keluarga')
            ->first();
    }

    /**
     * Check if this member is the head of family.
     *
     * @return bool
     */
    public function getIsHeadOfFamilyAttribute(): bool
    {
        return $this->relationship === 'Kepala Keluarga';
    }

    /**
     * Get children of this family member.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getChildrenAttribute()
    {
        if (!$this->relationLoaded('family') || !$this->family) {
            return collect();
        }

        return $this->family->members()
            ->where('relationship', 'like', '%Anak%')
            ->when($this->gender === 'Laki-laki', function ($query) {
                return $query->where('father_id', $this->id);
            })
            ->when($this->gender === 'Perempuan', function ($query) {
                return $query->where('mother_id', $this->id);
            })
            ->get();
    }

    // app/Models/FamilyMember.php
    // Tambahkan method relasi berikut

    /**
     * Get the medical records for the family member.
     */
    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }

    /**
     * Alias untuk has_tuberculosis untuk kompabilitas backward
     * 
     * @return bool
     */
    public function getHasTbAttribute(): bool
    {
        return (bool) $this->has_tuberculosis;
    }

    /**
     * Alias untuk takes_tb_medication_regularly untuk kompabilitas backward
     * 
     * @return bool
     */
    public function getTbTreatedAttribute(): bool
    {
        return (bool) $this->takes_tb_medication_regularly;
    }

    /**
     * Alias untuk takes_hypertension_medication_regularly untuk kompabilitas backward
     * 
     * @return bool
     */
    public function getHypertensionTreatedAttribute(): bool
    {
        return (bool) $this->takes_hypertension_medication_regularly;
    }

    /**
     * Generate sequence number in family for new member
     */
    public function generateSequenceNumber()
    {
        if (!$this->sequence_number_in_family) {
            $maxSequence = static::where('family_id', $this->family_id)
                ->max('sequence_number_in_family');
            
            $this->sequence_number_in_family = ($maxSequence ?? 0) + 1;
        }
        
        return $this->sequence_number_in_family;
    }

    /**
     * Get village sequence number from family's building's village
     */
    public function getVillageSequenceNumber()
    {
        return $this->family->building->village->sequence_number ?? 0;
    }

    /**
     * Get building number (extract number from B-001 format)
     */
    public function getBuildingNumber()
    {
        $buildingNumber = $this->family->building->building_number ?? '';
        // Extract number from format like "B-001"
        preg_match('/\d+/', $buildingNumber, $matches);
        return isset($matches[0]) ? str_pad($matches[0], 3, '0', STR_PAD_LEFT) : '000';
    }

    /**
     * Get family sequence number in building
     */
    public function getFamilySequenceNumber()
    {
        return str_pad($this->family->sequence_number_in_building ?? 0, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get member sequence number in family
     */
    public function getMemberSequenceNumber()
    {
        return $this->sequence_number_in_family ?? 1;
    }

    /**
     * Generate RM Number with format: [VillageSeq][BuildingNum][FamilySeq]-[MemberSeq]
     */
    public function generateRmNumber()
    {
        if (!$this->rm_number) {
            $villageSeq = $this->getVillageSequenceNumber();
            $buildingNum = $this->getBuildingNumber();
            $familySeq = $this->getFamilySequenceNumber();
            $memberSeq = $this->getMemberSequenceNumber();
            
            $this->rm_number = "{$villageSeq}{$buildingNum}{$familySeq}-{$memberSeq}";
        }
        
        return $this->rm_number;
    }

}
