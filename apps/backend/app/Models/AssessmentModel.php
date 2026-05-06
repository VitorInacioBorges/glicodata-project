<?php

// Representa o model, e a tabela por conseguinte, da tabela AVALIAÇÃO (avaliacao de cada medico para um paciente especifico) 

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AssessmentModel extends Model
{
    /** @use HasFactory<\Database\Factories\AssessmentFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'patient_id',
        'user_id',
        'ubs_id',
        'symptoms',
        'answers',
    ];

    protected $table = 'assessments';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'answers' => 'array',
        ];
    }

    /**
     * @return BelongsTo<PatientModel, $this>
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientModel::class, 'patient_id');
    }

    /**
     * @return BelongsTo<UserModel, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }

    /**
     * @return BelongsTo<UbsModel, $this>
     */
    public function ubs(): BelongsTo
    {
        return $this->belongsTo(UbsModel::class, 'ubs_id');
    }

    /**
     * @return HasOne<RiskModel, $this>
     */
    public function risk(): HasOne
    {
        return $this->hasOne(RiskModel::class, 'assessment_id');
    }

    /**
     * @return HasOne<ReportModel, $this>
     */
    public function report(): HasOne
    {
        return $this->hasOne(ReportModel::class, 'assessment_id');
    }
}
