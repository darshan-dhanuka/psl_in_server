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
Route::get('tasks', 'TaskController@index');
Route::get('tasks/{id}', 'TaskController@show');
Route::post('tasks', 'TaskController@store');
Route::put('tasks/{id}', 'TaskController@update');
Route::delete('tasks/{id}', 'TaskController@delete');


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('cors')->group(function(){
    //your_routes
    Route::post('register', 'UserController@register');
    Route::post('login', 'UserController@login');
    Route::get('profile', 'UserController@getAuthenticatedUser');
    Route::get('state', 'StateCityController@getStates');
    Route::get('city/{stateId}', 'StateCityController@getCities');
    Route::post('social', 'SocialController@socialogin');
    Route::post('forgetpw', 'UserController@forgetpw');
    Route::post('verify_otp', 'UserController@verify_otp');
    Route::post('reset_password', 'UserController@reset_password');
    Route::post('send_otp', 'UserController@send_otp');
    Route::post('psl_register', 'UserController@psl_register');										 
    Route::post('e_awsses', 'UserController@e_awsses');										 
    //Route::post('update_password_once', 'UserController@update_password_once');										 
    Route::post('user_details', 'UserController@user_details');										 
    Route::post('change_password_user', 'UserController@change_password_user');										 
	Route::post('get_videos', 'IonicController@get_videos');										 
    Route::post('get_pro', 'UserController@pro_player_registration');										 
    Route::post('up_id', 'UserController@update_adda_id');										   
	Route::post('up_link', 'UserController@update_adda_redirect');																			 
	Route::post('psl_register_landing', 'UserController@psl_register_landing');																			 
	Route::post('psl_register_app', 'UserController@psl_register_app');																			 
 });
