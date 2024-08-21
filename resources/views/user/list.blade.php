@extends('app')
@section('heading',default_trans($organizationId.'/user_list.ui_elements_messages.user_list', __('default/user_list.ui_elements_messages.user_list')))
@section('title',default_trans($organizationId.'/user_list.ui_elements_messages.user_title', __('default/user_list.ui_elements_messages.user_title')))
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
            @can('superadmin')
            <div class="select-custom margin-right-1">

                <select class="select dropdown-search" id="select-organization" name="organization_id">
                    <option  selected disabled> --{{default_trans($organizationId.'/user_list.ui_elements_messages.select_org', __('default/user_list.ui_elements_messages.select_org'))}}--</option>
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
            <div>
                <input type="text" class="custom-input custom-input-search {{ $languageClass }}" id="datatable-search" autocomplete="off" placeholder="{{default_trans($organizationId.'/user_list.ui_elements_messages.search', __('default/user_list.ui_elements_messages.search'))}}">
            </div>
        </div>
        @canany(['superadmin','admin'])
        <button class="custom-button custom-button-green popup-btn" id="add-user"  onclick="showAddModal()">
            <i class="fas fa-plus-square"></i>
            {{default_trans($organizationId.'/user_list.ui_elements_messages.add_user_heading', __('default/user_list.ui_elements_messages.add_user_heading'))}}
        </button>
        @endcanany
    </div>
    <div class="content__wrapper content__wrapper--organization margin-top-2">
        <div class=" margin-top-2">
            <table class="table table-sorting image-list {{ $languageClass }}" id="myTable">
                <thead>
                    <tr>
                        <th class=""></th>
                        <th class="">{{default_trans($organizationId.'/user_list.ui_elements_messages.name', __('default/user_list.ui_elements_messages.name'))}}</th>
                        <th class="">{{default_trans($organizationId.'/user_list.ui_elements_messages.email', __('default/user_list.ui_elements_messages.email'))}}</th>
                        <th class="">{{default_trans($organizationId.'/user_list.ui_elements_messages.mobile', __('default/user_list.ui_elements_messages.mobile'))}}</th>
                        <th class="">{{default_trans($organizationId.'/user_list.ui_elements_messages.role', __('default/user_list.ui_elements_messages.role'))}}</th>
                        <th class="">{{default_trans($organizationId.'/user_list.ui_elements_messages.password', __('default/user_list.ui_elements_messages.password'))}}</th>
                        <th class="">{{default_trans($organizationId.'/user_list.ui_elements_messages.last_login', __('default/user_list.ui_elements_messages.last_login'))}}</th>
                        <th class="">{{default_trans($organizationId.'/user_list.ui_elements_messages.status', __('default/user_list.ui_elements_messages.status'))}}</th>
                        <th class="">{{default_trans($organizationId.'/user_list.ui_elements_messages.action', __('default/user_list.ui_elements_messages.action'))}}</th>
                    </tr>
                </thead>

            </table>

            <div class="custom-dropdown float-for-datatable">
                <div class="flex-center head_sorting">
                    <label>{{default_trans($organizationId.'/user_list.ui_elements_messages.show', __('default/user_list.ui_elements_messages.show'))}}</label>
                    <div class="select-custom">
                        <select name="table_length" class="select" id="datatable-length" autocomplete="off">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                    <label>{{default_trans($organizationId.'/user_list.ui_elements_messages.enteries', __('default/user_list.ui_elements_messages.enteries'))}}</label>
                </div>
            </div>
            <!-- </div> -->
        </div>
        <!-- <div class="table__footer">
            <label class="table__footer--info">Showing 1 to 25 of 57 entries</label>
            <ul class="table__footer--pagination">
                <li class="disabled">Previous</li>
                <li class="active">1</li>
                <li>2</li>
                <li>3</li>
                <li>Next</li>
            </ul>
        </div> -->
        <form id="adduser_form" enctype="multipart/form-data" method="post" role="form" action="{{ action('UserController@store') }}" autocomplete="off">
            <div class="popup popup__container" id="add__popup">
                <!-- <span class='ajax-response-message'></span> -->
                <div class="popup__wrapper">
                    <a class="close-btn {{ $languageClass }}"><i class="fas fa-times"></i></a>
                    <div class="popup__wrapper__heading">{{default_trans($organizationId.'/user_list.ui_elements_messages.add_user', __('default/user_list.ui_elements_messages.add_user'))}}</div>
                    <div id="add_user_partial"></div>
                    <input type="hidden" name="user_id" id="hidden_user_id"  value="" />
                    <input type="hidden" name="organization_id" id='organization_id'  value="" />
                    <div class="buttons__all">
                        <button type="reset" class="custom-button custom-button-green" id="cancel">{{default_trans($organizationId.'/user_list.ui_elements_messages.cancel', __('default/user_list.ui_elements_messages.cancel'))}}</button>
                        <button type="submit" class="custom-button custom-button-blue">{{default_trans($organizationId.'/user_list.ui_elements_messages.submit', __('default/user_list.ui_elements_messages.submit'))}}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <form id="edituser_form" enctype="multipart/form-data" method="post" role="form" action="{{ action('UserController@update') }}" autocomplete="off">
        <div class="popup popup__container" id="edit__popup">
            <div class="popup__wrapper">
                <a class="close-btn {{ $languageClass }}"><i class="fas fa-times"></i></a>
                <div class="popup__wrapper__heading">{{default_trans($organizationId.'/user_list.ui_elements_messages.edit_user', __('default/user_list.ui_elements_messages.edit_user'))}}</div>
                <div id="edit_user_partial"></div>
                <div class="buttons__all">
                    <button type=button class="custom-button custom-button-green" id="cancel">{{default_trans($organizationId.'/user_list.ui_elements_messages.cancel', __('default/user_list.ui_elements_messages.cancel'))}}</button>
                    <button class="custom-button custom-button-blue">{{default_trans($organizationId.'/user_list.ui_elements_messages.submit', __('default/user_list.ui_elements_messages.submit'))}}</button>
                </div>
            </div>
        </div>
    </form>

    <div class="popup popup__container view__organization__popup" id="view__popup">
        <div class="popup__wrapper popup__small">
            <a class="close-btn {{ $languageClass }}"><i class="fas fa-times"></i></a>
            <div class="popup__wrapper__heading">{{default_trans($organizationId.'/user_list.ui_elements_messages.user_details', __('default/user_list.ui_elements_messages.user_details'))}}</div>
            <div id="detail_user_partial"></div>
        </div>
    </div>

    <form id="edituser_permission_form"  method="post" role="form" action="{{ url('user/update-user-permission') }}" autocomplete="off">
        <div class="popup popup__container" id="permission__popup">
            <div class="popup__wrapper">
                <a class="close-btn {{ $languageClass }}"><i class="fas fa-times"></i></a>
                <div class="popup__wrapper__heading">{{default_trans($organizationId.'/user_list.ui_elements_messages.permission_details', __('default/user_list.ui_elements_messages.permission_details'))}}</div>
                <div id="user_permission_partial"></div>
                <div class="buttons__all">
                    <button type=button class="custom-button custom-button-green" id="cancel">{{default_trans($organizationId.'/user_list.ui_elements_messages.cancel', __('default/user_list.ui_elements_messages.cancel'))}}</button>
                    <button class="custom-button custom-button-blue">{{default_trans($organizationId.'/user_list.ui_elements_messages.submit', __('default/user_list.ui_elements_messages.submit'))}}</button>
                </div>
                <p class="popup__wrapper__heading hidden permission_error ">{{default_trans($organizationId.'/user_list.fail_messages.some_thing_wrong', __('default/user_list.fail_messages.some_thing_wrong'))}} </p>
            </div>
        </div>
    </form>


    <div class="popup popup__container" id="delete__popup">
        <div class="popup__wrapper popup__small">
            <a class="close-btn {{ $languageClass }}"><i class="fas fa-times"></i></a>
            <div class="popup__content">
                <span>{{default_trans($organizationId.'/user_list.ui_elements_messages.delete_user_confirm', __('default/user_list.ui_elements_messages.delete_user_confirm'))}}</span>
                <div class="buttons__all">
                    <button class="custom-button custom-button-primary" id="delete_id" value='' onclick="deleteUser()">{{default_trans($organizationId.'/user_list.ui_elements_messages.delete_yes', __('default/user_list.ui_elements_messages.delete_yes'))}}</button>
                    <button class="custom-button" id="cancel">{{default_trans($organizationId.'/user_list.ui_elements_messages.delete_no', __('default/user_list.ui_elements_messages.delete_no'))}}</button>
                </div>
            </div>
        </div>
    </div>
    
    
    <div class="popup popup__container" id="clear-login__popup">
        <div class="popup__wrapper popup__small">
            <a class="close-btn {{ $languageClass }}"><i class="fas fa-times"></i></a>
            <div class="popup__content">
                <span><i class="fas fa-exclamation-triangle"></i>{{default_trans($organizationId.'/user_list.ui_elements_messages.clear_user_login_message', __('default/user_list.ui_elements_messages.clear_user_login_message'))}}</span>
                <span>{{default_trans($organizationId.'/user_list.ui_elements_messages.clear_user_login', __('default/user_list.ui_elements_messages.clear_user_login'))}}</span>
                <div class="buttons__all">
                    <button class="custom-button custom-button-primary" id="clear_user_id" value='' onclick="clearUserLogin()">{{default_trans($organizationId.'/user_list.ui_elements_messages.delete_yes', __('default/user_list.ui_elements_messages.delete_yes'))}}</button>
                    <button class="custom-button" id="cancel">{{default_trans($organizationId.'/user_list.ui_elements_messages.delete_no', __('default/user_list.ui_elements_messages.delete_no'))}}</button>
                </div>
            </div>
        </div>
    </div>
    
    <div id="update_password_partial"></div>
</div>

<div class="overlay"></div>
@endsection

@push('custom-scripts')
<script src="{{mix('js/user.js')}}" type="text/javascript"></script>
@endpush
