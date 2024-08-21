@php
$languageClass = '';
if (Auth::user()->language === 'ar') {
    $languageClass = 'arabic';
}
$width = 'wid-18';
if ($data['toShowChatCount']) {
    $width = 'wid-28';
}
@endphp

<div class="dashboard__availability">
    <div class="dashboard__availability__top">
        <div class="dashboard__availability__top__head">{{default_trans($organizationId.'/dashboard.ui_elements_messages.availability', __('default/dashboard.ui_elements_messages.availability'))}}</div>
        <div class="dashboard__availability__top__filter {{ $width }}">
            <div class="stv-radio-tabs-wrapper">
                <input type="radio" class="stv-radio-tab" name="availability" id="Time" checked="checked" onChange="getOnlineDuration()"/>
                <label for="Time">{{default_trans($organizationId.'/dashboard.ui_elements_messages.time', __('default/dashboard.ui_elements_messages.time'))}}</label>
                <input type="radio" class="stv-radio-tab" name="availability" id="Chats" onChange="getChatCount()" />
                <label for="Chats">{{default_trans($organizationId.'/dashboard.ui_elements_messages.chats', __('default/dashboard.ui_elements_messages.chats'))}}</label>
               @if($data['toShowChatCount'])
                <input type="radio" class="stv-radio-tab" name="availability" id="AwaitingChats" onChange="getAwaitingChatCount()" />
                <label for="AwaitingChats">{{default_trans($organizationId.'/dashboard.ui_elements_messages.awaiting', __('default/dashboard.ui_elements_messages.awaiting'))}}</label>
                <input type="radio" class="stv-radio-tab" name="availability" id="ActiveChats" onChange="getActiveChatCount()" />
                <label for="ActiveChats">{{default_trans($organizationId.'/dashboard.ui_elements_messages.active', __('default/dashboard.ui_elements_messages.active'))}}</label>
                @endif
            </div>
        </div>
    </div>
    <div class="width100" >
        <div class="dashboard__table">
          <table class="table table-sorting {{ $languageClass }}" id="availability" >
                <thead>
                    <tr>
                        <th style="width: 20%">{{default_trans($organizationId.'/dashboard.ui_elements_messages.particulars', __('default/dashboard.ui_elements_messages.particulars'))}}</th>
                        @foreach($data['dates'] as $value)
                        	<th style="width: 10%">{{$value}}</th>
                       	@endforeach
                        <th style="width: 10%">{{default_trans($organizationId.'/dashboard.ui_elements_messages.total', __('default/dashboard.ui_elements_messages.total'))}}</th>
                    </tr>
                </thead>
                <tbody>
                	 @foreach($data['onlineData'] as $chats)
                	 	<tr>
                	 		<td>{{$chats['name']}}</td>
                	 		@foreach($chats['count'] as $count)
                	 				<td>{{convert_average_time($count, true)}}</td>
                	 			
                	 		@endforeach 
                	 		<td>{{convert_average_time($chats['sum'], true)}}</td>
                	 	</tr>
                	 @endforeach
                </tbody>
            </table>
            
            
             <table class="table table-sorting" id="chat" style="display:none;">
                <thead>
                    <tr>
                        <th style="width: 20%">{{default_trans($organizationId.'/dashboard.ui_elements_messages.particulars', __('default/dashboard.ui_elements_messages.particulars'))}}</th>
                        @foreach($data['dates'] as $value)
                        	<th style="width: 10%">{{$value}}</th>
                       	@endforeach
                        <th style="width: 10%">{{default_trans($organizationId.'/dashboard.ui_elements_messages.total', __('default/dashboard.ui_elements_messages.total'))}}</th>
                    </tr>
                </thead>
                <tbody>
                	 @foreach($data['chatData'] as $chats)
                	
                	 	<tr>
                	 		<td>{{$chats['name']}}</td>
                	 		@foreach($chats['count'] as $count)
                	 				<td>{{$count}}</td>
                	 			
                	 		@endforeach 
                	 		<td>{{$chats['sum']}}</td>
                	 	</tr>
                	 @endforeach
                </tbody>
            </table>
            <table class="table table-sorting" id="awaitingchat" style="display:none;">
                <thead>
                    <tr>
                        <th style="width: 20%">{{default_trans($organizationId.'/dashboard.ui_elements_messages.particulars', __('default/dashboard.ui_elements_messages.particulars'))}}</th>
                        @foreach($data['dates'] as $value)
                        	<th style="width: 10%">{{$value}}</th>
                       	@endforeach
                        
                    </tr>
                </thead>
                <tbody>
                	 @foreach($data['awaitingChatData'] as $chats)
                	
                	 	<tr>
                	 		<td>{{$chats['name']}}</td>
                	 		<td>{{$chats['count']}}</td>
                	 	</tr>
                	 @endforeach
                </tbody>
            </table>
            <table class="table table-sorting" id="activechat" style="display:none;">
                <thead>
                    <tr>
                        <th style="width: 20%">{{default_trans($organizationId.'/dashboard.ui_elements_messages.particulars', __('default/dashboard.ui_elements_messages.particulars'))}}</th>
                        @foreach($data['dates'] as $value)
                        	<th style="width: 10%">{{$value}}</th>
                       	@endforeach
                        
                    </tr>
                </thead>
                <tbody>
                	 @foreach($data['activeChatData'] as $chats)
                	 	<tr>
                	 		<td>{{$chats['name']}}</td>
                	 		<td>{{$chats['count']}}</td>
                	 	</tr>
                	 @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
