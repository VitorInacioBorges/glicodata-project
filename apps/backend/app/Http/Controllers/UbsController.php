<?php

namespace App\Http\Controllers;

use App\Repositories\UbsRepository;

class UbsController extends RepositoryController
{
    public function __construct(UbsRepository $repository)
    {
        parent::__construct($repository);
    }
}
