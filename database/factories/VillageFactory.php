<?php

namespace Database\Factories;

use App\Models\District;
use App\Models\Village;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class VillageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Village::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->city();
        return [
            'district_id' => District::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'code' => $this->faker->unique()->numerify('####'),
            'postal_code' => $this->faker->unique()->postcode(),
            'status' => $this->faker->randomElement(['Kelurahan', 'Desa']),
        ];
    }
}
