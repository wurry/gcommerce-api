<?php

namespace App\Classes;

class System
{
	public static function getUserId($token)
	{
		$explode = explode('.', $token);
		$userData = json_decode(base64_decode($explode[1]));
		$userId = $userData->sub;
		
		return $userId;
	}
}

?>