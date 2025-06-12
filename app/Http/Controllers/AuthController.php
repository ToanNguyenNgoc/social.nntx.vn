<?php

namespace App\Http\Controllers;

use App\Jobs\VerifyRegisterMail;
use App\Models\MediaTemporary;
use App\Models\Otp;
use App\Models\User;
use App\Utils\CommonUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    //
    public function login(Request $request, $withPassword = true)
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
        if (!$user->email_verified_at) return $this->jsonResponse([], 400, 'Account is not verify!');
        if($withPassword == true){
            if (!Hash::check($request->get('password'), $user->password)) return $this->jsonResponse([], 401, 'Wrong password');
        }
        $user->token = $user->createToken('api_token')->plainTextToken;
        return $this->jsonResponse($user);
    }

    public function loginGoogle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'credential'     => 'required'
        ]);
        if ($validator->fails()) {
            return $this->jsonResponse($validator->errors(), 400, 'Validation Fail');
        }
        $credential = $request->get('credential');
        $payload = CommonUtils::decodeJwtPayload($credential);
        if (!$payload || !$payload['email']) {
            return $this->jsonResponse([], 403, 'Invalidate code from Google');
        }

        $user = User::firstOrCreate(
            ['email' => $payload['email']],
            [
                'name' => $payload['name'] ?? $payload['email'],
                'email_verified_at' => now(),
                'password' => $payload['iat'],
                'platform' => User::PLATFORM_REGISTER_GOOGLE,
                'avatar_social_url' => $payload['picture'],
            ]
        );

        $request->replace([
            'email' => $user->email,
            'password' => $user->email,
        ]);

        return $this->login($request, false);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'     => 'required|email',
            'telephone' => 'digits_between:8,15',
            'password'  => 'required|between:6,128',
            'gender'    => 'in:0,1',
            'birthday'  => 'nullable|date|before:today'
        ]);
        if ($validator->fails()) return $this->jsonResponse($validator->errors(), 400, 'Validation Fail');
        if (User::where('email', $request->get('email'))->whereNotNull('email_verified_at')->first()) {
            return $this->jsonResponse([], 400, 'Email is exist!');
        }
        if ($request->has('telephone') && User::where('telephone', $request->get('telephone'))->whereNotNull('email_verified_at')->first()) {
            return $this->jsonResponse([], 400, 'Telephone is exist!');
        }
        $user = User::updateOrCreate(
            ['email' => $request->get('email')],
            $request->only(['name', 'telephone', 'password', 'gender', 'birthday'])
        );
        if ($request->has('otp')) {
            $otp = Otp::where(['email' => $request->get('email'), 'otp' => $request->get('otp')])->orderBy('created_at', 'desc')->first();
            if (!$otp) return $this->jsonResponse([], 404, 'OTP code is invalidate');
            if (Carbon::now()->gt($otp->expired_at)) return $this->jsonResponse([], 400, 'OTP code has expired');
            Otp::where('email', $request->get('email'))->delete();
            $user->email_verified_at = Carbon::now();
            $user->save();
        } else {
            VerifyRegisterMail::dispatch($request->get('email'));
        }
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
