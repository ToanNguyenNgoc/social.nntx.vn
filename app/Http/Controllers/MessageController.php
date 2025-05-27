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

    public function index(Request $request)
    {
        $topic = $request->get('topic');
        $messages = $this->message_repo->filter->where('topic_id', $topic->id);
        return $this->jsonResponse($messages->paginate());
    }

    public function show(int $id)
    {
        return $this->jsonResponse($this->message_repo->findOrFail($id));
    }

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
            if ($this->onUserAuth()->id !== $user->id) {
                broadcast(new NotificationEvent(
                    $this->onUserAuth()->name . ' đã gửi tin nhắn',
                    $topic->id,
                    $this->onUserAuth()->id,
                    $user->id,
                    NotificationEvent::NOTI_TYPE_CHAT_MESSAGE
                ));
            }
        }
        return $this->jsonResponse($message->refresh());
    }
}
