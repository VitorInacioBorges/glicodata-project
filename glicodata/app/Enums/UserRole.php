<?php

// Representa o papel do perfil vinculado a uma UBS.

namespace App\Enums;

enum UserRole: string
{
    case Professional = 'professional';
    case Admin = 'admin';
}
