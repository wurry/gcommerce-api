<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $table = 'province';
	
	protected $fillable = 
	[
		'id', 'name'
	];

	public static function allProvince()
	{
		return self::get();
	}
}
