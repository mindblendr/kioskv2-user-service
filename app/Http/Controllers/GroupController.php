<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use Validator;

class GroupController extends Controller
{
    public function create(Request $request)
	{
		$validator = $this->validator($request, 'create');
		
		if ($validator->fails()) {
			return response()->json([
				'messaage' => $validator->errors(),
				'status' => 0
			]);
		}

		$group_data = $request->only([     
            'id',     
            'name'
        ]);

        $group = Group::create($group_data);

		$status = ($group) ? 1 : 0;

        return response()->json([
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

		
		$group_data = $request->only([     
            'id',     
            'name'
        ]);

        $group = Group::where('id', $request->id)->update($group_data);

        $status = ($group > 0) ? 1 : 0;

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

		$group = Group::where('id', $request->id)->delete();

     	$status = ($group > 0) ? 1 : 0;

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
        if($x == 'delete' || $x == 'get') {

        	$rules = [
	            'id' => 'required|integer'
	        ];

        }
        if($x == 'create') {

        	$rules = [
	        ];

        }
        else if($x == 'update') {

        	$rules = [
	        ];

        }
        $validate =  Validator::make($request->all(), $rules, $messages);

		return $validate;
    }
}
