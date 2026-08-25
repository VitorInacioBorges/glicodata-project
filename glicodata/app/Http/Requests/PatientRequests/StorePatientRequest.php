<?php

namespace App\Http\Requests\PatientRequests;

use App\Http\Requests\Support\ApiFormRequest;
use Closure;

class StorePatientRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->normalizeStrings(['first_name', 'neighborhood']);
        $this->normalizeNullableStrings(['street_name']);
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:80', 'regex:/^[\pL][\pL\x{2019}\x{0027}-]*$/u'],
            'sex' => ['required', 'boolean'],
            'neighborhood' => ['required', 'string', 'max:120'],
            'street_name' => ['nullable', 'string', 'max:160', $this->streetNameRule()],
            ...$this->removedIdentityFields(),
        ];
    }

    /** @return array<string, array<int, string>> */
    protected function removedIdentityFields(): array
    {
        return array_fill_keys(['name', 'cpf', 'birth', 'address', 'phone'], ['prohibited']);
    }

    protected function streetNameRule(): Closure
    {
        return static function (string $attribute, mixed $value, Closure $fail): void {
            if (! is_string($value) || $value === '') {
                return;
            }

            $hasExplicitNumber = preg_match('/(?:\b(?:n|n[oº°]|numero|número)\.?\s*|#)\d+/iu', $value) === 1;
            $hasTrailingHouseNumber = preg_match('/(?:,\s*|\s+)\d+[A-Za-z]?\s*$/u', $value) === 1;

            if ($hasExplicitNumber || $hasTrailingHouseNumber) {
                $fail('Informe somente o nome da rua, sem número do imóvel ou complemento.');
            }
        };
    }
}
