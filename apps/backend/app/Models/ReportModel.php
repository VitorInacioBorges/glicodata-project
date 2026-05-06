<?php

// Representa o model, e a tabela por conseguinte, da tabela RELATÓRIO (relatorio que compoe parte de uma avaliação) 

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportModel extends Model
{
    /** @use HasFactory<\Database\Factories\ReportFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'assessment_id',
        'comment',
        'description',
        'title',
    ];

    protected $table = 'reports';

    /**
     * @return BelongsTo<AssessmentModel, $this>
     */
    public function assessment(): BelongsTo
    {
        return $this->belongsTo(AssessmentModel::class, 'assessment_id');
    }
}
