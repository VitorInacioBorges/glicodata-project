<?php

// Representa o model, e a tabela por conseguinte, da tabela AVALIAÇÃO (avaliacao de cada medico para um paciente especifico) 

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Assessment extends Model
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
     * @return BelongsTo<Patient, $this>
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Ubs, $this>
     */
    public function ubs(): BelongsTo
    {
        return $this->belongsTo(Ubs::class);
    }

    /**
     * @return HasOne<Risk, $this>
     */
    public function risk(): HasOne
    {
        return $this->hasOne(Risk::class);
    }

    /**
     * @return HasOne<Report, $this>
     */
    public function report(): HasOne
    {
        return $this->hasOne(Report::class);
    }
}
