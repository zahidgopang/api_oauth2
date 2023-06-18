<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::middleware(['cors', 'throttle:api'])->group(function () {
    Route::post('register', 'Api\RegisterController@SignUp');
    Route::post('login', 'Api\RegisterController@login');
    Route::post('show-all-user', 'Api\UserController@ShowAllUser');
});

Route::middleware(['cors','auth:api', 'throttle:api'])->group(function () {
    Route::post('create-user', 'Api\UserController@CreateUser');
    Route::post('update-user', 'Api\UserController@UpdateUser');
    Route::post('delete-user', 'Api\UserController@DeleteUser');
    Route::post('logout', 'Api\UserController@Logout');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});