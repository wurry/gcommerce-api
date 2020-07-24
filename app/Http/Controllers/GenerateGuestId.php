<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GenerateGuestId extends Controller
{
	public function generateGuestId()
	{
		return uniqid();
	}
}
