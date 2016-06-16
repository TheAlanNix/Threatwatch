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

Route::get('ip/{ip_address}',			'InvestigationController@ipSummary');
Route::get('ip-details/{ip_address}',	'InvestigationController@ipDetails');
Route::get('domain/{domain}',			'InvestigationController@domainDetails');

Route::controller('config', 'ConfigurationController');

Route::controller('/', 'InvestigationController');
