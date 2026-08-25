<?php

// Representa a UBS institucional ligada a um distrito, usuarios e pacientes.
// A API administra ativacao e dados cadastrais; nao expoe delecao normal de UBS.

namespace App\Models;

use Database\Factories\UbsFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class UbsModel extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UbsFactory> */
    use HasApiTokens, HasFactory, HasUuids;

    protected $table = 'ubs';

    protected $fillable = [
        'cnes',
        'district_id',
        'name',
        'bairro_ref',
        'address',
        'phone',
        'email',
        'password',
        'is_active',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<DistrictModel, $this>
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(DistrictModel::class, 'district_id');
    }

    /**
     * @return HasMany<UserModel, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(UserModel::class, 'ubs_id');
    }

    /**
     * @return HasMany<PatientModel, $this>
     */
    public function patients(): HasMany
    {
        return $this->hasMany(PatientModel::class, 'ubs_id');
    }

    /**
     * @return HasMany<AssessmentModel, $this>
     */
    public function assessments(): HasMany
    {
        return $this->hasMany(AssessmentModel::class, 'ubs_id');
    }

    /**
     * @return HasMany<AuditEventModel, $this>
     */
    public function auditEvents(): HasMany
    {
        return $this->hasMany(AuditEventModel::class, 'actor_ubs_id');
    }

    protected static function newFactory(): UbsFactory
    {
        return UbsFactory::new();
    }
}
