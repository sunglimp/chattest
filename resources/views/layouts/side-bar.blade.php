@php
$languageClass = '';
if (Auth::user()->role_id !=1 && Auth::user()->language =="ar"){
    $languageClass = 'arabic';
}
@endphp

<section class="nav-bar {{ $languageClass }}"   @if(Auth::user()->role_id !=1 && Auth::user()->language =="ar") dir="rtl" @endif >
    <div class="logo">
        <img src="{{asset("images/logo_small.png")}}" alt="logo" class="logo__img">
    </div>
    <ul class="menubar">
        @if(isset($permissions[config('constants.PERMISSION.DASHBOARD-ACCESS')]) && (count($permissions)> 0) && $permissions[config('constants.PERMISSION.DASHBOARD-ACCESS')] === true)
            <li class="menubar__items menubar__items__nocollapse" data-url="dashboard">
                <a href="{{route('dashboard')}}">
                    <img src="{{asset('images/dashboard.svg')}}" alt="Dashboard">
                    <span>{{default_trans($organizationId.'/sidebar.ui_elements_messages.dashboard', __('default/sidebar.ui_elements_messages.dashboard'))}}</span>
                </a>
            </li>
        @endif
        @cannot('all-admin')
            <li class="menubar__items menubar__items__nocollapse" data-url="chat">
                <a href="{{asset('/chat')}}">
                    <img src="{{asset("images/chats.svg ")}}" alt="">
                    <span>{{default_trans($organizationId.'/sidebar.ui_elements_messages.chat', __('default/sidebar.ui_elements_messages.chat'))}}</span>
                </a>
            </li>
        @endcannot
        @cannot('superadmin')
            <li class="menubar__items menubar__items__nocollapse" data-url="chat/archive">
                <a href="{{asset('/chat/archive')}}">
                    <img src="{{asset("images/archive.svg ")}}" alt="">
                    <span>{{default_trans($organizationId.'/sidebar.ui_elements_messages.archive', __('default/sidebar.ui_elements_messages.archive'))}}</span>
                </a>
            </li>
        @endcannot

        @can('superadmin')
            <li class="menubar__items menubar__items__nocollapse" data-url="organization">
                <a href="{{route('organization.index')}}">
                    <img src="{{asset("images/organizationlist.svg")}}" alt="">
                    <span>{{default_trans($organizationId.'/sidebar.ui_elements_messages.organization_list', __('default/sidebar.ui_elements_messages.organization_list'))}}</span>
                </a>
            </li>
        @endcan
        @if(Gate::allows('superadmin') || Gate::allows('admin'))
            <li class="menubar__items menubar__items__nocollapse" data-url="permission">
                <a href="{{route("permission.index")}}">
                    <img src="{{asset("images/permissionslist.svg ")}}" alt="">
                    <span>{{default_trans($organizationId.'/sidebar.ui_elements_messages.permission_list', __('default/sidebar.ui_elements_messages.permission_list'))}}</span>
                </a>
            </li>
            <li class="menubar__items menubar__items__nocollapse" data-url="user">
                <a href="{{route("user.index")}}">
                    <img src="{{asset("images/userlist.svg ")}}" alt="">
                    <span>{{default_trans($organizationId.'/sidebar.ui_elements_messages.user_list', __('default/sidebar.ui_elements_messages.user_list'))}}</span>
                </a>
            </li>
        @endif
        @if(isset($permissions[config('constants.PERMISSION.CANNED-RESPONSE')]) && (count($permissions)> 0) && $permissions[config('constants.PERMISSION.CANNED-RESPONSE')] === true)
            <li class="menubar__items menubar__items__nocollapse" data-url="chat/canned">
                <a href="{{asset('/chat/canned')}}">
                    <img src="{{asset("images/canned.svg ")}}" alt="">
                    <span>{{default_trans($organizationId.'/sidebar.ui_elements_messages.canned_response', __('default/sidebar.ui_elements_messages.canned_response'))}}</span>
                </a>
            </li>
        @endif
        @if((isset($permissions[config('constants.PERMISSION.EMAIL')]) && (count($permissions)> 0) && $permissions[config('constants.PERMISSION.EMAIL')] === true))
            @cannot('all-admin')
                <li class="menubar__items menubar__items__nocollapse" data-url="email/sent">
                    <a href="{{route("email.sent")}}">
                        <img src="{{asset("images/emailsent.svg ")}}" alt="">
                        <span>{{default_trans($organizationId.'/sidebar.ui_elements_messages.sent_emails', __('default/sidebar.ui_elements_messages.sent_emails'))}}</span>
                    </a>
                </li>
            @endcannot
        @endif

        @cannot('all-admin')
            @if(isset($permissions[config('constants.PERMISSION.SUPERVISE-TIP-OFF')]) && (count($permissions)> 0) && $permissions[config('constants.PERMISSION.SUPERVISE-TIP-OFF')] === true && !Gate::allows('associate'))
                <li class="menubar__items menubar__items__nocollapse" data-url="chat/supervise">
                    <a href="{{asset('/chat/supervise')}}">
                        <img src="{{asset("images/supervise.svg ")}}" alt="">
                        <span>{{default_trans($organizationId.'/sidebar.ui_elements_messages.supervise_tipoff', __('default/sidebar.ui_elements_messages.supervise_tipoff'))}} </span>
                    </a>
                </li>
            @endif
            @if(isset($permissions[config('constants.PERMISSION.CLASSIFIED-CHAT')]) && (count($permissions)> 0) && $permissions[config('constants.PERMISSION.CLASSIFIED-CHAT')] === true)
                <li class="menubar__items menubar__items__nocollapse" data-url="chat/ticket">
                    <a href="{{asset('/chat/ticket')}}">
                        <img src="{{asset("images/archive.svg ")}}" alt="">
                        <span>{{default_trans($organizationId.'/sidebar.ui_elements_messages.classified_chat', __('default/sidebar.ui_elements_messages.classified_chat'))}} </span>
                    </a>
                </li>
            @endif
        @endcannot

        @cannot('all-admin')
            @if(isset($permissions[config('constants.PERMISSION.TMS-KEY')]) && (count($permissions)> 0) && $permissions[config('constants.PERMISSION.TMS-KEY')] === true)
                <li class="menubar__items menubar__items__nocollapse" data-url="chat/status">
                    <a href="{{asset('/chat/status')}}">
                        <img src="{{asset("images/supervise.svg ")}}" alt="">
                        <span>{{default_trans($organizationId.'/sidebar.ui_elements_messages.ticket_enquire', __('default/sidebar.ui_elements_messages.ticket_enquire'))}} </span>
                    </a>
                </li>
            @endif
        @endcannot
        @cannot('all-admin')
            @if(isset($permissions[config('constants.PERMISSION.TMS-KEY')]) && (count($permissions)> 0) && $permissions[config('constants.PERMISSION.TMS-KEY')] === true)
                <li class="menubar__items menubar__items__nocollapse" data-url="chat/lead-status">
                    <a href="{{asset('/chat/lead-status')}}">
                        <img src="{{asset("images/supervise.svg ")}}" alt="">
                        <span>{{default_trans($organizationId.'/sidebar.ui_elements_messages.lead_enquire', __('default/sidebar.ui_elements_messages.lead_enquire'))}} </span>
                    </a>
                </li>
            @endif
        @endcannot
        
        @if((isset($permissions[config('constants.PERMISSION.BAN-USER')]) && (count($permissions)> 0) && $permissions[config('constants.PERMISSION.BAN-USER')] === true))
             @can('all-admin')
                <li class="menubar__items menubar__items__nocollapse" data-url="chat/banned-users">
                    <a href="{{asset('/chat/banned-users')}}">
                        <img src="{{asset("images/emailsent.svg ")}}" alt="">
                        <span>{{default_trans($organizationId.'/sidebar.ui_elements_messages.banned_users', __('default/sidebar.ui_elements_messages.banned_users'))}}</span>
                    </a>
                </li>
            @endcan
        @endif
        @if((isset($permissions[config('constants.PERMISSION.LOGIN-HISTORY')]) && (count($permissions)> 0) && $permissions[config('constants.PERMISSION.LOGIN-HISTORY')] === true))
             
                <li class="menubar__items menubar__items__nocollapse" data-url="history">
                    <a href="{{route("history.index")}}">
                        <img src="{{asset("images/emailsent.svg ")}}" alt="">
                        <span>{{default_trans($organizationId.'/sidebar.ui_elements_messages.user_logging', __('default/sidebar.ui_elements_messages.user_logging'))}}</span>
                    </a>
                </li>
        @endif  

        
        @cannot('superadmin')
        @if((isset($permissions[config('constants.PERMISSION.OFFLINE-FORM')]) && (count($permissions)> 0) && $permissions[config('constants.PERMISSION.OFFLINE-FORM')] === true))
        <li class="menubar__items menubar__items__nocollapse" data-url="chat/offline-queries">
                <a href="{{route('offline-queries')}}">
                    <img src="{{asset("images/archive.svg ")}}" alt="">
                    <span>{{default_trans($organizationId.'/sidebar.ui_elements_messages.offline_queries', __('default/sidebar.ui_elements_messages.offline_queries'))}}</span>
                </a>
            </li>
        @endif
        @if((isset($permissions[config('constants.PERMISSION.MISSED-CHAT')]) && $permissions[config('constants.PERMISSION.MISSED-CHAT')] === true))
        <li class="menubar__items menubar__items__nocollapse" data-url="chat/missed">
                <a href="{{route('missed-chat')}}">
                    <img src="{{asset("images/missed-chat.svg")}}" alt="">
                    <span>{{default_trans($organizationId.'/sidebar.ui_elements_messages.missed_chat', __('default/sidebar.ui_elements_messages.missed_chat'))}}</span>
                </a>
            </li>
        @endif 
        @endcannot

   @if(Gate::allows('superadmin') || Gate::allows('admin'))          
                <li class="menubar__items menubar__items__nocollapse" data-url="translator/get">
                    <a href="{{route("translator.index")}}">
                        <img src="{{asset("images/emailsent.svg ")}}" alt="">
                        <span>{{default_trans($organizationId.'/sidebar.ui_elements_messages.customize_fields', __('default/sidebar.ui_elements_messages.customize_fields'))}}</span>
                    </a>
                </li>
 @endif
    </ul>
</section>
