<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Image;
use App\Province;
use App\City;
use Storage;
use App\Classes\System;

class ProfileController extends Controller
{
	public $server = 'http://m.egemanusa.com';
	
	public $userId;
	public function __construct(Request $request)
	{
		$token = $request->header('Authorization');
		$this->userId = System::getUserId($token);
		//var_dump($userId);die;
	}
	
	
	public function getUserProfile()
	{
		if($this->userId){
			$user = User::where('id', $this->userId)->first();
			if($user->imageId != null){
				$image = Image::select('location')->where('id', $user->imageId)->first();
				
				$user['image'] = $image->location;
			}
			//var_dump($user);die;
			return response()->json($user);
		}
		else
		{
			return response()->json(['Data Not Found'], 404);
		}
	}
	
	public function updateProfile(Request $request)
	{
		$data = $request->input('updateData');
		$validate = \Validator::make($data, [
			'fullname' => 'required',
			'phone' => 'required',
			'address' => 'required',
			'provinceId' => 'required',
			'cityId' => 'required',
		]);
		
		if($validate->fails()){
			return response()->json(["message" => "There is an Empty Data"], 404);
		}
		if(isset($data['image'])){
			$dataImage['id'] = Image::generateId();
			$imageId = $dataImage['id'];
			$dataImage['imageType'] = 1;
			$image = $data['image']; 
			$image = str_replace('data:image/png;base64,', '', $image);
			$image = str_replace(' ', '+', $image);
			$fileName = 'US'.uniqid().'.'.'jpg';  
			$dataImage['imageName'] = $fileName; 
			Storage::put('/public/User/'.$fileName,base64_decode($image));
			$dataImage['location'] = $this->server.Storage::url('public/User/'.$fileName);
			
			$newImage = Image::create($dataImage);
			//$data ['imageId'] = $imageId;  
		}else{
			$user = User::select('imageId')->where('id',$this->userId)->first(); 
			$imageId = $user['imageId'];
		}
		$dataProfile = [
			'fullname' => $data['fullname'],
			'phone' => $data['phone'],
			'address' => $data['address'],
			'provinceId' => $data['provinceId'],
			'cityId' => $data['cityId'],
			'imageId' => $imageId
		];
		$updateResult = User::where('id',$this->userId)->update($dataProfile);
		
		//var_dump(json_encode($dataProfile));die;
		return response()->json($updateResult == 1?["status" => true, "title" => "Success", "message" => "Data Successfully Updated"]:["status" => false, "title" => "Failed", "message" => "Update Data Failed"]);
	}
	
	public function getProvince(){
		return Province::allProvince();
	}
	public function getCity(){
		return City::allCity();
	}
	
	public function getCityByProvince($provincesId){
		return City::where('provincesId', $provincesId)->get();
	}
}
