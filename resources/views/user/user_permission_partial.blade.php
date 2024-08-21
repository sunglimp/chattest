<div class="popup__content">
    <div class="user_priviliges">
        <table class="table table-bordered table-striped">
            <tbody>
            <input type="hidden" name="organization_id" value='{{$userData->organization_id}}'>
            <input type="hidden" name="role_id" value='{{$userData->role_id}}'>
            <input type="hidden" name="user_id" value='{{$userData->id}}'>
            <ul>
                @foreach($permission_data as $k=>$v )
                    <li>
                        @php $checked_status=($v->permission_status==true)?"checked":"";  @endphp
                        {{$v->name}}
                        <input type="checkbox" id="user-priviliges-3" name="user_permisions[{{$v->id}}]" value='{{ $v->permission_status}}' {{$checked_status}}>
                    </li>
                @endforeach
            </ul>
            </tbody>
        </table>
    </div>
</div>
