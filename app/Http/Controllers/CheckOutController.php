<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CheckOutController extends Controller
{
	public $userId;
	public function __construct(Request $request)
	{
		if($request->header('Authorization') != null){
			$token = $request->header('Authorization');
			$this->userId = System::getUserId($token);
		}
	}
    public function checkout(Request $request)
	{
		if($request->header('guestId') != null)
		{
			$error= [
				'error' => [
					'code' => 404,
					'message' => 'Anda Belum Login'
				]
			]
			return response()->json($err);
		}
		return response()->json(['error'=> 'Anda Sudah Login']);
	}
}
