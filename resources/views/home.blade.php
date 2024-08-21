
@extends('layouts.app')
<style>
    .client {
        text-align:right;
        background-color: blue;
        color: white;
        width: 50%;
        float: right;
        padding: 10px;
        margin-top: 5px;
        margin-right: 5px;
    }
    .surbo {
        text-align:left;
        background-color: green;
        color: yellow;
        width: 50%;
        float: left;
        padding: 10px;
        margin-top: 5px;
        margin-left: 5px;
    }
    .panel{
        min-height: 300px;
        border:1px solid gray;
        background: #efefef;
        height: 300px;
        overflow: scroll;
    }
    .internal {
    text-align: center;
    background-color: red;
    color: white;
    width: 90%;
    float: right;
    padding: 9px;
    margin-top: 5px;
    margin-left: 32px;
    }
    
     /* Chat containers */
.subscribers {
    border: 2px solid #dedede;
    background-color: #f1f1f1;
    border-radius: 5px;
    padding: 10px;
    margin: 10px 0;
    cursor: pointer;
}

/* Darker chat container */
.darker {
    border-color: #ccc;
    background-color: #ddd;
    
}
.active {
    border: 2px solid white;
    background-color: blue;
    color:white;
    font-weight: bold;
    
}

/* Clear floats */
.subscribers::after {
    content: "";
    clear: both;
    display: table;
}

/* Style images */
.subscribers img {
    float: left;
    max-width: 60px;
    width: 100%;
    margin-right: 20px;
    border-radius: 50%;
}

/* Style the right image */
.subscribers img.right {
    float: right;
    margin-left: 20px;
    margin-right:0;
}

/* Style time text */
.time-right {
    float: right;
    color: #aaa;
}

/* Style time text */
.time-left {
    float: left;
    color: #999;
} 
.history{
    border: 1px solid yellow;
width: 155px;
text-align: center;
background-color: green;
color: yellow;
cursor: pointer
}
</style>
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Dashboard</div>
                
                <div class="card-body">
                    <div style='float:right;clear:both;'>
                        <span>
                            <select id='agents'>
                                <option value=''>--Available Agents--</option>
                                <?php foreach(App\Models\UserOnlineStatus::with('user')->online()->get() as $onlineUser): ?>
                                <option value='{{$onlineUser->user->id}}'>{{$onlineUser->user->name}}</option>
                                <?php endforeach;?>
                            </select>
                        </span>
                    </div>
                            
                    <table border="0" class="col-md-12">
                        <tr>
                            <td class="col-md-4">
                                <div id="clients" class="panel" style="min-height:100%; margin-top:-70px;">
<!--                                    <div class="subscribers darker">
                                        <p>Dummy</p>
                                        <span class="time-right">11:02</span>
                                    </div>-->
                                </div>
                            </td>
                            <td class="col-md-8">
                                
                                <div id="chat" class="panel col-md-12">
                                    
                                </div>
                                <br>
                                <form method="post" action="" id="frmChat">
                                    <input type="text" size="50" id="text" value="" placeholder="Write message..."/>
                                    <input type="submit" value="Send" id="send">
                                </form>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
    
    function subscribe()
    {
        $('.subscribers').unbind('click').bind('click', function(){
            
            $('#chat').html('').append('<div class="history">Load History...</div>');
            
            var channel = $(this).attr('rel');
            
            $('#clients').find('.active').removeClass('active').addClass('darker');
            
            $(this).removeClass('darker').addClass('active');
            
            $.get('/api/v1/chat/messages', {channel : channel}, function(response){
                $.each(response.data, function(key, messageBody){
                    console.log(messageBody);
                    console.log(messageBody.id);
                    if(messageBody.recipient == 'subscriber')
                    {
                        $('#chat').append('<p class="client">' + messageBody.message.text + '</p>');
                    }
                    if(messageBody.recipient == 'agent')
                    {
                        $('#chat').append('<p class="surbo">' + messageBody.message.text + '</p>');
                    }
                });
            });
            
            window.Echo.channel(channel)
                .listen('MessageArrived', (e) => {
                    console.log(e);
                    if(e.recipient == 'subscriber')
                    {
                        $('#chat').append('<p class="client">' + e.message.text + '</p>');
                    }
                    if(e.recipient == 'agent')
                    {
                        $('#chat').append('<p class="surbo">' + e.message.text + '</p>');
                    }
                });
            
            window.Echo.private('supervision-' + channel)
                .listen('SupervisorSuggested', (e) => {
                    console.log(e);
                    $('#chat').append('<p class="internal">' + e.message.text + '</p>');
                });
         });
    }
    window.Echo.private("subscribers-<?=auth()->user()->id;?>")
    .listen('NewSubscription', (e) => {
        console.log(e);
        $('#clients').append('<div class="subscribers darker" rel="'+e.channel+'"><p>'+e.subscriber_info.name+'</p></div>')
        subscribe();
    });

function getSubscribers(agent)
{
    $('#clients').html('');
    $.get('/api/v1/chat/subscribers', {agent: agent}, function(response){
        $.each(response.data, function(key, subscriber){
           console.log(subscriber); 
           $('#clients').append('<div class="subscribers darker" rel="'+subscriber.channel+'"><p>'+subscriber.subscriber_info.name+'</p></div>')
           subscribe();
        });
    }, 'json');
}

$(document).ready(function(){
    $('#frmChat').submit(function(e){
       e.preventDefault();
        var text = $('#text').val();
        $('#chat').append('<p class="surbo">' + text + '</p>');
        $('#text').val('').focus();
        $.post('/chat', {'message' : text, 'type' : 'agent'}, function(){

        });
    });
    
    getSubscribers(<?=auth()->user()->id;?>);
    
    $('#agents').change(function(){
        getSubscribers($(this).val());
    });
    
});
</script>
@endsection