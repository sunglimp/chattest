@php
$languageClass = '';
if (Auth::user()->role_id !=1 && Auth::user()->language =="ar"){
    $languageClass = 'arabic';
}
@endphp

<header class="main__header" >
    <meta name="csrf-token" content="{{ csrf_token() }}">
   <div class="header__text">
       @yield('heading')
   </div>
   	@if((Route::current()->getName() == 'dashboard' &&  Gate::allows('not-admins')) || (Route::current()->getName() == '' &&  Gate::allows('not-admins')))
        <div class="dashboard__right">
            <div class="dashboard__right__timer timer">
                    {{default_trans($organizationId.'/dashboard.ui_elements_messages.online_duration', __('default/dashboard.ui_elements_messages.online_duration'))}} : 
                    <span class="hour">{{$data['onlineDuration']['hours']}}</span>
                    <span>: </span>
                    <span class="minute">{{$data['onlineDuration']['min']}}</span>
                    <span>: </span>
                    <span class="second">{{$data['onlineDuration']['seconds']}}</span>
                    <span>| </span>
                </div>
     @endif
   <div class="header__user {{ $languageClass }}">
       <img src="{{Auth::user()->image}}" alt="" class="header__user-img">
       <span class="header__user-name end_caret">{{Auth::user()->name}}</span>
   </div>
   <div class="header__card {{ $languageClass }}">
       <a class="header__card-container">
           <img src="{{Auth::user()->image}}" alt="image" class="header__card-img">
       </a>
       <div class="header__card-details">
           <span class="header__card-name">{{Auth::user()->name}}</span>
           <span class="header__card-email">{{Auth::user()->email}}</span>
       </div>
       @if(Session::has('sneak_in'))
            <div class="header__card-details">
           			<a class="sneak-user" onclick="deleteRedisKey();@cannot('all-admin')preLogout('switchBack');@endcannot  @can('all-admin')event.preventDefault(); switchBack();@endcan">Switch back</a>
            </div>
        @endif
       <a  onclick="deleteRedisKey();@cannot('all-admin')preLogout();@endcannot @can('all-admin')event.preventDefault(); document.getElementById('logout-form').submit();@endcan"
           class="header__card-logout">
           LOGOUT</a>
       <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
       </form>
   </div>
 	@if((Route::current()->getName() == 'dashboard' &&  Gate::allows('not-admins')) || (Route::current()->getName() == '' &&  Gate::allows('not-admins')))
   </div>
   @endif
</header>

<div class="popup popup__container" id="offline__popup" onclick="hideWarning()">
 <div class="popup__wrapper popup__small" onclick="event.stopPropagation()">
     <a class="close-btn {{ $languageClass }}" onclick="hideWarning()"><i class="fas fa-times"></i></a>
     <div class="popup__content">
         <div class="popup__content">
             <span>{{  default_trans($organizationId.'/chat.ui_elements_messages.force_logout_proceed_confirmation', __('default/chat.ui_elements_messages.force_logout_proceed_confirmation'))}} </span>
         </div>
         <div class="buttons__all">
         	<input type="hidden" id="is-sneak" value="">
         		<button class="custom-button custom-button-primary" onclick="logout()">{{  default_trans($organizationId.'/chat.ui_elements_messages.yes', __('default/chat.ui_elements_messages.yes'))}} </button>
             	<button class="custom-button" onclick="hideWarning()">{{  default_trans($organizationId.'/chat.ui_elements_messages.no', __('default/chat.ui_elements_messages.no'))}} </button>
         </div>
     </div>
 </div>
</div>
<script>
    
    toggleUserInfo();
    activeSideBar();
    const agentId ='{{Auth::user()->id}}';
    const accessToken = '{{Auth::user()->api_token}}';
    const hostUrl = window['APP_URL'];
    const defaultOptions = {
       method: 'put',
       headers: {
           'Authorization': 'Bearer ' + accessToken
       },
    };
	
    function preLogout(action){
       let url = `${hostUrl}/api/v1/agents/${agentId}/offline/1`;
       document.querySelector('.header__card').classList.remove('show');

       fetch(url,defaultOptions)
           .then(response => response.json())
           .then(data => {
              if(!data.status){
                if (action != undefined) {
                	document.getElementById("is-sneak").value = true;
                }
                document.getElementById('offline__popup').classList.add('show');
              }
              else{
                  if (action == undefined) {
				      logout();
                  } else {
                	  window.location.href = hostUrl + "/user/return-sneak-in";
                  }
              }
           });
   }
	
    function logout(){
       let url = `${hostUrl}/api/v1/agents/${agentId}/offline/0/1`;
       fetch(url,defaultOptions)
           .then(response => response.json())
           .then(data => {
               if(data.status){
            	   if (document.getElementById("is-sneak").value != "true") {
                	   document.getElementById('logout-form').submit();
		           } else if (document.getElementById("is-sneak").value == "true") {
		        	   window.location.href = hostUrl + "/user/return-sneak-in";
			       }
               }
           });
   }
   
   function deleteRedisKey(){
        const agentId ='{{Auth::user()->id}}';
       let url = `${hostUrl}/api/v1/agents/remove-key/${agentId}`;
       fetch(url,defaultOptions)
           .then(response => { 
               
              if(response.status == '405' || response.status == '302')
              {
                  location.reload();
              }
               response.json() 
            })    
            .then(data => {
               if(data.status){
                  
               }
           });
   }

   function hideWarning(){
       document.getElementById('offline__popup').classList.remove('show');
       return false;
   }

   /**
    * Make side bar active according to url.
    * 
    * @returns
    */
   function activeSideBar() {
       var url = window.location.pathname;
       var url = url.substring(1);
       var ultag = document.querySelectorAll("ul.menubar li");
       for(var i=0;i<ultag.length;i++) {
			var dataUrl = (ultag[i].getAttribute('data-url'));
			if (dataUrl == url) {
				ultag[i].classList.add('menubar__items-active');
			}
        }
   }

   function toggleUserInfo(){
    document.getElementsByClassName("header__user")[0].addEventListener("click", function (e) {
        e.stopPropagation();
        var checkShowClass = document.getElementsByClassName("show")
        if (checkShowClass.length) {
            document.getElementsByClassName("header__card")[0].classList.remove('show');
        } else
        document.getElementsByClassName("header__card")[0].classList.add('show');
    });
   }

   function switchBack(){
      window.location.href = hostUrl + "/user/return-sneak-in";
   }
   
   document.querySelector('body').addEventListener('click',()=>{
    document.querySelector('.header__card').classList.remove('show');
   });

</script>
