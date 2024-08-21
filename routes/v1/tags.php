<?php

Route::get('/agents/{agentId}/chat/{chatId}', 'TagController@index');
Route::post('/add', 'TagController@store');
Route::post('/link', 'TagController@link');
Route::delete('{tagId}', 'TagController@delete');
Route::delete('{tagId}/unlink/chat/{chatId}', 'TagController@unlink');
Route::get('get_chat_tags','TagController@chatTags');