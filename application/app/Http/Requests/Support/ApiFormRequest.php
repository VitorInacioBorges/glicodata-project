<?php

namespace App\Http\Requests\Support;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

abstract class ApiFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @param  array<int, string>  $fields
     * @param  array<int, string>  $lowercaseFields
     */
    protected function normalizeStrings(array $fields, array $lowercaseFields = []): void
    {
        $normalized = [];

        foreach ($fields as $field) {
            $value = $this->input($field);

            if (! is_string($value)) {
                continue;
            }

            $value = trim($value);
            $normalized[$field] = in_array($field, $lowercaseFields, true)
                ? Str::lower($value)
                : $value;
        }

        $this->merge($normalized);
    }
}
