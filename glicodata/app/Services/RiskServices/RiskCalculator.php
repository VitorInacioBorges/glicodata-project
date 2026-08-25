<?php

namespace App\Services\RiskServices;

use App\Enums\RiskClassification;
use App\Models\PatientModel;
use App\Models\QuestionnaireVersionModel;
use App\Services\QuestionnaireServices\QuestionnaireAnswerValidator;
use Illuminate\Validation\ValidationException;

class RiskCalculator
{
    public function __construct(
        private readonly QuestionnaireAnswerValidator $validator,
    ) {}

    /**
     * @param  array<string, mixed>  $answers
     * @return array{answers: array<string, int|float|string>, score: int, percentage: float, classification: string}
     */
    public function calculate(
        QuestionnaireVersionModel $version,
        PatientModel $patient,
        array $answers,
    ): array {
        $answers = $this->validator->validate($version, $patient, $answers, true);
        $score = 0;

        foreach ($version->schema ?? [] as $question) {
            $code = (string) ($question['code'] ?? '');
            $type = $question['type'] ?? null;
            $value = $answers[$code] ?? null;

            if ($type === 'choice') {
                $option = collect($question['options'] ?? [])->firstWhere('value', $value);
                $score += (int) ($option['score'] ?? 0);

                continue;
            }

            if ($type === 'number') {
                $ranges = $question['ranges'] ?? null;
                if (isset($question['sex_ranges'])) {
                    $ranges = $question['sex_ranges'][$patient->sex ? 'male' : 'female'] ?? [];
                }

                $score += $this->scoreRange((float) $value, $ranges ?? [], $code);
            }
        }

        $tier = collect($version->risk_rules['tiers'] ?? [])->first(
            fn (array $candidate): bool => $score >= (int) ($candidate['min'] ?? 0)
                && $score <= (int) ($candidate['max'] ?? PHP_INT_MAX),
        );

        if ($tier === null || RiskClassification::tryFrom((string) ($tier['classification'] ?? '')) === null) {
            throw ValidationException::withMessages([
                'questionnaire_version_id' => ['A versão do questionário não possui regras de risco válidas para esta pontuação.'],
            ]);
        }

        return [
            'answers' => $answers,
            'score' => $score,
            'percentage' => (float) $tier['percentage'],
            'classification' => (string) $tier['classification'],
        ];
    }

    /**
     * @param  array<int, array<string, int|float>>  $ranges
     */
    private function scoreRange(float $value, array $ranges, string $code): int
    {
        foreach ($ranges as $range) {
            $matches = (! isset($range['min']) || $value >= (float) $range['min'])
                && (! isset($range['min_exclusive']) || $value > (float) $range['min_exclusive'])
                && (! isset($range['max']) || $value <= (float) $range['max'])
                && (! isset($range['max_exclusive']) || $value < (float) $range['max_exclusive']);

            if ($matches) {
                return (int) ($range['score'] ?? 0);
            }
        }

        throw ValidationException::withMessages([
            "answers.{$code}" => ['A resposta está fora das faixas de pontuação configuradas.'],
        ]);
    }
}
