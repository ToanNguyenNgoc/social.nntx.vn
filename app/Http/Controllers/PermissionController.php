<?php

namespace App\Http\Controllers;

use App\Repositories\PermissionRepo;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    //
    public function __construct(protected PermissionRepo $permissionRepo) {}


    /**
     * @OA\Get(
     *     path="/api/permissions",
     *     summary="permissions.index",
     *     tags={"Roles & Permissions"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(name="limit", in="query", required=false, @OA\Schema(type="integer", example=15)),
     *     @OA\Parameter(name="filter[keyword]", in="query", required=false),
     *     @OA\Parameter(name="sort", in="query", required=false, description="-id, -created_at"),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *     ),
     * )
     */
    public function index(Request $request)
    {
        return $this->jsonResponse($this->permissionRepo->paginate());
    }
}
