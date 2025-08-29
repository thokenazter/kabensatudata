<?php

namespace Database\Factories;

use App\Models\Family;
use App\Models\Village;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FamilyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Family::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->lastName() . ' Family',
            'slug' => Str::slug($this->faker->unique()->lastName() . ' family'),
            'no_kk' => $this->faker->numerify('################'),
            'village_id' => Village::factory(),
            'address' => $this->faker->address(),
            'has_pus' => true, // Pasangan Usia Subur
            'follows_family_planning' => $this->faker->boolean(70),

            // Indikator kesehatan mental
            'has_mental_illness' => $this->faker->boolean(5),
            'takes_medication_regularly' => $this->faker->boolean(60),
            'has_restrained_member' => $this->faker->boolean(10),

            // Indikator air dan sanitasi
            'has_clean_water' => $this->faker->boolean(85),
            'is_water_protected' => $this->faker->boolean(80),
            'has_toilet' => $this->faker->boolean(85),
            'is_toilet_sanitary' => $this->faker->boolean(75),
        ];
    }

    /**
     * Indicate that the family has mental illness.
     *
     * @return $this
     */
    public function withMentalIllness()
    {
        return $this->state(function (array $attributes) {
            return [
                'has_mental_illness' => true,
                'takes_medication_regularly' => $this->faker->boolean(),
                'has_restrained_member' => $this->faker->boolean(30),
            ];
        });
    }
}
