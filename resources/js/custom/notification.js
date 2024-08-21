$(document).ready(function () {

    $(document).on('click', '#submit-notification-setting-btn', function (event) {
        notificationSetting();
    });

});
function notificationSetting() {
	var notificationEvents = [];
   //console.log($('.notification-event:checkbox:checked').val());
   $('.notification-event:checkbox:checked').each(function () {
	     notificationEvents.push($(this).val());
	});
   var notificationData = {
		   'notificationEvents' : notificationEvents,
		   'organizationId'     : $("#organization_list").val(),
   };
   	$.ajax({
   		type : "post",
   		dataType: 'json',
   		url : '/notification/add',
   		data : notificationData
   	}).done(function(response) {
   		$('body').removeClass('overflow-hidden');
   		$('#notification__popup').removeClass('show');
   		showNotification('success', messages.SETTING_UPDATED);
   	}).fail(function(response) {
   	 if (response.status === 422) {
         var validation = $.parseJSON(response.responseText);
         errors = validation.errors;
         $.each(errors, function (field, message) {
        	    $(".warning-text").text(message);
         });
   	 	}
   	});
}

