<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientModel extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'patients';

    protected $fillable = [
        'ubs_id',
        'first_name',
        'sex',
        'neighborhood',
        'neighborhood_normalized',
        'street_name',
    ];

    protected function casts(): array
    {
        return ['sex' => 'boolean'];
    }

    public function ubs(): BelongsTo
    {
        return $this->belongsTo(UbsModel::class, 'ubs_id');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(AssessmentModel::class, 'patient_id');
    }
}
