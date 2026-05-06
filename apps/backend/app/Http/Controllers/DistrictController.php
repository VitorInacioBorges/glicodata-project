<?php

namespace App\Http\Controllers;

use App\Repositories\DistrictRepository;

class DistrictController extends RepositoryController
{
    public function __construct(DistrictRepository $repository)
    {
        parent::__construct($repository);
    }
}
