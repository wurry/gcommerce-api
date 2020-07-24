<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Cart;
use App\Product;
use App\Image;
use App\Stock;
use App\Classes\System;
use DB;

class CartController extends Controller
{
	public $userId;
	public function __construct(Request $request)
	{
		if($request->header('Authorization') != null){
			$token = $request->header('Authorization');
			$this->userId = System::getUserId($token);
		}
	}
    public function addToCart(Request $request, $productId){
		if ($request->header('Authorization') != null)
		{
			$data ['session'] = $this->userId;
		}
		else if($request->header('guestId') != null)
		{
			$data ['session'] = $request->header('guestId');
		}
		if(!isset($data['session']))
		{
			$data['session'] = sprintf('%012s',base_convert(uniqid(), 8, 8).rand(4,9999));
		}
		//var_dump($data['session']);die; 
		$data ['productId'] = $productId;
		if(Cart::where(['session'=>$this->userId, 'productId'=>$productId])->exists()) 
		{
			$qty = Cart::select('stockId','qty')->where(['session'=>$this->userId, 'productId'=>$productId])->first();
			// var_dump($qty->stockId);die; 
			$updateQty = Cart::where(['session'=>$this->userId, 'productId'=>$productId])->update(['qty'=>($qty->qty+1)]);
			//var_dump($updateQty);die;
			if($updateQty == 1)
			{
				$balance = Stock::where('id', $qty->stockId)->first();
				//var_dump(json_encode($balance->balance));die;
				if($balance->balance < 1)
				{
					return response()->json(['Out of Stock'], 404);
				}

				$stock = Stock::where('id', $qty->stockId)->update([
					'qty'=>($qty->qty+1),
					'balance'=>($balance->balance - 1)
				]);
				
				//var_dump($stock);die;
				return response()->json(['message'=>'Added To Cart']);
			}

		}else{
			$data ['qty'] = 1;
		}
		
		$stock = Stock::select('balance')->where('productId', $data['productId'])->orderBy('created_at', 'desc')->first();

		//var_dump($stock);die;
		if($stock->balance < $data['qty'])
		{
			return response()->json(['Out of Stock'], 404);
		}

		$data ['id'] = Cart::generateId();

		$orderId = $data['id'];
		$stockId = Stock::generateId();
		$data['stockId'] = $stockId;
		//dd($data);
		if(Cart::create($data) == null)
		{
			return response()->json(['Cart ID Not Found'], 404);
		}

		$stock = [
			'id' => $stockId,
			'productId' => $data['productId'],
			'qty' => 1,
			'incoming' => false,
			'balance' => ($stock->balance - $data['qty']),
			'booked' => true
		];
		if(Stock::create($stock) == null)
		{
			return response()->json(['Update Stock Failed'], 403);
		} 
		$guestId = $request->header('guestId');
		$response =  $guestId == null && $request->header('Authorization') == null ? ['guestId'=>$data['session'], 'message'=>'Added To Cart']:['message'=>'Added To Cart'];
		//var_dump(isset($guestId));die;
		return response()->json($response);
		//return response()->json(['Berhasil Ditambahkan ke Cart']);
	}

	public function Cart(Request $request)
	{
		if($request->header('Authorization') != null)
		{
			$userId = $this->userId;
		}
		else if($request->header('guestId') != null)
		{
			$userId = $request->header('guestId');
		}
		else
		{
			return response()->json(['message' => 'Cart is Empty']);
		}
		//$dataCart = Cart::rightJoin('stock', 'cart.productId', '=', 'stock.productId')->where('cart.userId', $userId)->get();
		$product = Cart::join('products','cart.productId','=','products.id')->where('cart.session', $userId)->get();
		//var_dump(json_encode($product));die;
		$dataCart = [];
		foreach($product as $dataProduk){
			$stock = Stock::select('id')->where('productId',$dataProduk->productId)->orderBy('created_at', 'desc')->first();
			$image = Image::select('location')->where('id', $dataProduk->imageId)->first();
			$response=[
				"id"=> $dataProduk->id,
				"userId"=> $dataProduk->session,
				"productId"=> $dataProduk->productId,
				"qty"=> $dataProduk->qty,
				"title"=> $dataProduk->title,
				"categoryId"=> $dataProduk->categoryId,
				"price"=> $dataProduk->price,
				"description"=> $dataProduk->description,
				"image"=> $image->location,
				"stockId"=>$stock->id
			];
			array_push($dataCart,$response);
		}

		return response()->json(compact('dataCart'));
	}

    public function incQty(Request $request, $productId){
		if($request->header('Authorization') != null)
		{
			$userId = $this->userId;
		}
		else if($request->header('guestId') != null)
		{
			$userId = $request->header('guestId');
		}
		$dataCart = Cart::where('session', $userId)->where('productId', $productId)->first();
		$data = [
			'id' => $dataCart['id'],
			'userId' => $dataCart['session'],
			'productId' => $dataCart['productId'],
			'stockId' => $dataCart['stockId'],
			'qty' => $dataCart['qty']
		];
		//var_dump($data);die;
		//$stock = Cart::select('stockId','qty')->where('productId', $productId)->orderBy('created_at', 'desc')->first();

		if($data['qty'] >= 1){
			$data = [
				'qty' => $data['qty'] + 1
			];
		}
		$updateResult = Cart::where('session', $userId)->where('productId' , $productId)->update($data);
		//var_dump($data['qty']);die;
		if($updateResult == 1)
			{
				$balance = Stock::select('balance', 'qty')->where('id', $dataCart->stockId)->first();
				//var_dump($dataCart->stockId);die;
				if($balance->balance < 1)
				{
					return response()->json(['Out of Stock'], 404);
				}

				$stock = Stock::where('id', $dataCart->stockId)->update([
					'qty'=> $data['qty'],
					'balance'=>($balance->balance - 1)
				]);
				return response()->json($data);
			}
	}

    public function decQty(Request $request, $productId){
		if($request->header('Authorization') != null)
		{
			$userId = $this->userId;
		}
		else if($request->header('guestId') != null)
		{
			$userId = $request->header('guestId');
		}
		$dataCart = Cart::where('session', $userId)->where('productId', $productId)->first();
		$data = [
			'id' => $dataCart['id'],
			'userId' => $dataCart['session'],
			'productId' => $dataCart['productId'],
			'stockId' => $dataCart['stockId'],
			'qty' => $dataCart['qty']
		];
		//var_dump($data);die;
		if($data['qty'] >= 1){
			$data = [
				'qty' => $data['qty'] - 1
			];
		}
		$updateResult = Cart::where('session', $userId)->where( 'productId' , $productId)->update($data);
		if($updateResult == 1)
			{
				$balance = Stock::select('balance', 'qty')->where('id', $dataCart->stockId)->first();
				//var_dump($balance->balance);die;
				if($balance->balance < 1)
				{
					return response()->json(['Out of Stock'], 404);
				}

				$stock = Stock::where('id', $dataCart->stockId)->update([
					'qty'=> $data['qty'],
					'balance'=>($balance->balance + 1)
				]);
				return response()->json($data);
			}
	}

	 public function removeFromCart(Request $request, $productId){
		if($request->header('Authorization') != null)
		{
			$userId = $this->userId;
		}
		else if($request->header('guestId') != null)
		{
			$userId = $request->header('guestId');
		}
		$cart = Cart::where('session', $userId)->where( 'productId' , $productId)->first();
		$delete = Cart::where('session', $userId)->where('productId' , $productId)->delete();
		//var_dump($cart);die;
		if($delete > 0){
			$stock = Stock::where('id', $cart->stockId)->delete();
			return response()->json(['Data Has Been Deleted ']);
		}
		return response()->json(['Failed To Delete Data']);
	}

	public function checkout(Request $request){
		if($request->header('Authorization') != null)
		{
			$userId = $this->userId;
		}
		$user = User::where('id', $userId)->first();
		if($user->address == null){
			return response()->json(['Please Complete Your Data'], 404);
		}
		return response()->json($user->address);
	}
	public function countCart(Request $request)
	{
		$cart = Cart::select(DB::raw("(products.price * cart.qty) as total, cart.*, products.*"))->join('products','cart.productId','=','products.id')->where('cart.session', $this->userId)->get();
		//var_dump(json_encode($cart));die;
		$dataCart = [];
		$subTotal = [];
		foreach($cart as $dataProduk){
			array_push($subTotal,($dataProduk->total));
			$response=[
				"price"=> $dataProduk->price,
				"qty"=> $dataProduk->qty,
				"total" => $dataProduk->price * $dataProduk->qty
			];
			array_push($dataCart,$response);
		}
		//var_dump(json_encode($dataCart));die;
		$currency = 15045;
		$dataCart['subTotal'] = (array_sum($subTotal)) / $currency;
		$dataCart['shipping'] = 0;
		$dataCart['tax'] = (($dataCart['subTotal']  * 10) / 100)/$currency;
		$dataCart['totalPrice'] = ($dataCart['subTotal'] + $dataCart['tax']) ;
		return response()->json($dataCart);
	}
}
