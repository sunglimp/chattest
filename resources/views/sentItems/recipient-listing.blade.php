@php
$languageClass = '';
if (Auth::user()->role_id !=1 && Auth::user()->language =="ar"){
    $languageClass = 'arabic';
}
@endphp

@if($totalItems != 0)
<!-- <ul class="email__sent__ul" id="recipient-listing"> -->
	@foreach($email as $val)
		 @if($val->attachment_size != null)
        	@php $attachment = 'has-attachement'@endphp
        @else
        	@php $attachment = ''@endphp
        @endif
		<li onClick="getEmail({{$val->id}}, this)" data-id = {{$val->id}} id={{'email'.$val->id}} tabindex='-1' class="">
        <div class="name-alphabets {{ $languageClass }}" style="background-color: {{$val->color_code}}">
            {{$val->initials}}
        </div>
       
        <div class="email__details {{$attachment}}">
            <span class="email__details__time  {{ $languageClass }}" dir="ltr">{{$val->sent_date}}</span>
            <span class="email__details__name">{{$val->senders}}</span>
            <span class="email__details__subject">{{$val->subject}}</span>
            <span class="email__details__content">{!! $val->body !!}</span>
            <i class="fa fa-paperclip" aria-hidden="true"></i>
        </div>
    </li>
	@endforeach
<!-- </ul> -->
@else
<div >{{default_trans($organizationId.'/sent_emails.ui_elements_messages.no_records_found', __('default/sent_emails.ui_elements_messages.no_records_found'))}}</div>      
@endif               