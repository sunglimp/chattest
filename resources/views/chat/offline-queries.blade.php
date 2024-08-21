@extends('app')
@section('heading',default_trans($organizationId.'/offline_queries.ui_elements_messages.offline_queries', __('default/offline_queries.ui_elements_messages.offline_queries')))
@section('title',default_trans($organizationId.'/offline_queries.ui_elements_messages.offline_queries', __('default/offline_queries.ui_elements_messages.offline_queries')))
@section('main-content')
@php
$languageClass = '';
if (Auth::user()->language === 'ar') {
    $languageClass = 'arabic';
}
@endphp

<div id="offline-querry" class="main-container {{ $languageClass }}" style="overflow: visible;"  @if(Auth::user()->role_id !=1 && Auth::user()->language =="ar") dir="rtl" @endif>
    <!-- <div class="loader">
        <div class="loader__spinner"></div>
    </div> -->
    <div class="content__filters">
        <div class="left_column">
            <div class="input-ctr">
                <input type="text" class="custom-input custom-input-searchtable {{ $languageClass }}" id="datatable-search" autocomplete="off" placeholder="{{default_trans($organizationId.'/offline_queries.ui_elements_messages.search', __('default/offline_queries.ui_elements_messages.search'))}}">
            </div>
            <!-- <div>
                <select class="select" id="query-status" name="status">
                    <option  value='' selected> Select Status </option>
                    @foreach($status as $k=>$v)
                    <option value={{$k}}>{{$v}}</option>
                    @endforeach
                </select>
            </div> -->
            <div class="select-custom margin-right-1">
                <select id="select-agents" class="select" name="agentIds">
                    <option  value='' selected> --Select Status--</option>
                    @foreach($status as $k=>$v)
                    <option value={{$k}}>{{$v}}</option>
                    @endforeach
                </select>
                <div class="dropdown select" tabindex="0" onmousedown="openDropdown(event)">
                    <span class="current">Select Status</span>
                    <input class="current-val" type="hidden" value=""/>
                    <div class="list">
                        <ul>
                            <li  data-val="">Select Status</li>
                            @foreach($status as $k=>$v)
                            <li data-val={{$k}}>{{$v}}</li>
                            @endforeach
                        </ul>
                        <div class="ps__rail-x" style="left: 0px; bottom: 0px;">
                            <div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div>
                        </div>
                        <div class="ps__rail-y" style="top: 0px; height: 160px; right: 0px;">
                            <div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 75px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="submit-btn-ctr">
                <input type="text" name="date" id="dashboard-7" class="custom-input-calendar {{ $languageClass }}" placeholder="DD-MM-YYYY - DD-MM-YYYY" onkeydown="dateRangeKeyDownUserLogging(event)">
                <button style="display:inline-block" class="custom-button custom-button-green margin-right-1" onClick="selectedDateRange();">{{default_trans($organizationId.'/user_logging.ui_elements_messages.submit', __('default/user_logging.ui_elements_messages.submit'))}}</button>
            </div>

            <a href={{url('offline-query/export?')}} id="offline-export" class="custom-button custom-button-blue">
                <i class="fa fa-download" aria-hidden="true"></i>
            </a>
        </div>
    
    <div class="right-filter {{ $languageClass }}">
            <div class="stv-radio-tabs-wrapper">
                <input type="radio" class="stv-radio-tab select-days" name="filter" id="15days" value="15" checked  />
                <label for="15days" >15 {{default_trans($organizationId.'/offline_queries.ui_elements_messages.days', __('default/offline_queries.ui_elements_messages.days'))}} </label>
                <input type="radio" class="stv-radio-tab select-days" name="filter" id="30days" value="30" />
                <label for="30days">30 {{default_trans($organizationId.'/offline_queries.ui_elements_messages.days', __('default/offline_queries.ui_elements_messages.days'))}}</label>
                <input type="radio" class="stv-radio-tab select-days" name="filter" id="45days" value="45"   />
                <label for="45days">45 {{default_trans($organizationId.'/offline_queries.ui_elements_messages.days', __('default/offline_queries.ui_elements_messages.days'))}}</label>
            </div>
        </div>
        </div>
    <div class="content__wrapper content__wrapper--organization margin-top-2"  style="overflow: visible;">
        <div class="margin-bottom-2 margin-top-2">
            <table class="table table-sorting image-list {{ $languageClass }}" id="offline-query-table">
                <thead>
                    <tr>
                        <th class="">{{default_trans($organizationId.'/offline_queries.ui_elements_messages.group', __('default/offline_queries.ui_elements_messages.group'))}}</th>
                        <th class="">{{default_trans($organizationId.'/offline_queries.ui_elements_messages.source_type', __('default/offline_queries.ui_elements_messages.source_type'))}}</th>
                        <th class="">{{default_trans($organizationId.'/offline_queries.ui_elements_messages.identifier', __('default/offline_queries.ui_elements_messages.identifier'))}}</th>
                        <th class="">{{default_trans($organizationId.'/offline_queries.ui_elements_messages.client_query', __('default/offline_queries.ui_elements_messages.client_query'))}}</th>
                        <th class="">{{default_trans($organizationId.'/offline_queries.ui_elements_messages.status', __('default/offline_queries.ui_elements_messages.status'))}}</th>
                        <th class="">{{default_trans($organizationId.'/offline_queries.ui_elements_messages.query_date', __('default/offline_queries.ui_elements_messages.query_date'))}}</th>
                        @cannot('all-admin')
                        <th class="">{{default_trans($organizationId.'/offline_queries.ui_elements_messages.action', __('default/offline_queries.ui_elements_messages.action'))}}</th>
                        @endcannot
                    </tr>
                </thead>

            </table>
        </div>
</div>
<div class="popup popup__container" id="confirm-offline-popup">
<div class="popup__wrapper popup__small">
    <a class="close-btn {{ $languageClass }}"><i class="fas fa-times"></i></a>
    <div class="popup__content">
    <div id="is_free_push_time_over_old" style="display:none;">
        <p style="padding: 15px 30px;">
        You have passed the 24 hour duration of free text messaging. Please wait for the customer to reply in order to connect again.
        </p>
    </div>
    <div id="is_free_push_time_over_new" style="display:none;">
        <div class="width100 text-center pad-tb-10 hide"  id="whatsapp-template-container">
            <select class="select" id="whatsapp-template">
                <option  selected="true" disabled="disabled">No templates</option>
            </select>
        </div>
        <div class="width100 text-center">
            <span id="popup-cinfirm-text"></span>
        </div>
        <div class="buttons__all">
            <button class="custom-button custom-button-primary" id="confirm-offline-action" value='' data-id="" data-url="">{{default_trans($organizationId.'/offline_queries.ui_elements_messages.yes', __('default/offline_queries.ui_elements_messages.yes'))}}</button>
            <button class="custom-button" id="cancel">{{default_trans($organizationId.'/offline_queries.ui_elements_messages.no', __('default/offline_queries.ui_elements_messages.no'))}}</button>
        </div>
    </div>
    </div>
</div>
</div>
<div class="overlay"></div>
@endsection

@push('custom-scripts')
<script>
    var table_columns = '{!! $table_columns !!}';
</script>
<script src="{{mix('js/offline-queries.js')}}" type="text/javascript"></script>
@endpush
