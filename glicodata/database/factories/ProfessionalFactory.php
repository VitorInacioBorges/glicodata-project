<?php

namespace Database\Factories;

use App\Models\DistrictModel;
use App\Models\ProfessionalModel;
use App\Models\UbsModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProfessionalModel>
 */
class ProfessionalFactory extends Factory
{
    protected $model = ProfessionalModel::class;

    public function definition(): array
    {
        return [
            'ubs_id' => function (): string {
                $district = DistrictModel::query()->create(['name' => fake()->unique()->city()]);

                return UbsModel::query()->create([
                    'cnes' => fake()->unique()->numerify('#######'),
                    'district_id' => $district->id,
                    'name' => fake()->company(),
                    'bairro_ref' => fake()->streetName(),
                    'address' => fake()->address(),
                    'phone' => fake()->numerify('###########'),
                    'email' => fake()->unique()->safeEmail(),
                    'password' => Str::password(32),
                    'is_active' => true,
                ])->id;
            },
            'first_name' => fake()->firstName(),
            'specialty' => fake()->randomElement([
                'Clínica médica',
                'Medicina de família e comunidade',
                'Endocrinologia',
            ]),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
