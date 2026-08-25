<?php

namespace App\Models;

use Database\Factories\AdministratorFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class AdministratorModel extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids;

    protected $table = 'administrators';

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    protected static function newFactory(): AdministratorFactory
    {
        return AdministratorFactory::new();
    }
}
