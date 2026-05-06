<?php

namespace App\Repositories;

use App\Models\ReportModel;

class ReportRepository extends BaseRepository
{
    public function __construct(ReportModel $model)
    {
        parent::__construct($model);
    }
}
