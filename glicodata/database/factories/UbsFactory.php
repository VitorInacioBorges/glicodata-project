<?php

namespace Database\Factories;

use App\Models\DistrictModel;
use App\Models\UbsModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UbsModel>
 */
class UbsFactory extends Factory
{
    protected $model = UbsModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cnes' => fake()->unique()->numerify('#######'),
            'district_id' => fn (): string => DistrictModel::query()->create([
                'name' => fake()->unique()->city(),
            ])->id,
            'name' => fake()->company(),
            'bairro_ref' => fake()->streetName(),
            'address' => fake()->address(),
            'phone' => fake()->numerify('(##) #####-####'),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'StrongPassword!123',
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
