<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\RoleRepo;
use App\Utils\RegexUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(protected RoleRepo $roleRepo) {}


    //
    /**
     * @OA\Get(
     *     path="/api/roles",
     *     summary="roles.index",
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
        return $this->jsonResponse($this->roleRepo->paginate());
    }


    /**
     * @OA\Get(
     *     path="/api/roles/{id}",
     *     summary="roles.show",
     *     tags={"Roles & Permissions"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *     ),
     * )
     */
    public function show(string $id)
    {
        $role = $this->roleRepo->findOrFail($id);
        $role->load('permissions');
        return $this->jsonResponse($role);
    }


    /**
     * @OA\Post(
     *     path="/api/roles",
     *     summary="roles.store",
     *     tags={"Roles & Permissions"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","permissions_id"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="permissions_id", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *     ),
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'               => ['required', 'string', RegexUtils::REGEX_TAGS],
            'permissions_id'     => 'array',
            'permissions_id.*'   => 'required|integer'
        ]);
        if ($validator->fails()) return $this->jsonResponse($validator->errors(), 400, 'Validation Fail');
        $attributes = $request->only(['name']);
        $attributes['guard_name'] = 'api';
        $role = Role::create($attributes);
        if ($request->has('permissions_id')) {
            $permissions = Permission::whereIn('id', $request->input('permissions_id'))->get();
            $role->givePermissionTo($permissions);
        }
        $role->permissions = $role->getPermissionNames();
        return $this->jsonResponse($role);
    }


    /**
     * @OA\Put(
     *     path="/api/roles/{id}",
     *     summary="roles.update",
     *     tags={"Roles & Permissions"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","permissions_id"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="permissions_id", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *     ),
     * )
     */
    public function update(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'permissions_id'  => 'required_with:permissions|array',
            'permissions_id.*' => 'exists:permissions,id',
        ]);
        if ($validator->fails()) {
            return $this->jsonResponse($validator->errors(), 400, 'Validation Fail');
        }
        $role = Role::findOrFail($id);
        $role->update($request->only('name'));
        if ($request->has('permissions_id')) {
            $role->permissions()->detach();
            $permissions = Permission::whereIn('id', $request->input('permissions_id'))->get();
            $role->givePermissionTo($permissions);
        }
        $role->permissions = $role->getPermissionNames();
        return $this->jsonResponse($role);
    }

    /**
     * @OA\Delete(
     *     path="/api/roles/{id}",
     *     summary="roles.destroy",
     *     tags={"Roles & Permissions"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true),
     *     @OA\Response(
     *         response=202,
     *         description="Success",
     *     ),
     * )
     */
    public function destroy(int $id)
    {
        $role = $this->roleRepo->findOrFail($id);
        if ($role['name'] == User::ROLE_SUPER_ADMIN) return $this->jsonResponse([], 403, 'Cannot delete this role');
        $role->permissions()->detach();
        $role->delete();
        return $this->jsonResponse([], 202);
    }
}
