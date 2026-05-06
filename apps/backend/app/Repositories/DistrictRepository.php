<?php

namespace App\Repositories;

use App\Models\DistrictModel;

class DistrictRepository extends BaseRepository
{
    public function __construct(DistrictModel $model)
    {
        parent::__construct($model);
    }
}
