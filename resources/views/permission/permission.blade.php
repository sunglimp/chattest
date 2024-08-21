@extends('app')
@section('main-content')
@section('heading',default_trans($organizationId.'/permission.ui_elements_messages.permission', __('default/permission.ui_elements_messages.permission')))
@section('title',default_trans($organizationId.'/permission.ui_elements_messages.permissions', __('default/permission.ui_elements_messages.permissions')))
@php
$languageClass = '';
if (Auth::user()->role_id !=1 && Auth::user()->language =="ar"){
    $languageClass = 'arabic';
}
@endphp

<div class="main-container {{ $languageClass }}"  @if(Auth::user()->role_id !=1 && Auth::user()->language =="ar") dir="rtl" @endif>
    <form id="add-organization-permission-1" method="post" role="form" action="{{ action('PermissionController@store') }}">

        <div class="content__filters">

            <div class="left_column">
                @if(Gate::allows('superadmin'))
                    <div class="select-custom">
                        <select autocomplete="off"
                            class="select dropdown-search"
                            id="organization_list"
                            name="organization_id">
                            <option  value="0" disabled selected
                                     data-display-text="Select Organization">
                                Select Organization
                            </option>
                            @foreach($organization_list as $k=>$v)
                                <option value={{$v->id}}>{{$v->company_name}}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <input type="hidden" id="organization_list" name="organization_id" value="{{auth()->user()->organization_id}}"/>
                @endif

            </div>
        </div>
       
        <span class='response-message'></span>

        <div id="organization-permission-ajax">
                <div class="spin__preloader" id="spin__preloader_permission" style="display: none">
                        <div class="loader__spinner"></div>
                        <!-- <div class="spin__bg"></div> -->
                </div>
            @if(Gate::allows('admin'))
            @include('permission/permission_partial')
            @endif
        </div>
    </form>
</div>
<div id="tag-popup"></div>
<div id="offline_form-popup"></div>
<div class="overlay"></div>
@endsection
@push('custom-scripts')
<script src="{{mix('js/permission.js')}}"></script>
@endpush

