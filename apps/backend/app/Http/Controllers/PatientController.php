<?php

namespace App\Http\Controllers;

use App\Repositories\PatientRepository;

class PatientController extends RepositoryController
{
    public function __construct(PatientRepository $repository)
    {
        parent::__construct($repository);
    }
}
