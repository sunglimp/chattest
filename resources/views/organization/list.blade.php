@extends('app')
@section('heading','ORGANIZATION LIST')
@section('title','Organizations')
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
        <div>
            <input type="text" class="custom-input custom-input-search {{ $languageClass }}" id="datatable-search" placeholder="Search">

        </div>
        <div class="custom-button custom-button-green popup-btn" id="add" onclick="showOrganizationModal()">
            <i class="fas fa-plus-square"></i>
            Organization

        </div>
    </div>
    <div class="content__wrapper content__wrapper--organization margin-top-2">

        <div class=" margin-top-2">
            <table class="table table-sorting image-list {{ $languageClass }}" id="myTable">
                <thead>
                    <tr>
                        <th class=" "></th>
                        <th class=" ">Company</th>
                        <th class=" ">Name</th>
                        <th class=" ">Contact</th>
                        <th class=" ">Email</th>
                        <th class=" ">Seats</th>
                        <th class="">Account Type</th>
                        <th class="">Status</th>
                        <th class="">Action</th>
                    </tr>
                </thead>

            </table>

            <div class="custom-dropdown float-for-datatable">
                <div class="flex-center head_sorting">
                    <label>Show</label>
                    <div class="select-custom">
                        <select name="table_length" class="select"
                        id="datatable-length"
                        autocomplete="off">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                    <label>enteries</label>
                </div>
            </div>
            <!-- </div> -->
        </div>
        <div id="add_organization_partial"></div>
        <div class="popup popup__container" id="edit__popup">
            <form id="edit-organization-form" enctype="multipart/form-data" method="post" role="form" action="{{ action('OrganizationController@update') }}">
                <div class="popup__wrapper">
                    <a class="close-btn {{ $languageClass }}"><i class="fas fa-times"></i></a>
                    <div class="popup__wrapper__heading">Edit Organization</div>
                    <div id="edit_organization_partial"></div>
                    <div class="buttons__all">
                            <button type="reset" class="custom-button custom-button-green" id="cancel">Cancel</button>
                            <button type="submit" class="custom-button custom-button-blue">Update</button>
                        </div>
                </div>
            </form>
        </div>
        <div class="popup popup__container view__organization__popup" id="view__popup" >
            <div class="popup__wrapper popup__small">
                <a class="close-btn {{ $languageClass }}"><i class="fas fa-times"></i></a>
                <div class="popup__wrapper__heading">Organization Details</div>
                <div class="popup__content">
                    <div class="popup__small__content--wrap">
                        <img id="organization-logo" src="{{asset("images/johndoe.jpeg")}}" class="picture"/>
                    </div>
                    <div class="popup__small__content--wrap">
                        <label>Company Name </label>
                        <span id="view_company_name"></span>
                    </div>
                    <div class="popup__small__content--wrap">
                        <label>Contact Name <span class="astrick">*</span></label>
                        <span id="view_contact_name"></span>
                    </div>
                    <div class="popup__small__content--wrap">
                        <label>Mobile Number <span class="astrick">*</span></label>
                        <span id="view_mobile_number"></span>
                    </div>
                    <div class="popup__small__content--wrap">
                        <label>Email Id <span class="astrick">*</span></label>
                        <span id="view_email"></span>
                    </div>
                    <div class="popup__small__content--wrap">
                        <label>No. of Seats</label>
                        <span id="view_seat_alloted"></span>
                    </div>
                    <div class="popup__small__content--wrap">
                        <label>Website</label>
                        <span id="view_website"></span>
                    </div>
                    <div class="popup__small__content--wrap">
                        <label>Time Zone</label>
                        <span id="view_timezone"></span>
                    </div>
                    <div class="popup__small__content--wrap">
                        <label>Account Type</label>
                        <span id="view_account_type"></span>
                    </div>

                </div>
            </div>
        </div>
        <div class="popup popup__container" id="delete__popup">
            <div class="popup__wrapper popup__small">
                <a class="close-btn {{ $languageClass }}"><i class="fas fa-times"></i></a>
                <div class="popup__content">
                    <span>Do you want to delete this organization ?</span>
                    <div class="buttons__all">
                        <button class="custom-button custom-button-primary" id="delete_id" value='' onclick="deleteOrganization()">Yes</button>
                        <button class="custom-button" id="cancel">No</button>
                    </div>
                </div>
            </div>
        </div>
          <div class="popup popup__container" id="key__popup">
                <div class="popup__wrapper">
                    <a class="close-btn {{ $languageClass }}"><i class="fas fa-times"></i></a>
                    <div class="popup__wrapper__heading">Unique Key</div>
                    <div class="popup__content">
                        <div class="popup__content popup__unique-key">
                            <input type="text" id="org-key"class="custom-input" size=38
                                readonly>
                            <span class="icon copyClipboard">
                                <span class="icon-hover-text icon-hover-copy">Copy</span>
                                <i class="far fa-copy" title="copy to clipboard"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</div>
@endsection
@push('custom-scripts')
<script src="{{mix('js/organization.js')}}" type="text/javascript"></script>
@endpush
