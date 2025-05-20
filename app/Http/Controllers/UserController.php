<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepo;

class UserController extends Controller
{
  public function __construct(protected UserRepo $user_repo) {}
  public function index()
  {
    return $this->jsonResponse($this->user_repo->paginate());
  }
}