<?php

namespace App\Http\Requests\AuthRequests;

use App\Enums\AccountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class WebLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'account_type' => Str::lower(trim((string) $this->input('account_type'))),
            'identifier' => trim((string) $this->input('identifier')),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $accountType = AccountType::tryFrom((string) $this->input('account_type'));
        $identifierRules = $accountType === AccountType::Ubs
            ? ['required', 'string', 'size:7', 'regex:/^[0-9]{7}$/']
            : ['required', 'string', 'max:40', 'regex:/^[A-Za-z0-9_-]+$/'];

        return [
            'account_type' => ['required', Rule::enum(AccountType::class)],
            'identifier' => $identifierRules,
            'password' => ['required', 'string', 'max:255'],
        ];
    }
}
