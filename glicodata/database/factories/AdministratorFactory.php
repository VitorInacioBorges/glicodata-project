<?php

namespace Database\Factories;

use App\Models\AdministratorModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdministratorModel>
 */
class AdministratorFactory extends Factory
{
    protected $model = AdministratorModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
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
