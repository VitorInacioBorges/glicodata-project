<?php

namespace App\Http\Controllers;

use App\Repositories\ReportRepository;

class ReportController extends RepositoryController
{
    public function __construct(ReportRepository $repository)
    {
        parent::__construct($repository);
    }
}
