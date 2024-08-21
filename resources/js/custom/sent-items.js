var nextPage = 1;
var result = true;
$(document).ready(function () {

	activeSideBar();
	var ps1 = new PerfectScrollbar('#listing', {
		minScrollbarLength: 40
	});

	var ps2 = new PerfectScrollbar('#email-body', {});

	var element = $("#recipient-listing li:first-child");
	element.addClass('active');
	var firstEmailId = element.attr('data-id');
	$("#recipient-listing li:first-child").on('click', getEmail(firstEmailId, element));
	$("#email-cc-bcc").hide();

	var page = 1;
	$("#listing").on(' ps-y-reach-end', function () {
		getRecipients(++page);
	});
});

/**
 * Function to get email by particular Id.
 * 
 * @param emailId
 * @returns
 */
function getEmail(emailId, element) {
	$("#recipient-listing li").removeClass('active');
	if ($(element).is('li')) {
		$(element).addClass('active');
	}

	$("#email-cc-parent").hide();
	$("#email-bcc-parent").hide();
	$("#email-attachment").hide();

	var element = $("li.active");
	if (element.is('li:last-child')) {
		$("#next-email").addClass('inactive');
	}
	else if(element.is('li:first-child')){
		$("#prev-email").addClass('inactive');
	}
	else{
		$("#prev-email,#next-email").removeClass('inactive');
	}

	$.ajax({
		dataType: 'json',
		type: 'GET',
		url: '/email/get/' + emailId,
		success: function (response) {
			if (response.status === true) {
				getEmailSuccess(response, emailId);
			}
		}
	});
}

/**
 * Function to get Email Success.
 * 
 * @param response
 * @returns
 */
function getEmailSuccess(response, emailId) {
	$("#email-body").html('');
	$(".email__fcontent__attachments").remove();
	$("#email-subject").html(response.data.subject);
	$("#email-subject").attr('title', response.data.subject);
	$("#email-to").html(response.data.to);
	$("#email-time").html(response.data.sent_date);
	//$("#email-body").html(response.data.body);
	$("#email-initail").html(response.data.initails);
	$("#email-initail").css("background-color", response.data.color_code);
	var ccEmail = response.data.cc;
	var bccEmail = response.data.bcc;
	var attachment = response.data.attachments;
	showHideCcBcc(ccEmail, bccEmail);
	showHideArrows(ccEmail, bccEmail);

	if (attachment.length != 0) {
		$("#email-attachment").show();
		html = '';
		$.each(attachment, function (key, value) {
			var fileUnit = (value.attachment_unit == null)?'mB': value.attachment_unit;
			html = '<div class="email__fcontent__attachments" id="email-attachment">' +
				'<a id= "attachment-download" href="/email/download/' + value.attachment_id + '">' +
				'<i class="' + value.attachment_class + '"></i>' +
				'<div class="mail__fcontent__attachments__container">' +
				'<span class="email__fcontent__attachments__name" id="email-attachment-name">' + value.attachment_file_name + '</span>' +
				'<span class="email__fcontent__attachments__size"><span id="email-attachment-size">' + value.attachment_size + '</span>'+ fileUnit+'</span>' +
				'</div></a></div>';
			
			$("#email-body").append(html);
		});
		$("#email-body").append('<div>'+response.data.body+'</div>');
	} else {
		$("#email-body").html('<div>'+response.data.body+'</div>');
	}
}

/**
 * Function to search email.
 * 
 * @returns
 */
function searchEmail() {
	resetScroll();
	var keyword = {
		'keyword': $("#email-search").val(),
		'parameter': $("input[name='email-filter']:checked").val()
	};

	$.ajax({
		dataType: 'json',
		type: 'GET',
		url: '/email/search',
		data: keyword,
		success: function (response) {

			$("#recipient-listing").empty();
			$("#recipient-listing").html(response.html);
			$("#email-count").html(response.totalItems);
			if (response.isDataEmpty == true) {
				$(".List__filter").hide();
			} else {
				$(".List__filter").show();
			}

			if (response.totalItems != 0) {
				$(".no__records").hide();
				$(".email__sent__detail").show();
				$("#recipient-listing li:first-child").addClass('active');
				getEmail(response.firstEmailId, $("#recipient-listing li:first-child"));
			} else {
				$(".no__records").show();
				$(".email__sent__detail").hide();
			}

		}
	});
}

/**
 * Function to filter email.
 * 
 * @returns
 */
function filterEmail() {
	resetScroll();
	var keyword = {
		'keyword': $("#email-search").val(),
		'parameter': $("input[name='email-filter']:checked").val()
	};

	$.ajax({
		dataType: 'json',
		type: 'GET',
		url: '/email/search',
		data: keyword,
		success: function (response) {
			$("#recipient-listing").empty();
			$("#recipient-listing").html(response.html);
			$("#email-count").html(response.totalItems);

			if (response.totalItems != 0) {
				$("#recipient-listing li:first-child").addClass('active');
				getEmail(response.firstEmailId, $("#recipient-listing li:first-child"));
			} else {
				$(".no__records").show();
				$(".email__sent__detail").hide();
			}
		}
	});
}

/**
 * Function to show hide cc and bcc.
 * 
 * @param ccEmail
 * @param bccEmail
 * @returns
 */
function showHideCcBcc(ccEmail, bccEmail) {
	if ((ccEmail != undefined && ccEmail !== null) ||
		(bccEmail != undefined && bccEmail !== null)) {
		$("#upper-arrows").removeClass('hidden');
		$("#email-cc-bcc").show();
		if (ccEmail != undefined && ccEmail !== null) {
			$("#email-cc-parent").show();
			$("#email-cc").html(ccEmail);
			$("#email-cc-parent").removeClass('hidden');
		}
		if (bccEmail != undefined && bccEmail !== null) {
			$("#email-bcc-parent").show();
			$("#email-bcc").html(bccEmail);
			$("#email-bcc-parent").removeClass('hidden');
		}
	}
}

/**
 * function to show hidd arrows of cc and bcc.
 * 
 * @param ccEmail
 * @param bccEmail
 * @returns
 */
function showHideArrows(ccEmail, bccEmail) {
	if (ccEmail != undefined && bccEmail == undefined) {
		$("#email-cc").append('<i id="down-arrows" class="fas fa-angle-up detail_cc_close"></i>');
	} else if ((ccEmail == undefined && bccEmail != undefined) ||
		(ccEmail != undefined && bccEmail != undefined)) {
		$("#email-bcc").append('<i id="down-arrows" class="fas fa-angle-up detail_cc_close"></i>');
	}
}

/**
 * Function to get recipients.
 * 
 * @param page
 * @returns
 */
function getRecipients(page, callback) {
	var isPageLeft = true;
	$.ajax({
		dataType: 'json',
		type: 'GET',
		url: '/email/recipients?page=' + page,
		success: function (response) {
			if (response.status === true) {
				$("#recipient-listing").append(response.html);
			} else {
				if (callback !== undefined) {
					callback();
				}
				return false;
			}
		}
	});

}

$("#prev-email").on('click', function (event) {
	var activeElement = $("#recipient-listing li.active").is(":first-child");
	if (activeElement !== true) {
		$("#next-email").removeClass('inactive');
		if ($(this).hasClass('inactive') !== true) {
			var element = $("li.active").prev('li');
			var emailId = element.attr('data-id');
			var id = element.attr('id');
			window.location.hash = id;
			getEmail(emailId, element);
			if (element.is('li:first-child')) {
				$("#prev-email").addClass('inactive');
			}
		}
	}
});


$("#next-email").on('click', function (event) {
	var activeElement = $("#recipient-listing li.active").is(":last-child");
	if (activeElement !== true) {
		$("#prev-email").removeClass('inactive');
		if ($(this).hasClass('inactive') !== true) {
			var element = $("li.active").next('li');
			var id = element.attr('id');
			window.location.hash = id;
			if (element.is('li:last-child')) {
				$("#next-email").addClass('inactive');
			}
			var emailId = element.attr('data-id');
			getEmail(emailId, element);
		}
	}
});

function resetScroll() {
	const container = document.querySelector('#listing');
	container.scrollTop = 0;
}
