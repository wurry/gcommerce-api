<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\Stock;

class StockController extends Controller
{
    	
	
    public function updateStock(Request $request, $productId)
	{
		$data = $request->input('updateStock');
		$validate = \Validator::make($data, [
			'incoming' => 'required',
			'qty' => 'required'
		]);
		
		if($validate->fails()){
			return response()->json(['There is an Empty Data'], 404);
		}
		
		$stock = Stock::select('balance')->where('productId', $productId)->orderBy('created_at', 'desc')->first();
		
		if($data['incoming'] == 1)
		{
			$data['balance'] = ($stock->balance + $data['qty']);
			$data['booked'] = false;
		}
		else
		{
			$data['balance'] = ($stock->balance - $data['qty']);
			$data['booked'] = false;
		}
		
		$stock = [
			'id' => Stock::generateId(),
			'productId' => $productId,
			'qty' => $data['qty'],
			'incoming' => $data['incoming'],
			'balance' => $data['balance'],
			'booked' => false
		];
		/*if(Stock::create($stock) == null){
			return response()->json(['Update Stock Failed'], 404);
		}
		return response()->json(['Stock Has Been Updated']);*/
		
		$updateResult = Stock::create($stock);
		
		return response()->json($updateResult == null?["status" => false, "title" => "Failed", "message" => "Update Data Failed"]:["status" => true, "title" => "Success", "message" => "Data Successfully Updated"]);
	
	}
	
}
