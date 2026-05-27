<?php

namespace App\Rules\CpfRules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCpf implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match('/^\d{3}\.\d{3}\.\d{3}-\d{2}$/', $value)) {
            $fail('O campo :attribute deve estar no formato 000.000.000-00.');

            return;
        }

        $digits = preg_replace('/\D/', '', $value);

        if ($digits === null || strlen($digits) !== 11 || preg_match('/^(\d)\1{10}$/', $digits)) {
            $fail('O campo :attribute deve conter um CPF valido.');

            return;
        }

        for ($position = 9; $position < 11; $position++) {
            $sum = 0;

            for ($index = 0; $index < $position; $index++) {
                $sum += ((int) $digits[$index]) * (($position + 1) - $index);
            }

            $digit = ((10 * $sum) % 11) % 10;

            if ((int) $digits[$position] !== $digit) {
                $fail('O campo :attribute deve conter um CPF valido.');

                return;
            }
        }
    }
}
