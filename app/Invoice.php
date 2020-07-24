<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
	public $timestamps = false;
    protected $table = 'invoice'; 
	
	protected $fillable = [
        'id', 'userId', 'paymentId', 'statusId', 'date'
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
