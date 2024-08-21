$(document).on('click', '.popup-btn', function () {
    $('#popup__wrapper').focus();
    // $('body').addClass('overflow-hidden');
    $id = '#' + $(this).attr('id');
    $($id + '__popup').addClass('show');
});

function openPopup($id) {
    $('#popup__wrapper').focus();
    $('body').addClass('overflow-hidden');
    $('#' + $id + '__popup').addClass('show');
}

$(document).on('click', '.header__user', function (e) {
    e.stopPropagation();
    $('.header__card').toggleClass('show');
});

$(document).on('keypress', '#login-email', function (e) {
    if (e.keyCode === 32) {
        e.preventDefault();
    }
});


$(document).on('click', 'body', function () {
    $('.header__card').removeClass('show');
    $('.sorting__dropdown').hide();
})

// close the modal
$('#cancel,.close-btn').on('click', () => {
    $('.popup').add();
    resetFormErrors();
    $('body').removeClass('overflow-hidden');
});

// close for Esc key
$(document).on('keydown', function (e) {
    if (e.keyCode === 27) { // ESC
        $('.popup').removeClass('show');
        resetFormErrors();
        $('body').removeClass('overflow-hidden');
        $('.add-permission-input').hide();
        $('.add-permission-tags').show();
        $('#add_tag').val('');
    }
});


$(document).on('click', '#cancel,.close-btn', function (e) {
    e.stopPropagation();
    $('.popup').removeClass('show');
    $('body').removeClass('overflow-hidden');
    $('.header__card').removeClass('show');
});

function showNotification(type, text) {
    $('.notifier').addClass('notifier-show');
    $('.notifier__icon').html('');

    if (type === "success") {
        $('.notifier__icon').removeClass('notifier__warning').addClass('notifier__success').html('<i class="fas fa-check"></i>');
    } else if (type === "warning") {
        $('.notifier__icon').removeClass('notifier__success').addClass('notifier__warning').html('<i class="fas fa-times"></i>');
    } else if (type === "info") {
        $('.notifier__icon').addClass('notifier__info').html('<i class="fas fa-exclamation"></i>');
    }

    $('.notifier__text').text(text);

    setTimeout(function () {
        $('.notifier').removeClass('notifier-show');
    }, 1500);
}

$(document).on('click', '.header__card', function (e) {
    e.stopPropagation();
});

$(document).on(
    'click', '.table tr th, .head_sorting .select-custom .dropdown .list, .paginate_button'
    , function () {
        $('html, body').animate({
            scrollTop: $(".dataTables_wrapper").offset().top
        }, 'slow');
    });



$(document).ajaxError(function (event, jqxhr, settings, exception) {

    if (exception == 'Unauthorized') {
        // Prompt user if they'd like to be redirected to the login page
        // bootbox.confirm("Your session has expired. Would you like to be redirected to the login page?", function(result) {
        //     if (result) {
        window.location = '/login';
        // }
        // });
    }
});

$(document).ready(function () {
    activeSideBar();
    setTimeout(() => {
        $('.loader').fadeOut(1500);
    }, 500);

    $(document).on('mousedown', '.popup', function (event) {
        if ($('.popup').is(event.target)) {
            $('.popup').removeClass('show');
            resetFormErrors();
            $('body').removeClass('overflow-hidden');
            $('.add-permission-input').hide();
            $('.add-permission-tags').show();
            $('#add_tag').val('');
        }
    });

    $(document).on('click', '.sorting a', function (e) {
        e.stopPropagation();
        $('.sorting__dropdown').slideToggle();
    });
    $(document).on('click', '.detail_cc', function (e) {
        // e.stopPropagation();
        $(this).hide();
        $('.email_cc_bcc').show();
    });
    $(document).on('click', '.detail_cc_close', function (e) {
        e.stopPropagation();
        $('.detail_cc').show();
        $('.email_cc_bcc').hide();
    });


    //    var ps1 = new PerfectScrollbar('.email__sent__ul',{
    //        minScrollbarLength:40
    //    });

    $('#myselect').val('Group 3');

    $(document).on('click', '.add-permission-tags', function () {
        $(this).hide();
        $('.add-permission-input').fadeIn();
    });

    $(document).on('click', '.close-btn-tags', function () {
        $('.popup').removeClass('show');
        $('.add-permission-input').hide();
        $('.add-permission-tags').show();
        $('#add_tag').val('');
    });


    $(document).on('click', '.login__inputs', function (event) {
        event.stopPropagation();
        $id = $(this).attr('id');
        $('.login__inputs').removeClass('focused');
        $('#' + $id).addClass('focused');
    });

    $(document).on('change', '.master-switch', function (event) {
        $id = $(this).attr('id');
        if (!$(this).find('input').prop('checked')) {
            $('.' + $id + '-switch').addClass('disabled').find('input').prop('checked', false);
        } else {
            $('.' + $id + '-switch').removeClass('disabled');
        }
    })

    $(document).on('blur', '#login-email', function () {
        if ($(this).is(":invalid") || $(this).val() === '') {
            $('#email-isvalid')
                .removeClass('icon--check')
                .addClass('icon--cross')
                .css('visibility', 'visible')
                .find('i')
                .removeClass('fa-check')
                .addClass('fa-times')
        } else {
            $('#email-isvalid')
                .removeClass('icon--cross')
                .addClass('icon--check')
                .css('visibility', 'visible')
                .find('i')
                .removeClass('fa-times')
                .addClass('fa-check')
        }
    })

    $(document).on('change', '.input-file', function (event) {
        var filename = event.target.files[0].name;
        var fileExtension = filename.substring(filename.lastIndexOf('.') + 1).toLowerCase();
        const validExtensions = ["jpg", "jpeg", "gif", "png", "bmp"];
        if (validExtensions.indexOf(fileExtension) > -1) {
            $(this).prev('label').addClass('file-uploader-active').find('.file-name').text(filename);
        } else {
            $(this).prev('label').addClass('file-uploader-active').find('.file-name').addClass('warning-text').text('Wrong file format');
            $(this).prev('label').next('input').val('');
        }
        $(this).prev('label').addClass('file-uploader-active').find('.file-delete').css('display', 'inline-flex');
        // console.log($(this).prev('label').find('.file-name'));
    });

    $(document).on('click', '.file-delete', function (event) {
        event.preventDefault();
        $(this).parent().next().val('');
        $(this).parent().removeClass('file-uploader-active').find('.file-name').removeClass('warning-text').text('Drag and Upload Image');
        $(this).hide();
    });

    $(document).on('click', '.copyClipboard', function (event) {
        $(this).prev().select();
        document.execCommand("copy");
        $(this).children('span').text('Copied');
    });

    $(document).on('mouseout', '.copyClipboard', function (event) {
        setTimeout(() => {
            $(this).children('span').text('Copy');
        })
    });
});

/**
 * Make side bar active according to url.
 * 
 * @returns
 */
function activeSideBar() {
    var url = window.location.pathname;
    var url = url.substring(1);
    $("ul").find('[data-url="' + url + '"]').addClass('menubar__items-active');
    $("ul").find('li').not('li[data-url="' + url + '"]').removeClass('menubar__items-active');
}

/*
 * 
 * @todo remove after adding in common
 */
function resetFormErrors() {
    $('.popup__content--wrap').removeClass('has-error');
    $('.popup__content--wrap').find('.help-block-white').remove();
    $('.popup__content--wrap').find('.help-block').remove();
    $('.popup__permissions--addtags').next('.warning-text').empty();
}


// $('#dashboard-1').dcalendarpicker();
// $('#dashboard-2').dcalendarpicker();
// $('#calendar-demo').dcalendar();

$(document).ready(function () {

    $(document).on('focus', ':input[type=text],:input[type=email]', function () {
        $(this).attr('autocomplete', 'off');
    });
    $(document).on('focus', ':input[type=password]', function () {
        $(this).attr('autocomplete', 'new-password');
    });

    // $(window).on("beforeunload", function() {
    //     // alert(document.activeElement.href);
    //     console.log(document.activeElement)
    //     return false;
    //     return confirm("Do you really want to close?")
    //
    // })
})
//
// window.addEventListener("beforeunload", function (e){
//     var message = 'Important: Please click on \'Save\' button to leave this page.';
//     if (typeof event == 'undefined') {
//         event = window.event;
//     }
//     if (event) {
//         event.returnValue = message;
//     }
//     return message;
// });







