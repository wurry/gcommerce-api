<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Cart;
use App\Order;
use App\Order_status;
use App\Invoice;
use App\Stock;
use App\Product;
use App\Image;
use App\Classes\System;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{ 
	public $userId;
	public function __construct(Request $request)
	{
		if($request->header('Authorization') != null){
			$token = $request->header('Authorization');
			$this->userId = System::getUserId($token);
		}
	}
    public function addOrder(Request $request){
		$data = $request->input('addOrder');
		$validate = \Validator::make($data, [
			'state' => 'required',
			'paymentId' => 'required'
		]);
		if($validate->fails()){
			return response()->json(['There is an Empty Data'], 404);
		}
		if($data['state'] == "approved"){
			$invoice = [
				'id' => Invoice::generateId(),
				'userId' => $this->userId,
				'paymentId' => $data['paymentId'],
				'statusId' => 1,
				'date' => date('Y-m-d H:i:s')
			];
			if(Invoice::create($invoice) == null)
			{
				return response()->json(['Create Invoice Failed'], 403);
			}
			$user = User::where('id', $this->userId)->first();
			//$cart = Cart::where('userId', $this->userId)->get();
			$product = Cart::join('products','cart.productId','=','products.id')->where('cart.session', $this->userId)->get();
			//var_dump(json_encode($product));die;
			//$image = Image::select('location')->where('id', $dataProduct->imageId)->first();
				
			$dataOrder = [];
			foreach($product as $dataProduct){
				//var_dump(json_encode($image));die;
				$response = [
					'id' => Order::generateId(),
					'name' => $user['fullname'],
					'address' => $user['address'],
					'email' => $user['email'],
					'phone' => $user['phone'],
					"productTitle"=> $dataProduct->title,
					"price"=> $dataProduct->price,
					"qty"=> $dataProduct->qty,
					"invoiceId"=> $invoice['id'],
					'date' => date('Y-m-d H:i:s')
				];
				array_push($dataOrder,$response);
				//var_dump(json_encode($image));die; 
			}
			$result = Order::insert($dataOrder);
			//return response()->json($result);
			if($result == true)
			{
				$stock = Cart::join('stock','cart.stockId','=','stock.id')->where('cart.session', $this->userId)->get();
				//var_dump(json_encode($stock));die;
				$dataStock = [];
				foreach($stock as $dataProduct){
					$response = [
						'id' => $dataProduct->stockId
					];
					array_push($dataStock,$response);
				}
				$result = Stock::whereIn('id', $dataStock)->update(['booked'=> 0]);
				$removeCart = Cart::whereIn('stockId', $dataStock)->delete(); 
				
				//return response()->json('Payment Success');
				return response()->json(['status'=>true, 'message'=>'Payment Success',  "invoiceId" => $invoice['id']]);
			}		
		}
		return response()->json('Data State Not Found');
	}
	public function getAllOrders()
	{
		$dataInvoice = Invoice::join('order_status', 'invoice.statusId','=', 'order_status.id')->select('invoice.*', 'order_status.name')->orderBy('date', 'desc')->get();
		//var_dump(json_encode($dataInvoice));die;
		$dataOrder = [];
		foreach($dataInvoice as $invoice){
			$user = User::select('fullname')->where('id',$invoice->userId)->first();
			$order = Order::select('price', 'qty')->where('invoiceId', $invoice->id)->first();
			//var_dump(json_encode($order));die;  
			$response = [
				'id' => $invoice->id,
				'name' => $user->fullname,
				'statusId' => $invoice->statusId,
				'status' => $invoice->name,
				'total' => $order->price * $order->qty,
				'date' => $invoice->date
			];
			array_push($dataOrder,$response);
		} 
		
		return response()->json(compact('dataOrder'));
	}
	
	public function orderDetail(Request $request, $invoiceId)
	{
		
		$dataInvoice = Invoice::join('order_status', 'invoice.statusId','=', 'order_status.id')->select('invoice.*', 'order_status.name')->where('invoice.id', $invoiceId)->get();
		$invoice = [];
		foreach($dataInvoice as $data){
			$user = User::join('invoice', 'users.id', '=', 'invoice.userId')->select('users.username', 'users.fullname', 'users.email', 'users.phone', 'users.address')->where('invoice.id', $invoiceId)->first();
			$response = [
				'id' => $data->id,
				'paymentId' => $data->paymentId,
				'status' => $data->name,
				'date' => $data->date,
				'userId' => $data->userId,
				'username' => $user->username,
				'name' => $user->fullname,
				'email' => $user->email,
				'phone' => $user->phone,
				'address' => $user->address,
			];
			array_push($invoice, $response);
		}
		//var_dump($invoice);die;
		$order = Order::where('invoiceId', $invoiceId)->get();
		//var_dump($user);die; 
		return	response()->json(compact('invoice', 'order'));
	}
	
	public function getOrdersById(Request $request) 
	{
		if($request->header('Authorization') != null)
		{
			$userId = $this->userId;
		}
		else if($request->header('guestId') != null)
		{
			$userId = $request->header('guestId');
		}
		$dataInvoice = Invoice::join('order_status', 'invoice.statusId','=', 'order_status.id')->select('invoice.*', 'order_status.name')->orderBy('date', 'desc')->where('invoice.userId', $userId)->get();
		//var_dump(json_encode($dataInvoice));die;
		$dataOrder = []; 
		foreach($dataInvoice as $invoice){
			$user = User::select('fullname')->where('id',$this->userId)->first();
			$order = Order::select('price', 'qty')->where('invoiceId', $invoice->id)->first();
			//var_dump(json_encode($user));die;  
			$response = [
				'id' => $invoice->id,
				'name' => $user->fullname,
				'statusId' => $invoice->statusId,
				'total' => $order->price * $order->qty,
				'status' => $invoice->name,
				'date' => $invoice->date
			];
			array_push($dataOrder,$response);
		}
		
		return response()->json(compact('dataOrder'));
	}
	
	public function send($invoiceId) 
	{
		$data = ['statusId'=>2];
		$response = Invoice::where('id',$invoiceId)->update($data);
		if($response == 1)
		{
			return response()->json(['Sending']);
		}; 
		
		return response()->json(['Data Not Found'], 404);
	}
	
	public function received($invoiceId)
	{
		$data = ['statusId'=>3];
		$response = Invoice::where('id',$invoiceId)->update($data);
		if($response == 1)
		{
			return response()->json(['Received']); 
		};
		
		return response()->json(['Data Not Found'], 404);
	}
	public function invoice(Request $request, $invoiceId)
	{
		
		$dataInvoice = Invoice::join('order_status', 'invoice.statusId','=', 'order_status.id')->select('invoice.*', 'order_status.name')->where('invoice.id', $invoiceId)->get();
		$invoice = [];
		foreach($dataInvoice as $data){
			$user = User::join('invoice', 'users.id', '=', 'invoice.userId')->select('users.username', 'users.fullname', 'users.email', 'users.phone', 'users.address')->where('invoice.id', $invoiceId)->first();
			$response = [
				'id' => $data->id,
				'paymentId' => $data->paymentId,
				'status' => $data->name,
				'date' => $data->date,
				'userId' => $data->userId,
				'username' => $user->username,
				'name' => $user->fullname,
				'email' => $user->email,
				'phone' => $user->phone,
				'address' => $user->address,
			];
			array_push($invoice, $response);
		}
		//var_dump($invoice);die;
		$order = Order::where('invoiceId', $invoiceId)->get();
		//var_dump($user);die; 
		return	response()->json(compact('invoice', 'order'));
	}
}
