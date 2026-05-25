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

    /**
     * @param  array<int, string>  $fields
     */
    protected function normalizeNullableStrings(array $fields): void
    {
        $normalized = [];

        foreach ($fields as $field) {
            if (! array_key_exists($field, $this->all())) {
                continue;
            }

            $value = $this->input($field);

            if ($value === null) {
                $normalized[$field] = null;

                continue;
            }

            if (! is_string($value)) {
                continue;
            }

            $value = trim($value);
            $normalized[$field] = $value === '' ? null : $value;
        }

        $this->merge($normalized);
    }
}
