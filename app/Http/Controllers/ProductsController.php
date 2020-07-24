<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\Image;
use App\Stock;
use App\Category;
use Storage;

class ProductsController extends Controller
{
	//public $server = 'http://m.egemanusa.com';
	public $server = 'http://localhost:8000';
	
    public function newProduct(Request $request)
	{
		$data = $request->input('newProduct');
		$validate = \Validator::make($data, [
			'title' => 'required',
			'categoryId' => 'required',
			'price' => 'required',
			'description' => 'required',
			'stock' => 'required',
			'image' => 'required'
		]);
 
		if($validate->fails()){
			return response()->json(['There is an Empty Data'], 404);
		}
		$dataImage['id'] = Image::generateId();
		$imageId = $dataImage['id'];
		$dataImage['imageType'] = 1;
		$image = $data['image']; 
		$image = str_replace('data:image/png;base64,', '', $image);
		$image = str_replace(' ', '+', $image);
		$fileName = 'PD'.uniqid().'.'.'jpg';  
		$dataImage['imageName'] = $fileName; 
		Storage::put('/public/Product/'.$fileName,base64_decode($image));
		$dataImage['location'] = $this->server.Storage::url('public/Product/'.$fileName);
		if(Image::create($dataImage) != null)
		{
		
			$data ['id'] = Product::generateId();
			$productId = $data['id'];
			$data ['deleted'] = 0;
			$data ['imageId'] = $imageId;  
			//var_dump($data ['imageId']);die;
			//var_dump(Product::create($data)!= null);die;
			
			if(Product::create($data)!= null)
			{
				$dataStock = [ 
					'id' => Stock::generateId(),
					'qty' => $data['stock'],
					'productId' => $productId, 
					'incoming' => 1,
					'booked' => 0,
					'balance' => $data['stock']
				];
				if(Stock::create($dataStock)){
					return response()->json(['Create New Product']);
				}
			
				return response()->json(['Add Stock Failed'], 404);
			}
			return response()->json(['Create New Product Failed'], 404);
		}
		return response()->json(['Image Not Found'], 404);
	}
	
	/*public function imageUpload(Request $request){
		$data = $request->input('newProduct');
		$validate = \Validator::make($data, [
			'title' => 'required',
			'categoryId' => 'required',
			'price' => 'required',
			'description' => 'required',
			'stock' => 'required',
			'image' => 'required'
		]); 
 
		if($validate->fails()){
			return response()->json(['There is an Empty Data'], 404);
		}
		$dataImage['id'] = Image::generateId();
		$imageId = $dataImage['id'];
		$dataImage['imageType'] = 1;
		$image = $data['image']; 
		$image = str_replace('data:image/png;base64,', '', $image);
		$image = str_replace(' ', '+', $image);
		$fileName = 'COBA_'.uniqid().'.'.'jpg';  
		$dataImage['imageName'] = $fileName; 
		Storage::put('/public/product/'.$fileName,base64_decode($image));
		$dataImage['location'] = $this->server.Storage::url('public/product/'.$fileName);
		if(Image::create($dataImage) != null)
		{
		
			$data ['id'] = Product::generateId();
			$productId = $data['id'];
			$data ['deleted'] = 0;
			$data ['imageId'] = $imageId;  
			//var_dump($data ['imageId']);die;
			//var_dump(Product::create($data)!= null);die;
			
			if(Product::create($data)!= null)
			{
				$dataStock = [ 
					'id' => Stock::generateId(),
					'qty' => $data['stock'],
					'productId' => $productId, 
					'incoming' => 1,
					'booked' => 0,
					'balance' => $data['stock']
				];
				if(Stock::create($dataStock)){
					return response()->json(['Create New Product']);
				}
			
				return response()->json(['Add Stock Failed'], 404);
			}
			return response()->json(['Create New Product Failed'], 404);
		}
		return response()->json(['Image Not Found'], 404);
	}	
	public function newProduct(Request $request)
	{
		$data = $request->input('newProduct');
		$validate = \Validator::make($data, [
			'title' => 'required',
			'categoryId' => 'required',
			'price' => 'required',
			'description' => 'required',
			'stock' => 'required',
			'image' => 'required'
		]);
 
		if($validate->fails()){
			return response()->json(['There is an Empty Data'], 404);
		}
		$dataImage['id'] = Image::generateId();
		$imageId = $dataImage['id'];
		$dataImage['imageType'] = 1;
		$image = $data['image']; 
		$image = str_replace('data:image/png;base64,', '', $image);
		$image = str_replace(' ', '+', $image);
		$fileName = 'PD'.uniqid().'.'.'jpg';  
		$dataImage['imageName'] = $fileName; 
		Storage::put('/public/Product/'.$fileName,base64_decode($image));
		$dataImage['location'] = $this->server.Storage::url('public/Product/'.$fileName);
		if(Image::create($dataImage) != null)
		{
		
			$data ['id'] = Product::generateId();
			$productId = $data['id'];
			$data ['deleted'] = 0;
			$data ['imageId'] = $imageId;  
			//var_dump($data ['imageId']);die;
			//var_dump(Product::create($data)!= null);die;
			
			if(Product::create($data)!= null)
			{
				$dataStock = [ 
					'id' => Stock::generateId(),
					'qty' => $data['stock'],
					'productId' => $productId,
					'incoming' => 1,
					'booked' => 0,
					'balance' => $data['stock']
				];
				if(Stock::create($dataStock)){
					return response()->json(['Create New Product']);
				}
			
				return response()->json(['Add Stock Failed'], 404);
			}
			return response()->json(['Create New Product Failed'], 404);
		}
		return response()->json(['Image Not Found'], 404);
	}
	
	public function imageUpload(Request $request){
		$image = $request->gambar; 
		$image = str_replace('data:image/png;base64,', '', $image);
		$image = str_replace(' ', '+', $image);
		$fileName = 'PD'.uniqid().'.'.'jpg'; 
		Storage::put('/public/product/'.$fileName,base64_decode($image));
		return $this->server.Storage::url('public/product/'.$fileName);
	}	*/
	
	public function detailProduct($productId)
	{
		$dataProduct = Product::where('id', $productId)->first();
		//var_dump(json_decode($dataProduct,true));die;
		
		$product = json_decode($dataProduct,true);
		$stock = Stock::select('balance')->where('productId', $productId)->orderBy('created_at', 'desc')->first();
		$image = Image::select('location')->where('id', $dataProduct->imageId)->first();
		$category = Category::select('name')->where('id', $product['categoryId'])->first();
		//var_dump($category);die;
		
		$product['stock'] = $stock->balance;
		$product['category'] = $category->name;
		$product['image'] = $image->location;
		if(isset($dataProduct))
		{
			return response()->json($product);
		};
		
		return response()->json(['Data Not Found'], 404); 
	}
	
	public function updateProduct(Request $request, $productId)
	{
		$data = $request->input('Product');
		$validate = \Validator::make($data, [
			'title' => 'required',
			'categoryId' => 'required',
			'price' => 'required',
			'description' => 'required',
			'deleted' => 'required'
		]);
		
		if($validate->fails()){
			return response()->json(['There is an Empty Data'], 404);
		}
		if(isset($data['image'])){
			$dataImage['id'] = Image::generateId();
			$imageId = $dataImage['id'];
			$dataImage['imageType'] = 1;
			$image = $data['image']; 
			$image = str_replace('data:image/png;base64,', '', $image);
			$image = str_replace(' ', '+', $image);
			$fileName = 'PD'.uniqid().'.'.'jpg';  
			$dataImage['imageName'] = $fileName; 
			Storage::put('/public/Product/'.$fileName,base64_decode($image));
			$dataImage['location'] = $this->server.Storage::url('public/Product/'.$fileName);
			
			$newImage = Image::create($dataImage);
			//$data ['imageId'] = $imageId;  
		}else{
			$product = Product::select('imageId')->where('id', $productId)->first(); 
			$imageId = $product['imageId'];
		}
		$dataProduct = [
			'title' => $data['title'],
			'categoryId' => $data['categoryId'],
			'price' => $data['price'],
			'description' => $data['description'],
			'deleted' => $data['deleted'],
			'imageId' => $imageId
		];
		$updateResult = Product::where('id', $productId)->update($dataProduct);
		
		return response()->json($updateResult == 1?["status" => true, "title" => "Success", "message" => "Data Successfully Updated"]:["status" => false, "title" => "Failed", "message" => "Update Data Failed"]);
	}
	
	public function deleteProduct($productId)
	{
		$deleteProduct = Product::where('id', $productId)->delete();
		//var_dump($dataProduct);die;
		if($deleteProduct == 1)
		{
			return response()->json($deleteProduct);
		};
		
		return response()->json(['Data Not Found'], 404);
	}
	
	public function deletedProduct($productId)
	{
		$data = ['deleted' => 1];
		$response = Product::where('id',$productId)->update($data);
		if($response == 1)
		{
			return response()->json(['Deleted']);
		};
		
		return response()->json(['Data Not Found'], 404);
	}
	
	public function getAllProducts(){
		$dataProduct = Product::join('images', 'products.imageId', '=', 'images.id')->select('products.*', 'images.imageName', 'images.location')->orderBy('products.created_at', 'desc')->get();
		
		//var_dump(json_encode($dataProduct));die;
		return response()->json($dataProduct);
	}
	
	public function getProducts(){
		$product = Product::where('delete', 0)->get();

		return Compact('product');
		//return Product::allProducts();
	}
	
	/*public function getDeletedProducts(){
		$product = Product::where('deleted', 1)->get();

		return Compact('product');
		//return Product::allProducts();
	}*/
	
	public function getCategory(){
		return Category::allCategory();
	}
	
	public function getCategoryById($categoryId){
		return Category::select('name')->where('id', $categoryId)->first();
	}
	
	public function getProductByCategory($categoryId){
		$name = Category::select('name')->where('id', $categoryId)->first();
		$name = $name->name;
		$product = Product::where('categoryId', $categoryId)->get();
		//var_dump(json_encode($product));die;
		$dataProduct= [];
		foreach($product as $data){
			$image = Image::select('location')->where('id', $data->imageId)->first();
			$response =[
				'id' => $data->id,
				'title' => $data->title,
				'categoryId' => $data->categoryId,
				'price' => $data->price,
				'description' => $data->description,
				'image'=> $image->location, 
				'created_at'=> $data->created_at->toDateTimeString(),
				'deleted'=> $data->deleted
			];
			array_push($dataProduct, $response);
		}
		return response()->json(compact('name', 'dataProduct'));
	}
	
}



