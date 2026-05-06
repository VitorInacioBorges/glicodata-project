<?php

namespace App\Repositories;

use App\Models\UbsModel;

class UbsRepository extends BaseRepository
{
    public function __construct(UbsModel $model)
    {
        parent::__construct($model);
    }
}
