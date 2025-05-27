<?php

namespace App\Http\Controllers;

use App\Events\NotificationEvent;
use App\Models\Follow;
use App\Repositories\FollowRepo;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function __construct(protected FollowRepo $follow_repo) {}

    public function index(Request $request)
    {
        $follows = $this->follow_repo;
        return $this->jsonResponse($follows->paginate());
    }
    public function store(Request $request) //Start follow user
    {
        $validator = Validator::make($request->all(), [
            'follower_user_id' => 'required|exists:users,id',
        ]);
        if ($validator->fails()) return $this->jsonResponse($validator->errors(), 400, 'Validation Fail');
        $user = $this->onUserAuth();
        if ($request->get('follower_user_id') == $user->id) return $this->jsonResponse([], 400, 'You can not flow yourself');
        $flow = Follow::firstOrCreate(
            [
                'user_id' => $request->get('follower_user_id'),
                'follower_user_id' => $user->id,
            ],
            [
                'user_id' => $request->get('follower_user_id'),
                'follower_user_id' => $user->id
            ]
        );
        broadcast(new NotificationEvent(
            $user->name . ' đã bắt đầu theo dõi bạn',
            $user->id,
            $user->id,
            $request->get('follower_user_id'),
            NotificationEvent::NOTI_TYPE_START_FOLLOW
        ));
        return $this->jsonResponse($flow);
    }
    public function delete(int $follower_user_id) //Unfollow user
    {
        $my_id = $this->onUserAuth()->id;
        $follow = Follow::where('user_id', $follower_user_id)->where('follower_user_id', $my_id)->first();
        $follow->delete();
        return $this->jsonResponse([], 202);
    }
}
