
<label class="switch">
    @php $checked = '' @endphp
    @if($status==1)
        @php $checked = 'checked' @endphp
    @endif
    <input type="checkbox" id='{{$id}}' onchange="changeUserStatus ({{$id}})" {{$checked}}>
    <span class="slider round"></span>
</label>
