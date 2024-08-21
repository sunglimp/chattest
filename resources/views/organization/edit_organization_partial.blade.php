<div class="popup__content">
    <div class="popup__content--wrap">
        <label>Company Name <span class="astrick">*</span></label>
        <input type="text" id="company_name" name="company_name" value="{{$organization_detail->company_name}}" class="custom-input" />
    </div>
    <div class="popup__content--wrap">
        <label>Contact Name <span class="astrick">*</span></label>
        <input type="text" id="contact_name" name="contact_name" value="{{$organization_detail->contact_name}}" class="custom-input" />
    </div>
    <div class="popup__content--wrap">
        <label>Mobile Number <span class="astrick">*</span></label>
        <input type="text" id="mobile_number" name="mobile_number" value="{{$organization_detail->mobile_number}}" class="custom-input" />
    </div>
    <div class="popup__content--wrap">
        <label>Email Id <span class="astrick">*</span></label>
        <input type="text" id="email" name="email" value="{{$organization_detail->email}}" class="custom-input" />
    </div>
    <div class="popup__content--wrap">
        <label>No. of Seats<span class="astrick">*</span></label>
        <input type="text" id="seat_alloted" name="seat_alloted" value="{{$organization_detail->seat_alloted}}" class="custom-input" />
    </div>
    <div class="popup__content--wrap">
        <label>Website</label>
        <input type="text" id="website" name="website" value="{{$organization_detail->website}}" class="custom-input" />
    </div>
    <div class="popup__content--wrap">
        <label>Time Zone<span class="astrick">*</span></label>
        <div class="select-custom time_zone no-shadow">

            <select name="timezone" class="dropdown-search">
                <option disabled selected="" >Select Timezone</option>
                @foreach($timezone_list as $k=>$v)
                @php $selected = $k == $organization_detail->timezone?"selected":''; @endphp
                <option {{$selected}}
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
                    @foreach ($org_langauges as $language=>$selected_lang)
                    	@if ($selected_lang['value'] == true)
                    		<option selected value="{{$language}}">{{$selected_lang['label']}}</option>
                    	@else
                    		<option value="{{$language}}" >{{$selected_lang['label']}}</option>
                    	@endif
                    @endforeach
			</select>
		</div>
    </div>
    <div class="popup__content--wrap" style="position: relative; top: 0px;">
        <i class="fa fa-times-circle" id="reset-icon" title="Click to reset Date" aria-hidden="true" onClick="resetDatePicker()" style="display: none;"></i>
        <label>Validity Date</label>
        <input type="text" name="validity_date" value="{{$organization_detail->validity_date}}" id="calendar-editorganization" class="custom-input-calendar" data-mindate="today"/>
    </div>
    <div class="popup__content--wrap relative">
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
            <input type="checkbox" class="notification-event" name="is_testing" value="1"  {{ $organization_detail->is_testing ? "checked" : "" }} /> 
            <span class="checkmark"></span> 
            <span class="checkbox__title" style="font-size: 1.25rem;">Is Demo?</span> 
        </label> 
    </div>

    <input type="hidden" name="organization_id" id="organization_id" value="{{$organization_detail->id}}" />

</div>
<style>
#edit_organization_partial #calendar-editorganization.custom-input-calendar{
    width: 21rem;
}
#edit_organization_partial i.fa-times-circle{
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
#edit_organization_partial .date.disabled {
    opacity: 1;
}
</style>
<script src="https://raw.githubusercontent.com/dmuy/DCalendar/Material/dcalendar.picker.js"></script>
<script>
$(document).ready(function(){
    $('#calendar-editorganization').dcalendarpicker({format: 'yyyy-mm-dd'}).on('datechanged', function(e){
        $('#reset-icon').show();
    });
    if ($('#calendar-editorganization').val().length > 0) {
        $('#reset-icon').show();
    }
  });

  function resetDatePicker() {
      $('#calendar-editorganization').val('');
      $('#reset-icon').hide();
  }
</script>