@php
$languageClass = '';
if (Auth::user()->role_id !=1 && Auth::user()->language =="ar"){
    $languageClass = 'arabic';
}
@endphp

<form id="add-organization-form" enctype="multipart/form-data" method="post" role="form" action="{{ action('OrganizationController@store') }}" autocomplete="off">
    <div class="popup popup__container" id="add__popup">
<!--         <span class='ajax-response-message'></span> -->
        <div class="popup__wrapper">
            <a class="close-btn {{ $languageClass }}"><i class="fas fa-times"></i></a>
            <div class="popup__wrapper__heading">Add Organization</div>
            <div class="popup__content">
                <div class="popup__content--wrap">
                    <label>{{default_trans('85/dashboard.ui_elements_messages.organization_name', "Default Organization")}}<span class="astrick">*</span></label>
                    <input type="text" name="company_name" class="custom-input" />
                </div>
                <div class="popup__content--wrap">
                    <label>Contact Name <span class="astrick">*</span></label>
                    <input type="text" name="contact_name" class="custom-input" />
                </div>
                <div class="popup__content--wrap">
                    <label>Mobile Number <span class="astrick">*</span></label>
                    <input type="text" name="mobile_number" class="custom-input" />
                </div>
                <div class="popup__content--wrap">
                    <label>Email Id <span class="astrick">*</span></label>
                    <input type="text" name="email" class="custom-input" />
                </div>
                <div class="popup__content--wrap">
                    <label>No. of Seats<span class="astrick">*</span></label>
                    <input type="text" name="seat_alloted" class="custom-input" />
                </div>
                <div class="popup__content--wrap">
                    <label>Website</label>
                    <input type="text" name="website" class="custom-input" placeholder="eg- https://abc.com" / >
                </div>
                <div class="popup__content--wrap">
                    <label>Time Zone<span class="astrick">*</span></label>
                    <div class="select-custom time_zone no-shadow">
                        <select name="timezone" class="dropdown-search">
                            <option disabled selected="" >Select Timezone</option>
                            @foreach($timezone_list as $k=>$v)
                            <option
                                value="{{ $k }}"
                                >{{ $k.'('.$v.')' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="popup__content--wrap">
       			    <label>Languages</label>
       				<div class="select-custom">
            		    <select multiple="multiple" name="language[]" id="language" class="groupMultiple">
                                @foreach ($languages as $key=>$lang)
                                    <option value="{{ $key }}">{{$lang}}</option>
                                @endforeach
            			</select>
        		    </div>
    			</div>
                <div class="popup__content--wrap"  style="position: relative; top: 0px;">
                    <i class="fa fa-times-circle" id="reset-icon" title="Click to reset Date" aria-hidden="true" onClick="resetDatePicker()" style="display: none;"></i>
                    <label>Validity Date</label>
                    <input type="text" name="validity_date" id="calendar-organization" class="custom-input-calendar" data-mindate="today"/>
                </div>
                <div class="popup__content--wrap">
                    <label>Logo</label>
                    <label for="input-file" class="file-uploader">
                        <i class="far fa-file-image"></i>
                        <span class="file-name">Drag and Upload Image</span>
                        <span class="file-delete"><i class="fas fa-times"></i></span>
                    </label>
                    <input type="file" class="input-file" id="input-file-custom" name="image" />
                </div>
                <div class="popup__content--wrap">
                    <label class="checkbox__container"> 
                        <input type="checkbox" class="notification-event" name="is_testing" value="1"> 
                        <span class="checkmark"></span> 
                        <span class="checkbox__title" style="font-size: 1.25rem;">Is Demo?</span> 
                    </label>
                </div>

                <input type="hidden" name="organization_id"  value="" />
            </div>
            <div class="buttons__all">
                <button  type="reset" class="custom-button custom-button-green" id="cancel">Cancel</button>
                <button type="submit" class="custom-button custom-button-blue">Submit</button>
            </div>
        </div>
    </div>
</form>
<style>
#add-organization-form .date.disabled {
    opacity: 1;
}
#add-organization-form #calendar-organization.custom-input-calendar{
    width: 21rem;
}
#add-organization-form i.fa-times-circle{
    position: absolute;
    top: 25px;
    right: 30px;
    color: #949494;
    cursor: pointer;
    font-size: 15px;
}
.calendar-wrapper[data-theme='blue'] .calendar-head-card{
    background-color: #2d4059;
}
</style>
<script>
$(document).ready(function(){
    $('#calendar-organization').dcalendarpicker({format: 'yyyy-mm-dd'}).on('datechanged', function(e){
        $('#reset-icon').show();
    });
  });

  function resetDatePicker() {
      $('#calendar-organization').val('');
      $('#reset-icon').hide();
  }
</script>