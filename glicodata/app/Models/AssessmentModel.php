<?php

namespace App\Models;

use App\Enums\AssessmentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssessmentModel extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'assessments';

    protected $fillable = [
        'patient_id',
        'professional_id',
        'ubs_id',
        'questionnaire_version_id',
        'answers',
        'status',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'status' => AssessmentStatus::class,
            'started_at' => 'immutable_datetime',
            'completed_at' => 'immutable_datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientModel::class, 'patient_id')->withTrashed();
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(ProfessionalModel::class, 'professional_id')->withTrashed();
    }

    public function ubs(): BelongsTo
    {
        return $this->belongsTo(UbsModel::class, 'ubs_id');
    }

    public function questionnaireVersion(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireVersionModel::class, 'questionnaire_version_id');
    }

    public function risk(): HasOne
    {
        return $this->hasOne(RiskModel::class, 'assessment_id');
    }

    public function report(): HasOne
    {
        return $this->hasOne(ReportModel::class, 'assessment_id');
    }
}
