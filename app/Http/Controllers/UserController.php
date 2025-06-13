<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepo;

class UserController extends Controller
{
  public function __construct(protected UserRepo $user_repo) {}

  /**
   * @OA\Get(
   *     path="/api/users",
   *     summary="users.index",
   *     tags={"Users"},
   *     security={{"bearerAuth": {}}},
   *     @OA\Parameter(
   *         name="page",
   *         in="query",
   *         required=false,
   *         @OA\Schema(type="integer", example=1)
   *     ),
   *     @OA\Parameter(name="limit", in="query", required=false, @OA\Schema(type="integer", example=15)),
   *     @OA\Parameter(name="filter[keyword]", in="query", required=false),
   *     @OA\Parameter(name="include", in="query", required=false, description="roles|followers"),
   *     @OA\Parameter(name="sort", in="query", required=false, description="-id, -created_at"),
   *     @OA\Response(
   *         response=200,
   *         description="Success",
   *     ),
   * )
   */
  public function index()
  {
    return $this->jsonResponse($this->user_repo->paginate());
  }

  /**
   * @OA\Get(
   *     path="/api/users/{id}",
   *     summary="users.show",
   *     tags={"Users"},
   *     security={{"bearerAuth": {}}},
   *     @OA\Parameter(name="id", in="path", required=true),
   *     @OA\Parameter(name="filter[keyword]", in="query", required=false),
   *     @OA\Parameter(name="include", in="query", required=false, description="roles|followers"),
   *     @OA\Response(
   *         response=200,
   *         description="Success",
   *     ),
   * )
   */
  public function show(int $id)
  {
    return $this->jsonResponse($this->user_repo->findOrFail($id));
  }
}
