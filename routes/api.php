<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/register', 'AuthController@register');
Route::post('/login', 'AuthController@login');
Route::post('/checkUniqueValue', 'AuthController@isUniqueValue');

Route::get('generateGuestId', 'generateGuestId@generateGuestId');
Route::get('getProductsByCategory', 'ProductsController@getProductsByCategory');
Route::get('detailProduct/{productId}', 'ProductsController@detailProduct');
Route::get('getAllProducts', 'ProductsController@getAllProducts');
Route::get('getCategory', 'ProductsController@getCategory');
Route::get('getCategoryById/{categoryId}', 'ProductsController@getCategoryById');
Route::get('getProductByCategory/{categoryId}', 'ProductsController@getProductByCategory');

Route::post('/addToCart/{productId}', 'CartController@addToCart');
Route::get('/Cart', 'CartController@Cart');
Route::get('/incQty/{productId}', 'CartController@incQty');
Route::get('/decQty/{productId}', 'CartController@decQty');
Route::delete('/removeFromCart/{productId}', 'CartController@removeFromCart');

Route::get('getAllBanners', 'TestController@getAllBanners'); 
//Route::get('/getSession', 'TestController@getSession');
//Route::group(['middleware' => 'jwt.auth'], function (){
	Route::get('user', 'AuthController@getToken');
	Route::get('refresh', 'AuthController@refresh');
	
	Route::post('imageUpload', 'TestController@imageUpload');
	
	//Route::post('createProfile', 'ProfileController@createProfile');
	Route::get('getUserProfile', 'ProfileController@getUserProfile');
	Route::post('updateProfile', 'ProfileController@updateProfile');
	Route::get('getProvince', 'ProfileController@getProvince');
	Route::get('getCity', 'ProfileController@getCity');
	Route::get('getCityByProvince/{provinceId}', 'ProfileController@getCityByProvince');
	
	Route::get('getDeletedProducts', 'ProductsController@getDeletedProducts');
	Route::post('newProduct', 'ProductsController@newProduct');
	//Route::post('imageUpload', 'ProductsController@imageUpload');
	Route::put('updateProduct/{productId}', 'ProductsController@updateProduct');
	Route::delete('deletedProduct/{productId}', 'ProductsController@deletedProduct');
	Route::delete('deleteProduct/{productId}', 'ProductsController@deleteProduct');
	
	Route::post('updateStock/{productId}', 'StockController@updateStock');
		 
	Route::get('detailUser/{userId}', 'UsersController@detailUser');
	Route::put('updateUsersData/{userId}', 'UsersController@updateUsersData');
	Route::get('/getUsersData', 'UsersController@getUsersData');
	Route::get('/getInactiveUsersData', 'UsersController@getInactiveUsersData');
	Route::get('/getAllRole', 'UsersController@getAllRole');
	Route::put('activatingUser/{userId}', 'UsersController@activatingUser');
	Route::delete('deleteUser/{userId}', 'UsersController@deleteUser');
	 
	Route::post('addOrder', 'OrderController@addOrder');
	Route::get('getAllOrders', 'OrderController@getAllOrders'); 
	Route::get('getOrdersById', 'OrderController@getOrdersById'); 
	Route::get('invoice/{invoiceId}', 'OrderController@invoice'); 
	Route::get('orderDetail/{invoiceId}', 'OrderController@orderDetail'); 
	Route::post('/send/{invoiceId}', 'OrderController@send');
	Route::post('/received/{invoiceId}', 'OrderController@received');
	
	Route::get('/countCart', 'CartController@countCart');
	Route::get('/checkout', 'CartController@checkout');
	
	Route::get('/countCust', 'ReportController@countCust');
	Route::get('/countProduct', 'ReportController@countProduct');
	Route::get('/countTransaction', 'ReportController@countTransaction');
	Route::get('/countProfit', 'ReportController@countProfit');
	Route::get('stockReport', 'ReportController@stockReport');
	Route::get('transactionReport', 'ReportController@transactionReport');
	Route::get('transactionReportDetail/{invoiceId}', 'ReportController@transactionReportDetail'); 
	Route::get('stockReportById/{productId}', 'ReportController@stockReportById'); 

	Route::post('changeEmail', 'UsersController@changeEmail');
	Route::post('changePass', 'UsersController@changePass');
	
	Route::post('logout', 'AuthController@logout');
//});
