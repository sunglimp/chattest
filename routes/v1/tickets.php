<?php

Route::get('fields/{type}', 'TicketController@getTicketFields');
Route::post('create-ticket', 'TicketController@addTicket');
Route::get('ticket-details/{ticketId}', 'TicketController@getTicketDetails');
Route::get('lead-details/{leadId}', 'TicketController@getLeadDetails');
