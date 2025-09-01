<?php

namespace Database\Factories;

use App\Models\District;
use App\Models\Regency;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DistrictFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = District::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->city();
        return [
            'regency_id' => function () {
                // If no Regency exists, create a fake one to avoid circular dependency
                $regencyCount = Regency::count();
                if ($regencyCount === 0) {
                    return [
                        'id' => 1,
                        'province_id' => 1,
                        'name' => 'Kabupaten Test',
                        'slug' => 'kabupaten-test',
                        'code' => '1234'
                    ];
                }
                return Regency::inRandomOrder()->first()->id;
            },
            'name' => $name,
            'slug' => Str::slug($name),
            'code' => $this->faker->unique()->numerify('####'),
        ];
    }
}
