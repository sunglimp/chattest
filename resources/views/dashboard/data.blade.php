<div class="main-container {{ $languageClass }}"  @if(Auth::user()->role_id !=1 && Auth::user()->language =="ar") dir="rtl" @endif>
    <div class="dashboard-filter">
        <form id="filter-agent-form" method="get" action={{url('dashboard')}}>
            <input type="hidden" name="organization_id" id="organization_id" value="{{$organization_id}}" />
            @if(!empty($data))<input type="hidden" id="agent-id" value="{{$data['agentIds']}}">@endif
            <div class="left-filter">
                @if(!empty($data))
                @if($data['isTeam'] == true)
                <div class="select-custom margin-right-1">
                    <select id="select-agents" class="select" name="agentIds">
                        @cannot('all-admin')
                        <option @if($data['teamData']['id'] == $data['agentIds']) selected @endif value='{{$data['teamData']['id']}}' >Self</option>
                        @endcannot
                        <option @if($data['agentIds'] == 'team' || $data['agentIds'] == 0) selected @endif value='team' >Team</option>
                        @if(count($data['teamData']['child']))
                        @foreach($data['teamData']['child'] as $k =>$v)
                        <option @if($v['id'] == $data['agentIds']) selected @endif value={{$v['id']}}>{{$v['name']}}</option>
                        @endforeach
                        @endif
                    </select>
                </div>
                @endif
                @endif
                <input type="text" name="date" id="dashboard-1" class="custom-input-calendar {{ $languageClass }}" placeholder="DD-MM-YYYY - DD-MM-YYYY" onkeydown="dateRangeKeyDown(event)">
                <button class="custom-button custom-button-green {{ $marginClass }}" >{{default_trans($organizationId.'/dashboard.ui_elements_messages.submit', __('default/dashboard.ui_elements_messages.submit'))}}</button>
                @if(!empty($data['isExportAllowed']) && $data['isExportAllowed'] == true)
                <a href={{url('dashboard/export?')}}  id='dashboard-export' class="custom-button custom-button-blue">
                    <i class="fa fa-download" aria-hidden="true"></i>
                </a>
                @endif
            </div>
        </form>
        <div class="right-filter {{ $languageClass }}">
            @if(!empty($data))
            <div class="stv-radio-tabs-wrapper">
                <input type="radio" class="stv-radio-tab select-days" name="filter" id="7days" value="7" {{$data['days']==7?"checked":"" }}  />
                <label for="7days" >7 {{default_trans($organizationId.'/dashboard.ui_elements_messages.days', __('default/dashboard.ui_elements_messages.days'))}} </label>
                <input type="radio" class="stv-radio-tab select-days" name="filter" id="15days" value="15" {{$data['days']==15?"checked":"" }} />
                <label for="15days">15 {{default_trans($organizationId.'/dashboard.ui_elements_messages.days', __('default/dashboard.ui_elements_messages.days'))}}</label>
                <input type="radio" class="stv-radio-tab select-days" name="filter" id="30days" value="30" {{$data['days']==30?"checked":"" }}  />
                <label for="30days">30 {{default_trans($organizationId.'/dashboard.ui_elements_messages.days', __('default/dashboard.ui_elements_messages.days'))}}</label>
            </div>
            @endif
        </div>
    </div>
    <div class="bottom__container margin-top-5">
        @if(!empty($data))
        @widget('slider')
        @widget('chatReport')
        @widget('chatTermination')
        @widget('chatInQueue')
        @widget('availability')
        @endif
    </div>
</div>

