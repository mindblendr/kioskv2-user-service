<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Validator;
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
		$validator = $this->validator($request, 'create');
		
		if ($validator->fails()) {
			return response()->json([
				'messaage' => $validator->errors(),
				'status' => 0
			]);
		}
        
        $user_data = $request->only([     
            'email',     
            'username',
            'firstname',
            'lastname',
            'phone',
            'money',
            'max_bet',
            'max_draw_bet',
            'board_access',
            'allowed_sides',
            'lang_code',
            'ui_code',
            'group_id',
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
        $status = ($user) ? 1 : 0;

        return response()->json([
			'status' => $status
		]);
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

    private function validator(Request $request, $x)
    {
    	//custom validation error messages.
        $messages = [
            'required' => 'The :attribute field is required.',
            'unique' => 'The :attribute field is already exist'
        ];

        //validate the request.
        if($x == 'create') {

        	$rules = [
                'email' => 'email|unique:user',
	            'username' => 'required|unique:user',
	            'password' => 'required|between:5,255|confirmed'
	        ];

        }
        $validate =  Validator::make($request->all(), $rules, $messages);

		return $validate;
    }
}
