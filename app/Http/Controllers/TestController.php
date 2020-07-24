<?php

namespace App\Http\Controllers;

use App\Image;
use Storage;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function getSession() 
	{
		return response()->json(session()->get('token'));
	}
	
	public $server = 'http://m.egemanusa.com';
	public function imageUpload(Request $request){
		$data = $request->input('banner');
		$validate = \Validator::make($data, [
			'image' => 'required'
		]);
 
		if($validate->fails()){
			return response()->json(['There is an Empty Data'], 404);
		}
		$dataImage['id'] = Image::generateId();
		$imageId = $dataImage['id'];
		$dataImage['imageType'] = 3;
		$image = $data['image']; 
		$image = str_replace('data:image/png;base64,', '', $image);
		$image = str_replace(' ', '+', $image);
		$fileName = 'BN'.uniqid().'.'.'jpg'; 
		$dataImage['imageName'] = $fileName; 
		Storage::put('/public/Banner/'.$fileName,base64_decode($image));
		$dataImage['location'] = $this->server.Storage::url('public/Banner/'.$fileName);
		if(Image::create($dataImage) != null){
			return response()->json($dataImage);
		}
	}
	public function getAllBanners(){
		return Image::select('location')->where('imageType', 3)->get();  
	}
}
