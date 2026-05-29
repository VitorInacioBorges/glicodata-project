<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\DistrictModel;
use App\Models\UbsModel;
use App\Models\UserModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $district = DistrictModel::query()->firstOrCreate(
            ['name' => 'Distrito Teste'],
        );

        $ubs = UbsModel::query()->updateOrCreate(
            ['email' => 'ubs@example.com'],
            [
                'district_id' => $district->id,
                'name' => 'UBS Teste',
                'bairro_ref' => 'Centro',
                'address' => 'Rua Teste, 100',
                'phone' => '42999999999',
                'keycloak_id' => 'ubs-teste-keycloak-id',
                'is_active' => true,
            ],
        );

        UserModel::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'ubs_id' => $ubs->id,
                'name' => 'Test User',
                'birth' => '1990-01-01',
                'sex' => true,
                'cpf' => '529.982.247-25',
                'address' => 'Rua Teste, 200',
                'phone' => '42988888888',
                'password' => null,
                'role' => UserRole::Professional->value,
            ],
        );
    }
}
