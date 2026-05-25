<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Catalogo inicial institucional informado para Ponta Grossa, PR, Brasil.
     * Registros com dados provisorios ficam inativos ate confirmacao.
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

        $ubs = [
            ['Boa Vista / Esplanada', 'US Adam Polan', 'Palmeirinha', 'Rua Alberto de Oliveira, S/N', '(42) 3901-1753', 'adampolan-psf@smspg.pr.gov.br'],
            ['Boa Vista / Esplanada', 'US Antônio Horácio de Miranda', 'Santa Mônica', 'Rua Gaza, 610', '(42) 3901-1730', 'us-antonio-horacio-de-miranda@seed.local'],
            ['Boa Vista / Esplanada', 'US Antônio Russo', 'Centro / Órfãs', 'Rua Saldanha da Gama, S/N', '(42) 3901-1747', 'us-antonio-russo@seed.local'],
            ['Boa Vista / Esplanada', 'US Aurélio Grott', 'Los Angeles', 'Rua Fabio Fanucche, S/N', '(42) 3901-1756', 'aureliogrott-psf@smspg.pr.gov.br'],
            ['Boa Vista / Esplanada', 'US Eugênio José Bocchi', 'Parque Santa Lúcia / Jardim Carvalho', 'Rua Paulo Kloth, 75', '(42) 3901-1761', 'us-eugenio-jose-bocchi@seed.local'],
            ['Boa Vista / Esplanada', 'US Jose Bueno', 'Jardim Jacarandá', 'Rua David Hilgemberg Junior, S/N', '(42) 3901-1764', 'us-jose-bueno@seed.local'],
            ['Boa Vista / Esplanada', 'US Júlio de Azevedo', 'Vila Vilela', 'Rua Desembargador Lauro Lopes, 15', '(42) 3238-2824', 'us-julio-de-azevedo@seed.local'],
            ['Boa Vista / Esplanada', 'US Dra Zilda Arns', 'Parque Nossa Senhora das Graças', 'Rua Aguinaldo Guimarães da Cunha, S/N', '(42) 3236-1570', 'zildaarns@smspg.pr.gov.br'],
            ['Oficinas', 'US Jayme Gusmann', 'Vila Estrela', 'Rua Nilo Peçanha, S/N', '(42) 3901-1740', 'jaymegusman-ubs@smspg.pr.gov.br'],
            ['Oficinas', 'US Agostinho Brenner', 'Maria Otília', 'Rua Freud, S/N', '(42) 3901-1729', 'us-agostinho-brenner@seed.local'],
            ['Oficinas', 'US Adão Ademar Andrade', 'Jardim Cerejeira / Colônia Dona Luiza', 'Rua Luiz Carlos Prestes, S/N', 'Não informado', 'adaoandrade-ub@smspg.pr.gov.br'],
            ['Oficinas', 'US Jose Carlos Araujo', 'Cará-Cará', 'Rua 14 Bis esquina Ipanema, S/N', '(42) 3901-1715', 'josearaujo-ubs@smspg.pr.gov.br'],
            ['Oficinas', 'US Ezebedeu Linhares', 'Jardim Amália', 'Rua Joanita Costa Ribeiro, S/N', '(42) 3229-9513', 'us-ezebedeu-linhares@seed.local'],
            ['Oficinas', 'US João de Oliveira Bello', 'Guaragi', 'Rua Tiburcio Puppo, 95', '(42) 3270-1207', 'us-joao-de-oliveira-bello@seed.local'],
            ['Oficinas', 'US Ottoniel Pimentel Santos', 'Vila Cipa', 'Rua Lapa, S/N', '(42) 3901-1714', 'us-ottoniel-pimentel-santos@seed.local'],
            ['Oficinas', 'US Dr Cyro de Lima Garcia', 'Oficinas', 'Rua Dom Pedro I, 0', '(42) 3901-1482', 'cirogarcia-ubs@smspg.pr.gov.br'],
            ['Oficinas', 'US Lauro Muller', 'Santa Maria', 'Rua Tucano, S/N', '(42) 3901-1749', 'us-lauro-muller@seed.local'],
            ['Nova Rússia / Contorno / Santa Paula', 'US Paulo Madureira Novaes', 'Parque Dom Pedro II', 'Rua Lizandro A. Araújo, S/N', '(42) 3901-1768', 'us-paulo-madureira-novaes@seed.local'],
            ['Nova Rússia / Contorno / Santa Paula', 'US Egon Roskamp', 'Santa Paula', 'Rua Castanheira, S/N', '(42) 3901-1716', 'us-egon-roskamp@seed.local'],
            ['Nova Rússia / Contorno / Santa Paula', 'US Carlos Dezaunet Neto', 'Parque Shangrilá', 'Rua Plácido Cardon, S/N', '(42) 3901-1758', 'carlosdezaunet-ubs@smspg.pr.gov.br'],
            ['Nova Rússia / Contorno / Santa Paula', 'US Clyceu Carlos de Macedo', 'Santa Terezinha / Contorno', 'Rua Papoula, S/N', '(42) 3901-1760', 'us-clyceu-carlos-de-macedo@seed.local'],
            ['Nova Rússia / Contorno / Santa Paula', 'US Alfredo Levandovski', 'Gralha Azul / Contorno', 'Rua General Aldo Bonde, S/N', '(42) 3236-5024', 'alfredolevandoski-ubs@smspg.pr.gov.br'],
            ['Nova Rússia / Contorno / Santa Paula', 'US Roberto de Jesus Portella', 'Ronda', 'Rua Cruzeiro do Oeste, S/N', '(42) 3901-1722', 'us-roberto-de-jesus-portella@seed.local'],
            ['Nova Rússia / Contorno / Santa Paula', 'US Adilson Baggio', 'Santo Antônio', 'Rua Pinhalão, 20', '(42) 3901-1743', 'adilsonbaggio-psf@smspg.pr.gov.br'],
            ['Nova Rússia / Contorno / Santa Paula', 'US Felix Vianna', 'Vila Cristina / Hilgemberg', 'Rua Paes de Andrade, S/N', '(42) 3901-1741', 'felixvianna-psf@smspg.pr.gov.br'],
            ['Nova Rússia / Contorno / Santa Paula', 'US Alexandre Aracema', 'Contorno / Campos Elísios', 'Rua Bachir Steiman Fayad esquina Maria Karpstein, S/N', '(42) 3220-1000', 'us-alexandre-aracema@seed.local'],
            ['Uvaranas I', 'US Abrahão Federmann', 'Ana Rita', 'Rua Quinze de Setembro, S/N', '(42) 3901-1705', 'us-abrahao-federmann@seed.local'],
            ['Uvaranas I', 'US Lubomir Urban', '31 de Março', 'Rua Washington Luis, 760', '(42) 3901-1770', 'lubomirurban-psf@smspg.pr.gov.br'],
            ['Uvaranas I', 'US Antero Machado de Mello', 'Rio Verde / Pitangui', 'Rua Darci Taques de Araújo, S/N', '(42) 3901-1754', 'us-antero-machado-de-mello@seed.local'],
            ['Uvaranas I', 'US Silas Sallen', 'Vila Claudionora', 'Rua Rodrigo Silva, 989', '(42) 3901-7054', 'us-silas-sallen@seed.local'],
            ['Uvaranas I', 'US Santo Domingo Zampier', 'Olarias / Neves', 'Loteamento Residencial Costa Rica, S/N', '(42) 3220-1213', 'santodomingo-ubs@smspg.pr.gov.br'],
            ['Uvaranas I', 'US Crutac', 'Itaiacoca', 'Cerrado Grande, S/N', '(42) 3222-6581', 'us-crutac@seed.local'],
            ['Uvaranas I', 'US Cleon Francisco de Macedo', 'Vila Rubini', 'Rua Padre Dênis Quilty, S/N', '(42) 3901-1760', 'us-cleon-francisco-de-macedo@seed.local'],
            ['Uvaranas I', 'US Sady Macedo Silveira', 'Olarias', 'Rua Ricardo Wagner, 285', '(42) 3901-1746', 'us-sady-macedo-silveira@seed.local'],
            ['Uvaranas II', 'US Sharise Angelica Arruda', 'Recanto Verde', 'Alzimiro Baptista Siqueira esquina Benedito Pedro Silva, S/N', '(42) 3220-1000', 'us-sharise-angelica-arruda@seed.local'],
            ['Uvaranas II', 'US Nilton Luis de Castro', 'Tarobá', 'Rua Alfredo Bochina, S/N', '(42) 3235-5127', 'niltoncastro-psf@smspg.pr.gov.br'],
            ['Uvaranas II', 'US Antônio Saliba', 'Parque Sabiá / Borsato', 'Rua Siqueira Campos, 753', '(42) 3901-7063', 'us-antonio-saliba@seed.local'],
            ['Uvaranas II', 'US Horácio Droppa', 'Borsato', 'Rua Santa Rosa, S/N', '(42) 3901-1762', 'horaciodropa-psf@smspg.pr.gov.br'],
            ['Uvaranas II', 'US Cesar Milleo', 'Vila Santana', 'Rua Ribeirão Claro, S/N', '(42) 3901-1745', 'us-cesar-milleo@seed.local'],
            ['Uvaranas II', 'US Madre Josefa Stenmanns', 'Vila Princesa', 'Rua Bituruna, S/N', '(42) 3901-1724', 'madrejosefa-pf@smspg.pr.gov.br'],
            ['Uvaranas II', 'US Dr Luiz Conrado Mansani', 'Uvaranas', 'A validar', 'A validar', 'us-dr-luiz-conrado-mansani@seed.local'],
            ['Uvaranas II', 'US Aluízio Grochoski', 'Vila Guaíra', 'Rua Theodoro Sampaio, S/N', '(42) 3901-1748', 'us-aluizio-grochoski@seed.local'],
        ];

        $records = [];

        foreach ($ubs as $position => [$district, $name, $bairroRef, $address, $phone, $email]) {
            $provisional = str_ends_with($email, '@seed.local')
                || in_array($phone, ['A validar', 'Não informado'], true)
                || $address === 'A validar';

            $records[] = [
                'id' => sprintf('20000000-0000-4000-8000-%012d', $position + 1),
                'district_id' => $districts[$district],
                'name' => $name,
                'bairro_ref' => $bairroRef,
                'address' => $address,
                'phone' => $phone,
                'email' => strtolower($email),
                'password' => null,
                'keycloak_id' => null,
                'is_active' => ! $provisional,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('ubs')->insert($records);
    }

    public function down(): void
    {
        DB::table('ubs')->whereIn('id', array_map(
            static fn (int $position): string => sprintf('20000000-0000-4000-8000-%012d', $position),
            range(1, 42),
        ))->delete();
        DB::table('districts')->whereIn('id', [
            '10000000-0000-4000-8000-000000000001',
            '10000000-0000-4000-8000-000000000002',
            '10000000-0000-4000-8000-000000000003',
            '10000000-0000-4000-8000-000000000004',
            '10000000-0000-4000-8000-000000000005',
        ])->delete();
    }
};
