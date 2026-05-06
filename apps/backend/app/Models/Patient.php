<?php

// Representa o model, e a tabela por conseguinte, da tabela PACIENTE (paciente atrelado a UBS) 

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    /** @use HasFactory<\Database\Factories\PatientFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'ubs_id',
        'name',
        'age',
        'sex',
        'cpf',
        'address',
        'phone',
        'birth',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'age' => 'integer',
            'sex' => 'boolean',
            'birth' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Ubs, $this>
     */
    public function ubs(): BelongsTo
    {
        return $this->belongsTo(Ubs::class);
    }

    /**
     * @return HasMany<Assessment, $this>
     */
    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }
}
