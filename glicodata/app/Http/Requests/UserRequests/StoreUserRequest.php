<?php

namespace App\Http\Requests\UserRequests;

use App\Enums\ProfessionalCouncil;
use App\Enums\UserRole;
use App\Http\Requests\Support\ApiFormRequest;
use App\Rules\CpfRules\ValidCpf;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->normalizeStrings(
            ['name', 'cpf', 'email'],
            ['email'],
        );
        $this->normalizeNullableStrings(['address', 'phone', 'council_type', 'council_number', 'council_uf', 'specialty']);

        $this->merge([
            'council_type' => is_string($this->input('council_type')) ? strtoupper($this->input('council_type')) : $this->input('council_type'),
            'council_uf' => is_string($this->input('council_uf')) ? strtoupper($this->input('council_uf')) : $this->input('council_uf'),
        ]);
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
            'password' => ['required', 'string', 'max:255', 'confirmed', Password::defaults()],
            'is_active' => ['sometimes', 'boolean'],
            'council_type' => ['nullable', 'required_if:role,professional', 'prohibited_if:role,admin', Rule::enum(ProfessionalCouncil::class)],
            'council_number' => [
                'nullable',
                'required_if:role,professional',
                'prohibited_if:role,admin',
                'string',
                'max:30',
                Rule::unique('users', 'council_number')->where(fn ($query) => $query
                    ->where('council_type', $this->input('council_type'))
                    ->where('council_uf', $this->input('council_uf'))),
            ],
            'council_uf' => ['nullable', 'required_if:role,professional', 'prohibited_if:role,admin', 'string', 'size:2', 'regex:/^[A-Z]{2}$/'],
            'specialty' => ['nullable', 'required_if:role,professional', 'prohibited_if:role,admin', 'string', 'max:255'],
        ];
    }
}
