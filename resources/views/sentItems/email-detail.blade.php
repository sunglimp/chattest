@php
$languageClass = '';
if (Auth::user()->role_id !=1 && Auth::user()->language =="ar"){
    $languageClass = 'arabic';
}
@endphp

@if($totalItems != 0)
<div class="email__sent__detail">
   <div class="email__header">
       <span id="email-subject" title=""></span>
           <div dir="ltr" class="email__navigate {{ $languageClass }}">
               <span class="inactive" id="prev-email"><i class="fas fa-angle-left"></i></span>
               <span class="next__email" id="next-email"><i class="fas fa-angle-right"></i></span>
           </div>
           </div>
           <div class="email__header__details">
               <div class="email__header__details__alphabets" id="email-initail"></div>
               <div class="email__header__details__fname">
                   <span id="email-to"></span>
                   <div class="time">{{default_trans($organizationId.'/sent_emails.ui_elements_messages.time', __('default/sent_emails.ui_elements_messages.time'))}}: <span id="email-time"></span>
                       <i class="fas fa-angle-down detail_cc hidden" id="upper-arrows"></i>
                   </div>
                   <div class="email__name" id="email-cc-bcc">
                       <div class="email_cc_bcc">
                           <ul>
                               <li id="email-cc-parent" class="hidden">{{default_trans($organizationId.'/sent_emails.ui_elements_messages.cc', __('default/sent_emails.ui_elements_messages.cc'))}}: <span class="cc__email" id="email-cc"></span>
                               </li>
                               <li id="email-bcc-parent" class="hidden">{{default_trans($organizationId.'/sent_emails.ui_elements_messages.bcc', __('default/sent_emails.ui_elements_messages.bcc'))}}: <span class="bcc__email" id="email-bcc"></span>
<!--                                     <i id="down-arrows" class="fas fa-angle-up detail_cc_close"></i> -->
                               </li>

                           </ul>
                       </div>
                   </div>
               </div>
           </div>
       <div class="email__fcontent" id="email-body">
       </div>
</div>
@else
<div class="no__records">{{default_trans($organizationId.'/sent_emails.ui_elements_messages.no_records_found', __('default/sent_emails.ui_elements_messages.no_records_found'))}}</div>
@endif
