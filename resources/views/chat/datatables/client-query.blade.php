<span class="icon icon--warning" id="{{$id}}" data-id=''>
@if(!empty($client_query))
    @php
    $querry_message = json_decode($client_query,true);    
    @endphp
    @if(is_array($querry_message) && !empty($querry_message))
        <div  class="icon-hover-text-long">
            @foreach($querry_message as $query)
            <div>
                <span>{{ ($query['message_type'] === 'VISITOR') ? 'CUSTOMER' : $query['message_type']  }} </span> : 
                <span>{{ $query['response_text'] }} </span>
            </div>
            @endforeach
        </div>
    @else
        @if (strlen($client_query) > 0)
        <span @if (strlen($client_query) > 50) class="icon-hover-text-long" @else class="icon-hover-text" @endif>{{$client_query}} </span>
        @endif
    @endif
    <i class="fas fa-eye"></i>
@endif
</span>