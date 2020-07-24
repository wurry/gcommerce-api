<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products'; 
	
	protected $fillable = [
        'id', 'title', 'categoryId', 'price', 'description', 'imageId', 'deleted'
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
	
	/*public static function allProducts()
	{
		return self::get();
	}*/
}
