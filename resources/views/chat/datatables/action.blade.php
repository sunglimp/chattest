@php $class= '' @endphp
@if($source_type != 'whatsapp')
@php $class = 'hidden' @endphp
@endif

@if($status === 1)
<span class="icon icon--accepted popup-btn test offline-query-action {{$class}}" onClick="getTemplates({{$id}})" id="send-push" data-id="{{$id}}" data-text="{{default_trans($organizationId.'/offline_queries.ui_elements_messages.confirm_push', __('default/offline_queries.ui_elements_messages.confirm_push'))}}" data-url="{{route('send-wa-push')}}">
    <span class="icon-hover-text">{{default_trans($organizationId.'/offline_queries.ui_elements_messages.push', __('default/offline_queries.ui_elements_messages.push'))}}</span>
    <i class="fab fa-whatsapp"></i>
</span>

<span class="icon icon--rejected offline-query-action" onClick="getAddclassHide()" id="reject-query" data-id="{{$id}}" data-text="{{default_trans($organizationId.'/offline_queries.ui_elements_messages.confirm_reject', __('default/offline_queries.ui_elements_messages.confirm_reject'))}}" data-url="{{route('reject-offline-query')}}">
    <span class="icon-hover-text">{{default_trans($organizationId.'/offline_queries.ui_elements_messages.reject', __('default/offline_queries.ui_elements_messages.reject'))}}</span>
    <i class="fas fa-times"></i>
</span>
@endif

