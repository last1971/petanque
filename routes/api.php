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

Route::middleware('auth:api')->group(function(){
    Route::apiResources(
        [
            'event' =>  'Api\EventController',
            'group' =>  'Api\GroupController',
            'track' =>  'Api\TrackController',
            'team' =>   'Api\TeamController',
            'round' =>  'Api\RoundController',
            'game' =>   'Api\GameController',
            'member' => 'Api\MemberController',
        ]
    );
    Route::get('/logout','Api\AuthController@logout')->name('logout');
});


Route::post('/login','Api\AuthController@login')->name('login.api');
