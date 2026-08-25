<?php

namespace App\Http\Requests\UserRequests;

use App\Enums\ProfessionalCouncil;
use App\Enums\UserRole;
use App\Http\Requests\Support\ApiFormRequest;
use App\Models\UserModel;
use App\Rules\CpfRules\ValidCpf;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends ApiFormRequest
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
        $current = UserModel::withTrashed()->find((string) $this->route('id'));
        $councilType = $this->input('council_type', $current?->council_type?->value);
        $councilUf = $this->input('council_uf', $current?->council_uf);

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
            'address' => ['sometimes', 'nullable', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'email' => [
                'sometimes',
                'required',
                'email:rfc',
                'max:255',
                Rule::unique('users', 'email')->ignore((string) $this->route('id')),
            ],
            'role' => ['sometimes', 'required', Rule::enum(UserRole::class)],
            'password' => ['sometimes', 'nullable', 'string', 'max:255', 'confirmed', Password::defaults()],
            'is_active' => ['sometimes', 'boolean'],
            'council_type' => ['sometimes', 'nullable', Rule::enum(ProfessionalCouncil::class)],
            'council_number' => [
                'sometimes',
                'nullable',
                'string',
                'max:30',
                Rule::unique('users', 'council_number')
                    ->ignore((string) $this->route('id'))
                    ->where(fn ($query) => $query
                        ->where('council_type', $councilType)
                        ->where('council_uf', $councilUf)),
            ],
            'council_uf' => ['sometimes', 'nullable', 'string', 'size:2', 'regex:/^[A-Z]{2}$/'],
            'specialty' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
