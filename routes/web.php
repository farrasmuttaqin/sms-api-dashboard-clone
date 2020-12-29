<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['namespace'=>'Auth'], function(){
	Route::get('login', 'LoginController@showLoginForm')->name('auth.login');
	Route::post('login', 'LoginController@login');
	Route::post('logout', 'LoginController@logout')->name('auth.logout');
	Route::get('login/captcha', 'LoginController@refreshCaptcha')->name('captcha.refresh');

	// Password Reset Routes...
	Route::get('password/reset', 'ForgotPasswordController@showLinkRequestForm')->name('auth.password.request');
	Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('auth.password.email');
	Route::get('password/reset/{token}', 'ResetPasswordController@showResetForm')->name('auth.password.reset');
	Route::post('password/reset', 'ResetPasswordController@reset');

});



Route::group(['middleware'=>'auth'], function(){

	Route::get('/', 'HomeController@index')->name('home');

	Route::group(['namespace'=>'Admin'], function(){
		Route::get('users', 'UserController@index')->name('user.index');
		Route::delete('users/{user}', 'UserController@destroy')->name('user.delete');
		Route::get('users/table', 'UserController@table')->name('user.table');
		Route::get('users/create', 'UserController@create')->name('user.create');
		Route::post('users/create', 'UserController@store');
		Route::get('users/edit/{user}', 'UserController@edit')->name('user.edit');
		Route::post('users/edit/{user}', 'UserController@update');
		Route::get('clients/select','ClientController@select')->name('client.select');
		Route::get('roles/select','RoleController@select')->name('role.select');
		Route::get('apiusers/select','ApiUserController@select')->name('apiuser.select');
		Route::get('apiusers/all','ApiUserController@all')->name('apiuser.all');
	});

	Route::get('profile/edit','ProfileController@edit')->name('profile.edit');
	Route::post('profile/edit','ProfileController@update');

	Route::Get('reports','ReportController@index')->name('report.index');
	Route::get('reports/create','ReportController@create')->name('report.create');
	Route::post('reports/create','ReportController@store');
	Route::get('reports/table','ReportController@table')->name('report.table');
	Route::delete('reports/{report}','ReportController@destroy')->name('report.delete');
	Route::get('reports/generate/{report}','ReportController@regenerate')->name('report.regenerate');
	Route::get('reports/download/{report}','ReportController@download')->name('report.download');
	Route::get('reports/processing','ReportController@onProcessReport')->name('report.processing');
	Route::get('reports/cancel','ReportController@cancelReport')->name('report.cancel');

});
