$(document).ready(function () {
    showUserTable();
    $(".custom-input-search").val("");
    $(document).on('submit', '#adduser_form', function (event) {
        event.preventDefault();
        resetFormErrors();
        // Set vars.
        var form = $(this);
        url = form.attr('action');

        if (form.find('[type=file]').length) {
            // If found, prepare submission via FormData object.
            var input = form.serializeArray();
            data = new FormData();

            contentType = false;

            // Append input to FormData object.
            $.each(input, function (index, input) {
                data.append(input.name, input.value);
            });

            // Append files to FormData object.
            $.each(form.find('[type=file]'), function (index, input) {
                errorMessage = '';
                if (input.files[0] !== undefined) {
                    data.append(input.name, input.files[0]);
                    var extension = getFileExtension(input.files[0].name);
                }
                $('span.ajax-response-message').addClass('text-red').html(errorMessage).fadeIn().fadeOut(3000);
            });
        }
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
                showUserTable();
                $('#add__popup').removeClass('show');
                $('body').removeClass('overflow-hidden');
                showNotification('success', response.message);
            }
            if (response.status == 423) {
                showNotification('warning', user_js_var.admin_already_exist);
            }
            if (response.status == 424) {
                showNotification('warning', user_js_var.no_seats_exceeded);
            }

        }).fail(function (response) {
            // Check for errors.
            if (response.status === 422) {
                var validation = $.parseJSON(response.responseText);
                if (validation.common === undefined) {
                    errors = validation.errors;
                    $.each(errors, function (field, message) {
                        if (field == 'group') {
                            var formGroup = $('[id=' + field + ']', form).closest('.popup__content--wrap');
                        } else {
                            var formGroup = $('[name=' + field + ']', form).closest('.popup__content--wrap');
                        }
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

    $(document).on('submit', '#edituser_form', function (event) {
        event.preventDefault();
        resetFormErrors();
        // Set vars.
        var form = $(this);
        url = form.attr('action');

        if (form.find('[type=file]').length) {
            // If found, prepare submission via FormData object.
            var input = form.serializeArray();
            data = new FormData();

            contentType = false;

            // Append input to FormData object.
            $.each(input, function (index, input) {
                data.append(input.name, input.value);
            });

            // Append files to FormData object.
            $.each(form.find('[type=file]'), function (index, input) {
                errorMessage = '';
                if (input.files[0] !== undefined) {
                    data.append(input.name, input.files[0]);
                    var extension = getFileExtension(input.files[0].name);
                }
                $('span.ajax-response-message').addClass('text-red').html(errorMessage).fadeIn().fadeOut(3000);
            });
        }
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
                showUserTable();
                $('#edit__popup').removeClass('show');
                $('body').removeClass('overflow-hidden');
                showNotification('success', response.message);

            }

            if (response.status == 423) {
                showNotification('warning', response.errors);
            }
        }).fail(function (response) {
            // Check for errors.
            if (response.status === 422) {
                var validation = $.parseJSON(response.responseText);
                if (validation.common === undefined) {
                    errors = validation.errors;
                    $.each(errors, function (field, message) {
                        if (field == 'group') {
                            var formGroup = $('[id=' + field + ']', form).closest('.popup__content--wrap');
                        } else {
                            var formGroup = $('[name=' + field + ']', form).closest('.popup__content--wrap');
                        }
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


    //edit organization view
    $(document).on('click', '#edit', function (event) {

        user_id = ($(this).attr('data-id'));
        // Request.
        $.ajax({
            type: "get",
            url: "user/edit/" + user_id,
            data: {},
            dataType: 'json',
            processData: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        }).done(function (response) {
            console.log(response);
            if (response.status === true) {
                $("#edit_user_partial").html(response.html);
                $('.overlay').show();
                $('body').addClass('overflow-hidden');

                create_custom_dropdowns();
                $('#edit__popup').addClass('show');
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

    //detail user
    $(document).on('click', '#view', function (event) {
        user_id = ($(this).attr('data-id'));
        // Request.
        $.ajax({
            type: "get",
            url: "user/detail/" + user_id,
            data: {},
            dataType: 'json',
            processData: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        }).done(function (response) {
            if (response.status === true) {
                $("#detail_user_partial").html(response.html);
                // $('#view__popup').addClass('show');
                openPopup('view');
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

    //update user password
    $(document).on('click', '#update_password', function (event) {
        user_id = ($(this).attr('data-id'));
        // Request.
        $.ajax({
            type: "get",
            url: "user/update_password/" + user_id,
            data: {},
            dataType: 'json',
            processData: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        }).done(function (response) {
            if (response.status === true) {
                $("#update_password_partial").html(response.html);
                $('.overlay').hide();
                $('#password__popup').addClass('show');
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

    //edit organization view
    $(document).on('click', '#update_password_button', function (event) {
        resetPartialViewErrors();
        var password = $("#password").val();
        var confirm_password = $("#confirm_password").val();
        var user_id = $("#user_id").val();
        // Request.
        $.ajax({
            type: "post",
            url: "user/update_user_password",
            data: { "password": password, "confirm_password": confirm_password, "user_id": user_id },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        }).done(function (response) {
            if (response.status === 200) {
                $('body').removeClass('overflow-hidden');
                $('#password__popup').removeClass('show');
                showNotification('success', response.message);
            }
        }).fail(function (response) {
            // Check for errors.
            if (response.status === 422) {
                var validation = $.parseJSON(response.responseText);
                errors = validation.errors;
                $.each(errors, function (field, message) {
                    var formGroup = $('[name=' + field + ']', '.popup__content').closest('.popup__small__content--wrap');
                    formGroup.addClass('has-error').append('<p class="help-block" style="color:red">' + message + '</p>');
                });
            }
        });

    });

    //delete user
    $(document).on('click', '#delete', function (event) {
        user_id = ($(this).attr('data-id'));
        $("#delete_id").val(user_id);
        checkReportee();
    });
    
    //clear user logins
    $(document).on('click', '.clear-login', function (event) {
        user_id = ($(this).attr('data-id'));
        $("#clear_user_id").val(user_id);
        openPopup('clear-login');
    });


    //for dynamic report to menu
    $(document).on('change', '#select-role', function (e) {
        const role_id = $(this).val();
        var user_id = $('#user_id').val();

        $("#report-to").empty();

        if (role_id == 2) {
            $('.report-to').addClass('disabled');
            $('#no_of_chats').attr("disabled", true);
            $('.groupMultiple').addClass("disabled");
            create_custom_dropdowns();

        } else {
            $('.report-to').removeClass('disabled');
            $('#no_of_chats').attr("disabled", false);
            $('.groupMultiple').removeClass("disabled");
            create_custom_dropdowns();
            var organization_id = $('#select-organization').val();




            $.ajax({
                method: "post",
                url: APP_URL + "/user/get-report-to",
                data: {
                    role_id: role_id,
                    organization_id: organization_id,
                    user_id: user_id
                },
                dataType: 'json',

                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            }).done(function (response) {
                if (response.status === true) {
                    $("#add-report-to").html(response.html);

                    create_custom_dropdowns();
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
        }
    })

    $(document).on('click', '#log-in', function (e) {
        var user_id = $(this).attr('data-id');
        checkSneakIn(user_id);
    });
});

/*function resetFormErrors() {
    $('.popup__content--wrap').removeClass('has-error');
    $('.popup__content--wrap').find('.help-block-white').remove();
    $('.popup__content--wrap').find('.help-block').remove();
}*/

function resetPartialViewErrors() {
    $('.popup__small__content--wrap').removeClass('has-error');
    $('.popup__small__content--wrap').find('.help-block-white').remove();
    $('.popup__small__content--wrap').find('.help-block').remove();
}






/**
 * Function to show User table.
 * 
 * @returns
 */
function showUserTable() {

    if ($.fn.DataTable.isDataTable('#myTable')) {
        $('#myTable').DataTable().destroy();
    }
    // disable datatables error prompt
    $.fn.dataTable.ext.errMode = 'none';

    var datatable = $('#myTable').DataTable({
        fixedHeader: true,
        info: false,
        processData: true,
        serverSide: true,
        'oLanguage': {
            sEmptyTable: user_js_var.no_data_available,
            sZeroRecords: user_js_var.no_matching_records_found,
            oPaginate: {
                sFirst: user_js_var.first_page, // This is the link to the first page
                sPrevious: user_js_var.previous_page, // This is the link to the previous page
                sNext: user_js_var.next_page, // This is the link to the next page
                sLast: user_js_var.last_page // This is the link to the last page
            }
        },
        ajax: {
            url: '/user/get-user-by-organization',
            data: function (d) {
                d.organization_id = $('#select-organization').val();
                if (!$('#select-organization').val()) {
                    $('#add-user').attr('disabled', true);
                    $("#add-user").css(" cursor", "not-allowed");
                } else {
                    $('#add-user').attr('disabled', false);

                }

                // d.email = $('input[name=email]').val();
            }
        },

        columns: [
            { data: 'image', name: 'image', class: 'prevent-overflow col-width-5', searchable: false },
            { data: 'name', name: 'name', class: 'prevent-overflow col-width-10' },
            { data: 'email', name: 'email', class: 'prevent-overflow col-width-20' },
            { data: 'mobile_number', name: 'mobile_number', class: 'prevent-overflow col-width-20' },
            { data: 'role_id', name: 'role_id', class: 'prevent-overflow col-width-10', searchable: false },
            { data: 'password', name: 'password', class: 'prevent-overflow col-width-10', searchable: false, orderable: false },
            { data: 'last_login', name: 'last_login', class: 'prevent-overflow col-width-10', searchable: false },
            { data: 'status', name: 'status', class: 'prevent-overflow col-width-10', searchable: false },
            { data: 'action', name: 'action', class: 'col-width-25', searchable: false },
        ],
        columnDefs: [
            { orderable: false, targets: [-1, -2, -9] }
        ],
        "order": []
    });

    // let scrollCount = 0;
    // $('#myTable').on('header.dt,page.dt', function () {
    //     if (scrollCount++) {
    //         $('html, body').animate({
    //             scrollTop: $(".dataTables_wrapper").offset().top
    //         }, 'slow');
    //     }
    // });

    $('#select-organization').on('change', function (e) {
        datatable.draw();
        // e.preventDefault();
    });


    $('#datatable-search').keyup(function () {
        datatable.search($(this).val()).draw();
    });

    $('#datatable-length').change(function () {
        datatable.page.len($(this).val()).draw();
    });

    // $( window ).resize(function() {
    //     // datatable.columns.adjust().draw();
    // });


}

function showAddModal() {



    $.ajax({
        type: "POST",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: APP_URL + '/user/create',
        data: { organization_id: $('#select-organization').val() },
        success: function (msg) {
            $("#report-to").empty();
            $("#hidden_user_id").removeAttr('value');
            $("#user_id").removeAttr('value');


            var id = $("#select-organization").val();
            $("#organization_id").val(id);
            $("#add_user_partial").html(msg.html);
            create_custom_dropdowns()

            $('#add__popup').addClass('show');
        }
    });


}



function changeUserStatus(id) {
    var status = 0;
    if ($('#' + id).is(":checked")) {
        status = 1;
    }
    $.ajax({
        type: "post",
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: 'user/user-status',
        data: {
            user_id: id,
            status: status,

        }
    }).done(function (response) {
        if (response.status === 200) {
            showNotification('success', response.message);
            $('span.ajax-response-message').addClass('text-green').html(response.message).fadeIn().fadeOut(3000);
        }
    }).fail(function (response) {
        var errors = $.parseJSON(response.responseText);
        if (response.status === 422) {
            showNotification('warning', errors.errors);
            $('span.ajax-response-message').addClass('text-red').html(errors.errors).fadeIn().fadeOut(3000);
        }
    });
}

function deleteUser(id) {
    var user_id = $('#delete_id').val();
    $.ajax({
        type: "get",
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: 'user/delete/' + user_id,
        data: []
    }).done(function (response) {
        if (response.status === 200) {
            showUserTable();
            $('#delete__popup').removeClass('show');
            $('body').removeClass('overflow-hidden');
            showNotification('success', response.message);
        }
    }).fail(function (response) {
        var errors = $.parseJSON(response.responseText);
        if (response.status === 422) {
            $('span.ajax-response-message').addClass('text-red').html(errors.errors).fadeIn().fadeOut(3000);
        }
    });
}

function clearUserLogin() {
    var user_id = $('#clear_user_id').val();
    $.ajax({
        type: "get",
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: 'user/clear-login/' + user_id,
        data: []
    }).done(function (response) {
        if (response.status === 200) {
            showUserTable();
            $('#clear-login__popup').removeClass('show');
            $('body').removeClass('overflow-hidden');
            showNotification('success', response.message);
        }
    }).fail(function (response) {
        var errors = $.parseJSON(response.responseText);
        if (response.status === 422) {
            $('span.ajax-response-message').addClass('text-red').html(errors.errors).fadeIn().fadeOut(3000);
        }
    });
}



/**
 * 
 * @param id
 * @returns
 */
function checkReportee() {
    var postRequest = {
        'userId': $('#delete_id').val()
    };
    $.ajax({
        type: "post",
        dataType: 'json',
        url: 'user/check-reportee',
        data: postRequest
    }).done(function (response) {
        if (response.status == false) {
            showNotification('info', response.data);
        } else {
            openPopup('delete');
        }

    }).fail(function (response) {
        showNotification('error', messages.SOMETHING_WENT_WRONG);
    });
}

$(document).on('click', '#user-permission', function (event) {
    user_id = ($(this).attr('data-id'))

    $.ajax({
        type: "get",
        url: "user/show-permission/" + user_id,
        data: {},
        dataType: 'json',
        processData: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
    }).done(function (response) {
        if (response.status === true) {

            $("#user_permission_partial").html(response.html);
            $('#permission__popup').addClass('show');
            $(".permission_error").addClass('hidden');
            //openPopup('view');
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



$(document).on('submit', '#edituser_permission_form', function (event) {
    event.preventDefault();
    resetFormErrors();
    // Set vars.
    var form = $(this);
    url = form.attr('action');


    // If found, prepare submission via FormData object.
    var input = form.serializeArray();
    var data = new FormData();

    var contentType = false;

    // Append input to FormData object.
    $.each(input, function (index, input) {
        data.append(input.name, input.value);
    });

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
            // showUserTable();
            $('#permission__popup').removeClass('show');
            $('body').removeClass('overflow-hidden');
            showNotification('success', response.message);
        }

    }).fail(function (response) {
        // Check for errors.
        if (response.status === 422) {
            showNotification('warning', user_js_var.extra_permission_found);
            $(".permission_error").removeClass('hidden');
            var validation = $.parseJSON(response.responseText);

            $(".permission_error").text(validation.errors);

            return false;
        }
    });


});

/**
 * Function to check whether senak in is possible.
 * 
 * @param user_id
 * @returns
 */
function checkSneakIn(user_id) {
    $.ajax({
        method: "post",
        url: APP_URL + "/user/check-sneak-in",
        data: {
            user_id: user_id
        },
        dataType: 'json',
        success: function (response) {
            if (response.status == false) {
                showNotification('warning', response.message);
            } else {
                userSneakIn(user_id);
            }
        }
    }).fail(function (response) {
        showNotification('error', messages.SOMETHING_WENT_WRONG);
    });
}

/**
 * Function to check user sneak in.
 * 
 * @param user_id
 * @returns
 */
function userSneakIn(user_id) {
    $.ajax({
        method: "post",
        url: APP_URL + "/user/sneak-in",
        data: {
            user_id: user_id
        },
        dataType: 'json',
        success: function (response) {
            if (response.status == true) {
                window.location.href = APP_URL + response.data;
            } else {
                showNotification('error', response.message);
            }
        }
    }).fail(function (response) {
        showNotification('error', messages.SOMETHING_WENT_WRONG);
    });
}
