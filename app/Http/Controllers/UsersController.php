<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Image;
use App\Role;
use App\Classes\System;
use Hash;
use Auth;
//use App\Profile;

class UsersController extends Controller
{
	public $userId;
	public function __construct(Request $request)
	{
		if($request->header('Authorization') != null){
			$token = $request->header('Authorization');
			$this->userId = System::getUserId($token);
		}
	}
    public function detailUser($userId)
	{ 
		$dataUser = User::where('id', $userId)->first();
		
		/*$profile = Profile::where('userId', $userId)->first();
		$dataUser['name'] = isset($profile->name)?$profile->name:"Name is not defined Yet";
		$dataUser['phone'] = isset($profile->phone)?$profile->phone:"Phone is not defined Yet";
		$dataUser['address'] = isset($profile->address)?$profile->address:"Address is not defined Yet";*/
		if($dataUser->imageId != null){
			$image = Image::select('location')->where('id', $dataUser['imageId'])->first();
			$dataUser['image'] = $image->location;
		}
		$role = Role::select('name')->where('id', $dataUser['roleId'])->first();
		$dataUser['role']= $role->name;
		
		if(isset($dataUser))
		{
			return response()->json($dataUser);
		};
		
		return response()->json(['Data Not Found'], 404);
	}
	
	public function updateUsersData(Request $request, $userId) 
	{
		$dataUser = $request->input('UsersData');
		$validate = \Validator::make($dataUser,[
			'roleId' => 'required'
		]);
		
		if($validate->fails()){
			return response()->json(['There is an Empty Data'], 404);
		}
		$updateResult = User::where('id', $userId)->update($dataUser);
		
		return response()->json($updateResult == 1?["status" => true, "title" => "Success", "message" => "Data Successfully Update"]:["status" => false, "title" => "Failed", "message" => "Update Data Failed"]);
	}
	
	public function getUsersData(){
		$user = User::dataUser();
		
		//$image = Image::select('location')->where('id', $user->imageId);
		//var_dump(json_encode($user));die;
		
		
		//$dataUser['image'] = $image->location;
		//$user = User::get();
		//$role = Role::select('name')->where('id', $user['roleId'])->get();
		//var_dump($user('roleId'));die;
		return response()->json($user);
	}
	
	public function getInactiveUsersData(){
		//$user = User::where('active', 0)->get();
		$user = User::join('roles', 'users.roleId','=', 'roles.id')->select('users.id', 'users.username', 'roles.name as role')->where('users.active', 0)->get();
		//$user['role'] = $user->name;
		/*$user = [
			'role' => $user[{'name'}]
		];*/
		return response()->json($user);
	}
	
	public function getAllRole(){
		return Role::allRole();
	
	}
	
	public function deleteUser($userId)
	{
		$data = ['active'=>0];
		$response = User::where('id',$userId)->update($data);
		if($response == 1)
		{
			return response()->json(['Delete']);
		};
		
		return response()->json(['Data Not Found'], 404);
	}
	
	public function changeEmail(Request $request)
	{ 
		$data = $request->input('Email');
		$validate = \Validator::make($data, [
			'email' => 'required',
			'newEmail' => 'required'
		]);
		//var_dump();die;
		if(User::where(['id' => $this->userId, 'email' => $data['email']])->exists())
		{
			$data = [
				'email' => $data['newEmail']
			];
			$updateResult = User::where('id', $this->userId)->update($data);
			
			return response()->json($updateResult == 1?["status" => true,"title" => "Success", "message" => "Email Successfully Change"]:["Change Email Failed"]);
		}
		return response()->json(['Email Not Match'], 404);
	}
	
	public function changePass(Request $request)
	{ 
		$data = $request->input('Password');
		$validate = \Validator::make($data, [
			'password' => 'required',
			'newPassword' => 'required'
		]);
		$user = User::select('password')->where('id',$this->userId)->first();
		if(Hash::check($data['password'],  $user->password))
		{
			$data = [
				'password' => bcrypt($data['newPassword'])
			];
			$updateResult = User::where('id', $this->userId)->update($data);
			return response()->json($updateResult == 1?["status" => true,"title" => "Success", "message" => "Password Successfully Change"]:["Change Password Failed"]);
		}
		return response()->json(['Password Not Match'], 404);
	}
	
	public function activatingUser($userId)
	{
		$data = ['active'=>1];
		$response = User::where('id',$userId)->update($data);
		if($response == 1)
		{
			return response()->json(['Deleted']);
		};
		
		return response()->json(['Data Not Found'], 404);
	}
	
}
