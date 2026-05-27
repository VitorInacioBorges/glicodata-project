<?php

namespace App\Http\Requests\UserRequests;

use App\Enums\UserRole;
use App\Http\Requests\Support\ApiFormRequest;
use App\Rules\CpfRules\ValidCpf;
use Illuminate\Validation\Rule;

class StoreUserRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->normalizeStrings(
            ['name', 'cpf', 'email'],
            ['email'],
        );
        $this->normalizeNullableStrings(['address', 'phone']);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'birth' => ['required', 'date', 'before_or_equal:today'],
            'sex' => ['required', 'boolean'],
            'cpf' => ['required', 'string', 'max:14', new ValidCpf, Rule::unique('users', 'cpf')],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('users', 'email')],
            'role' => ['required', Rule::enum(UserRole::class)],
        ];
    }
}
