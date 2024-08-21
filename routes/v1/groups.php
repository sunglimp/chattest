<?php
Route::get('/{groupId}/agents', 'GroupController@agents');
Route::get('/organizations/{organizationId}', 'GroupController@index');
