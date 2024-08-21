<select name="report_to" id="report-to" class="report-to">
    <option disabled selected> {{default_trans($organizationId.'/user_list.ui_elements_messages.select_manager', __('default/user_list.ui_elements_messages.select_manager'))}}</option>
    @foreach($report_to as $key =>$value)
    <option value="{{$value->id}}">{{$value->name}}</option>
    @endforeach
</select>
