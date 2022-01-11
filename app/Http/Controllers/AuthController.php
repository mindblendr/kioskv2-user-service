<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
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
        
        Redis::set($user->id, $token, 'EX', env('REDIS_TTL', 3600));
        return response([
            'data' => [
                'token'=> $token,
                'type' => $user->type
            ],
            'status' => 1
        ]);
    }

    public function register(Request $request)
    {
        $user_data = $request->only([     
            'email',     
            'username',
            'firstname',
            'lastname',
            'phone',
            'password',
            'password_raw',
            'pin',
            'type',
            'status',
            'login_id',
        ]);
        
        $user_data['password_raw'] = $user_data['password'];
        $user_data['password'] = Hash::make($user_data['password']);

        $user = User::create($user_data);
        return response($user);
    }

    public function profile(Request $request)
    {
        $token = explode(' ', $request->header('Authorization'))[1];
        $decoded_token = JWT::decode($token, new Key(config('jwt.secret'), config('jwt.algo')));
        
		$user = User::find($decoded_token->sub);

        $status = ($user) ? 1 : 0;

		return response([
			'data' => $user,
			'status' => $status
		]);
    }

	public function logout(Request $request)
	{
        $token = explode(' ', $request->header('Authorization'))[1];
        $decoded_token = JWT::decode($token, new Key(config('jwt.secret'), config('jwt.algo')));
        $redis_token = Redis::del($decoded_token->sub);
        
        $status = ($redis_token) ? 1 : 0;

		return response()->json([
		 	'status' => $status
		]);
	}
}
