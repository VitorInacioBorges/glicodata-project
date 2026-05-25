<?php

// Representa a UBS institucional ligada a um distrito, usuarios e pacientes.
// A API administra ativacao e dados cadastrais; nao expoe delecao normal de UBS.

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class UbsModel extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UbsFactory> */
    use HasFactory, HasUuids;

    protected $table = 'ubs';

    protected $fillable = [
        'district_id',
        'name',
        'bairro_ref',
        'address',
        'phone',
        'email',
        'password',
        'keycloak_id',
        'is_active',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'audit_admin',
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

    public function setAuditAdmin(bool $isAuditAdmin): self
    {
        $this->setAttribute('audit_admin', $isAuditAdmin);

        return $this;
    }

    public function isAuditAdmin(): bool
    {
        return (bool) $this->getAttribute('audit_admin');
    }
}
