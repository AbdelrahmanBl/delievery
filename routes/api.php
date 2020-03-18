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
/*    Start User Methods     */
//Route::group(['middleware' => 'UserToken'], function () {
	Route::get('/user/get_profile','usersController@get_profile');
	Route::patch('/user/update_profile','usersController@update_profile');
	Route::post('/user/add_request','usersController@add_request');
	Route::post('/user/update_request','usersController@update_request');
	Route::patch('/user/cancel_request','usersController@cancel_request');
	Route::patch('/user/register_device','usersController@register_device');
	Route::get('/user/check_status','usersController@check_status');
	Route::get('/user/history_trips','usersController@history_trips');
	Route::get('/user/trip_details','usersController@trip_details');
	Route::patch('/user/change_mobile','usersController@change_mobile');
	Route::patch('/user/rate_trip','usersController@rate_trip');
	Route::post('/user/transfer_money','usersController@transfer_money');
	Route::post('/user/verify_transfer','usersController@verify_transfer');
	Route::get('/user/check_promocode','usersController@check_promocode');
	Route::get('/user/cancels_trips','usersController@cancels_trips');
	Route::get('/user/upcoming_trips','usersController@upcoming_trips');
	Route::get('/user/user_wallets','usersController@user_wallets');
	Route::post('/user/add_card','usersController@add_card');
	Route::patch('/user/set_default','usersController@set_default');
	Route::get('/user/get_cards','usersController@get_cards');
	Route::delete('/user/delete_card','usersController@delete_card');
	Route::get('/user/get_locations','usersController@get_locations');
	Route::post('/user/complain','usersController@complain');
	Route::post('/user/complained','usersController@complained');
	Route::post('/user/reply_complained','usersController@reply_complained');
	Route::post('/user/send_payment','usersController@send_payment');
//});
Route::post('/user/sent_otp','usersController@sent_otp');
Route::post('/user/verify_otp','usersController@verify_otp');
Route::post('/user/signup','usersController@signup');
Route::get('/user/get_services','usersController@get_services');
Route::get('/user/check_email','usersController@check_email');
/////////  Send Notification & Payment   ///////////////
Route::post('/sendFCM','usersController@sendFCM2');
Route::get('/paytabs_payment','usersController@paytabs_payment');
Route::post('/paytabs_response','usersController@paytabs_response');
/*    End User Methods     */

/*    Start Provider Methods     */
//Route::group(['middleware' => 'ProviderToken'], function () {
	Route::get('/provider/get_profile','providersController@get_profile');
	Route::patch('/provider/update_profile','providersController@update_profile');
	Route::get('/provider/get_documents','providersController@get_documents');
	Route::post('/provider/update_documents','providersController@update_documents');
	Route::post('/provider/update_request','providersController@update_request');
	Route::patch('/provider/cancel_request','providersController@cancel_request');
	Route::patch('/provider/register_device','providersController@register_device');
	Route::get('/provider/history_trips','providersController@history_trips');
	Route::get('/provider/trip_details','providersController@trip_details');
	Route::get('/provider/earning','providersController@earning');
	Route::get('/provider/wallet','providersController@wallet');
	Route::post('/provider/transfer_change','providersController@transfer_change');
	Route::patch('/provider/change_mobile','providersController@change_mobile');
	Route::patch('/provider/update_status','providersController@update_status');
	Route::patch('/provider/send_payment','providersController@send_payment');
	///////////////////////////////////////
	Route::patch('/provider/set_password','providersController@set_password');
	Route::patch('/provider/change_password','providersController@change_password');
	Route::patch('/provider/forgot_password','providersController@forgot_password');
	Route::patch('/provider/complain','providersController@complain');
	Route::post('/provider/complained','providersController@complained');
	Route::post('/provider/reply_complained','providersController@reply_complained');
//});
Route::post('/provider/sent_otp','providersController@sent_otp');
Route::post('/provider/verify_otp','providersController@verify_otp');
Route::post('/provider/signup','providersController@signup');
Route::get('/provider/check_status','providersController@check_status');
Route::get('/provider/check_email','providersController@check_email');
//Route::patch('/provider/rate_trip','providersController@rate_trip');
//Route::post('/provider/transfer_money','providersController@transfer_money');
//Route::post('/provider/verify_transfer','providersController@verify_transfer');
/*    End Provider Methods     */

