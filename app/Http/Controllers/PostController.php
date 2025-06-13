<?php

namespace App\Http\Controllers;

use App\Models\MediaTemporary;
use App\Models\Post;
use App\Repositories\PostRepo;
use App\Utils\RegexUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class PostController extends Controller
{
    //
    public function __construct(protected PostRepo $post_repo) {}

    /**
     * @OA\Get(
     *     path="/api/posts",
     *     summary="posts.index",
     *     tags={"Posts"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(name="limit", in="query", required=false, @OA\Schema(type="integer", example=15)),
     *     @OA\Parameter(name="filter[keyword]", in="query", required=false),
     *     @OA\Parameter(name="filter[status]", in="query", required=false, @OA\Schema(type="boolean", example=true)),
     *     @OA\Parameter(name="include", in="query", required=false, description="user|favorites"),
     *     @OA\Parameter(name="sort", in="query", required=false, description="-id, -created_at"),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *     ),
     * )
     */
    public function index()
    {
        return $this->jsonResponse($this->post_repo->paginate());
    }

    /**
     * @OA\Get(
     *     path="/api/posts/{id}",
     *     summary="posts.show",
     *     tags={"Posts"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true),
     *     @OA\Parameter(name="include", in="query", required=false, description="user|favorites"),
     *     @OA\Parameter(name="sort", in="query", required=false, description="-id, -created_at"),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *     ),
     * )
     */
    public function show(int $id)
    {
        return $this->jsonResponse($this->post_repo->findOrFail($id));
    }

    /**
     * @OA\Post(
     *     path="/api/posts",
     *     summary="posts.store",
     *     tags={"Posts"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content","media_ids"},
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="media_ids", type="array", @OA\Items(type="integer"))
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
            'content'       => ['required', 'string', RegexUtils::REGEX_TAGS],
            'media_ids'     => 'array',
            'media_ids.*'   => 'required|integer'
        ]);
        if ($validator->fails()) return $this->jsonResponse($validator->errors(), 400, 'Validation Fail');
        $post = $this->post_repo->create([
            'user_id' => $this->onUserAuth()->id,
            'content' => $request->content,
        ]);
        if ($request->has('media_ids')) {
            foreach ($request->media_ids as $media_id) {
                $this->addMediaToModel($post, $media_id, MediaTemporary::COLLECTION_POST);
            }
        }
        return $this->jsonResponse($post->refresh());
    }

    /**
     * @OA\Put(
     *     path="/api/posts/{id}",
     *     summary="posts.update",
     *     tags={"Posts"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content","media_ids"},
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="media_ids", type="array", @OA\Items(type="integer"))
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
            'media_ids'     => 'array',
            'media_ids.*'   => 'required|integer'
        ]);
        if ($validator->fails()) return $this->jsonResponse($validator->errors(), 400, 'Validation Fail');
        $user_id = $this->onUserAuth()->id;
        $post = Post::where('user_id', $user_id)->findOrFail($id);
        foreach ($request->media_ids as $media_id) {
            $this->addMediaToModel($post, $media_id, MediaTemporary::COLLECTION_POST);
        }
        $post->update($request->only(['content', 'status']));
        return $this->jsonResponse($post->refresh());
    }

    /**
     * @OA\Delete(
     *     path="/api/posts/{id}",
     *     summary="posts.destroy",
     *     tags={"Posts"},
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
        Post::where('user_id', $this->onUserAuth()->id)->findOrFail($id)->delete();
        return $this->jsonResponse([], 202);
    }
}
