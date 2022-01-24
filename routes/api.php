<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::middleware('auth.jwt:admin')->get('/me', [AuthController::class, 'me']);

Route::group([
    'namespace' => 'App\Http\Controllers'
], function($router) {
    Route::post('login', 'AuthController@login');
    Route::post('register', 'AuthController@register');
    Route::post('profile', 'AuthController@profile');
    Route::group([
        'middleware' => ['auth.jwt:player'],
        'prefix' => 'player'
    ], function($router) {
        Route::post('logout', 'AuthController@logout');
    });
    Route::group([
        'middleware' => ['auth.jwt:admin'],
        'prefix' => 'admin'
    ], function($router) {
        Route::post('logout', 'AuthController@logout');
    }); 
    Route::group([
        'middleware' => ['auth.jwt:admin'],
    ], function($router) {
        Route::group([
            'prefix' => 'group'
        ], function($router) {
            Route::post('create', 'GroupController@create');
            Route::post('update', 'GroupController@update');
            Route::post('delete', 'GroupController@delete');
        });
        Route::group([
            'prefix' => 'user'
        ], function($router) {
            Route::post('get', 'UserController@get');
            Route::post('get-all', 'UserController@list');
            Route::post('details', 'UserController@details');
            Route::post('create', 'UserController@create');
            Route::post('edit', 'UserController@edit');
            Route::post('confirm-pin', 'UserController@confirmPin');
            Route::post('update', 'UserController@update');
            Route::post('update-streaming', 'UserController@updateStreaming');
            Route::post('update-status', 'UserController@updateStatus');
            Route::post('update-password', 'UserController@updatePassword');
            Route::post('update-pin', 'UserController@updatePin');
            Route::post('update-group-maxbet', 'UserController@updateGroupMaxbet');
            Route::post('delete', 'UserController@delete');
        });
    });
});