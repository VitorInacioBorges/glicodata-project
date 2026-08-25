<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Mantém somente o catálogo institucional de distritos.
     *
     * UBS são cadastradas exclusivamente pelo fluxo da aplicação.
     */
    public function up(): void
    {
        $now = now();
        $districts = [
            'Boa Vista / Esplanada' => '10000000-0000-4000-8000-000000000001',
            'Oficinas' => '10000000-0000-4000-8000-000000000002',
            'Nova Rússia / Contorno / Santa Paula' => '10000000-0000-4000-8000-000000000003',
            'Uvaranas I' => '10000000-0000-4000-8000-000000000004',
            'Uvaranas II' => '10000000-0000-4000-8000-000000000005',
        ];

        DB::table('districts')->insert(array_map(
            static fn (string $id, string $name): array => [
                'id' => $id,
                'name' => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            array_values($districts),
            array_keys($districts),
        ));
    }

    public function down(): void
    {
        DB::table('districts')->whereIn('id', [
            '10000000-0000-4000-8000-000000000001',
            '10000000-0000-4000-8000-000000000002',
            '10000000-0000-4000-8000-000000000003',
            '10000000-0000-4000-8000-000000000004',
            '10000000-0000-4000-8000-000000000005',
        ])->delete();
    }
};
