<?php

namespace Database\Factories;

use App\Models\DistrictModel;
use App\Models\UbsModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserModel>
 */
class UserFactory extends Factory
{
    protected $model = \App\Models\UserModel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ubs_id' => function (): string {
                $district = DistrictModel::query()->create([
                    'name' => fake()->unique()->city(),
                ]);

                return UbsModel::query()->create([
                    'district_id' => $district->id,
                    'name' => fake()->company(),
                    'bairro_ref' => fake()->streetName(),
                    'address' => fake()->address(),
                    'phone' => fake()->numerify('###########'),
                    'email' => fake()->unique()->safeEmail(),
                    'keycloak_id' => (string) fake()->uuid(),
                    'is_active' => true,
                ])->id;
            },
            'name' => fake()->name(),
            'age' => fake()->numberBetween(18, 90),
            'sex' => fake()->boolean(),
            'cpf' => fake()->unique()->numerify('###########'),
            'address' => fake()->address(),
            'phone' => fake()->numerify('###########'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => null,
            'role' => fake()->randomElement(['admin', 'user']),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
