@extends('app')
@section('heading',default_trans($organizationId.'/dashboard.ui_elements_messages.dashboard', __('default/dashboard.ui_elements_messages.dashboard')))
@section('title',default_trans($organizationId.'/dashboard.ui_elements_messages.dashboard', __('default/dashboard.ui_elements_messages.dashboard')))
@section('main-content')
@php
$languageClass = '';
$marginClass = 'margin-right-1';
if (Auth::user()->role_id !=1 && Auth::user()->language =="ar"){
    $languageClass = 'arabic';
    $marginClass = 'margin-left-1';
}
@endphp
@can('superadmin')
<div class="dashboard-filter">
<div class="select-custom margin-right-1">
    <select class="select dropdown-search" id="dashboard-organization" name="organization_id">
        <option  selected disabled> --{{default_trans($organizationId.'/user_list.ui_elements_messages.select_org', __('default/user_list.ui_elements_messages.select_org'))}}--</option>
        @foreach($organization as $k=>$v)
        <option value={{$v->id}} @if($organization_id == $v->id) selected @endif>{{$v->company_name}}</option>
        @endforeach
    </select>
</div>
</div>
@endcan
<div id="dashboard-container">
    @include('dashboard.data')
</div>
@endsection
@push('custom-scripts')
<script src="{{asset('js/dashboard.js')}}"></script>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/data.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
@endpush
