<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Repositories\FollowRepo;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function __construct(protected FollowRepo $follow_repo) {}

    public function index(Request $request)
    {
        $my = $this->onUserAuth();
        $follows = $this->follow_repo->filter->where('user_id', $request->has('user_id') ? $request->get('user_id') : $my->id);
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
