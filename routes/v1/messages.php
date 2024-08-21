<?php
Route::get('/channels/{channelId}/agents/{userId}', 'MessageController@index');
Route::post('/attachments', 'MessageController@uploadAttachment');
Route::post('/', 'MessageController@store');
Route::get('/attachments/download/{attachmentPath}', 'MessageController@downloadAttachment');
Route::get('history/channels/{channelId}/agents/{userId}', 'MessageController@historyMessages');
