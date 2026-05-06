<?php

namespace App\Repositories;

use App\Models\AssessmentModel;

class AssessmentRepository extends BaseRepository
{
    public function __construct(AssessmentModel $model)
    {
        parent::__construct($model);
    }
}
