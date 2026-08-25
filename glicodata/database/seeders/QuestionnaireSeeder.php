<?php

namespace Database\Seeders;

use App\Enums\QuestionnaireVersionStatus;
use App\Models\QuestionnaireModel;
use App\Models\QuestionnaireVersionModel;
use Illuminate\Database\Seeder;

class QuestionnaireSeeder extends Seeder
{
    public const QUESTIONNAIRE_ID = '30000000-0000-4000-8000-000000000001';

    public const VERSION_ID = '31000000-0000-4000-8000-000000000001';

    public function run(): void
    {
        $questionnaire = QuestionnaireModel::query()->updateOrCreate(
            ['id' => self::QUESTIONNAIRE_ID],
            [
                'code' => 'findrisc-br',
                'title' => 'Avaliação de risco para Diabetes Mellitus tipo 2',
                'description' => 'Questionário de rastreio baseado nos fatores do FINDRISC, adaptado ao fluxo assistencial do GlicoData.',
                'is_active' => true,
            ],
        );

        // Uma versão publicada é imutável: alterações futuras devem criar v2,
        // nunca reescrever as regras usadas por anamneses históricas.
        QuestionnaireVersionModel::query()->firstOrCreate(
            ['id' => self::VERSION_ID],
            [
                'questionnaire_id' => $questionnaire->id,
                'version' => 1,
                'status' => QuestionnaireVersionStatus::Published,
                'schema' => $this->schema(),
                'risk_rules' => [
                    'method' => 'sum',
                    'tiers' => [
                        ['min' => 0, 'max' => 6, 'percentage' => 1, 'classification' => 'low'],
                        ['min' => 7, 'max' => 11, 'percentage' => 4, 'classification' => 'moderate'],
                        ['min' => 12, 'max' => 14, 'percentage' => 17, 'classification' => 'moderate'],
                        ['min' => 15, 'max' => 20, 'percentage' => 33, 'classification' => 'high'],
                        ['min' => 21, 'max' => 99, 'percentage' => 50, 'classification' => 'high'],
                    ],
                ],
                'published_at' => now(),
            ],
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function schema(): array
    {
        return [
            [
                'code' => 'age',
                'label' => 'Idade',
                'help' => 'Calculada automaticamente a partir da data de nascimento do paciente.',
                'type' => 'computed_age',
                'required' => true,
                'ranges' => [
                    ['max' => 44, 'score' => 0],
                    ['min' => 45, 'max' => 54, 'score' => 2],
                    ['min' => 55, 'max' => 64, 'score' => 3],
                    ['min' => 65, 'score' => 4],
                ],
            ],
            [
                'code' => 'bmi',
                'label' => 'Índice de massa corporal (IMC)',
                'type' => 'number',
                'required' => true,
                'min' => 10,
                'max' => 80,
                'step' => 0.1,
                'ranges' => [
                    ['max_exclusive' => 25, 'score' => 0],
                    ['min' => 25, 'max' => 30, 'score' => 1],
                    ['min_exclusive' => 30, 'score' => 3],
                ],
            ],
            [
                'code' => 'waist',
                'label' => 'Circunferência abdominal (cm)',
                'type' => 'number',
                'required' => true,
                'min' => 40,
                'max' => 250,
                'step' => 0.1,
                'sex_ranges' => [
                    'male' => [
                        ['max_exclusive' => 94, 'score' => 0],
                        ['min' => 94, 'max' => 102, 'score' => 3],
                        ['min_exclusive' => 102, 'score' => 4],
                    ],
                    'female' => [
                        ['max_exclusive' => 80, 'score' => 0],
                        ['min' => 80, 'max' => 88, 'score' => 3],
                        ['min_exclusive' => 88, 'score' => 4],
                    ],
                ],
            ],
            [
                'code' => 'physical_activity',
                'label' => 'Pratica pelo menos 30 minutos de atividade física diariamente?',
                'type' => 'choice',
                'required' => true,
                'options' => [
                    ['value' => 'yes', 'label' => 'Sim', 'score' => 0],
                    ['value' => 'no', 'label' => 'Não', 'score' => 2],
                ],
            ],
            [
                'code' => 'vegetables',
                'label' => 'Consome verduras, frutas ou legumes todos os dias?',
                'type' => 'choice',
                'required' => true,
                'options' => [
                    ['value' => 'yes', 'label' => 'Todos os dias', 'score' => 0],
                    ['value' => 'no', 'label' => 'Não todos os dias', 'score' => 1],
                ],
            ],
            [
                'code' => 'antihypertensive',
                'label' => 'Usa regularmente medicamento para pressão alta?',
                'type' => 'choice',
                'required' => true,
                'options' => [
                    ['value' => 'no', 'label' => 'Não', 'score' => 0],
                    ['value' => 'yes', 'label' => 'Sim', 'score' => 2],
                ],
            ],
            [
                'code' => 'high_glucose',
                'label' => 'Já apresentou glicemia elevada em exame, doença ou gestação?',
                'type' => 'choice',
                'required' => true,
                'options' => [
                    ['value' => 'no', 'label' => 'Não', 'score' => 0],
                    ['value' => 'yes', 'label' => 'Sim', 'score' => 5],
                ],
            ],
            [
                'code' => 'family_history',
                'label' => 'Há diagnóstico de diabetes na família?',
                'type' => 'choice',
                'required' => true,
                'options' => [
                    ['value' => 'none', 'label' => 'Não', 'score' => 0],
                    ['value' => 'second_degree', 'label' => 'Sim, avós, tios ou primos', 'score' => 3],
                    ['value' => 'first_degree', 'label' => 'Sim, pais, irmãos ou filhos', 'score' => 5],
                ],
            ],
        ];
    }
}
