<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Redis;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        $credentials = $request->only(['username', 'password']);

        $user = User::where(['username' => $credentials['username']])->first();
        
        if (! Hash::check($credentials['password'], $user->password)) {
            return response(['message' => 'Login failed'], 401);
        }

        $token = JWT::encode($user->getPayload(), config('jwt.secret'), config('jwt.algo'));

        return response([$token,Redis::set($user->id, $token, 'EX', env('REDIS_TTL', 3600))]);
    }

    public function register(Request $request)
    {
        $user_data = $request->only([
            'username',
            'email',
            'password',
            'type'
        ]);

        $user_data['password'] = Hash::make($user_data['password']);

        $user = User::create($user_data);
        return response($user);
    }

    public function me(Request $request)
    {
        return response(['message' => $request->get('user_data')]);
    }
}
