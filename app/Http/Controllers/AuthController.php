<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use JWTAuth;
use App\User;
use App\Role;
//use App\Cart;
use App\Cart;
use App\Classes\System;
use JWTAuthException;

class AuthController extends Controller
{
    private $user;
	public $userId;
	public function __construct(User $user, Request $request)
	{
		$this->user = $user;
		//var_dump($request->header('Authorization'));die;
		if($request->header('Authorization') != null){
			$token = $request->header('Authorization');
			$this->userId = System::getUserId($token);
		}
	}
	
	public function register(Request $request)
	{
		$data = $request->input('newUser');
		$validate = \Validator::make($data, [
			'username' => 'required',
			'email' => 'required',
			'password' => 'required'
		]);		
		if($validate->fails()){
			return response()->json(['There is an Empty Data'], 404);
		}
		
		if(User::where('username', $data['username'])->exists())
		{
			$failed=[
				'error' => [
					'code' => 404,
					'message' => 'Username Is Already Exists'
				]
			];
		}
		if(User::where('email', $data['email'])->exists())
		{
			$failed=[
				'error' => [
					'code' => 404,
					'message' => 'Email Is Already Exists'
				]
			];
		}
		if(isset($failed))
		{
			return response()->json($failed);
		}
		$user = $this->user->create([
			'id' => User::generateId(),
			'username' => $data['username'],
			'fullname' => "",
			'phone' => "",
			'address' => "",
			'provinceId' => 0,
			'cityId' => 0,
			'imageId' => 0,
			'email' => $data['email'],
			'password' => bcrypt($data['password']),
			'roleId' => 3,
			'active' => 1
		]);
	
		return response()->json(['status'=>true, 'message'=>'User created successfully','data'=>$user]);
	}
	
	public function login(Request $request)
	{
		$credentials = $request->only('username', 'password');
		$token = null;
		
		try{
			if(!$token = JWTAuth::attempt($credentials))
			{
				return response()->json(['Invalid Username or Password'], 404);
			}
			
		}catch (JWTAuthException $e){
			return response()->json(['Failed to Create Token'], 500);
		}
		//var_dump($token);die;
		$this->userId = System::getUserId($token);
		//var_dump($request->header('guestId'));die;
		if($request->header('guestId') != null)
		{
			//var_dump($request->header('guestId'));die;
			$guestId = $request->header('guestId');
			$data = [
				'session' => $this->userId
			];
			Cart::where('session', $guestId)->update($data);
			
			$orderStatus = true;
		}
		else
		{	
			$orderStatus = false;
		}
		if(User::where('id', $this->userId)->where('active', 0)->exists()){
			return response()->json(['Your Account is Inactive'], 404);
		}
		$userData = User::where('id', $this->userId)->first();
		$userRole = Role::select('name')->where('id', $userData['roleId'])->first();
		$address = $userData['address'];
		$role = $userRole->name;
		//session()->put('getSession', $token);
		return response()->json(compact('token', 'role', 'orderStatus', 'address'));
		
	}
	
	public function logout(Request $request)
	{
		if($request->header('Authorization') != null)
		{
			$userId = $this->userId;
		}
		$data = [
			'lastLogin' => date('Y-m-d H:i:s')
		];
		return User::where('id', $userId)->update($data);
	}
	
	public function getToken(Request $request)
	{
		$user = JWTAuth::toUser($request->token);
		//$newToken = JWTAuth::refresh($token);
		//var_dump($user);die;
		return response()->json($user);
	}
	
	public function refresh(Request $request)
	{
		$token = $request->get('token');
		$newToken = JWTAuth::refresh($token);
		//var_dump($user);die;
		return response()->json($newToken);
	}
	
	
	public function isUniqueValue(Request $request)
	{
		$id = $request->get('id');
		$property = $request->get('property');
		$value = $request->get('value');
		
		$user = User::where($property, $value)->get();
		$result = (count($user) == 0);
		
		return response()->json(array(
				'error' => false,
				'isUnique' => $result
			),
			200
		);
		
	}
	
}
