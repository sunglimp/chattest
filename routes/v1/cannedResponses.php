<?php
Route::get('/', 'CannedResponseController@index');
Route::post('add', 'CannedResponseController@store');
Route::put('update', 'CannedResponseController@update');
Route::delete('{cannedResponseId}', 'CannedResponseController@delete');
Route::get('canned-response/{cannedResponseId}', 'CannedResponseController@getCannedResponseById');
Route::get('/canned-responses', 'CannedResponseController@getCannedResponses');

