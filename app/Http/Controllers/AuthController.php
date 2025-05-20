<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    //
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|between:6,128'
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

    public function profile()
    {
        $user = $this->onUserAuth();;
        return $this->jsonResponse($user);
    }
}
