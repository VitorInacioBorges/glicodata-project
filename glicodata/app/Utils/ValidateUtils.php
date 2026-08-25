<?php

namespace App\Utils;

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
}
