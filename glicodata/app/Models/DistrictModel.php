<?php

// Representa o distrito institucional associado as UBS.
// Distritos sao catalogo de leitura na API e nao expoem operacoes de escrita.

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DistrictModel extends Model
{
    /** @use HasFactory<\Database\Factories\DistrictFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
    ];

    protected $table = 'districts';

    /**
     * @return HasMany<UbsModel, $this>
     */
    public function ubs(): HasMany
    {
        return $this->hasMany(UbsModel::class, 'district_id');
    }
}
