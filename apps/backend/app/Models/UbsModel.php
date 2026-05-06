<?php

// Representa o model, e a tabela por conseguinte, da tabela UBS (ubs atrelada a um distrito especifico e que possui usuarios e pacientes) 

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UbsModel extends Model
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
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
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
}
