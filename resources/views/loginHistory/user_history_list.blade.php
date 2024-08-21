@extends('app')
@section('heading',default_trans($organizationId.'/user_logging.ui_elements_messages.user_history_list', __('default/user_logging.ui_elements_messages.user_history_list')))
@section('title',default_trans($organizationId.'/user_logging.ui_elements_messages.user_history_list', __('default/user_logging.ui_elements_messages.user_history_list')))
@section('main-content')
@php
$languageClass = '';
if (Auth::user()->language === 'ar') {
    $languageClass = 'arabic';
}
@endphp

<div class="main-container {{ $languageClass }}"  @if(Auth::user()->role_id !=1 && Auth::user()->language =="ar") dir="rtl" @endif>
    <!-- <div class="loader">
        <div class="loader__spinner"></div>
    </div> -->
    <div class="content__filters">
        <div class="left_column">
            <div class="margin-right-1">
                <input type="text" class="custom-input custom-input-search {{ $languageClass }}" id="datatable-search" autocomplete="off" placeholder="{{default_trans($organizationId.'/user_logging.ui_elements_messages.search', __('default/user_logging.ui_elements_messages.search'))}}">
                <input type="hidden" name="user_id" value="{{$id}}">
            </div>
            <div >
                              <input type="text" name="date" id="dashboard-8" class="custom-input-calendar {{ $languageClass }}" placeholder="DD-MM-YYYY - DD-MM-YYYY" onkeydown="dateRangeKeyDownUserLogging(event)">
                              <button style="display:inline-block" id ="history-submit-button" class="custom-button custom-button-green margin-right-1" onClick="selectedDateRange();">{{default_trans($organizationId.'/user_logging.ui_elements_messages.submit', __('default/user_logging.ui_elements_messages.submit'))}}</button>
                        </div>
        </div>
    </div>
    <div class="content__wrapper content__wrapper--organization margin-top-2">
        <div class=" margin-top-2">
            <table class="table table-sorting image-list {{ $languageClass }}" id="userLoginHistoryTable">
                <thead>
                    <tr>
                        <th class="">{{default_trans($organizationId.'/user_logging.ui_elements_messages.ip', __('default/user_logging.ui_elements_messages.ip'))}}</th>
                        <th class="">{{default_trans($organizationId.'/user_logging.ui_elements_messages.device', __('default/user_logging.ui_elements_messages.device'))}}</th>
                        <th class="">{{default_trans($organizationId.'/user_logging.ui_elements_messages.login_date_and_time', __('default/user_logging.ui_elements_messages.login_date_and_time'))}}</th>
                        <th class="">{{default_trans($organizationId.'/user_logging.ui_elements_messages.logout_date_and_time', __('default/user_logging.ui_elements_messages.logout_date_and_time'))}}</th>
                        <th class="">{{default_trans($organizationId.'/user_logging.ui_elements_messages.duration(h:i)', __('default/user_logging.ui_elements_messages.duration(h:i)'))}}</th>
                        <th class="">{{default_trans($organizationId.'/user_logging.ui_elements_messages.no_of_chats', __('default/user_logging.ui_elements_messages.no_of_chats'))}}</th>
                        <th class="">{{default_trans($organizationId.'/user_logging.ui_elements_messages.banned_users', __('default/user_logging.ui_elements_messages.banned_users'))}}</th>
                                               
                    </tr>
                </thead>

            </table>

            <div class="custom-dropdown float-for-datatable">
                <div class="flex-center head_sorting">
                    <label>{{default_trans($organizationId.'/user_logging.ui_elements_messages.show', __('default/user_logging.ui_elements_messages.show'))}}</label>
                    <div class="select-custom">
                        <select name="table_length" class="select select-custom-length" id="datatable-length" autocomplete="off">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                    <label>{{default_trans($organizationId.'/user_logging.ui_elements_messages.enteries', __('default/user_logging.ui_elements_messages.enteries'))}}</label>
                </div>
            </div>
            <!-- </div> -->
        </div>
<div class="overlay"></div>
@endsection

@push('custom-scripts')
<script src="{{mix('js/history.js')}}" type="text/javascript"></script>
@endpush
