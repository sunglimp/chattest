<?php

Route::post('/{channelId}', 'BannedClientController@store');
Route::delete('/{clientId}', 'BannedClientController@destroy');
Route::get('/', 'BannedClientController@index');
Route::get('/{clientId}', 'BannedClientController@show');
