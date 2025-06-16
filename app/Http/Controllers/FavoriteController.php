<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Favorite;
use App\Models\Message;
use App\Models\Post;
use App\Models\Story;
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

    /**
     * @OA\Get(
     *     path="/api/favorites",
     *     summary="favorites.index",
     *     tags={"Favorites"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(name="limit", in="query", required=false, @OA\Schema(type="integer", example=15)),
     *     @OA\Parameter(name="include", in="query", required=false, description="user"),
     *     @OA\Parameter(name="sort", in="query", required=false, description="-id, -created_at"),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *     ),
     * )
     */
    public function index()
    {
        $favorites = $this->favorite_repo->filter;
        return $this->jsonResponse($favorites->paginate());
    }

    public function show(Request $request)
    {
        return $this->jsonResponse([]);
    }

    /**
     * @OA\Post(
     *     path="/api/favorites",
     *     summary="favorites.store",
     *     tags={"Favorites"},
     *     security={{"bearerAuth": {}}},
     *     description="favoritetable_type: POST, COMMENT, MESSAGE, STORY",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"favoritetable_id","favoritetable_type"},
     *             @OA\Property(property="favoritetable_id", type="integer"),
     *             @OA\Property(property="favoritetable_type", type="string"),
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
        if($favoritetable_type == Favorite::TYPE_STORY){
            $model_type = Story::class;
            $favoritetable = Story::findOrFail($favoritetable_id);
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

    /**
     * @OA\Delete(
     *     path="/api/favorites",
     *     summary="favorites.destroy",
     *     tags={"Favorites"},
     *     security={{"bearerAuth": {}}},
     *     description="favoritetable_type: POST, COMMENT, MESSAGE",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"favoritetable_id","favoritetable_type"},
     *             @OA\Property(property="favoritetable_id", type="integer"),
     *             @OA\Property(property="favoritetable_type", type="string"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=202,
     *         description="Success",
     *     ),
     * )
     */
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
