<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Api\V1\Auth\CustomerAuthController;
// use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\DaftarKoiController;

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


// API MOBILE
Route::group(['namespace' => 'Api\V1'], function () {

    // get products - commented out due to missing ProductController
    // Route::group(['prefix' => 'products'], function () {
    //     Route::get('popular', 'ProductController@get_popular_products');
    //     Route::get('recommended', 'ProductController@get_recommended_products');
    //     Route::get('drinks', 'ProductController@get_drinks');
    //     //   Route::get('test', 'ProductController@test_get_recommended_products');
    // });

    // Route::group(['prefix' => 'pond'], function () {
    //     Route::get('list', 'PondController@get_pond_list');
    //     Route::get('sensor', 'DashboardController@get_sensor_list');
    //     Route::get('all-sensor', 'DashboardController@get_sensor_list_all');
    //     Route::post('update-relay', 'PondController@updateRelayCondition');
    //     Route::get('details', 'PondController@get_order_details');
    //     Route::post('place', 'PondController@place_order');
    // });

    // Route::group(['prefix' => 'koi'], function () {
    //     Route::get('list', 'DaftarKoiController@get_koi_list');
    //     Route::get('details', 'DaftarKoiController@get_koi_details');
    //     Route::post('place', 'DaftarKoiController@place_order');
    //     Route::get('ponds', 'DaftarKoiController@getKoiByPondId');
    // });


    // registration and login - commented out due to missing controllers
    // Route::group(['prefix' => 'auth'], function () {
    //     Route::post('register', 'Auth\CustomerAuthController@register');
    //     Route::post('login', 'Auth\CustomerAuthController@login');
    // });


    // Route::group(['prefix' => 'customer', 'middleware' => 'auth:api'], function () {
    //     Route::get('notifications', 'NotificationController@get_notifications');
    //     Route::get('info', 'CustomerController@info');
    //     Route::post('update-profile', 'CustomerController@update_profile');
    //     Route::post('update-interest', 'CustomerController@update_interest');
    //     Route::put('cm-firebase-token', 'CustomerController@update_cm_firebase_token');
    //     Route::get('suggested-foods', 'CustomerController@get_suggested_food');

    //     // Route::group(['prefix' => 'address'], function () {
    //     //     Route::get('list', 'CustomerController@address_list');
    //     //     Route::post('add', 'CustomerController@add_new_address');
    //     //     Route::put('update/{id}', 'CustomerController@update_address');
    //     //     Route::delete('delete', 'CustomerController@delete_address');
    //     // });
    //     Route::group(['prefix' => 'order'], function () {
    //         Route::get('list', 'OrderController@get_order_list');
    //         Route::get('running-orders', 'OrderController@get_running_orders');
    //         Route::get('details', 'OrderController@get_order_details');
    //         Route::post('place', 'OrderController@place_order');
    //         Route::put('payment-method', 'OrderController@update_payment_method');
    //     });

    // });
});


    // Route::group(['prefix' => 'config'], function () {
    //     Route::get('/', 'ConfigController@configuration');
    //     Route::get('/get-zone-id', 'ConfigController@get_zone');
    //     Route::get('place-api-autocomplete', 'ConfigController@place_api_autocomplete');
    //     Route::get('distance-api', 'ConfigController@distance_api');
    //     Route::get('place-api
