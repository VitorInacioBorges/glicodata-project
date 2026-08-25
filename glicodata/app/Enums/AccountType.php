<?php

namespace App\Enums;

enum AccountType: string
{
    case Ubs = 'ubs';
    case User = 'user';
    case Administrator = 'admin';

    public function ability(): string
    {
        return match ($this) {
            self::Ubs => 'ubs',
            self::User => 'user',
            self::Administrator => 'admin',
        };
    }

    public function guard(): string
    {
        return match ($this) {
            self::Ubs => 'ubs',
            self::User => 'user',
            self::Administrator => 'admin',
        };
    }
}
