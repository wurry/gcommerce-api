<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public $timestamps = false;
    protected $table = 'orders'; 
	
	protected $fillable = [
        'id', 'name', 'address', 'email', 'phone', 'productTitle', 'price', 'qty', 'invoiceId', 'date'
    ];
	public static function generateId()
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
