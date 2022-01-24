<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Validator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Redis;

class UserController extends Controller
{
    public function get(Request $request)
    {
        $validator = $this->validator($request, 'get');
        $user = User::with(['group'])->find($request->id);

        $status = ($user) ? 1 : 0;

        return response()->json([
			'data' => $user,
			'status' => $status
		]);
    }

    public function confirmPin(Request $request)
    {
        $validator = $this->validator($request, 'confirm-pin');
		
		if ($validator->fails()) {
			return response()->json([
				'messaage' => $validator->errors(),
				'status' => 0
			]);
		}

        $user = User::where([
			['type', $request->type],
			['pin', $request->pin]
		])->get();
		
        $status = (count($user)) ? 1 : 0;

        return response()->json([
			'status' => $status
		]);
    }

    public function list(Request $request)
	{
        $username = ($request->username) ? $request->username : '';

        $limit = ($request->limit) ? $request->limit : 50;
        $sort_column = ($request->sort_column) ? $request->sort_column : 'id';
        $sort_order = ($request->sort_order) ? $request->sort_order : 'desc';
		
		$user = User::with(['group'])->where('username', 'like', "%{$username}%");

		if($request->group_id){
            $user = $user->where('group_id', $request->group_id);
        }

		if($request->type){
            $user = $user->where('type', $request->type);
        }

		if($request->status){
            $user = $user->where('status', $request->status);
        }

		if($request->streaming){
            $user = $user->where('streaming', $request->streaming);
        }

		if($request->from && $request->to){
            $user = $user->whereBetween('created_at', [Carbon::parse($request->from)->format('Y-m-d H:i:s'), Carbon::parse($request->to)->format('Y-m-d H:i:s')]);
        }

		$user = $user->orderBy($sort_column, $sort_order)->paginate($limit);

        $status = ($user) ? 1 : 0;

        return response()->json([
			'data' => $user,
			'status' => $status
		]);
		
	}

    public function create(Request $request)
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
    
	public function edit(Request $request)
	{
		$validator = $this->validator($request, 'edit');

		if ($validator->fails()) {
			return response()->json([
				'messaage' => $validator->errors(),
				'status' => 0
			]);
		}

		$user = User::find($request->id);

		$status = ($user) ? 1 : 0; 

        return response()->json([
			'data' => $user,
			'status' => $status
		]);
	}

	public function update(Request $request)
	{
		$validator = $this->validator($request, 'update');

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

        $user = User::where('id', $request->id)->update($user_data);

        $status = ($user > 0) ? 1 : 0;

        return response()->json([
			'status' => $status
		]);
	}

	public function updateStreaming(Request $request)
	{
		$validator = $this->validator($request, 'update-streaming');

		if ($validator->fails()) {
			return response()->json([
				'messaage' => $validator->errors(),
				'status' => 0
			]);
		}

		$update = [
			'streaming' => $request->streaming
        ];

        $user = User::where('id', $request->id)->update($update);

        $status = ($user > 0) ? 1 : 0;

        return response()->json([
			'status' => $status
		]);
	}

	public function updateStatus(Request $request)
	{
		$validator = $this->validator($request, 'update-status');

		if ($validator->fails()) {
			return response()->json([
				'messaage' => $validator->errors(),
				'status' => 0
			]);
		}

		$update = [
			'status' => $request->status
        ];

        $user = User::where('id', $request->id)->update($update);

        $status = ($user > 0) ? 1 : 0;

        return response()->json([
			'status' => $status
		]);
	}

	public function updatePassword(Request $request)
	{
		$validator = $this->validator($request, 'update-password');

		if ($validator->fails()) {
			return response()->json([
				'messaage' => $validator->errors(),
				'status' => 0
			]);
		}

		$update = [
			'password' => Hash::make($request->password),
			'password_raw' => $request->password,
        ];

        $user = User::where('id', $request->id)->update($update);

        $status = ($user > 0) ? 1 : 0;

        return response()->json([
			'status' => $status
		]);
	}

	public function updatePin(Request $request)
	{
		$validator = $this->validator($request, 'update-pin');

		if ($validator->fails()) {
			return response()->json([
				'messaage' => $validator->errors(),
				'status' => 0
			]);
		}

		$update = [
			'pin' => $request->pin,
        ];

        $user = User::where('id', $request->id)->update($update);

        $status = ($user > 0) ? 1 : 0;

        return response()->json([
			'status' => $status
		]);
	}

	public function updateMaxbet(Request $request)
	{
		$validator = $this->validator($request, 'update-maxbet');

		if ($validator->fails()) {
			return response()->json([
				'messaage' => $validator->errors(),
				'status' => 0
			]);
		}

		$update = [
			'max_bet' => $request->max_bet,
        ];

        $user = User::where('id', $request->id)->update($update);

        $status = ($user > 0) ? 1 : 0;

        return response()->json([
			'status' => $status
		]);
	}

	public function updateGroupMaxbet(Request $request)
	{
		$validator = $this->validator($request, 'update-group-maxbet');

		if ($validator->fails()) {
			return response()->json([
				'messaage' => $validator->errors(),
				'status' => 0
			]);
		}

		$update = [
			'max_bet' => $request->max_bet,
        ];

        $user = User::where('group_id', $request->group_id)->update($update);

        $status = ($user > 0) ? 1 : 0;

        return response()->json([
			'status' => $status
		]);
	}

    public function delete(Request $request)
	{
		$validator = $this->validator($request, 'delete');

		if ($validator->fails()) {
			return response()->json([
				'messaage' => $validator->errors(),
				'status' => 0
			]);
		}

		$admin = User::where('id', $request->id)->delete();

     	$status = ($admin > 0) ? 1 : 0;

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
	            'password' => 'required|between:5,255|confirmed',
	            'status' => 'required|digits_between:0,1',
	        ];

        }
        else if($x == 'update') {

        	$rules = [
	            'id' => 'required|integer',
	            'username' => 'unique:user,username,' . $request->id
	        ];

        }
        else if($x == 'edit' || $x == 'delete' || $x == 'get') {

        	$rules = [
	            'id' => 'required|integer'
	        ];

        }
        else if($x == 'update-streaming') {

        	$rules = [
	            'id' => 'required|integer',
	            'streaming' => 'required|integer',
	        ];

        }
        else if($x == 'update-status') {

        	$rules = [
	            'id' => 'required|integer',
	            'status' => 'required|integer',
	        ];

        }
        else if($x == 'update-password') {

        	$rules = [
	            'id' => 'required|integer',
	            'password' => 'required|min:6',
	        ];

        }
        else if($x == 'update-pin') {

        	$rules = [
	            'id' => 'required|integer',
	            'pin' => 'required|min:6|max:6',
	        ];

        }
        else if($x == 'update-group-maxbet') {

        	$rules = [
	            'group_id' => 'required|integer',
	            'max_bet' => 'required',
	        ];

        }
        else if($x == 'update-maxbet') {

        	$rules = [
	            'id' => 'required|integer',
	            'max_bet' => 'required',
	        ];

        }
        else if($x == 'confirm-pin') {

        	$rules = [
	            'type' => 'required',
	            'pin' => 'required',
	        ];

        }

        $validate =  Validator::make($request->all(), $rules, $messages);

		return $validate;
    }
}
