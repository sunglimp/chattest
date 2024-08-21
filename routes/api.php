<?php

/*
  |--------------------------------------------------------------------------
  | API Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register API routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | is assigned the "api" middleware group. Enjoy building your API!
  |
 */

Route::prefix('v1')->namespace('Api\V1')->group(function () {
    Route::group(['middleware' => 'last_activity'], function () {
        Route::middleware('token')->group(function () {
            Route::middleware('log_after_request')->group(function (){
                Route::post('/channels', 'ChannelController@store');
                Route::post('/message', 'MessageController@store');
                Route::post('/attachments', 'MessageController@uploadBotAttachments');
                Route::post('/feedback', 'ChannelController@feedback');
                Route::get('/groups', 'GroupController@withHash');
                Route::post('/offlineForm', 'OfflineFormController@store');
                Route::post('/chat/close', 'ChannelController@closeByVisitor');
                Route::get('/status/online/group/{id?}', 'GroupController@onlineStatus');
            });
            Route::get('/organizations/{organizationId}', 'GroupController@index');
            Route::get('/get-chats', 'ArchiveChatControlller@getAllChats');
        });

        //APIs exposed to TMS
        Route::middleware('tms')->group(function () {
            Route::post('/update-ticket-fields', 'TicketController@updateFields');
        });

        array_map(function ($prefix) {
            Route::group(['middleware' => 'auth:api'], function () use ($prefix) {
                Route::prefix($prefix)->group(function () use ($prefix) {
                    require_once "v1/$prefix.php";
                });
            });
        }, [
            'messages',
            'cannedResponses',
            'tags',
            'agents',
            'chats',
            'offlineQueries',
            'groups',
            'emails',
            'supervisors',
            'internalComments',
            'bannedClients',
            'tickets',
            'mlmodel',
            'organizations'
        ]);
        // Mobile Api's
        Route::post('/login', 'LoginController@login');
        
    });
});

Route::fallback('Api\V1\FallbackController@notFound');
