<?php

// Representa o enum da propriedade PAPEL (papel do usuario no sistema) 

namespace App\Enums;

enum UserRole: string
{
    case User = 'user';
    case Admin = 'admin';
}
