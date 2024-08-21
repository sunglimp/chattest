<a href="{{asset('/history/get-user-history/'.$id)}}" onClick="getDateVal();" class="link" >{{$name}}</a>
    
@push('custom-scripts')
<script src="{{mix('js/history.js')}}" type="text/javascript"></script>
@endpush
