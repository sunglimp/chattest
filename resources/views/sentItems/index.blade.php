@extends('app')
@section('heading',default_trans($organizationId.'/sent_emails.ui_elements_messages.sent_items', __('default/sent_emails.ui_elements_messages.sent_items')))
@section('title', default_trans($organizationId.'/sent_emails.ui_elements_messages.sent_items', __('default/sent_emails.ui_elements_messages.sent_items')))
@section('main-content')
@php
$languageClass = '';
if (Auth::user()->role_id !=1 && Auth::user()->language =="ar"){
    $languageClass = 'arabic';
}
@endphp
<div class="main-container {{ $languageClass }}"  @if(Auth::user()->role_id !=1 && Auth::user()->language =="ar") dir="rtl" @endif>
    <div class="email__sent">
        <div class="email__sent__listing">
            @if($totalItems != 0)
            <div class="email__sent--input">
                <input type="text" class="custom-input custom-input-search width-100  {{ $languageClass }}"/ onKeyUp = "searchEmail()" id="email-search">
            </div>

            @endif
            <div class="list__filter">
                <div class="count"><i class="fa fa-paper-plane {{ $languageClass }}" aria-hidden="true"></i>{{default_trans($organizationId.'/sent_emails.ui_elements_messages.sent_items', __('default/sent_emails.ui_elements_messages.sent_items'))}} <span id="email-count">({{$totalItems}})</span></div>
                @if($totalItems != 0)
                <div class="sorting"><a>{{default_trans($organizationId.'/sent_emails.ui_elements_messages.filter', __('default/sent_emails.ui_elements_messages.filter'))}} <i class="fas fa-angle-down"></i></a>
                    <div class="sorting__dropdown">
                        <ul>
                            <li><input type="radio" class="custom-radio" name="email-filter" id="all" onchange="filterEmail()" value="all"/> <label for="all" >{{default_trans($organizationId.'/sent_emails.ui_elements_messages.all', __('default/sent_emails.ui_elements_messages.all'))}}</label></li>
                            <li><input type="radio" class="custom-radio" name="email-filter" id="read" onchange="filterEmail()" value="attachment"/><label for="read">{{default_trans($organizationId.'/sent_emails.ui_elements_messages.with_attachment', __('default/sent_emails.ui_elements_messages.with_attachment'))}}</label></li>
                        </ul>
                    </div>
                </div>
                @endif
            </div>
            <div id ="listing">
                <ul class="email__sent__ul" id="recipient-listing">
                    @include('sentItems.recipient-listing')
                </ul>
            </div>
        </div>
        @include('sentItems.email-detail')
    </div>
</div>
@endsection
@push('custom-scripts')
<script src="{{asset('js/sent-items.js')}}"></script>
@endpush