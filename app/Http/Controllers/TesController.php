<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\TesModel;

class TesController extends Controller
{
    public function getUserData(){
		return TesModel::dataUser();
	}
}
