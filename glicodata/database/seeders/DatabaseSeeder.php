<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Dados institucionais versionados são seguros para todos os ambientes.
        // UBS, usuários e credenciais continuam fora dos seeders.
        $this->call(QuestionnaireSeeder::class);
    }
}
