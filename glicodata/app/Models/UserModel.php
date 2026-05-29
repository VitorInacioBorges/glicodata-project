<?php

// Representa o perfil da UBS que opera o sistema: profissional (medico ou enfermeiro) ou administrador.

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class UserModel extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasUuids, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'ubs_id',
        'name',
        'birth',
        'sex',
        'cpf',
        'address',
        'phone',
        'email',
        'password',
        'role',
    ];

    protected $table = 'users';

    /**
     * @var list<string>
     */
    protected $appends = [
        'age',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birth' => 'date',
            'sex' => 'boolean',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    /**
     * @return BelongsTo<UbsModel, $this>
     */
    public function ubs(): BelongsTo
    {
        return $this->belongsTo(UbsModel::class, 'ubs_id');
    }

    /**
     * @return HasMany<AssessmentModel, $this>
     */
    public function assessments(): HasMany
    {
        return $this->hasMany(AssessmentModel::class, 'user_id');
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birth?->age;
    }
}
