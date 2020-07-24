<?php

namespace App\Http\Controllers;

use App\User;
use App\Product;
use App\Stock;
use App\Order;
use App\Invoice;
use DB;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function countCust()
	{
		$data = User::where('roleId', 3)->count();
		return response()->json($data);
	}
    public function countProduct()
	{
		$data = Product::count();
		return response()->json($data);
	}
    public function countTransaction()
	{
		$data = Invoice::where('statusId', 3)->count();
		$input = number_format($data); 
		$input_count = substr_count($input, ','); 
		if($input_count != '0'){ 
			if($input_count == '1'){ 
				return substr($input, 0, -4).' K'; 
			} else if($input_count == '2'){ 
				return substr($input, 0, -8).' M'; 
			} else if($input_count == '3'){ 
				return substr($input, 0, -12).' B'; 
			}
			else { return; } 
		} 
		else { return $input; } 
	}
    public function countProfit()
	{
		$data = Invoice::select(DB::raw("SUM(orders.price * orders.qty) as total"))->join('orders','invoice.id','=','orders.invoiceId')->where('statusId', 3)->first();
		//var_dump(json_encode($data->total));die;
		//return response()->json($data)
		$input = number_format($data->total); 
		$input_count = substr_count($input, ','); 
		if($input_count != '0'){ 
			if($input_count == '1'){ 
				return $input; 
			} else if($input_count == '2'){ 
				return substr($input, 0, -8).' M'; 
			} else if($input_count == '3'){ 
				return substr($input, 0, -12).' B'; 
			}
			else { return; } 
		} 
		else { return $input; } 
	}
	public function stockReport()
	{
		$data = Stock::join('products', 'stock.productId','=', 'products.id')->select('products.title','products.price', 'stock.*')->orderBy('created_at', 'desc')->get();
		//var_dump($data);die;
		$unique = $data->unique('title');
		//$unique->values()->all();
		$dataStock = [];
		foreach($unique as $dataProduct){
			$response=[
				"id"=> $dataProduct->id,
				"productId"=> $dataProduct->productId,
				"title"=> $dataProduct->title,
				"price"=> $dataProduct->price,
				"qty"=> $dataProduct->qty,
				"incoming"=> $dataProduct->incoming,
				"balance"=> $dataProduct->balance,
				"booked"=> $dataProduct->booked,
				"created_at"=> $dataProduct->created_at->toDateTimeString(),
				"updated_at"=> $dataProduct->updated_at->toDateTimeString()
			];
			array_push($dataStock,$response);
		}
		
		return response()->json(compact('dataStock'));
		//return response()->json($unique);
	}
    public function stockReportById($productId)
	{
		$title = Product::select('title')->where('id', $productId)->first();
		$stock = Stock::join('products', 'stock.productId','=', 'products.id')->select('products.price', 'stock.*')->where('productId', $productId)->orderBy('created_at', 'desc')->get();
		return response()->json(compact('title', 'stock'));
		
	}	
	public function transactionReport()
	{
		$dataInvoice = Invoice::join('order_status', 'invoice.statusId','=', 'order_status.id')->select('invoice.*', 'order_status.name')->where('order_status.id', 3)->get();
		//var_dump(json_encode($dataInvoice));die;
		$transactions = [];
		foreach($dataInvoice as $invoice){
			$user = User::select('fullname')->where('id',$invoice->userId)->first();
			$order = Order::select('price', 'qty')->where('invoiceId', $invoice->id)->first();
			//var_dump(json_encode($user));die;  
			$response = [
				'id' => $invoice->id,
				'name' => $user->fullname,
				'total' => $order->price * $order->qty,
				'date' => $invoice->date
			];
			array_push($transactions,$response);
		} 
		
		return response()->json(compact('transactions'));
	}
	public function transactionReportDetail(Request $request, $invoiceId)
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
