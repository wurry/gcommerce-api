<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order_temp extends Model
{	
    protected $table = 'order_temp'; 
	
	protected $fillable = [
        'id', 'productId', 'userId', 'qty'
    ];
	
	public static function generateId ()
	{
		$number = date('ymd').sprintf('%04s',mt_rand(0, 9999));
		if (Self::isIdExist($number)) {
		   return generateId();
		}
		return $number;
	}

	public static function isIdExist ($number)
	{
		return self::where('id',$number)->exists();
	}
}
