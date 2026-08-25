<?php

namespace App\Models;

use Database\Factories\ProfessionalFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProfessionalModel extends Model
{
    /** @use HasFactory<ProfessionalFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'professionals';

    protected $fillable = [
        'ubs_id',
        'first_name',
        'specialty',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function ubs(): BelongsTo
    {
        return $this->belongsTo(UbsModel::class, 'ubs_id');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(AssessmentModel::class, 'professional_id');
    }

    protected static function newFactory(): ProfessionalFactory
    {
        return ProfessionalFactory::new();
    }
}
