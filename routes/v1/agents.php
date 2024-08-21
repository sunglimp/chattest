<?php

Route::put('/{agentId}/online', 'AgentController@online');
Route::put('/{agentId}/offline/{checkChatAvailable}/{ignorePickChats?}', 'AgentController@offline');
Route::get('/{agentId}/permissions', 'AgentController@permissions');
Route::get('/{agentId}/channel', 'AgentController@channel');
Route::post('/{agentId}/chat/{channelId}/pick', 'ChatController@pick');
Route::post('/{agentId}/chat/{channelId}/close', 'ChatController@close');
Route::put('/remove-key/{agentId}', 'AgentController@deleteUserLastActivityKey');
Route::post('/logout', 'LoginController@logout');
Route::get('/side-bar', 'LoginController@getSidebar');
Route::put('/chat-notification-setting', 'AgentController@changeChatNotificationStatus');

