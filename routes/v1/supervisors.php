<?php

Route::get('/{userId}/channels', 'SupervisorController@channel');
Route::get('/{userId}/agents', 'SupervisorController@agent');
Route::get('/client/{clientId}', 'ChatController@getClientDisplayName');
