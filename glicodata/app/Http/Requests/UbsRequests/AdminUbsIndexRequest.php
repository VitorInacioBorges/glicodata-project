<?php

namespace App\Http\Requests\UbsRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminUbsIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['active', 'pending'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function search(): ?string
    {
        $search = trim((string) $this->validated('q', ''));

        return $search !== '' ? $search : null;
    }

    public function active(): ?bool
    {
        return match ($this->validated('status')) {
            'active' => true,
            'pending' => false,
            default => null,
        };
    }
}
