<?php

namespace App\Models;

use App\Enums\QuestionnaireVersionStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class QuestionnaireVersionModel extends Model
{
    use HasUuids;

    protected $table = 'questionnaire_versions';

    protected $fillable = [
        'questionnaire_id',
        'version',
        'status',
        'schema',
        'risk_rules',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'status' => QuestionnaireVersionStatus::class,
            'schema' => 'array',
            'risk_rules' => 'array',
            'published_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (QuestionnaireVersionModel $version): void {
            if ($version->getRawOriginal('status') === QuestionnaireVersionStatus::Published->value) {
                throw new LogicException('Versões publicadas de questionário são imutáveis. Crie uma nova versão.');
            }
        });
    }

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireModel::class, 'questionnaire_id');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(AssessmentModel::class, 'questionnaire_version_id');
    }
}
