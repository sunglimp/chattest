@if(Gate::allows('superadmin'))
{{$chat_count}}
@else
<a id="archivechat_{{$id}}"  data-id="{{$id}}" data-startdate ="{{$lgn_dte}}" data-enddate="{{$lgt_dte}}" data-starttime ="{{$lgn_time}}" data-endtime ="{{$lgt_time}}" class="link archive_chat" >{{$chat_count}}</a>
@endif




    
