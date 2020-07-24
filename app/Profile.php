<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $table = 'profile';
	
	protected $fillable = [
        'id', 'userId', 'name', 'phone', 'address', 'provinceId', 'cityId'
    ];
	
	public static function generateId ()
	{
		$number = sprintf('%05s',mt_rand(0, 99999));
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
