<?php

namespace App\Http\Controllers;

use App\Repositories\AssessmentRepository;

class AssessmentController extends RepositoryController
{
    public function __construct(AssessmentRepository $repository)
    {
        parent::__construct($repository);
    }
}
