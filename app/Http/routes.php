<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| API Controller Route
|--------------------------------------------------------------------------
*/
Route::post(
    'oauth/access_token',
    '\App\Http\Controllers\APIController@authenticate'
);

//Create a test user, you don't need this if you already have.
Route::post(
    'registration',
    '\App\Http\Controllers\RegisterController@store'
);

Route::group(['prefix' => config('settings.PREFIX')], function()
{
    /*
     * Verification Controller Route
     */
    Route::resource(
        'users.verification',
        '\App\Http\Controllers\VerificationController',
        [
            'only' => ['update']
        ]
    );
});

Route::group(['prefix' => config('settings.PREFIX'), 'middleware' => ['oauth','oauth.login'] ], function()
{
    /*
     * User Controller Route
     */
    Route::resource(
        'users',
        '\App\Http\Controllers\UsersController',
        [
            'only' => ['index']
        ]
    );

    /*
     * Friend Controller Route
     */
    Route::group(['prefix' => 'users'], function() {

        Route::resource(
            'request',
            '\App\Http\Controllers\FriendsController',
            [
                'only' => ['index','store']
            ]
        );

        Route::get('mutual-friend-lists', '\App\Http\Controllers\FriendsController@show')
            ->name('api.users.friends.show');

    });


        Route::resource(
            'users.accept',
            '\App\Http\Controllers\FriendsController',
            [
                'parameters' => ['accept' => 'user_id'],
                'only' => ['update']
            ]
        );


});