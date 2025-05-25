<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Favorite;
use App\Models\Message;
use App\Models\Post;
use App\Repositories\FavoriteRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FavoriteController extends Controller
{
    //
    protected $user;
    public function __construct(protected FavoriteRepo $favorite_repo)
    {
        $this->user = $this->onUserAuth();;
    }

    public function index()
    {
        $favorites = $this->favorite_repo->filter;
        return $this->jsonResponse($favorites->paginate());
    }

    public function show(Request $request)
    {
        return $this->jsonResponse([]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'favoritetable_id'    => 'required',
            'favoritetable_type'  => 'required|in:' . Favorite::TYPE_POST . ',' . Favorite::TYPE_COMMENT . ',' . Favorite::TYPE_MESSAGE,
        ]);
        if ($validator->fails()) return $this->jsonResponse($validator->errors(), 400, 'Validation Fail');
        $favoritetable_id = $request->get('favoritetable_id');
        $favoritetable_type = $request->get('favoritetable_type');
        if ($favoritetable_type == Favorite::TYPE_POST) {
            $model_type = Post::class;
            $favoritetable = Post::findOrFail($favoritetable_id);
        }
        if ($favoritetable_type == Favorite::TYPE_COMMENT) {
            $model_type = Comment::class;
            $favoritetable = Comment::findOrFail($favoritetable_id);
        }
        if ($favoritetable_type == Favorite::TYPE_MESSAGE) {
            $model_type = Message::class;
            $favoritetable = Message::findOrFail($favoritetable_id);
        }

        $favorite_prev = Favorite::where([
            'user_id' => $this->user->id,
            'favoritetable_id' => $favoritetable_id,
            'favoritetable_type' => $model_type
        ])->first();
        if ($favorite_prev) {
            return $this->jsonResponse($favorite_prev);
        }
        $favorite = new Favorite();
        $favorite->user_id = $this->user->id;
        $favorite->favoritetable()->associate($favoritetable);
        $favorite->save();


        return $this->jsonResponse($favorite);
    }

    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'favoritetable_id'    => 'required',
            'favoritetable_type'  => 'required|in:' . Favorite::TYPE_POST . ',' . Favorite::TYPE_COMMENT . ',' . Favorite::TYPE_MESSAGE,
        ]);
        if ($validator->fails()) return $this->jsonResponse($validator->errors(), 400, 'Validation Fail');

        $favoritetable_id = $request->get('favoritetable_id');
        $favoritetable_type = $request->get('favoritetable_type');
        $model_type = Post::class;
        if ($favoritetable_type == Favorite::TYPE_COMMENT) $model_type = Comment::class;
        if ($favoritetable_type == Favorite::TYPE_MESSAGE) $model_type = Message::class;

        $favorite = Favorite::where([
            'user_id' => $this->user->id,
            'favoritetable_id' => $favoritetable_id,
            'favoritetable_type' => $model_type,
        ])->first();
        $favorite?->delete();
        return $this->jsonResponse([], 202);
    }
}
