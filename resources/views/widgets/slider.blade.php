
<div class="width100 position-rel">
    <div class="bottom__container__wrappers responsive" style="width:1000px; margin:0 auto;">
        <div>
            <div class="data-wrappers red-icon">
                <i class="fa fa-user" aria-hidden="true"></i>
                <div class="data-wrappers__count"><span id ="chat-user">{{!empty($data->numberOfChats)? $data->numberOfChats:0}}</span></div>
                <span class="desc__span">{{default_trans($organizationId.'/dashboard.ui_elements_messages.no_of_chats', __('default/dashboard.ui_elements_messages.no_of_chats'))}}</span>
            </div>
            <div class="data-wrappers pink-icon">
                <i class="fa fa-calendar" aria-hidden="true"></i>
                <div class="data-wrappers__count"><span id="chat-per-day">{{$data->averageChats}}</span> {{default_trans($organizationId.'/dashboard.ui_elements_messages.per_day', __('default/dashboard.ui_elements_messages.per_day'))}}</div>
                <span class="desc__span">{{default_trans($organizationId.'/dashboard.ui_elements_messages.avg_chats_per_day', __('default/dashboard.ui_elements_messages.avg_chats_per_day'))}}</span>
            </div>
        </div>
        <div>
            <div class="data-wrappers pink-icon ">
                <i class="fa fa-comments" aria-hidden="true"></i>
                <div class="data-wrappers__count"><span id="chat_resolved">{{!empty($data->chatsResolved)? $data->chatsResolved:0}}</span></div>
                <span class="desc__span">{{default_trans($organizationId.'/dashboard.ui_elements_messages.chat_resolved', __('default/dashboard.ui_elements_messages.chat_resolved'))}}</span>
            </div>
            <div class="data-wrappers spark-green-icon ">
                <i class="fa fa-paper-plane" aria-hidden="true"></i>
                <div class="data-wrappers__count"><span id="chat_transfer">{{!empty($data->chatsTransferred)? $data->chatsTransferred:0}}</span></div>
                <span class="desc__span">{{default_trans($organizationId.'/dashboard.ui_elements_messages.no_of_chat_transferred', __('default/dashboard.ui_elements_messages.no_of_chat_transferred'))}}</span>
            </div>
        </div>
        <div>
        	<div class="data-wrappers light-orange-icon ">
                <i class="fa fa-star" aria-hidden="true"></i>
                <div class="data-wrappers__count"><span id="chat_transfer3">{{!empty($data->missedChats)? $data->missedChats:0}}</span></div>
                <span class="desc__span">{{default_trans($organizationId.'/dashboard.ui_elements_messages.missed_chats', __('default/dashboard.ui_elements_messages.missed_chats'))}}</span>
            </div>
             <div class="data-wrappers red-icon ">
                <i class="fa fa-paper-plane" aria-hidden="true"></i>
                <div class="data-wrappers__count"><span id="chat_transfer4">{{!empty($data->emailSent) ? $data->emailSent:0}}</span></div>
                <span class="desc__span">{{default_trans($organizationId.'/dashboard.ui_elements_messages.email_sent', __('default/dashboard.ui_elements_messages.email_sent'))}}</span>
            </div>
        </div>
       <div>
       <div class="data-wrappers pink-icon">
                <i class="fa fa-user-secret" aria-hidden="true"></i>
                <div class="data-wrappers__count"><span id ="chat-user">{{!empty($data->numberOfUniqueChats)? ($data->numberOfUniqueChats > $data->numberOfChats ? $data->numberOfChats : $data->numberOfUniqueChats):0}}</span></div>
                <span class="desc__span">{{default_trans($organizationId.'/dashboard.ui_elements_messages.no_of_unique_chats', __('default/dashboard.ui_elements_messages.no_of_unique_chats'))}}</span>
            </div>
            <div class="data-wrappers spark-green-icon">
                <i class="fa fa-mouse-pointer" aria-hidden="true"></i>
                <div class="data-wrappers__count"><span id="avg_interaction">{{$data->averageInteractions}}</span></div>
                <span class="desc__span">{{default_trans($organizationId.'/dashboard.ui_elements_messages.avg_interactions', __('default/dashboard.ui_elements_messages.avg_interactions'))}}</span>
            </div>
        </div>
        <div>
            <div class="data-wrappers light-orange-icon">
                <i class="fa fa-cog" aria-hidden="true"></i>
                <div class="data-wrappers__count"><span id="avg_first_resp">{{$data->avgFirstResponseTime}}</span></div>
                <span class="desc__span">{{default_trans($organizationId.'/dashboard.ui_elements_messages.avg_first_response_time', __('default/dashboard.ui_elements_messages.avg_first_response_time'))}}</span>
            </div>
            <div class="data-wrappers reddish-orange-icon">
                <i class="fa fa-cog" aria-hidden="true"></i>
                <div class="data-wrappers__count"><span id="avg_resp_time">{{$data->avgResponseTime}}</span></div>
                <span class="desc__span">{{default_trans($organizationId.'/dashboard.ui_elements_messages.avg_response_time', __('default/dashboard.ui_elements_messages.avg_response_time'))}}</span>
            </div>
        </div>
        <div>
            <div class="data-wrappers light-blue-icon">
                <i class="fa fa-cog" aria-hidden="true"></i>
                <div class="data-wrappers__count"><span id="avg_sess_time">{{$data->averageSession}}</span></div>
                <span class="desc__span">{{default_trans($organizationId.'/dashboard.ui_elements_messages.avg_session_time', __('default/dashboard.ui_elements_messages.avg_session_time'))}}</span>
            </div>
            <div class="data-wrappers pink-icon ">
                <i class="fa fa-comments" aria-hidden="true"></i>
                <div class="data-wrappers__count"><span id="chat_transfer2">{{$data->averageOnlineDuration}}</span></div>
                <span class="desc__span">{{default_trans($organizationId.'/dashboard.ui_elements_messages.avg_online_duration', __('default/dashboard.ui_elements_messages.avg_online_duration'))}}</span>
             </div>

        </div>
        <div>
            <div class="data-wrappers light-blue-icon ">
                <i class="fa fa-star" aria-hidden="true"></i>
                <div class="data-wrappers__count"><span id="feedback">{{$data->avgFeedBack}}</span></div>
                    <span class="desc__span">{{default_trans($organizationId.'/dashboard.ui_elements_messages.feedback', __('default/dashboard.ui_elements_messages.feedback'))}}</span>
            </div>
            <div class="data-wrappers spark-green-icon">
                <i class="fa fa fa-clock" aria-hidden="true"></i>
                <div class="data-wrappers__count"><span id="feedback">{{empty($data->chatsTimeout) ? 0: $data->chatsTimeout}}</span></div>
                    <span class="desc__span">{{default_trans($organizationId.'/dashboard.ui_elements_messages.chats_timeout', __('default/dashboard.ui_elements_messages.chats_timeout'))}}</span>
            </div>
        </div>
        @if($organizationWiseDataFlag)
                <div>
            <div class="data-wrappers red-icon">
                <i class="fa fa fa-clock" aria-hidden="true"></i>
                <div class="data-wrappers__count"><span id="feedback">{{empty($data->outSessionTimeouts) ? 0 : $data->outSessionTimeouts}}</span></div>
                    <span class="desc__span">{{default_trans($organizationId.'/dashboard.ui_elements_messages.out_session_timeout', __('default/dashboard.ui_elements_messages.out_session_timeout'))}}</span>
            </div>
        <div class="data-wrappers light-blue-icon ">
                <i class="fa fa-cog" aria-hidden="true"></i>
                <div class="data-wrappers__count"><span id="feedback">{{$data->avgFirstResponseTimeToVisitor ?? 0}}</span></div>
                <span class="desc__span">{{default_trans($organizationId.'/dashboard.ui_elements_messages.avg_waiting_time', __('default/dashboard.ui_elements_messages.avg_waiting_time'))}}</span>
            </div>
        </div>

        <div>
            <div class="data-wrappers light-blue-icon ">
                <i class="fa fa-comments" aria-hidden="true"></i>
                <div class="data-wrappers__count"><span id="feedback">{{$data->countOfflineQuery}}</span></div>
                <span class="desc__span">{{default_trans($organizationId.'/dashboard.ui_elements_messages.offline_queries_count', __('default/dashboard.ui_elements_messages.offline_queries_count'))}}</span>
            </div>
            <div class="data-wrappers red-icon">
                <i class="fa fa-star" aria-hidden="true"></i>
                <div class="data-wrappers__count"><span id="feedback">{{empty($data->outSessionMissedChats) ? 0 : $data->outSessionMissedChats}}</span></div>
                <span class="desc__span">{{default_trans($organizationId.'/dashboard.ui_elements_messages.out_session_missed_chat', __('default/dashboard.ui_elements_messages.out_session_missed_chat'))}}</span>
            </div>
        </div>
    @endif
    <div>
        <div class="data-wrappers red-icon ">
            <i class="fa fa-user-times" aria-hidden="true"></i>
            <div class="data-wrappers__count"><span id="feedback">{{empty($data->chatsClosedByVisitor) ? 0 : $data->chatsClosedByVisitor}}</span></div>
                <span class="desc__span">{{default_trans($organizationId.'/dashboard.ui_elements_messages.chat_closed_by_visitor', __('default/dashboard.ui_elements_messages.chat_closed_by_visitor'))}}</span>
        </div>
    </div>
    </div>
    <div class="dprev">
        <i class="fa fa-chevron-left"></i>
    </div>
    <div class="dnext">
        <i class="fa fa-chevron-right"></i>
    </div>
</div>
