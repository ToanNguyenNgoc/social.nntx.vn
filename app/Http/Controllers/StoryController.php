<?php

namespace App\Http\Controllers;

use App\Models\MediaTemporary;
use App\Models\Story;
use App\Repositories\StoryRepo;
use App\Utils\RegexUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StoryController extends Controller
{

    public function __construct(protected StoryRepo $storyRepo) {}

    /**
     * @OA\Get(
     *     path="/api/stories",
     *     summary="stories.index",
     *     tags={"Stories"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="page",in="query",required=false,@OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="limit", in="query", required=false, @OA\Schema(type="integer", example=15)),
     *     @OA\Parameter(name="include", in="query", required=false, description="favorites|views"),
     *     @OA\Parameter(name="sort", in="query", required=false, description="-id, -created_at"),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *     ),
     * )
     */
    public function index(Request $request)
    {
        $stories = $this->storyRepo->filter->where('user_id', $this->onUserAuth()->id);
        return $this->jsonResponse($stories->paginate());
    }


    /**
     * @OA\Get(
     *     path="/api/stories/{id}",
     *     summary="stories.show",
     *     tags={"Stories"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true),
     *     @OA\Parameter(name="include", in="query", required=false, description="favorites|views"),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *     ),
     * )
     */
    public function show(int $id) {
        $story = $this->storyRepo->filter->where(['id' => $id, 'user_id' => $this->onUserAuth()->id])->first();
        if(!$story) return $this->jsonResponse([],404);
        return $this->jsonResponse($story);
    }


    /**
     * @OA\Post(
     *     path="/api/stories",
     *     summary="stories.store",
     *     tags={"Stories"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content","media_id"},
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="media_id", type="integer")
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
            'content'      => ['required', 'string', RegexUtils::REGEX_TAGS],
            'media_id'     => 'required|integer',
        ]);
        if ($validator->fails()) return $this->jsonResponse($validator->errors(), 400, 'Validation Fail');
        $user = $this->onUserAuth();
        $story = $this->storyRepo->create([
            'user_id' => $user->id,
            'content' => $request->input('content'),
            'expired_at' => Carbon::now()->addDay(1),
        ]);
        $this->addMediaToModel($story, $request->media_id, MediaTemporary::COLLECTION_STORY);
        return $this->jsonResponse($story);
    }

    /**
     * @OA\Delete(
     *     path="/api/stories/{id}",
     *     summary="stories.destroy",
     *     tags={"Stories"},
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
        Story::where('user_id', $this->onUserAuth()->id)->findOrFail($id)->delete();
        return $this->jsonResponse([], 202);
    }
}
