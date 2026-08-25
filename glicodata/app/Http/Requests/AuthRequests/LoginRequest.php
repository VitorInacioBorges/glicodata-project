<?php

namespace App\Http\Requests\AuthRequests;

use App\Enums\AccountType;
use App\Http\Requests\Support\ApiFormRequest;
use Illuminate\Validation\Rule;

class LoginRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->normalizeStrings(['account_type', 'identifier', 'device_name'], ['account_type']);
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
            'device_name' => ['required', 'string', 'max:100'],
        ];
    }
}
