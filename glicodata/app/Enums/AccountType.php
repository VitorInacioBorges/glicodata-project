<?php

namespace App\Enums;

enum AccountType: string
{
    case Ubs = 'ubs';
    case Administrator = 'admin';

    public function ability(): string
    {
        return match ($this) {
            self::Ubs => 'ubs',
            self::Administrator => 'admin',
        };
    }

    public function guard(): string
    {
        return match ($this) {
            self::Ubs => 'ubs',
            self::Administrator => 'admin',
        };
    }
}
