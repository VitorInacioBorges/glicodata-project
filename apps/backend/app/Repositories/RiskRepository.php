<?php

namespace App\Repositories;

use App\Models\RiskModel;

class RiskRepository extends BaseRepository
{
    public function __construct(RiskModel $model)
    {
        parent::__construct($model);
    }
}
