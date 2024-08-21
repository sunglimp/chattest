<?php

Route::delete('/close/channels/{chatChannelId}/agents/{agentId}', 'ChatController@deleteInternalComments');
Route::get('agent/{agentId}', 'ChatController@getInternalCommentChannels');
