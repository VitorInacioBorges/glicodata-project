<?php

namespace Database\Seeders;

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
        $district = DistrictModel::query()->create([
            'name' => 'Distrito Teste',
        ]);

        $ubs = UbsModel::query()->create([
            'district_id' => $district->id,
            'name' => 'UBS Teste',
            'bairro_ref' => 'Centro',
            'address' => 'Rua Teste, 100',
            'phone' => '42999999999',
            'email' => 'ubs@example.com',
            'keycloak_id' => 'ubs-teste-keycloak-id',
            'is_active' => true,
        ]);

        UserModel::factory()->create([
            'ubs_id' => $ubs->id,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'cpf' => '00000000000',
        ]);
    }
}
