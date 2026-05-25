<?php

namespace App\Http\Requests\UserRequests;

use App\Enums\UserRole;
use App\Http\Requests\Support\ApiFormRequest;
use App\Rules\CpfRules\ValidCpf;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->normalizeStrings(
            ['name', 'cpf', 'address', 'phone', 'email'],
            ['email'],
        );
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'birth' => ['sometimes', 'required', 'date', 'before_or_equal:today'],
            'sex' => ['sometimes', 'required', 'boolean'],
            'cpf' => [
                'sometimes',
                'required',
                'string',
                'max:14',
                new ValidCpf,
                Rule::unique('users', 'cpf')->ignore((string) $this->route('id')),
            ],
            'address' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['sometimes', 'required', 'string', 'max:30'],
            'email' => [
                'sometimes',
                'required',
                'email:rfc',
                'max:255',
                Rule::unique('users', 'email')->ignore((string) $this->route('id')),
            ],
            'role' => ['sometimes', 'required', Rule::enum(UserRole::class)],
        ];
    }
}
