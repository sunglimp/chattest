$(document).ready(function () {
    $(document).on('submit', '#add-organization-permission-1', function (event) {
        event.preventDefault();
    });
    $(document).on('click', '#add-organization-permission', function (event) {
        event.preventDefault();
        // Set vars.
        var form = $('#add-organization-permission-1');
        url = form.attr('action');
        var data = form.serialize();

        contentType = 'application/x-www-form-urlencoded; charset=UTF-8';
        // Request.
        $.ajax({
            type: "POST",
            url: url,
            data: data,
            dataType: 'json',
            contentType: contentType,
            processData: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        }).done(function (response) {
            if (response.status === 200) {
                showNotification('success', permission_js_var.successfully_updated);
                organizationPermission(response.organization_id);
            }
        }).fail(function (response) {
            // Check for errors.
            if (response.status === 422) {
                var validation = $.parseJSON(response.responseText);
                errors = validation.errors;
                $.each(errors, function (field, message) {
                    var formGroup = $('[name=' + field + ']', form).closest('.form-group');
                    formGroup.addClass('has-error').append('<p class="help-block">' + message + '</p>');
                });
            }
        });
    });

    //detail organization
    $(document).on('click', '#group-creation', function (event) {
        var organization_id = $("#organization_list").val();
        // Request.
        $.ajax({
            type: "post",
            url: "/group/create",
            data: { 'organization_id': organization_id },
            dataType: 'json',

        }).done(function (response) {
            if (response.status === true) {
                $("#add-group-popup").html(response.html);
                $('body').removeClass('overflow-hidden');

                openPopup("group-creation");
            }
        }).fail(function (response) {
            // Check for errors.
            if (response.status === 422) {
                var validation = $.parseJSON(response.responseText);
                errors = validation.errors;
                $.each(errors, function (field, message) {
                    var formGroup = $('[name=' + field + ']', form).closest('.popup__content--wrap');
                    formGroup.addClass('has-error').append('<p class="help-block" style="color:red">' + message + '</p>');
                });
            }
        });
    });

    //detail organization
    $(document).on('click', '#send-attachments', function (event) {
        var organization_id = $("#organization_list").val();
        // Request.
        $.ajax({
            type: "post",
            url: "/permission/show-setting",
            data: {
                'organization_id': organization_id,
                'permission': "SEND-ATTACHMENT",
                'popup': "permission.upload_attachment_popup"
            },
            dataType: 'json',

        }).done(function (response) {
            if (response.status === true) {
                $("#upload-attachment-popup").html(response.html);
                openPopup("send-attachments");
            }
        }).fail(function (response) {
            // Check for errors.
            if (response.status === 422) {
                var validation = $.parseJSON(response.responseText);
                errors = validation.errors;
                $.each(errors, function (field, message) {
                    var errorText = $('[name=' + field + ']', '.popup__container').closest('.popup__permissions--addtags').parent().next('.warning-text');
                    errorText.text(message);
                    // var formGroup = $('[name=' + field + ']', form).closest('.popup__content--wrap');
                    // formGroup.addClass('has-error').append('<p class="help-block" style="color:red">' + message + '</p>');
                });
            }
        });

    });

    $(document).on('click', '#send_attachment_button', function (event) {
        // resetFormErrors();
        var size = $("#send_attachment").val();
        var organization_id = $('#organization_list').val();

        // Request.
        $.ajax({
            type: "POST",
            url: "/permission/update-attachment-size",
            data: { 'size': size, 'organization_id': organization_id },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        }).done(function (response) {
            if (response.status === 200) {
                $('body').removeClass('overflow-hidden');
                $('#send-attachments' + '__popup').removeClass('show');
                showNotification('success', permission_js_var.setting_updated);
            }
        }).fail(function (response) {
            // Check for errors.
            if (response.status === 422) {
                var validation = $.parseJSON(response.responseText);
                if (validation.common === undefined) {
                    errors = validation.errors;
                    $.each(errors, function (field, message) {
                        var errorText = $('[name=' + field + ']', '.popup__container').closest('.popup__permissions--addtags').children('.warning-text');
                        errorText.text(message);
                    });
                } else {
                    if (validation.common === true) {
                        $('span.response-message').addClass('text-red').text(validation.errors).fadeIn().fadeOut(3000);
                    }
                }
            }
        });
    });


    //detail organization
    $(document).on('click', '#chat-feedback', function (event) {
        var organization_id = $("#organization_list").val();
        // Request.
        $.ajax({
            type: "post",
            url: "/permission/show-setting",
            data: {
                'organization_id': organization_id,
                'permission': "CHAT-FEEDBACK",
                'popup': "permission.feedback_popup"
            },
            dataType: 'json',

        }).done(function (response) {
            if (response.status === true) {
                $("#chat-feedback-popup").html(response.html);
                create_custom_dropdowns();
                openPopup("chat-feedback");
            }
        }).fail(function (response) {
            // Check for errors.
            if (response.status === 422) {
                var validation = $.parseJSON(response.responseText);
                errors = validation.errors;
                $.each(errors, function (field, message) {
                    var formGroup = $('[name=' + field + ']', form).closest('.popup__content--wrap');
                    formGroup.addClass('has-error').append('<p class="help-block" style="color:red">' + message + '</p>');
                });
            }
        });

    });

    $(document).on('click', '#update-chat-feedback-button', function (event) {
        resetFormErrors();
        var feedback = $("#feedback").val();
        var organization_id = $('#organization_list').val();

        // Request.
        $.ajax({
            type: "POST",
            url: "/permission/update-chat-feedback",
            data: { 'feedback': feedback, 'organization_id': organization_id },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        }).done(function (response) {
            if (response.status === 200) {
                $('body').removeClass('overflow-hidden');
                $('#chat-feedback' + '__popup').removeClass('show');
                showNotification('success', permission_js_var.setting_updated);
            }
        }).fail(function (response) {
            // Check for errors.
            if (response.status === 422) {
                var validation = $.parseJSON(response.responseText);
                if (validation.common === undefined) {
                    errors = validation.errors;
                    $.each(errors, function (field, message) {
                        var formGroup = $('[name=' + field + ']', '.popup__container').closest('.popup__permissions--addtags');
                        formGroup.addClass('has-error').append('<p class="help-block" style="color:red">' + message + '</p>');
                    });
                } else {
                    if (validation.common === true) {
                        $('span.response-message').addClass('text-red').text(validation.errors).fadeIn().fadeOut(3000);
                    }
                }
            }
        });
    });


    //    $(document).on('click', '#group-creation',function () {
    //        $('.overlay').show();
    //
    //        var inputs=$("#organization_list").val();
    //
    //        $('body').addClass('overflow-hidden');
    //        $id = '#' + $(this).attr('id');
    //
    //        $($id + '__popup').addClass('show');
    //    });

    $(document).on('click', '#chat-tags', function () {
        fetchTags();
    });

    $(document).on('keydown', '.permissions-input', function (event) {
        id = $(this).attr('id');
        if (event.keyCode === 13) {
            event.preventDefault();
            $(`#${id}_button`).trigger('click');
        }
    });

    $(document).on('keydown', '.timeout-input', function (event) {
        if (event.keyCode === 13) {
            event.preventDefault();
            $(`#update-timeout-button`).trigger('click');
        }
    });

    $(document).on('keydown', '.notifier-input', function (event) {
        if (event.keyCode === 13) {
            event.preventDefault();
            $(`#update-chatnotifier-button`).trigger('click');
        }
    });

    $(document).on('keydown', '.autotransfer-input', function (event) {
        if (event.keyCode === 13) {
            event.preventDefault();
            $(`#update-autochat-button`).trigger('click');
        }
    });

    //detail organization
    $(document).on('click', '#audio_notification', function (event) {
        var organization_id = $("#organization_list").val();
        // Request.
        $.ajax({
            type: "post",
            url: "/permission/show-setting",
            data: {
                'organization_id': organization_id,
                'permission': "AUDIO-NOTIFICATION",
                'popup': "permission.notification_popup"
            },
            dataType: 'json',

        }).done(function (response) {
            if (response.status === true) {
                $("#notification-popup").html(response.html);
                openPopup("notification");
            }
        }).fail(function (response) {
            showNotification('warning', messages.SOMETHING_WENT_WRONG);
        });

    });
});



$('#organization_list').on('change', function () {
    // console.log($("#spin__preloader_permission"))
    $("#spin__preloader_permission").show();
    var organization_id = $(this).val();
    organizationPermission(organization_id);

});


function organizationPermission(organization_id) {
    $.ajax({
        type: "POST",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: 'permission/organization-permission',
        data: { organization_id: organization_id },
        success: function (msg) {
            $("#spin__preloader_permission").hide();
            if (organization_id > 0) {
                $("#organization-permission-ajax").html(msg.html);
            }
            else {
                $("#organization-permission-ajax").html('');
            }
            disableSwitches();
        }
    });
}

function disableSwitches() {
    var ids = [];
    $('.master-switch').each(function () {
        ids.push(this.id);
    });
    for (i = 0; i < ids.length; i++) {
        if (ids[i] == 'admin') {
            $('#' + ids[i]).addClass('disabled').find('input').prop('checked', true);
        }
        if (!$('#' + ids[i]).find('input').prop('checked')) {
            $('.' + ids[i] + '-switch').addClass('disabled').find('input').prop('checked', false);
        }
    }

}

/**
 * Function to add tags.
 *
 * @returns
 */


function addTags() {
    $(".warning-text").remove();
    $("input").removeClass('warning-input');
    var organizationId = $('#organization_list').val();
    var postRequest = {
        'name': $("#add_tag").val(),
        'organizationId': organizationId
    }

    $.ajax({
        type: "POST",
        url: '/tags/ajax/add',
        data: postRequest,
        dataType: 'json',
        success: function (response) {
            if (response.status == true) {
                $("#add_tag").val('');
                $("#add_tag").focus();
                var tag = response.data;
                if (tag != null) {
                    $("#add-tags-show").prepend('<li class="tag-red"> <span>' + tag.name + '</span> <i class="fas fa-times" onClick="deleteTag(\'' + tag.id + '\')"></i></li>');
                }
            } else {
                showNotification('warning', messages.SOMETHING_WENT_WRONG);
            }
        },
        error: function (response) {
            $(".warning-text").remove();
            if (response.status == 422) {
                var validation = $.parseJSON(response.responseText);
                errors = validation.errors;
                $.each(errors, function (field, message) {
                    $("#" + field).addClass('warning-input');
                    $("#tag-input-div").after('<span class="warning-text">*' + message + '</span>');
                });
            } else {
                showNotification('warning', messages.SOMETHING_WENT_WRONG);
            }
        }
    });
}



/**
 * Function to fetch tags.
 *
 * @returns
 */
function fetchTags() {
    $("#add-tags-show").empty();
    $("#name").val('');
    $(".warning-text").remove();
    $("input").removeClass('warning-input');

    var permission_id = $("#chat-tags").data('value');
    var postRequest = {
        'organizationId': $('#organization_list').val(),
        'permissionId': permission_id
    };
    $.ajax({
        type: "GET",
        url: '/tags/ajax/getTag',
        dataType: 'json',
        data: postRequest,
        success: function (response) {
            $("#tag-popup").html(response.html);
            openPopup('tags');
            showTags(response);
        },
        error: function () {
            showNotification('warning', messages.SOMETHING_WENT_WRONG);
        }
    });
}

/**
 * Function to enable disable Setting button for corresponding switch.
 *
 * @param element
 * @returns
 */
function enableDisableSetting(element) {
    var settingElement = $(element).closest('tr').find('#setting-action').find('span');
    //If permission is enabled for any role
    if ($(element).prop("checked") == true) {
        if (settingElement.attr('data-disabled') == 0) {
            settingElement.removeClass('disabled');
        }
    } else {
        //If permission is disabled for all roles
        if ($(element).closest('tr').find("input:checked").length == 0) {
            settingElement.addClass('disabled');
        }
    }
}

/**
 * Restrict space in tag input.
 *
 * @returns
 */

$(document).on('keyup', '.addtag-input', function (e) {
    if (e.which === 32) {
        return false;
    }
    if (e.which === 13) {
        // return false;
    }
});

/**
 * FUnction to delete tag.
 *
 * @param tagId
 * @returns
 */
function deleteTag(tagId) {
    $("#delete__popup").removeClass('show');
    var postRequest = {
        'tagId': tagId
    };
    $.ajax({
        type: "POST",
        url: '/tags/ajax/deleteTag',
        data: postRequest,
        dataType: 'json',
        success: function (response) {
            if (response.status == true) {
                $("#add-tags-show").empty();
                showTags(response);
            }
        },
        error: function () {
            showNotification('warning', messages.SOMETHING_WENT_WRONG);
        }
    });
}

/**
 * Function to show tags.
 *
 * @param response
 * @returns
 */
function showTags(response) {
    if (response.data.length != 0) {
        var data = jQuery.parseJSON(response.data);
        $.each(data, function (key, tag) {
            if (tag.can_delete == 0) {
                $("#add-tags-show").prepend('<li class="tag-red"> <span>' + tag.name + '</span></li>');
            } else {
                $("#add-tags-show").prepend('<li class="tag-red"> <span>' + tag.name + '</span> <i class="fas fa-times" onClick="deleteTag(\'' + tag.tag_id + '\')"></i></li>');
            }
        });
    }

}

/**
 *
 * @todo remove after adding in common
 */
/*function resetFormErrors() {
    $('.popup__permissions--addtags').removeClass('has-error');
    $('.popup__permissions--addtags').find('.help-block-white').remove();
    $('.popup__permissions--addtags').find('.help-block').remove();
}*/



//detail organization
$(document).on('click', '#auto-chat-transfer', function (event) {
    var organization_id = $("#organization_list").val();
    // Request.
    $.ajax({
        type: "post",
        url: "/permission/show-setting",
        data: {
            'organization_id': organization_id,
            'permission': "AUTO-CHAT-TRANSFER",
            'popup': "permission.autochattransfer_popup"
        },
        dataType: 'json',

    }).done(function (response) {
        if (response.status === true) {
            $("#upload-attachment-popup").html(response.html);
            openPopup("auto-chat-transfer");
        }
    }).fail(function (response) {
        // Check for errors.
        if (response.status === 422) {
            var validation = $.parseJSON(response.responseText);
            errors = validation.errors;
            $.each(errors, function (field, message) {
                var errorText = $('[name=' + field + ']', '.popup__container').closest('.popup__permissions--addtags').parent().next('.warning-text');
                errorText.text(message);

            });
        }
    });

});


$(document).on('click', '#update-autochat-button', function (event) {
    resetFormErrors();
    var hour = $("#autotransfer-hour").val();
    var minute = $("#autotransfer-minute").val();
    var second = $("#autotransfer-second").val();
    var transfer_limit = $("#autotransfer-limit").val();
    var organization_id = $('#organization_list').val();

    // Request.
    $.ajax({
        type: "POST",
        url: "/permission/update-auto-chat",
        data: {
            'hour': hour,
            'organization_id': organization_id,
            'minute': minute,
            'second': second,
            'transfer_limit': transfer_limit

        },
        dataType: 'json',
    }).done(function (response) {
        if (response.status === 200) {
            $('body').removeClass('overflow-hidden');
            $('#auto-chat-transfer' + '__popup').removeClass('show');
            showNotification('success', permission_js_var.setting_updated);
        }
    }).fail(function (response) {
        // Check for errors.
        if (response.status === 422) {
            var validation = $.parseJSON(response.responseText);
            if (validation.common === undefined) {
                errors = validation.errors;
                $.each(errors, function (field, message) {
                    var errorText = $('[name=' + field + ']', '.popup__container').closest('.popup__permissions--addtags').parent().next('.warning-text');
                    errorText.text(message);
                });
            } else {
                if (validation.common === true) {
                    $('span.response-message').addClass('text-red').text(validation.errors).fadeIn().fadeOut(3000);
                }
            }
        }
    });
});



$(document).on('click', '#chat-notifier', function (event) {
    var organization_id = $("#organization_list").val();
    // Request.
    $.ajax({
        type: "post",
        url: "/permission/show-setting",
        data: {
            'organization_id': organization_id,
            'permission': "CHAT-NOTIFIER",
            'popup': "permission.chat_notifier_popup"
        },
        dataType: 'json',

    }).done(function (response) {
        if (response.status === true) {
            $("#upload-attachment-popup").html(response.html);
            openPopup("chat_notifier");
        }
    }).fail(function (response) {
        // Check for errors.
        if (response.status === 422) {
            var validation = $.parseJSON(response.responseText);
            errors = validation.errors;
            $.each(errors, function (field, message) {
                var errorText = $('[name=' + field + ']', '.popup__container').closest('.popup__permissions--addtags').parent().next('.warning-text');
                errorText.text(message);
            });
        }
    });

});

$(document).on('click', '#update-chatnotifier-button', function (event) {
    resetFormErrors();
    var hour = $("#notifier-hour").val();
    var minute = $("#notifier-minute").val();
    var second = $("#notifier-second").val();
    var organization_id = $('#organization_list').val();

    // Request.
    $.ajax({
        type: "POST",
        url: "/permission/update-chat-notifier",
        data: {
            'hour': hour,
            'organization_id': organization_id,
            'minute': minute,
            'second': second

        },
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
    }).done(function (response) {
        if (response.status === 200) {
            $('body').removeClass('overflow-hidden');
            $('#chat_notifier' + '__popup').removeClass('show');
            showNotification('success', permission_js_var.setting_updated);
        }
    }).fail(function (response) {
        // Check for errors.
        if (response.status === 422) {
            var validation = $.parseJSON(response.responseText);
            if (validation.common === undefined) {
                errors = validation.errors;
                $.each(errors, function (field, message) {
                    var errorText = $('[name=' + field + ']', '.popup__container').closest('.popup__permissions--addtags').parent().next('.warning-text');
                    errorText.text(message);
                });
            } else {
                if (validation.common === true) {
                    $('span.response-message').addClass('text-red').text(validation.errors).fadeIn().fadeOut(3000);
                }
            }
        }
    });
});


$(document).on('click', '#submit-chatdownload-setting-btn', function (event) {
    resetFormErrors();
    var agent_chat_download = false;
    if ($('#chatdownload').is(":checked")) {
        agent_chat_download = true;
    }

    var organization_id = $('#organization_list').val();

    // Request.
    $.ajax({
        type: "POST",
        url: "/permission/update-chat-download",
        data: {
            'chat_download': agent_chat_download,
            'organization_id': organization_id,


        },
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
    }).done(function (response) {
        if (response.status === 200) {
            $('body').removeClass('overflow-hidden');
            $('#chatdownload' + '__popup').removeClass('show');
            showNotification('success', permission_js_var.setting_updated);
        }
    }).fail(function (response) {
        // Check for errors.
        if (response.status === 422) {
            var validation = $.parseJSON(response.responseText);
            if (validation.common === undefined) {
                errors = validation.errors;
                $.each(errors, function (field, message) {
                    var errorText = $('[name=' + field + ']', '.popup__container').closest('.popup__permissions--addtags').parent().next('.warning-text');
                    errorText.text(message);
                });
            } else {
                if (validation.common === true) {
                    $('span.response-message').addClass('text-red').text(validation.errors).fadeIn().fadeOut(3000);
                }
            }
        }
    });
});


$(document).on('click', '#timeout', function (event) {
    var organization_id = $("#organization_list").val();
    // Request.
    $.ajax({
        type: "post",
        url: "/permission/show-setting",
        data: {
            'organization_id': organization_id,
            'permission': "TIMEOUT",
            'popup': "permission.timeout_popup"
        },
        dataType: 'json',

    }).done(function (response) {
        if (response.status === true) {
            $("#upload-attachment-popup").html(response.html);
            openPopup("timeout");
        }
    }).fail(function (response) {
        // Check for errors.
        if (response.status === 422) {
            var validation = $.parseJSON(response.responseText);
            errors = validation.errors;
            $.each(errors, function (field, message) {
                var errorText = $('[name=' + field + ']', '.popup__container').closest('.popup__permissions--addtags').parent().next('.warning-text');
                errorText.text(message);
            });
        }
    });

});

$(document).on('click', '#session_timeout', function (event) {
    var organization_id = $("#organization_list").val();
    // Request.
    $.ajax({
        type: "post",
        url: "/permission/show-setting",
        data: {
            'organization_id': organization_id,
            'permission': "SESSION_TIMEOUT",
            'popup': "permission.session_timeout_popup"
        },
        dataType: 'json',

    }).done(function (response) {
        if (response.status === true) {
            $("#upload-attachment-popup").html(response.html);
            openPopup("session_timeout");
        }
    }).fail(function (response) {
        // Check for errors.
        if (response.status === 422) {
            var validation = $.parseJSON(response.responseText);
            errors = validation.errors;
            $.each(errors, function (field, message) {
                var errorText = $('[name=' + field + ']', '.popup__container').closest('.popup__permissions--addtags').parent().next('.warning-text');
                errorText.text(message);
            });
        }
    });

});

$(document).on('click', '#archive_chat', function (event) {
    var organization_id = $("#organization_list").val();
    // Request.
    $.ajax({
        type: "post",
        url: "/permission/show-setting",
        data: {
            'organization_id': organization_id,
            'permission': "ARCHIVE_CHAT",
            'popup': "permission.archive_chat_popup"
        },
        dataType: 'json',

    }).done(function (response) {
        if (response.status === true) {
            $("#upload-attachment-popup").html(response.html);
            openPopup("archive_chat");
        }
    }).fail(function (response) {
        // Check for errors.
        if (response.status === 422) {
            var validation = $.parseJSON(response.responseText);
            errors = validation.errors;
            $.each(errors, function (field, message) {
                var errorText = $('[name=' + field + ']', '.popup__container').closest('.popup__permissions--addtags').parent().next('.warning-text');
                errorText.text(message);
            });
        }
    });

});

$(document).on('click', '#missed_chat', function (event) {
    var organization_id = $("#organization_list").val();
    // Request.
    $.ajax({
        type: "post",
        url: "/permission/show-setting",
        data: {
            'organization_id': organization_id,
            'permission': "MISSED-CHAT",
            'popup': "permission.missed_chat_popup"
        },
        dataType: 'json',

    }).done(function (response) {
        if (response.status === true) {
            $("#upload-attachment-popup").html(response.html);
            openPopup("missed_chat");
        }
    }).fail(function (response) {
        // Check for errors.
        if (response.status === 422) {
            var validation = $.parseJSON(response.responseText);
            errors = validation.errors;
            $.each(errors, function (field, message) {
                var errorText = $('[name=' + field + ']', '.popup__container').closest('.popup__permissions--addtags').parent().next('.warning-text');
                errorText.text(message);
            });
        }
    });

});

$(document).on('click', '#missed_form_button', function (event) {
    resetFormErrors();
    var api = $("#missed_chat_api").val();
    var templateId = $("#missed_chat_template_id").val();
    var botId = $("#missed_chat_bot_id").val();
    var token = $('#missed_chat_token').val();
    var orgId = $('#organization_list').val();

    // Request.
    $.ajax({
        type: "POST",
        url: "/permission/update-missed-chat-settings",
        data: {
            'api': api,
            'template_id': templateId,
            'bot_id': botId,
            'token': token,
            'organization_id': orgId
        },
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
    }).done(function (response) {
        if (response.status === 200) {
            $('body').removeClass('overflow-hidden');
            $('#missed_chat' + '__popup').removeClass('show');
            showNotification('success', 'Setting Updated');
        }
    }).fail(function (response) {
        // Check for errors.
        if (response.status === 422) {
            var validation = $.parseJSON(response.responseText);
            if (validation.common === undefined) {
                errors = validation.errors;
                $('p.warning-text').text('');
                $.each(errors, function (field, message) {
                    var errorText = $('[name=' + field + '] + p.warning-text');
                    errorText.text(message);
                });
            } else {
                if (validation.common === true) {
                    $('span.response-message').addClass('text-red').text(validation.errors).fadeIn().fadeOut(3000);
                }
            }
        }
    });
});

$(document).on('click', '#update-timeout-button', function (event) {
    resetFormErrors();
    var hour = $("#timeout-hour").val();
    var minute = $("#timeout-minute").val();
    var second = $("#timeout-second").val();
    var organization_id = $('#organization_list').val();

    // Request.
    $.ajax({
        type: "POST",
        url: "/permission/update-chat-timeout",
        data: {
            'hour': hour,
            'organization_id': organization_id,
            'minute': minute,
            'second': second

        },
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
    }).done(function (response) {
        if (response.status === 200) {
            $('body').removeClass('overflow-hidden');
            $('#timeout' + '__popup').removeClass('show');
            showNotification('success', permission_js_var.setting_updated);
        }
    }).fail(function (response) {
        // Check for errors.
        if (response.status === 422) {
            var validation = $.parseJSON(response.responseText);
            if (validation.common === undefined) {
                errors = validation.errors;
                $.each(errors, function (field, message) {
                    var errorText = $('[name=' + field + ']', '.popup__container').closest('.popup__permissions--addtags').parent().next('.warning-text');
                    errorText.text(message);
                });
            } else {
                if (validation.common === true) {
                    $('span.response-message').addClass('text-red').text(validation.errors).fadeIn().fadeOut(3000);
                }
            }
        }
    });
});

$(document).on('click', '#update-session-timeout-button', function (event) {
    resetFormErrors();
    var hour = $("#timeout-hour").val();
    var minute = $("#timeout-minute").val();
    var second = $("#timeout-second").val();
    var organization_id = $('#organization_list').val();
    var max_hours = $('#max-hours').val();

    // Request.
    $.ajax({
        type: "POST",
        url: "/permission/update-session-timeout",
        data: {
            'hour': hour,
            'organization_id': organization_id,
            'minute': minute,
            'second': second,
            'max_hours': max_hours

        },
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
    }).done(function (response) {
        if (response.status === 200) {
            $('body').removeClass('overflow-hidden');
            $('#session_timeout' + '__popup').removeClass('show');
            showNotification('success', 'Setting Updated');
        }
    }).fail(function (response) {
        // Check for errors.
        if (response.status === 422) {
            var validation = $.parseJSON(response.responseText);
            if (validation.common === undefined) {
                errors = validation.errors;
                $.each(errors, function (field, message) {
                    var errorText = $('[name=' + field + ']', '.popup__container').closest('.popup__permissions--addtags').parent().next('.warning-text');
                    errorText.text(message);
                });
            } else {
                if (validation.common === true) {
                    $('span.response-message').addClass('text-red').text(validation.errors).fadeIn().fadeOut(3000);
                }
            }
        }
    });
});

$(document).on('click', '#update-archive-chat-button', function (event) {
    resetFormErrors();
    var archive_type = $('input[name="archiveType"]:checked').val();
    var organization_id = $('#organization_list').val();

    // Request.
    $.ajax({
        type: "POST",
        url: "/permission/update-archive-chat",
        data: {
            'archive_type': archive_type,
            'organization_id': organization_id,


        },
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
    }).done(function (response) {
        if (response.status === 200) {
            $('body').removeClass('overflow-hidden');
            $('#archive_chat' + '__popup').removeClass('show');
            showNotification('success', 'Setting Updated');
        }
    }).fail(function (response) {
        // Check for errors.
        if (response.status === 422) {
            var validation = $.parseJSON(response.responseText);
            if (validation.common === undefined) {
                errors = validation.errors;
                $.each(errors, function (field, message) {
                    var errorText = $('[name=' + field + ']', '.popup__container').closest('.popup__permissions--addtags').parent().next('.warning-text');
                    errorText.text(message);
                });
            } else {
                if (validation.common === true) {
                    $('span.response-message').addClass('text-red').text(validation.errors).fadeIn().fadeOut(3000);
                }
            }
        }
    });
});

//chat download popup
$(document).on('click', '#chat_download', function (event) {
    var organization_id = $("#organization_list").val();
    // Request.
    $.ajax({
        type: "post",
        url: "/permission/show-setting",
        data: {
            'organization_id': organization_id,
            'permission': "CHAT-DOWNLOAD",
            'popup': "permission.chatdownload_popup"
        },
        dataType: 'json',

    }).done(function (response) {
        if (response.status === true) {
            $("#chatdownload-popup").html(response.html);
            openPopup("chatdownload");
        }
    }).fail(function (response) {
        showNotification('warning', messages.SOMETHING_WENT_WRONG);
    });

});


$(document).on('click', '#offline-form', function (event) {
    var organization_id = $("#organization_list").val();
    // Request.
    $.ajax({
        type: "post",
        url: "/permission/show-setting",
        data: {
            'organization_id': organization_id,
            'permission': "OFFLINE-FORM",
            'popup': "permission.offline_form_popup"
        },
        dataType: 'json',

    }).done(function (response) {
        if (response.status === true) {
            $("#offline_form-popup").html(response.html);
            openPopup("offline_form");
        }
    }).fail(function (response) {
        // Check for errors.
        if (response.status === 422) {
            var validation = $.parseJSON(response.responseText);
            errors = validation.errors;
            $.each(errors, function (field, message) {
                var errorText = $('[name=' + field + ']', '.popup__container').closest('.popup__permissions--addtags').parent().next('.warning-text');
                errorText.text(message);
            });
        }
    });

});



$(document).on('submit', 'form#offline_popup_form_element', function (event) {
    resetFormErrors();
    event.preventDefault();
    var form = $(this);
    var organization_id = $('#organization_list').val();
    var data = form.serialize() + "&organization_id=" + organization_id;

    console.log(JSON.parse(JSON.stringify(form.serializeArray())));
    console.log(data);

    if ($('.wa_push .canned__add--radio-container #in_session_push input')[0]['checked'] && $('.wa_push .canned__add--radio-container #out_session_push input')[0]['checked']) {
        data += "&session_push=3";
    } else if (!$('.wa_push .canned__add--radio-container #in_session_push input')[0]['checked'] && $('.wa_push .canned__add--radio-container #out_session_push input')[0]['checked']) {
        data += "&session_push=2";
    } else if ($('.wa_push .canned__add--radio-container #in_session_push input')[0]['checked'] && !$('.wa_push .canned__add--radio-container #out_session_push input')[0]['checked']) {
        data += "&session_push=1";
    } else {
        data += "&session_push=";
    }

    var message = $("#message").val();

    $.ajax({
        type: "POST",
        url: "/permission/update-offline-form",
        data: data,
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
    }).done(function (response) {
        if (response.status === 200) {
            $('body').removeClass('overflow-hidden');
            $('#offline_form' + '__popup').removeClass('show');
            showNotification('success', permission_js_var.setting_updated);
        }
    }).fail(function (response) {
        // Check for errors.
        if (response.status === 422) {
            var validation = $.parseJSON(response.responseText);
            console.log(response);
            console.log(validation);
            if (validation.common === undefined) {
                errors = validation.errors;
                $.each(errors, function (field, message) {
                    if (field.match("^email_id")) {
                        field = 'email_id';
                    }
                    if (field.match("^session_push")) {
                        $('.popup__container #in_session_error').addClass('warning-text show').text(message)
                    }
                    var errorText = $('.popup__container [name=' + field + '] + .warning-text').text(message);
                });
            } else {
                if (validation.common === true) {
                    $('span.response-message').addClass('text-red').text(validation.errors).fadeIn().fadeOut(3000);
                }
            }
        }
    });
});


$(document).on('click', '#ban-user', function (event) {
    var organization_id = $("#organization_list").val();
    // Request.
    $.ajax({
        type: "post",
        url: "/permission/show-setting",
        data: {
            'organization_id': organization_id,
            'permission': "BAN-USER",
            'popup': "permission.ban_popup"
        },
        dataType: 'json',
    }).done(function (response) {
        if (response.status === true) {
            $("#ban-user-popup").html(response.html);
            openPopup("ban");
        }
    }).fail(function (response) {
        // Check for errors.
        if (response.status === 422) {
            var validation = $.parseJSON(response.responseText);
            errors = validation.errors;
            $.each(errors, function (field, message) {
                var errorText = $('[name=' + field + ']', '.popup__container').closest('.popup__permissions--addtags').parent().next('.warning-text');
                errorText.text(message);
            });
        }
    });
});

$(document).on('click', '#surbo_ace_integration', function (event) {
    var organization_id = $("#organization_list").val();
    // Request.
    $.ajax({
        type: "post",
        url: "/permission/show-setting",
        data: {
            'organization_id': organization_id,
            'permission': "TMS-KEY",
            'popup': "permission.surbo_ace_integration_popup"
        },
        dataType: 'json',
    })
        .done(function (response) {
            if (response.status === true) {
                $("#tms-key-popup").html(response.html);
                openPopup("tms-key");
            }
        }).fail(function (response) {
            // Check for errors.
            if (response.status === 422) {
                var validation = $.parseJSON(response.responseText);
                errors = validation.errors;
                $.each(errors, function (field, message) {
                    var errorText = $('[name=' + field + ']', '.popup__container').closest('.popup__permissions--addtags').parent().next('.warning-text');
                    errorText.text(message);
                });
            }
        });
});




$(document).on('click', '#classified_chat', function (event) {
    var organization_id = $("#organization_list").val();
    // Request.
    $.ajax({
        type: "post",
        url: "/permission/show-setting",
        data: {
            'organization_id': organization_id,
            'permission': "CLASSIFIED-CHAT",
            'popup': "permission.classified_chat"
        },
        dataType: 'json',
    })
        .done(function (response) {
            if (response.status === true) {
                $("#tms-key-popup").html(response.html);
                openPopup("classified-chat");
            }
        }).fail(function (response) {
            // Check for errors.
            if (response.status === 422) {
                var validation = $.parseJSON(response.responseText);
                errors = validation.errors;
                $.each(errors, function (field, message) {
                    var errorText = $('[name=' + field + ']', '.popup__container').closest('.popup__permissions--addtags').parent().next('.warning-text');
                    errorText.text(message);
                });
            }
        });
});

$(document).on('click', '#ban_user_button', function (event) {
    resetFormErrors();
    var ban_days = $("#ban_user").val();
    var organization_id = $('#organization_list').val();    // Request.
    $.ajax({
        type: "POST",
        url: "/permission/update-ban-day",
        data: {
            'organization_id': organization_id,
            'ban_days': ban_days
        },
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
    }).done(function (response) {
        if (response.status === 200) {
            $('body').removeClass('overflow-hidden');
            $('#ban' + '__popup').removeClass('show');
            showNotification('success', permission_js_var.setting_updated);
        }
    }).fail(function (response) {
        // Check for errors.
        if (response.status === 422) {
            var validation = $.parseJSON(response.responseText);
            if (validation.common === undefined) {
                errors = validation.errors;
                $.each(errors, function (field, message) {
                    //                   console.log($('[name=' + field + ']', '.popup__container').next().next())
                    var errorText = $('[name=' + field + ']', '.popup__container').next().next('.warning-text');
                    errorText.text(message);
                });
            } else {
                if (validation.common === true) {
                    $('span.response-message').addClass('text-red').text(validation.errors).fadeIn().fadeOut(3000);
                }
            }
        }
    });
});

$(document).on('click', '#tms_key_button', function (event) {
    resetFormErrors();
    var tms_key = $("#tms_unique_key").val();
    var organization_id = $('#organization_list').val();    // Request.
    $.ajax({
        type: "POST",
        url: "/permission/update-tms-key",
        data: {
            'organization_id': organization_id,
            'tms_key': tms_key
        },
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
    }).done(function (response) {
        if (response.status === 200) {
            $('body').removeClass('overflow-hidden');
            $('#tms-key' + '__popup').removeClass('show');
            showNotification('success', permission_js_var.setting_updated);
        } else {
            var errorText = $('[name=tms_key]', '.popup__container').next('.warning-text');
            errorText.text(response.message);
        }
    }).fail(function (response) {
        // Check for errors.
        if (response.status === 422) {
            var validation = $.parseJSON(response.responseText);
            if (validation.common === undefined) {
                errors = validation.errors;
                $.each(errors, function (field, message) {
                    //                   console.log($('[name=' + field + ']', '.popup__container').next().next())
                    var errorText = $('[name=' + field + ']', '.popup__container').next('.warning-text');
                    errorText.text(message);
                });
            } else {
                if (validation.common === true) {
                    $('span.response-message').addClass('text-red').text(validation.errors).fadeIn().fadeOut(3000);
                }
            }
        }
    });
});

$(document).on('click', '#classified_chat_button', function (event) {
    resetFormErrors();
    var classified_token = $("#classified_unique_token").val();
    var organization_id = $('#organization_list').val();    // Request.
    $.ajax({
        type: "POST",
        url: "/permission/update-classified-token",
        data: {
            'organization_id': organization_id,
            'classified_token': classified_token
        },
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
    }).done(function (response) {
        if (response.status === 200) {
            $('body').removeClass('overflow-hidden');
            $('#classified-chat' + '__popup').removeClass('show');
            showNotification('success', permission_js_var.setting_updated);
        } else {
            var errorText = $('[name=tms_key]', '.popup__container').next('.warning-text');
            errorText.text(response.message);
        }
    }).fail(function (response) {
        // Check for errors.
        if (response.status === 422) {
            var validation = $.parseJSON(response.responseText);
            if (validation.common === undefined) {
                errors = validation.errors;
                $.each(errors, function (field, message) {
                    //                   console.log($('[name=' + field + ']', '.popup__container').next().next())
                    var errorText = $('[name=' + field + ']', '.popup__container').next('.warning-text');
                    errorText.text(message);
                });
            } else {
                if (validation.common === true) {
                    $('span.response-message').addClass('text-red').text(validation.errors).fadeIn().fadeOut(3000);
                }
            }
        }
    });
});

$(document).on('click', '#email', function (event) {
    var organization_id = $("#organization_list").val();
    // Request.
    $.ajax({
        type: "post",
        url: "/permission/show-setting",
        data: {
            'organization_id': organization_id,
            'permission': "EMAIL",
            'popup': "permission.email"
        },
        dataType: 'json',
    })
        .done(function (response) {
            if (response.status === true) {
                $("#email-popup").html(response.html);
                openPopup("email");
                handleGroupServieType();
            }
        }).fail(function (response) {
            // Check for errors.
            if (response.status === 422) {
                var validation = $.parseJSON(response.responseText);
                errors = validation.errors;
                $.each(errors, function (field, message) {
                    var errorText = $('[name=' + field + ']', '.popup__container').closest('.popup__permissions--addtags').parent().next('.warning-text');
                    errorText.text(message);
                });
            }
        });
});

$(document).on('click', '#email_button', function (event) {
    resetFormErrors();
    var organization_id = $('#organization_list').val();
    var username = $("#username").val();
    var password = $('#password').val();
    var host = $("#host").val();
    var port = $('#port').val();
    var encryption = $('#encryption').val();
    var provider_type = $('#provider_type').val();// Request.
    var from_email = $('#from_email').val();// Request.
    $.ajax({
        type: "POST",
        url: "/permission/update-email-credentials",
        data: {
            'organization_id': organization_id,
            'username': username,
            'password': password,
            'host': host,
            'port': port,
            'encryption': encryption,
            'provider_type': provider_type,
            'from_email': from_email
        },
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
    }).done(function (response) {
        if (response.status === 200) {
            $('body').removeClass('overflow-hidden');
            $('#email' + '__popup').removeClass('show');
            showNotification('success', permission_js_var.setting_updated);
        }
    }).fail(function (response) {
        // Check for errors.
        if (response.status === 422) {
            var validation = $.parseJSON(response.responseText);
            if (validation.common === undefined) {
                errors = validation.errors;
                $.each(errors, function (field, message) {
                    //                   console.log($('[name=' + field + ']', '.popup__container').next().next())
                    var errorText = $('[name=' + field + ']', '.popup__container').next('.warning-text');
                    errorText.text(message);
                });
            } else {
                if (validation.common === true) {
                    $('span.response-message').addClass('text-red').text(validation.errors).fadeIn().fadeOut(3000);
                }
            }
        }
    });
});

$(document).on("submit", "form#tag-settings-form", function (e) {
    e.preventDefault();
    var form = $(this);
    url = form.attr('action');
    var tag_required = $("input[name='tagType']:checked").val();
    var data = form.serialize() + '&tag_required=' + tag_required;
    $.ajax({
        type: "POST",
        url: url,
        data: data,
        dataType: 'json',
    }).done(function (response) {
        if (response.status === 200) {
            showNotification('success', response.message);
        }
    }).fail(function (response) {
        if (response.status === 422) {
            showNotification('warning', response.responseJSON.errors);
        }
    }).always(function () {
        $('#tags' + '__popup').removeClass('show');
        $('body').removeClass('overflow-hidden');
    });
});

/************Ajax request to open Customer Information setting pop up start******/
$(document).on('click', '#customer_information', function (event) {
    var organization_id = $("#organization_list").val();
    // Request.
    $.ajax({
        type: "post",
        url: "/permission/show-setting",
        data: {
            'organization_id': organization_id,
            'permission': "CUSTOMER-INFORMATION",
            'popup': "permission.customer_information_popup"
        },
        dataType: 'json',

    }).done(function (response) {
        if (response.status === true) {
            $("#customer-information-popup").html(response.html);
            openPopup("customer_information");
        }
    }).fail(function (response) {
        // Check for errors.
        if (response.status === 422) {
            var validation = $.parseJSON(response.responseText);
            errors = validation.errors;
            $.each(errors, function (field, message) {
                var errorText = $('[name=' + field + ']', '.popup__container').closest('.popup__permissions--addtags').parent().next('.warning-text');
                errorText.text(message);
            });
        }
    });

});
/************Ajax request to open Customer Information setting pop up End******/

/************Ajax request to save Customer Information setting START************/
$(document).on('click', '#update-customer-information-setting-button', function (event) {
    resetFormErrors();
    var customer_chat_info_label = $('input[name="customerChatInfoLabel"]:checked').val();
    var organization_id = $('#organization_list').val();

    // Request.
    $.ajax({
        type: "POST",
        url: "/permission/update-customer-information-setting",
        data: {
            'customerChatInfoLabel': customer_chat_info_label,
            'organization_id': organization_id,
        },
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
    }).done(function (response) {
        if (response.status === 200) {
            $('body').removeClass('overflow-hidden');
            $('#customer_information' + '__popup').removeClass('show');
            showNotification('success', 'Setting Updated');
        }
    }).fail(function (response) {
        // Check for errors.
        if (response.status === 422) {
            var validation = $.parseJSON(response.responseText);
            if (validation.common === undefined) {
                errors = validation.errors;
                $.each(errors, function (field, message) {
                    var errorText = $('[name=' + field + ']', '.popup__container').closest('.popup__content').parent().find('.warning-text');
                    console.log(errorText);
                    errorText.text(message);
                });
            } else {
                if (validation.common === true) {
                    $('span.response-message').addClass('text-red').text(validation.errors).fadeIn().fadeOut(3000);
                }
            }
        }
    });
});
/************Ajax request to save Customer Information setting END************/



/**
 * Section - Offline Form Popup
 * Different sections of offline form Popup on radio toggle is handled here
 */
$(document).on('change', '.canned__add--radio-container input[name=qc_slider], .canned__add--radio-container input[name=wa_push_slider], .canned__add--radio-container input[name=tms_slider], .canned__add--radio-container input[name=email_slider], .wa_push .canned__add--radio-container #in_session_push input[name=session_push]', function (event) {
    switch (event.target.name) {
        case 'wa_push_slider':
            $(".canned__add--radio-container .wa_push").toggle();
            $('.wa_push .canned__add--radio-container #in_session_push input')[0]['checked'] = false;
            $('.wa_push .canned__add--radio-container #out_session_push input')[0]['checked'] = false;
            $(".wa_push .free-in-session-push").addClass('viewDisabled');
            $(".wa_push .free-in-session-push input[type=text],  .wa_push .free-in-session-push textarea").val('');
            break;
        case 'session_push':
            $(".wa_push .free-in-session-push").toggleClass('viewDisabled');
            $(".wa_push .free-in-session-push input[type=text], .wa_push .free-in-session-push textarea").val('');
            break;
        case 'qc_slider':
            if (event.target.checked) {
                $('.canned__add--radio-container input[name=wa_push_slider], .canned__add--radio-container input[name=email_slider]').parent().removeClass('viewDisabled');
            } else {
                $('.canned__add--radio-container input[name=wa_push_slider], .canned__add--radio-container input[name=email_slider]').parent().addClass('viewDisabled');
                $(".canned__add--radio-container .email").fadeOut();
                $('.canned__add--radio-container input[name=wa_push_slider]')[0].checked = false;
                $('.canned__add--radio-container input[name=email_slider]')[0].checked = false;
                $(".canned__add--radio-container .wa_push").fadeOut();
            }
            break;
        case 'tms_slider':
            break;
        case 'email_slider':
            $(".canned__add--radio-container .email").toggle();
            break;
        default:
            break;
    }
});




function handleGroupServieType() {
    const val = $('#email__popup #provider_type').val();
    const encryptInput = document.getElementById("encryption");
    if (val.toString() === '1') {
        $('#email__popup #encryption')
        encryptInput.disabled = true;
        encryptInput.value = "";
    } else {
        encryptInput.disabled = false;
    }
}

