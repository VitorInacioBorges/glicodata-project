<?php

namespace Database\Factories;

use App\Models\AdministratorModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
            'admin_code' => 'ADMIN-'.Str::upper(fake()->unique()->bothify('####??')),
            'password' => Str::password(32),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
