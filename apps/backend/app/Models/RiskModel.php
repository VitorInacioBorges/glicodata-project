<?php

// Representa o model, e a tabela por conseguinte, da tabela RISCO (risco que compõe parte de uma avaliação) 

namespace App\Models;

use App\Enums\RiskClassification;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskModel extends Model
{
    /** @use HasFactory<\Database\Factories\RiskFactory> */
    use HasFactory, HasUuids;

    public const UPDATED_AT = null;

    protected $fillable = [
        'assessment_id',
        'percentage',
        'classification',
        'score',
    ];

    protected $table = 'risks';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'percentage' => 'float',
            'classification' => RiskClassification::class,
            'score' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<AssessmentModel, $this>
     */
    public function assessment(): BelongsTo
    {
        return $this->belongsTo(AssessmentModel::class, 'assessment_id');
    }
}
