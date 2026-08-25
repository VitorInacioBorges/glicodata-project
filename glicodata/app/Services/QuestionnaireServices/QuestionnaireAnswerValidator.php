<?php

namespace App\Services\QuestionnaireServices;

use App\Models\PatientModel;
use App\Models\QuestionnaireVersionModel;
use Illuminate\Validation\ValidationException;

class QuestionnaireAnswerValidator
{
    /**
     * @param  array<string, mixed>  $answers
     * @return array<string, int|float|string>
     */
    public function validate(
        QuestionnaireVersionModel $version,
        PatientModel $patient,
        array $answers,
        bool $requireComplete,
    ): array {
        $normalized = [];
        $errors = [];
        $questions = collect($version->schema ?? [])->keyBy('code');

        foreach (array_keys($answers) as $code) {
            if (! $questions->has($code)) {
                $errors["answers.{$code}"][] = 'Esta pergunta não pertence à versão selecionada.';
            }
        }

        foreach ($questions as $code => $question) {
            if (($question['type'] ?? null) === 'computed_age') {
                if ($patient->age === null) {
                    $errors["answers.{$code}"][] = 'Não foi possível calcular a idade do paciente.';
                }

                continue;
            }

            $hasAnswer = array_key_exists($code, $answers)
                && $answers[$code] !== null
                && $answers[$code] !== '';

            if (! $hasAnswer) {
                if ($requireComplete && ($question['required'] ?? false)) {
                    $errors["answers.{$code}"][] = 'Esta resposta é obrigatória para concluir a anamnese.';
                }

                continue;
            }

            $value = $answers[$code];

            if (($question['type'] ?? null) === 'number') {
                if (! is_numeric($value)) {
                    $errors["answers.{$code}"][] = 'Informe um número válido.';

                    continue;
                }

                $number = (float) $value;
                if (isset($question['min']) && $number < (float) $question['min']) {
                    $errors["answers.{$code}"][] = "O valor mínimo é {$question['min']}.";
                }
                if (isset($question['max']) && $number > (float) $question['max']) {
                    $errors["answers.{$code}"][] = "O valor máximo é {$question['max']}.";
                }

                $normalized[$code] = $number;

                continue;
            }

            if (($question['type'] ?? null) === 'choice') {
                $allowed = collect($question['options'] ?? [])->pluck('value')->map(strval(...));
                $choice = (string) $value;

                if (! $allowed->containsStrict($choice)) {
                    $errors["answers.{$code}"][] = 'Selecione uma das opções permitidas.';

                    continue;
                }

                $normalized[$code] = $choice;

                continue;
            }

            $errors["answers.{$code}"][] = 'O tipo desta pergunta não é suportado.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $normalized;
    }
}
