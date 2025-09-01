<?php

namespace Database\Factories;

use App\Models\Family;
use App\Models\FamilyMember;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FamilyMemberFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FamilyMember::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'family_id' => Family::factory(),
            'name' => $this->faker->name(),
            'nik' => $this->faker->numerify('################'),
            'relationship' => $this->faker->randomElement(['Kepala Keluarga', 'Istri', 'Anak', 'Saudara', 'Orang Tua', 'Lainnya']),
            'birth_place' => $this->faker->city(),
            'birth_date' => $this->faker->dateTimeBetween('-70 years', '-1 year'),
            'gender' => $this->faker->randomElement(['Laki-laki', 'Perempuan']),
            'is_pregnant' => false,
            'religion' => $this->faker->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu']),
            'education' => $this->faker->randomElement(['Tidak Sekolah', 'SD', 'SMP', 'SMA/SMK', 'D3', 'S1', 'S2', 'S3']),
            'marital_status' => $this->faker->randomElement(['Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati']),
            'occupation' => $this->faker->jobTitle(),
            'has_jkn' => $this->faker->boolean(70),
            'is_smoker' => $this->faker->boolean(30),
            'use_water' => $this->faker->boolean(90),
            'use_toilet' => $this->faker->boolean(85),
            'has_tuberculosis' => $this->faker->boolean(5),
            'takes_tb_medication_regularly' => $this->faker->boolean(80),
            'has_chronic_cough' => $this->faker->boolean(10),
            'has_hypertension' => $this->faker->boolean(15),
            'takes_hypertension_medication_regularly' => $this->faker->boolean(70),
            'uses_contraception' => $this->faker->boolean(60),
            'gave_birth_in_health_facility' => $this->faker->boolean(85),
            'exclusive_breastfeeding' => $this->faker->boolean(70),
            'complete_immunization' => $this->faker->boolean(80),
            'growth_monitoring' => $this->faker->boolean(75),
        ];
    }

    /**
     * Indicate that the member is a head of family.
     *
     * @return $this
     */
    public function headOfFamily()
    {
        return $this->state(function (array $attributes) {
            return [
                'relationship' => 'Kepala Keluarga',
                'gender' => 'Laki-laki',
            ];
        });
    }

    /**
     * Indicate that the member has tuberculosis.
     *
     * @return $this
     */
    public function withTuberculosis()
    {
        return $this->state(function (array $attributes) {
            return [
                'has_tuberculosis' => true,
                'takes_tb_medication_regularly' => $this->faker->boolean(50),
            ];
        });
    }

    /**
     * Indicate that the member has hypertension.
     *
     * @return $this
     */
    public function withHypertension()
    {
        return $this->state(function (array $attributes) {
            return [
                'has_hypertension' => true,
                'takes_hypertension_medication_regularly' => $this->faker->boolean(50),
            ];
        });
    }
}
