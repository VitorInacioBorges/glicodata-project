<?php

namespace App\Repositories;

use App\Models\PatientModel;

class PatientRepository extends BaseRepository
{
    public function __construct(PatientModel $model)
    {
        parent::__construct($model);
    }
}
