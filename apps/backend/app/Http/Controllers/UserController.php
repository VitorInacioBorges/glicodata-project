<?php

namespace App\Http\Controllers;

use App\Repositories\UserRepository;

class UserController extends RepositoryController
{
    public function __construct(UserRepository $repository)
    {
        parent::__construct($repository);
    }
}
