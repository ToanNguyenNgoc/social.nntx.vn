<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\MediaTemporary;
use App\Models\Post;
use App\Repositories\CommentRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    public function __construct(protected CommentRepo $comment_repo) {}
    //
    public function index(Request $request)
    {
        return $this->jsonResponse($this->comment_repo->paginate());
    }

    public function show(int $id)
    {
        return $this->jsonResponse($this->comment_repo->findOrFail($id));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'commentable_id'    => 'required',
            'commentable_type'  => 'required|in:' . Comment::COMMENTABLE_TYPE_POST . ',' . Comment::COMMENTABLE_TYPE_REPLY,
            'body'              => 'required',
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
}
