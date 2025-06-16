<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\Story;
use App\Models\StoryView;
use App\Repositories\UserRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StoryViewController extends Controller
{
    //
    public function __construct(protected UserRepo $userRepo) {}


    /**
     * @OA\Get(
     *     path="/api/stories-views",
     *     summary="stories-views.index",
     *     tags={"Stories"},
     *     security={{"bearerAuth": {}}},
     *     description="Get stories for client",
     *     @OA\Parameter(name="page",in="query",required=false,@OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="limit", in="query", required=false, @OA\Schema(type="integer", example=15)),
     *     @OA\Parameter(name="sort", in="query", required=false, description="-id, -created_at"),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *     ),
     * )
     */
    public function index(Request $request)
    {
        $user_id = $this->onUserAuth()->id;
        $stories = $this
            ->userRepo
            ->filter
            ->whereIn('id', function ($q) use ($user_id) {
                $q->select('user_id')->from('follows')->where('follower_user_id', $user_id);
            })
            ->whereHas('stories', fn($q) => $q->validExpired())
            ->with(['stories' => fn($q) => $q->validExpired()])
            ->withCount([
                'stories as total_stories' => fn($q) => $q->validExpired(),
                'stories as viewed_stories' => fn($q) =>
                $q->validExpired()->whereHas('views', fn($v) => $v->where('user_id', $user_id))
            ])
            ->orderByRaw('viewed_stories < total_stories desc')
            ->orderBy('viewed_stories');
        return $this->jsonResponse($stories->paginate());
    }


    /**
     * @OA\Post(
     *     path="/api/stories-views",
     *     summary="stories-views.store",
     *     tags={"Stories"},
     *     security={{"bearerAuth": {}}},
     *     description="Client posts view a story",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"story_id"},
     *             @OA\Property(property="story_id", type="integer"),
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
            'story_id'     => 'required|integer',
        ]);
        if ($validator->fails()) return $this->jsonResponse($validator->errors(), 400, 'Validation Fail');
        $user_id = $this->onUserAuth()->id;
        $story = Story::findOrFail($request->input('story_id'));
        $storyView = StoryView::firstOrCreate(
            [
                'user_id' => $user_id,
                'story_id' => $story->id
            ],
            [
                'user_id' => $user_id,
                'story_id' => $story->id
            ]
        );
        return $this->jsonResponse($storyView);
    }
}
