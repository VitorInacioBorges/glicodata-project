<?php

namespace App\Utils;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

trait ValidateUtils
{
    /**
     * @throws ValidationException
     */
    private function validateId(string $id): void
    {
        $id = trim($id);

        if ($id === '' || ! Str::isUuid($id)) {
            throw ValidationException::withMessages([
                'id' => ['O id informado deve ser um UUID valido.'],
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    private function validateEmail(string $email): void
    {
        $validator = Validator::make(
            ['email' => trim($email)],
            ['email' => ['required', 'email:rfc', 'max:255']]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    private function normalizeEmail(string $email): string
    {
        return Str::lower(trim($email));
    }
}
