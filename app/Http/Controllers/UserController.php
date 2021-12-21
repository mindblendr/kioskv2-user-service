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
        $user = User::find($request->id);
        
        $status = ($user) ? 1 : 0;

        return response()->json([
			'data' => $user,
			'status' => $status
		]);
    }

    public function list(Request $request)
	{
		$orWhere_columns = [
            'user.username',
            'user.status'
        ];

        $key = ($request->search_key) ? $request->search_key : '';

        if($request->search_key){
            $key = $request->search_key;
        }

        $limit = ($request->limit) ? $request->limit : 50;
        $sort_column = ($request->sort_column) ? $request->sort_column : 'id';
        $sort_order = ($request->sort_order) ? $request->sort_order : 'desc';

		$user = User::where(function ($q) use ($orWhere_columns, $key) {
                            foreach ($orWhere_columns as $column) {
                                $q->orWhere($column, 'LIKE', "%{$key}%");
                            }
                        });

		if($request->from && $request->to){
            $user = $user->whereBetween('created_at', [Carbon::parse($request->from)->format('Y-m-d H:i:s'), Carbon::parse($request->to)->format('Y-m-d H:i:s')]);
        }

		$user = $user->orderBy($sort_column, $sort_order)->paginate($limit);

        return response()->json([
			'data' => $user,
			'status' => 1
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

		$user = User::create([
			'email' => $request->email,
			'username' => $request->username,
			'firstname' => $request->firstname,
			'lastname' => $request->lastname,
			'password' => Hash::make($request->password),
			'password_raw' => $request->password,
			'pin' => $request->pin,
			'type' => $request->type,
			'status' => $request->status,
			'streaming' => $request->streaming,
		]);
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

		$update = [
			'email' => $request->email,
			'username' => $request->username,
			'firstname' => $request->firstname,
			'lastname' => $request->lastname,
			'password' => Hash::make($request->password),
			'password_raw' => $request->password,
			'type' => $request->type,
			'status' => $request->status
        ];

        $user = User::where('id', $request->id)->update($update);

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
                'email' => 'required|email|unique:user',
	            'username' => 'required|unique:user',
	            'password' => 'required|between:5,255|confirmed',
	            'status' => 'required|digits_between:0,1',
	        ];

        }
        else if($x == 'update') {

        	$rules = [
	            'id' => 'required|integer',
	            'username' => 'required|unique:user,username,' . $request->id,
	            'status' => 'required|digits_between:0,1',
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

        $validate =  Validator::make($request->all(), $rules, $messages);

		return $validate;
    }
}
