<?php

/**
 * =================================WARNING=====================================
 * DON'T PUT ANYTHING IN CLOSURE HERE. IF REALLY WANT CREATE ANOTHER CONTROLLER
 * ==========XXXXX======================XXXXX=================XXXXX=============
 */

use Illuminate\Support\Facades\Route;

/*
  |--------------------------------------------------------------------------
  | Web Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register web routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | contains the "web" middleware group. Now create something great!
  |
 */

Auth::routes();
Route::get('/logout', 'Auth\LoginController@logout');
Route::group(['middleware' => 'last_activity'], function () {


    Route::group(['middleware' => 'auth'], function () {
        Route::get('/', 'DashboardController@index')->middleware('check_permission:'. config('constants.PERMISSION.DASHBOARD-ACCESS'));
    });

    Route::match(['get', 'post', 'put'], 'register', 'Auth\RegisterController@index');
    Route::group(['middleware' => 'auth','cors'], function () {
        Route::get('/chat', 'ChatController@index')->name('chat');
        Route::get('/chat/archive', 'ChatController@archive')->name('archive')->middleware('can:not-superadmin');
        Route::get('/chat/ticket', 'ChatController@ticket')->name('ticket')->name('supervise')->middleware('check_permission:'. config('constants.PERMISSION.CLASSIFIED-CHAT'));
        ;
        Route::get('/chat/supervise', 'ChatController@supervise')->name('supervise')->middleware('check_permission:'. config('constants.PERMISSION.SUPERVISE-TIP-OFF'));
        Route::get('/chat/status', 'ChatController@status')->name('status')->middleware('check_permission:'. config('constants.PERMISSION.TMS-KEY'));
        Route::get('/chat/lead-status', 'ChatController@leadStatus')->name('lead-status')->middleware('check_permission:'. config('constants.PERMISSION.TMS-KEY'));
        Route::get('/chat/banned-users', 'BannedUsersController@list')->name('banned-users')->middleware('check_permission:'. config('constants.PERMISSION.BAN-USER'));

        Route::get('/chat/offline-queries', 'ChatController@offlineQueries')->name('offline-queries')->middleware(['can:not-superadmin', 'check_permission:'. config('constants.PERMISSION.OFFLINE-FORM')]);
        Route::get('/chat/get-offline-queries', 'ChatController@getOfflineQueries')->name('get-offline-queries')->middleware(['can:not-superadmin', 'check_permission:'. config('constants.PERMISSION.OFFLINE-FORM')]);
        Route::post('/chat/send-push', 'ChatController@sendWaPush')->name('send-wa-push')->middleware(['can:not-superadmin', 'check_permission:'. config('constants.PERMISSION.OFFLINE-FORM')]);
        Route::post('/chat/reject-query', 'ChatController@rejectQuery')->name('reject-offline-query')->middleware(['can:not-superadmin', 'check_permission:'. config('constants.PERMISSION.OFFLINE-FORM')]);
        Route::get('/chat/whatsapp-templates', 'ChatController@getOfflineWhatsappTemplates')->name('whatsapp-templates')->middleware(['can:not-superadmin', 'check_permission:'. config('constants.PERMISSION.OFFLINE-FORM')]);
        Route::get('/chat/missed', 'ChatController@missedChats')->name('missed-chat')->middleware(['can:not-superadmin', 'check_permission:'. config('constants.PERMISSION.MISSED-CHAT')]);
        Route::get('/chat/canned', 'ChatController@cannedResponse')->name('canned-response')->middleware(['check_permission:'. config('constants.PERMISSION.CANNED-RESPONSE')]);
    });

    Route::group(['middleware' => 'auth'], function () {
        Route::group(['prefix' => 'organization'], function () {
            Route::get('index', 'OrganizationController@index')->name('organization.index')->middleware('can:superadmin');
            Route::get('/', 'OrganizationController@index')->name('organization.index')->middleware('can:superadmin');
            Route::post('store', 'OrganizationController@store')->name('organization.store');
            Route::post('create', 'OrganizationController@create')->middleware('can:superadmin');
            Route::post('global-search', 'OrganizationController@globalSearch');
            Route::get('edit/{id}', 'OrganizationController@edit')->middleware('can:superadmin');
            Route::get('detail/{id}', 'OrganizationController@show')->middleware('can:superadmin');
            Route::post('update', 'OrganizationController@update');
            Route::post('organization-status', 'OrganizationController@organizationStatus');
            Route::get('getorganization', 'OrganizationController@getOrganization')->name('get.organization');
            Route::get('delete/{id}', 'OrganizationController@destroy');
        });

        Route::group(['prefix' => 'user'], function () {
            Route::get('/', 'UserController@index')->name('user.index');
            //Route::get('index', 'UserController@index')->name('user.index');
            Route::post('store', 'UserController@store');
            Route::post('create', 'UserController@create');
            Route::get('detail/{id}', 'UserController@show');
            Route::get('edit/{id}', 'UserController@edit');
            Route::get('get-user-by-organization', 'UserController@getUsersByOrganization');
            Route::post('user-status', 'UserController@userStatus');
            Route::get('delete/{id}', 'UserController@destroy');
            Route::get('clear-login/{id}', 'UserController@clearLogin');
            Route::post('update', 'UserController@update');
            Route::post('get-report-to', 'UserController@getReportTo');
            Route::get('update_password/{id}', 'UserController@getUpdatePassword');
            Route::post('update_user_password', 'UserController@updateUserPassword');
            Route::post('check-reportee', 'UserController@checkReportee');
            Route::get('update-login', 'UserController@loginUpdate');
            Route::get('show-permission/{id}','UserController@showUserPermission');
            Route::post('update-user-permission','UserController@updateUserPermission');
            Route::post('sneak-in','SneakUserController@sneakIn');
            Route::get('return-sneak-in','SneakUserController@returnSneak');
            Route::post('check-sneak-in', 'SneakUserController@checkSneak');
        });

        Route::group(['prefix' => 'group'], function () {
            Route::post('store', 'GroupController@store');
            Route::post('create', 'GroupController@create');
            Route::get('delete/{id}', 'GroupController@destroy');
            Route::get('get-group-by-organization/{id}', 'GroupController@getGroupByOrganization');
        });

        Route::group(['prefix' => 'tags'], function () {
            Route::group(['prefix' => 'ajax'], function () {
                Route::post('add', 'TagController@addTag');
                Route::get('getTag', 'TagController@fetchTags');
                Route::post('deleteTag', 'TagController@deleteTag');
            });
        });

    Route::group(['prefix' => 'permission'], function () {
        Route::get('/', 'PermissionController@index')->name('permission.index');
        //Route::get('index', 'PermissionController@index')->name('permission.index');
        Route::post('organization-permission', 'PermissionController@organizationPermission');
        Route::post('store', 'PermissionController@store');
        Route::post('upload-attachment', 'PermissionController@uploadAttachment');
        Route::post('update-attachment-size', 'PermissionController@updateAttachmentSize');
        Route::post('show-setting', 'PermissionController@showSetting');
        Route::post('update-auto-chat', 'PermissionController@updateAutoChatTransfer');
        Route::post('update-chat-feedback', 'PermissionController@updateChatFeedback');
        Route::post('update-chat-notifier', 'PermissionController@updateChatNotifier');
        Route::post('update-chat-timeout', 'PermissionController@updateChatTimeout');
        Route::post('update-offline-form', 'PermissionController@updateOfflineMessage');
        Route::post('update-ban-day', 'PermissionController@updateBanDay');
        Route::post('update-tms-key', 'PermissionController@updateTmsKey');
        Route::post('update-classified-token', 'PermissionController@updateclassifiedToken');
        Route::post('update-email-credentials', 'PermissionController@updateEmailCredentials');
        Route::post('update-tag-settings', 'PermissionController@updateTagSettings')->name('update.tag-settings');
        Route::post('update-session-timeout', 'PermissionController@updateSessionTimeout');
        Route::post('update-archive-chat', 'PermissionController@updateArchiveChat');
        Route::post('update-chat-download', 'PermissionController@updateChatDownload');
        Route::post('update-missed-chat-settings', 'PermissionController@updateMissedChatSettings');
        Route::post('update-customer-information-setting', 'PermissionController@updateCustomerInformationSettings');
    });

        Route::group(['prefix' => 'key'], function () {
            Route::group(['prefix' => 'ajax'], function () {
                Route::get('getKey/{organization}', 'OrganizationController@getOrganizationKey');
            });
        });

        Route::group(['prefix'=> 'dashboard'], function () {
            Route::get('chat-data', 'DashboardController@getChartData');
            Route::get('/', 'DashboardController@index')->name('dashboard')->middleware('check_permission:'. config('constants.PERMISSION.DASHBOARD-ACCESS'));
            Route::get('export', 'DashboardController@export');
            Route::get('check-online', 'DashboardController@checkOnline');
            Route::get('get-data', 'DashboardController@getDashboardData');
        });

        Route::group(['prefix' => 'email'], function () {
            Route::get('sent', 'SentItemsController@list')->name('email.sent')->middleware('check_permission:'. config('constants.PERMISSION.EMAIL'));
            Route::get('get/{emailId}', 'SentItemsController@view');
            Route::get('search', 'SentItemsController@search');
            Route::get('download/{emailId}', 'SentItemsController@downloadAttachment');
            Route::get('filter', 'SentItemsController@filter');
            Route::get('recipients', 'SentItemsController@getRecipients');
        });

        Route::group(['prefix' => 'notification'], function () {
            Route::post('add', 'PermissionController@addNotificationSettting');
        });
    });

    Route::group(['middleware' => 'auth'], function () {
    Route::group(['prefix' => 'history'], function () {
        Route::get('/', 'LoginHistoryController@index')->name('history.index')->middleware('check_permission:'. config('constants.PERMISSION.LOGIN-HISTORY'));
        Route::get('get-users-login-history', 'LoginHistoryController@getUsersLoginHistory');
        Route::get('get-user-history/{id}', 'LoginHistoryController@getUserHistory')->middleware('check_permission:'. config('constants.PERMISSION.LOGIN-HISTORY'))->name('get_user_history');;
        Route::get('get-user-login-history', 'LoginHistoryController@getUserLoginHistory');
});
    });

    Route::group(['middleware' => 'auth'], function () {
    Route::group(['prefix' => 'language'], function () {
    Route::get('/', 'LanguageController@index')->name('language.index');
});
    });

});

Route::group(['middleware' => 'auth'], function () {
    Route::get('download/agent-wise/{fileName}', 'DownloadController@downloadAgentWiseChatFile')->name('agnet-wise-chat-download');
    Route::get('download/offline-query/{fileName}', 'DownloadController@downloadOfflineQueryFile')->name('offline-query-download');
});
// Dummy Controller, will be removed shortly STARTS
Route::get('/dummy/newChat', 'DummyController@newChat')->name('assignAgent');
Route::get('test', 'DashboardController@test');

/**
 * =================================WARNING=====================================
 * DON'T PUT ANYTHING IN CLOSURE HERE. IF REALLY WANT CREATE ANOTHER CONTROLLER
 * ==========XXXXX======================XXXXX=================XXXXX=============
 */
