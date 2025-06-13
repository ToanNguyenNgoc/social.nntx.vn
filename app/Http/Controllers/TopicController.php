<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use App\Models\TopicUser;
use App\Repositories\TopicRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TopicController extends Controller
{
    //
    protected $user;
    public function __construct(protected TopicRepo $topic_repo)
    {
        $this->user = $this->onUserAuth();
    }

    /**
     * @OA\Get(
     *     path="/api/topics",
     *     summary="topics.index",
     *     tags={"Topics"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(name="limit", in="query", required=false, @OA\Schema(type="integer", example=15)),
     *     @OA\Parameter(name="include", in="query", required=false, description="users|messages|last_message"),
     *     @OA\Parameter(name="sort", in="query", required=false, description="-id, -created_at, -updated_at"),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *     ),
     * )
     */
    public function index()
    {
        $topics = $this->topic_repo->filter->whereHas('topicUsers', function ($query) {
            $query->where('user_id', $this->user->id)->whereNotNull('joined_at');
        });
        return $this->jsonResponse($topics->paginate());
    }

    /**
     * @OA\Get(
     *     path="/api/topics/{id}",
     *     summary="topics.show",
     *     tags={"Topics"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true),
     *     @OA\Parameter(name="include", in="query", required=false, description="users|messages|last_message"),
     *     @OA\Parameter(name="sort", in="query", required=false, description="-id, -created_at, -updated_at"),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *     ),
     * )
     */
    public function show(int $id)
    {
        $topic = $this->topic_repo->filter
            ->where('id', $id)
            ->whereHas('topicUsers', function ($query) {
                $query->where('user_id', $this->user->id)->whereNotNull('joined_at');
            })
            ->first();
        if (!$topic) return $this->jsonResponse([], 404);
        return $this->jsonResponse($topic);
    }

    /**
     * @OA\Post(
     *     path="/api/topics",
     *     summary="topics.store",
     *     tags={"Topics"},
     *     security={{"bearerAuth": {}}},
     *     description="type: DUOS, GROUP",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"recipient_ids","type"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="recipient_ids", type="array", @OA\Items(type="integer")),
     *             @OA\Property(property="type", type="string"),
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
            'recipient_ids'     => 'required|array',
            'recipient_ids.*'   => 'required|integer|exists:users,id',
            'type'              => 'required|in:' . Topic::TOPIC_TYPE_DUOS . ',' . Topic::TOPIC_TYPE_GROUP,
        ]);
        if ($validator->fails()) return $this->jsonResponse($validator->errors(), 400, 'Validation Fail');
        $recipient_ids = $request->get('recipient_ids');
        $type = count($request->get('recipient_ids')) > 1 ? Topic::TOPIC_TYPE_GROUP : $request->get('type');
        $name = $request->get('name');

        //Check has prev topic type=DUOS
        if ($type == Topic::TOPIC_TYPE_DUOS) {
            $topic = Topic::where('type', Topic::TOPIC_TYPE_DUOS)
                ->whereHas('topicUsers', function ($query) use ($recipient_ids) {
                    $query->whereIn('user_id', $recipient_ids);
                }, '=', count($recipient_ids))
                ->whereHas('topicUsers', function ($query) {
                    $query->where('user_id', $this->user->id);
                })
                ->first();
            if ($topic) {
                TopicUser::where('topic_id', $topic->id)->update(['joined_at' => now()]);
                return $this->jsonResponse($topic);
            }
        }
        //
        $topic = $this->topic_repo->create([
            'name' => $name,
            'type' => $type,
        ]);
        $topic->topicUsers()->save(new TopicUser(['user_id' => $this->user->id, 'joined_at' => now()]));
        foreach ($recipient_ids as $recipient_id) {
            $topic->topicUsers()->save(new TopicUser(['user_id' => $recipient_id, 'joined_at' => now()]));
        }
        return $this->jsonResponse($topic);
    }


    /**
     * @OA\Delete(
     *     path="/api/topics/{id}",
     *     summary="topics.destroy",
     *     tags={"Topics"},
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
        $topic = $this->topic_repo->filter
            ->where('id', $id)
            ->whereHas('topicUsers', function ($query) {
                $query->where('user_id', $this->user->id)->whereNotNull('joined_at');
            })
            ->first();
        if (!$topic) return $this->jsonResponse([], 404);
        $topic_user = TopicUser::where(['user_id' => $this->user->id, 'topic_id' => $topic->id])->first();
        $topic_user->update(['joined_at' => null]);
        return $this->jsonResponse([], 202);
    }
}
