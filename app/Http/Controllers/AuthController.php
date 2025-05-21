<?php

namespace App\Http\Controllers;

use App\Models\MediaTemporary;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    //
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'     => 'required|email',
            'password'  => 'required|between:6,128'
        ]);
        if ($validator->fails()) {
            return $this->jsonResponse($validator->errors(), 400, 'Validation Fail');
        }
        $user = User::where('email', $request->get('email'))->first();
        if (!$user) return $this->jsonResponse([], 404, 'User not found');
        if (!Hash::check($request->get('password'), $user->password)) return $this->jsonResponse([], 401, 'Wrong password');
        $user->token = $user->createToken('api_token')->plainTextToken;
        return $this->jsonResponse($user);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'     => 'email|unique:users',
            'telephone' => 'required|unique:users|digits_between:8,15',
            'password'  => 'required|between:6,128',
            'gender'    => 'in:0,1',
            'birthday'  => 'nullable|date|before:today'
        ]);
        if ($validator->fails()) return $this->jsonResponse($validator->errors(), 400, 'Validation Fail');
        $user = User::create($request->all());
        return $this->jsonResponse($user->refresh());
    }

    public function profile()
    {
        $user = $this->onUserAuth();
        $user['roles'] = $user->getRoleNames()->pluck('name');
        return $this->jsonResponse($user);
    }
    public function profileUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'     => 'email|unique:users',
            'telephone' => 'unique:users|digits_between:8,15',
            'gender'    => 'in:0,1',
            'birthday'  => 'nullable|date|before:today'
        ]);
        $user = $this->onUserAuth();
        if ($validator->fails()) return $this->jsonResponse($validator->errors(), 400, 'Validation Fail');
        $this->addMediaToModel($user, $request->get('media_id'), MediaTemporary::COLLECTION_AVATAR);
        $user->update($request->only('email', 'telephone', 'name', 'gender', 'birthday'));
        return $this->jsonResponse($user);
    }
}
