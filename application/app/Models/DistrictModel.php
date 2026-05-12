<?php

// Representa o model, e a tabela por conseguinte, da tabela DISTRITO (distrito de cada UBS) 
// nao deve possuir formas de delecao para esse tipo de dado que nao sejam HARD DELETE

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
