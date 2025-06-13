<?php

namespace App\Http\Controllers;

use App\Events\ChatEvent;
use App\Events\NotificationEvent;
use App\Models\MediaTemporary;
use App\Repositories\MessageRepo;
use App\Utils\RegexUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    //
    protected $user;
    public function __construct(protected MessageRepo $message_repo)
    {
        $this->user = $this->onUserAuth();
    }

    /**
     * @OA\Get(
     *     path="/api/messages",
     *     summary="messages.index",
     *     tags={"Messages"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="topic_id", in="query", required=true),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(name="limit", in="query", required=false, @OA\Schema(type="integer", example=15)),
     *     @OA\Parameter(name="include", in="query", required=false, description="favorites|reply"),
     *     @OA\Parameter(name="sort", in="query", required=false, description="-id, -created_at"),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *     ),
     * )
     */
    public function index(Request $request)
    {
        $topic = $request->get('topic');
        $messages = $this->message_repo->filter->where('topic_id', $topic->id);
        return $this->jsonResponse($messages->paginate());
    }

    /**
     * @OA\Get(
     *     path="/api/messages/{id}",
     *     summary="messages.show",
     *     tags={"Messages"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="topic_id", in="query", required=true),
     *     @OA\Parameter(name="include", in="query", required=false, description="favorites|reply"),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *     ),
     * )
     */
    public function show(int $id)
    {
        return $this->jsonResponse($this->message_repo->findOrFail($id));
    }

     /**
     * @OA\Post(
     *     path="/api/messages",
     *     summary="messages.store",
     *     tags={"Messages"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"topic_id","body","media_ids"},
     *             @OA\Property(property="topic_id", type="integer"),
     *             @OA\Property(property="body", type="string"),
     *             @OA\Property(property="reply_id", type="integer"),
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
            'body'          => ['string', RegexUtils::REGEX_TAGS],
            'media_ids'     => 'array',
            'media_ids.*'   => 'required|integer',
            'reply_id'      => 'exists:messages,id'
        ]);
        $media_ids = $request->has('media_ids') ? $request->get('media_ids') : [];
        if ($validator->fails() || (!$request->has('body') && count($media_ids) == 0)) {
            return $this->jsonResponse($validator->errors(), 400, 'Validation Fail');
        }
        $topic = $request->get('topic');
        $topic->updated_at = now();
        $topic->save();
        $message = $this->message_repo->create([
            'topic_id' => $topic->id,
            'body' => $request->get('body'),
            'user_id' => $this->user->id,
            'reply_id' => $request->get('reply_id'),
        ]);
        foreach ($media_ids as $media_id) {
            $this->addMediaToModel($message, $media_id, MediaTemporary::COLLECTION_MESSAGE);
        }
        $topic = $request->get('topic');
        $topic->load('users');
        foreach ($topic->users as $user) {
            broadcast(new ChatEvent($message, $user->id));
            broadcast(new NotificationEvent(
                $this->onUserAuth()->name . ' đã gửi tin nhắn',
                $topic->id,
                $this->onUserAuth()->id,
                $user->id,
                NotificationEvent::NOTI_TYPE_CHAT_MESSAGE
            ));
        }
        return $this->jsonResponse($message->refresh());
    }
}
