@extends('app')
@section('heading','Customize Field')
@section('title','Customize Field')
@section('main-content')
@php
$languageClass = '';
if (Auth::user()->role_id !=1 && Auth::user()->language =="ar"){
    $languageClass = 'arabic';
}
@endphp

<div class="main-container {{ $languageClass }}" @if(Auth::user()->role_id !=1 && Auth::user()->language =="ar") dir="rtl" @endif>
<div class="content__filters">
        <div class="left_column mar-btm-10px">
            @can('superadmin')
            <div class="select-custom margin-right-1">

                <select class="select dropdown-search" id="select-organization" name="organization_id">
                    <option  selected disabled value="null"> --Select Organization--</option>
                    @foreach($organization as $k=>$v)
                    <option value={{$v->id}}>{{$v->company_name}}</option>
                    @endforeach
                </select>

            </div>
            @else
            <select class="select" id="select-organization" name="organization_id" hidden>
                <option value={{Auth::user()->organization_id}}></option>
            </select>
            @endcan

        </div>
    </div>
    <div class="menu_nav_translator">
        <ul id="nav" class="{{ $languageClass }}">
            @php $index = 0;  @endphp
            @foreach ($types as $type_key => $type)
            <li class="check_{{ $index++ }}">
                <a class="sub_menu" href="#">{{$type}}</a>
                <ul class="{{ $languageClass }}">
                    @foreach ($features as $feature_key => $feature)
                    <li><a href="#" class="translator-features" data-feature="{{$feature_key}}" data-type="{{$type_key}}">{{$feature}}</a></li>
                    @endforeach
                </ul>
            </li>
            @endforeach
            </ul>
        </div>
<div class="overlay"></div>
<div  id="language-list-table">
</div>

<form id="translator_edit_form"  method="post" role="form" action="{{ route('translator.keys') }}" autocomplete="off">
    <div class="popup popup__container" id="translator_edit__popup">
        <div class="popup__wrapper">
            <a class="close-btn {{ $languageClass }}"><i class="fas fa-times"></i></a>
            <div class="popup__wrapper__heading">Language data edit</div>
            <div id="translator_edit_partial"></div>
            <div class="buttons__all">
                <button type=button class="custom-button custom-button-green" id="cancel">Cancel</button>
                <button class="custom-button custom-button-blue">Submit</button>
            </div>
            <p class="popup__wrapper__heading hidden permission_error ">{{default_trans($organizationId.'/user_list.ui_elements_messages.some_thing_wrong', __('default/user_list.some_thing_wrong'))}} </p>
        </div>
    </div>
</form>
</div>

@endsection
@push('custom-scripts')

<script src="{{mix('js/translator-common.js')}}" type="text/javascript"></script>
<script src="{{ asset('vendor/translator/js/translator.js')}}" type="text/javascript"></script>
<script src="{{ asset('vendor/translator/js/customize-field.js')}}" type="text/javascript"></script>
@can('admin')
<script type="text/javascript">
$(document).ready(function () {
    $(".menu_nav_translator").show();
});
</script>
@endcan
@endpush
