<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TesModel extends Model
{
	protected $table= 'user';
	public static function dataUser()
	{
		return self::get();
	}
}
