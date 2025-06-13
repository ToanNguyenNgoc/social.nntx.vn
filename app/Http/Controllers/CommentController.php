<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\MediaTemporary;
use App\Models\Post;
use App\Repositories\CommentRepo;
use App\Utils\RegexUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    public function __construct(protected CommentRepo $comment_repo) {}
    //

    /**
     * @OA\Get(
     *     path="/api/comments",
     *     summary="comments.index",
     *     tags={"Comments"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(name="limit", in="query", required=false, @OA\Schema(type="integer", example=15)),
     *     @OA\Parameter(name="filter[commentable_type]", in="query", required=false, @OA\Schema(type="array", @OA\Items(type="string", enum={"POST","REPLY_COMMENT"}))),
     *     @OA\Parameter(name="filter[commentable_id]", in="query", required=false),
     *     @OA\Parameter(name="include", in="query", required=false, description="user|favorites"),
     *     @OA\Parameter(name="sort", in="query", required=false, description="-id, -created_at"),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *     ),
     * )
     */
    public function index(Request $request)
    {
        return $this->jsonResponse($this->comment_repo->paginate());
    }

    /**
     * @OA\Get(
     *     path="/api/comments/{id}",
     *     summary="comments.show",
     *     tags={"Comments"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true),
     *     @OA\Parameter(name="include", in="query", required=false, description="user|favorites"),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *     ),
     * )
     */
    public function show(int $id)
    {
        return $this->jsonResponse($this->comment_repo->findOrFail($id));
    }

    /**
     * @OA\Post(
     *     path="/api/comments",
     *     summary="comments.store",
     *     tags={"Comments"},
     *     security={{"bearerAuth": {}}},
     *     description="commentable_type: POST, REPLY_COMMENT",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"commentable_id","commentable_type","body","media_ids"},
     *             @OA\Property(property="commentable_id", type="integer"),
     *             @OA\Property(property="commentable_type", type="string"),
     *             @OA\Property(property="body", type="string"),
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
            'commentable_id'    => 'required',
            'commentable_type'  => 'required|in:' . Comment::COMMENTABLE_TYPE_POST . ',' . Comment::COMMENTABLE_TYPE_REPLY,
            'body'              => ['required', 'string', RegexUtils::REGEX_TAGS],
            'media_ids'         => 'array',
            'media_ids.*'       => 'required|integer'
        ]);
        if ($validator->fails()) return $this->jsonResponse($validator->errors(), 400, 'Validation Fail');
        $commentable_type = $request->get('commentable_type');
        $commentable_id = $request->get('commentable_id');
        if ($commentable_type == Comment::COMMENTABLE_TYPE_POST) {
            $commentable =  Post::findOrFail($commentable_id);
        }
        $comment = new Comment();
        $comment->user_id = $this->onUserAuth()->id;
        $comment->body = $request->get('body');
        $comment->commentable()->associate($commentable);
        $comment->save();
        if ($request->has('media_ids')) {
            foreach ($request->get('media_ids') as $media_id) {
                $this->addMediaToModel($comment, $media_id, MediaTemporary::COLLECTION_COMMENT);
            }
        }
        return $this->jsonResponse($comment);
    }

    /**
     * @OA\Put(
     *     path="/api/comments/{id}",
     *     summary="comments.update",
     *     tags={"Comments"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="body", type="string"),
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
            'body' => ['required', 'string', RegexUtils::REGEX_TAGS],
        ]);
        if ($validator->fails()) return $this->jsonResponse($validator->errors(), 400, 'Validation Fail');
        $comment = Comment::where('user_id', $this->onUserAuth()->id)->findOrFail($id);
        $comment->update($request->only(['body']));
        return $this->jsonResponse($comment->refresh());
    }

    /**
     * @OA\Delete(
     *     path="/api/comments/{id}",
     *     summary="comments.destroy",
     *     tags={"Comments"},
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
        Comment::where('user_id', $this->onUserAuth()->id)->findOrFail($id)->delete();
        $this->jsonResponse([], 202);
    }
}
