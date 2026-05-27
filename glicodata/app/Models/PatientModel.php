<?php

// Representa o model, e a tabela por conseguinte, da tabela PACIENTE (paciente atrelado a UBS)

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientModel extends Model
{
    /** @use HasFactory<\Database\Factories\PatientFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'ubs_id',
        'name',
        'sex',
        'cpf',
        'address',
        'phone',
        'birth',
    ];

    protected $table = 'patients';

    /**
     * @var list<string>
     */
    protected $appends = [
        'age',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sex' => 'boolean',
            'birth' => 'date',
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
        return $this->hasMany(AssessmentModel::class, 'patient_id');
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birth?->age;
    }
}
