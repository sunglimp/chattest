<?php

Route::post('/agents/{agentId}/chats/{channelId}/pick', 'ChatController@pick');
Route::post('/agents/{agentId}/chats/{channelId}/close', 'ChatController@close');
Route::post('/transfer/channels/{channelId}/agents/{agentId}', 'ChatController@internalTransfer');
Route::post('/transfer/channels/{channelId}/groups/{groupId}', 'ChatController@externalTransfer');
Route::get('archive/{user}/client', 'ArchiveChatControlller@clientList');
Route::get('archive/{user}/client/{client}/chat', 'ArchiveChatControlller@clientChat');
Route::get('get_reportees_dropdown','ArchiveChatControlller@getReporteeDropdown');
Route::get('/language', 'ChatController@languageInterpretation');
Route::get('archive/{user}/client/{client}/download-chat','ArchiveChatControlller@downloadChat');

Route::get('archive/{user}/download-tag-report','ArchiveChatControlller@downloadTagReport');

Route::get('archive/{user}/download-chat','ArchiveChatControlller@downloadAgentWiseChat');

Route::get('/queue_count/{agentId}', 'ChatController@getQueueCount');
Route::get('/client/{clientId}', 'ChatController@getClientIdentifier');
Route::get('/missed', 'MissedChatController@index');
Route::post('/missed/{chatChannelId}', 'MissedChatController@update');
Route::get('/clients/{clientId}', 'ChatController@getClientInfo');
