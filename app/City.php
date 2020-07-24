<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $table = 'city';
	
	protected $fillable = 
	[
		'id', 'provinceId', 'name'
	];

	public static function allCity()
	{
		return self::get();
	}
	
}
