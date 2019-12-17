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

Route::get('/', function () {
    return view('layouts.home');
});

// PU import 
Route::get('/puImport', 'sal_loiController@index');
Route::post('/puImport', 'sal_loiController@puImport')->name('puImport');


// PU Draw using container
//Route::get('/ShowUseContainer', 'sal_loiController@ShowUseContainer')->name('ShowUseContainer');


Route::get('/index', 'pu_po_estimateController@index');

Route::get('/ConvertForeCastMaster', 'pu_po_estimateController@ConvertForeCastMaster');
Route::get('/ConvertForeCastDetail', 'pu_po_estimateController@ConvertForeCastDetail');
Route::get('/CreateAllPOEstimate', 'pu_po_estimateController@CreateAllPOEstimate');
Route::get('/ShowPOList', 'pu_po_estimateController@ShowPOList')->name('ShowPOList');

Route::get('/ShowPODetail/{POID}', 'pu_po_estimateController@ShowPODetail');
//Route::get('/test/{POID}', 'pu_po_estimateController@ShowPODetail');
Route::get('/TestSo', 'pu_po_estimateController@TestSo');



