<?php

namespace App\Http\Controllers;

use App\Repositories\RiskRepository;

class RiskController extends RepositoryController
{
    public function __construct(RiskRepository $repository)
    {
        parent::__construct($repository);
    }
}
