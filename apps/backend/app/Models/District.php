<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    /** @use HasFactory<\Database\Factories\DistrictFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
    ];

    /**
     * @return HasMany<Ubs, $this>
     */
    public function ubs(): HasMany
    {
        return $this->hasMany(Ubs::class);
    }
}
