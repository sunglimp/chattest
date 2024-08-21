$(document).ready(function () {
    showOrganizationTable();

    $(".custom-input-search").val("");
    //edit form submit
    $(document).on('submit', '#edit-organization-form', function (event) {
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
                errorStatus = false;
                if (input.files[0] !== undefined) {
                    data.append(input.name, input.files[0]);
                    var extension = getFileExtension(input.files[0].name);
                } else {
                    errorMessage = organization_js_var.no_file_selected;
                    errorStatus = false;
                }
                if (errorStatus) {
                    $('span.ajax-response-message').addClass('text-red').html(errorMessage).fadeIn().fadeOut(3000);
                }
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
                $('#edit__popup').removeClass('show');
                $('body').removeClass('overflow-hidden');
                showOrganizationTable();
                showNotification('success', organization_js_var.update_successfully);
            }
        }).fail(function (response) {
            // Check for errors.
            if (response.status === 422) {
                var validation = $.parseJSON(response.responseText);
                errors = validation.errors;
                $.each(errors, function (field, message) {
                    if (field == 'language') {
                        var formGroup = $('[id=' + field + ']', form).closest('.popup__content--wrap');
                    } else {
                        var formGroup = $('[name=' + field + ']', form).closest('.popup__content--wrap');
                    }

                    formGroup.addClass('has-error').append('<p class="help-block" style="color:red">' + message + '</p>');
                });
            }
        });
    });


    //Add form submit
    $(document).on('submit', '#add-organization-form', function (event) {
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
                errorStatus = false;
                if (input.files[0] !== undefined) {
                    data.append(input.name, input.files[0]);
                    var extension = getFileExtension(input.files[0].name);
                } else {
                    errorMessage = organization_js_var.no_file_selected;
                    errorStatus = false;
                }
                if (errorStatus) {
                    $('span.ajax-response-message').addClass('text-red').html(errorMessage).fadeIn().fadeOut(3000);
                }
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
                // $('span.ajax-response-message').addClass('text-green').text(response.message).fadeIn().fadeOut(3000);
                showOrganizationTable();
                $('#add__popup').removeClass('show');
                $('body').removeClass('overflow-hidden');
                showNotification('success', organization_js_var.added_successfully);
                // window.location = "/organization/index";
            }
        }).fail(function (response) {
            // Check for errors.
            if (response.status === 422) {
                var validation = $.parseJSON(response.responseText);
                errors = validation.errors;
                $.each(errors, function (field, message) {
                    if (field == 'language') {
                        var formGroup = $('[id=' + field + ']', form).closest('.popup__content--wrap');
                    } else {
                        var formGroup = $('[name=' + field + ']', form).closest('.popup__content--wrap');
                    }
                    formGroup.addClass('has-error').append('<p class="help-block" style="color:red">' + message + '</p>');
                });
            }
        });
    });

    //edit organization view
    $(document).on('click', '#edit', function (event) {
        organization_id = ($(this).attr('data-id'));
        // Request.
        $.ajax({
            type: "get",
            url: "/organization/edit/" + organization_id,
            data: {},
            dataType: 'json',
            processData: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        }).done(function (response) {
            if (response.status === true) {
                $("#edit_organization_partial").html(response.html);
                create_custom_dropdowns();
                openPopup("edit");
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
    $(document).on('click', '#view', function (event) {
        organization_id = ($(this).attr('data-id'));
        // Request.
        $.ajax({
            type: "get",
            url: APP_URL + "/organization/detail/" + organization_id,
            data: {},
            dataType: 'json',
            processData: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        }).done(function (response) {

            if (response.status === 200) {
                $("#organization-logo").attr('src', response.data.logo);
                $("#view_company_name").text(response.data.company_name);
                $("#view_contact_name").text(response.data.contact_name);
                $("#view_mobile_number").text(response.data.mobile_number);
                $("#view_email").text(response.data.email);
                $("#view_seat_alloted").text(response.data.seat_alloted);
                $("#view_website").text(response.data.website ? response.data.website : "N/A");
                $("#view_organization_id").text(response.data.id);
                $("#view_timezone").text(response.data.timezone);
                $("#view_account_type").text(response.data.is_testing ? 'Demo' : 'Live');
                $('span.ajax-response-message').addClass('text-green').text(response.message).fadeIn().fadeOut(3000);
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

    //delete organization
    $(document).on('click', '#delete', function (event) {

        $('.overlay').show();
        $('#delete__popup').addClass('show');

        organization_id = ($(this).attr('data-id'));
        $("#delete_id").val(organization_id);
    });

    $(document).on('submit', '#add-organization-permission', function (event) {
        event.preventDefault();
        // Set vars.
        var form = $(this);
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
                $('span.response-message').addClass('text-green').text(response.message).fadeIn().fadeOut(9000);
                setTimeout(function () {

                }, 6);
            }
        }).fail(function (response) {
            // Check for errors.
            if (response.status === 422) {
                var validation = $.parseJSON(response.responseText);
                errors = validation.errors;
                $.each(errors, function (field, message) {
                    var formGroup = $('[name=' + field + ']', form).closest('.popup__content--wrap');
                    formGroup.addClass('has-error').append('<p class="help-block">' + message + '</p>');
                });
            }
        });
    });
});


/**
 *
 * @todo remove after adding in common
 */
/*function resetFormErrors()
{
    $('.popup__content--wrap').removeClass('has-error');
    $('.popup__content--wrap').find('.help-block-white').remove();
    $('.popup__content--wrap').find('.help-block').remove();
}*/

function changeOrganizationStatus(id) {
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
        url: APP_URL + '/organization/organization-status',

        data: {
            organization_id: id,
            status: status,

        }
    }).done(function (response) {
        if (response.status === 200) {
            $('span.ajax-response-message').addClass('text-green').html(response.message).fadeIn().fadeOut(3000);
            showNotification('success', organization_js_var.status_changed);
        }
    }).fail(function (response) {
        var errors = $.parseJSON(response.responseText);
         if (response.status === 422 && errors.status == 423) {
            showNotification('warning', errors.errors);
            $('span.ajax-response-message').addClass('text-red').html(errors.errors).fadeIn().fadeOut(3000);
            $('#' + id).prop('checked', false);
    }
        else if (response.status === 422) {
            showNotification('warning', organization_js_var.something_wrong);
            $('span.ajax-response-message').addClass('text-red').html(errors.errors).fadeIn().fadeOut(3000);
        }
    });
}

function showOrganizationTable() {

    // disable datatables error prompt
    $.fn.dataTable.ext.errMode = 'none';

    if ($.fn.DataTable.isDataTable('#myTable')) {
        $('#myTable').DataTable().destroy();
    }




    var datatable = $('#myTable').DataTable({
        fixedHeader: true,
        info: false,
        processData: true,
        serverSide: true,
        aaSorting: [],
        oLanguage: {
            sEmptyTable: organization_js_var.no_data_available,
            sZeroRecords: organization_js_var.no_matching_records_found,
            oPaginate: {
                sFirst: organization_js_var.first_page, // This is the link to the first page
                sPrevious: organization_js_var.previous_page, // This is the link to the previous page
                sNext: organization_js_var.next_page, // This is the link to the next page
                sLast: organization_js_var.last_page // This is the link to the last page
            }
        },
        ajax: APP_URL + '/organization/getorganization',

        columns: [
            { data: 'logo', name: 'logo', class: 'prevent-overflow col-width-5', searchable: false },
            { data: 'company_name', name: 'company_name', class: 'prevent-overflow col-width-20' },
            { data: 'contact_name', name: 'contact_name', class: 'prevent-overflow col-width-10' },
            { data: 'mobile_number', name: 'mobile_number', class: 'prevent-overflow col-width-20' },
            { data: 'email', name: 'email', class: 'prevent-overflow col-width-10' },
            { data: 'seat_alloted', name: 'seat_alloted', class: 'prevent-overflow col-width-10', searchable: false },
            { data: 'account_type', name: 'account_type', class: 'prevent-overflow col-width-10' },
            { data: 'status', name: 'status', class: 'prevent-overflow col-width-10', searchable: false },
            { data: 'action', name: 'action', class: 'col-width-25', searchable: false },
            // { data: 'updated_at', name: 'updated_at' }
        ],
        columnDefs: [
            { orderable: false, targets: [-1, -2, -9] }
        ],
        "order": []
    });
    // let scrollCount = 0;
    // $('document').on('click','.table', function () {
    //     alert()
    //     $('html, body').animate({
    //         scrollTop: $(".dataTables_wrapper").offset().top
    //     }, 'slow');
    // });

    $('#datatable-search').keyup(function (event) {
        datatable.search($(this).val()).draw();
    })

    $('#datatable-length').change(function () {
        datatable.page.len($(this).val()).draw();
    });

}

function deleteOrganization(id) {
    var organization_id = $('#delete_id').val();
    $.ajax({
        type: "get",
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: APP_URL + "/organization/delete/" + organization_id,
        data: []
    }).done(function (response) {
        if (response.status === 200) {
            // $('span.ajax-response-message').addClass('text-green').html(response.message).fadeIn().fadeOut(3000);
            $('#delete__popup').removeClass('show');
            $('body').removeClass('overflow-hidden');
            showOrganizationTable();
            showNotification('success', organization_js_var.delete_successfully);

            // window.location = "/organization/index";
        }
    }).fail(function (response) {
        var errors = $.parseJSON(response.responseText);
        if (response.status === 422) {
            // $('span.ajax-response-message').addClass('text-red').html(errors.errors).fadeIn().fadeOut(3000);
            showNotification('warning', organization_js_var.something_wrong);
        }
    });


}

$('#organization_list').on('change', function () {
    $(".loader").show();
    var organization_id = $(this).val();
    organizationPermission(organization_id);
    $(".loader").hide();
});

function organizationPermission(organization_id) {
    $.ajax({
        type: "POST",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: 'organization-permission',
        data: { organization_id: organization_id },
        success: function (msg) {
            $("#loader").hide();
            $("#organization-permission-ajax").html(msg.html);
        }
    });
}

/**
 * Function retreives organzation key.
 *
 * @returns
 */
function getOrgKey(orgId) {
    $("#org-key").val('');
    if (orgId != 0) {
        $.ajax({
            dataType: 'json',
            type: 'GET',
            url: '/key/ajax/getKey/' + orgId,
            success: function (response) {
                console.log(response.data);
                $("#org-key").val(response.data);
            },
            error: function (response) {
                showNotification('warning', messages.SOMETHING_WENT_WRONG);
            }
        });
    }
}

function showOrganizationModal() {
    $.ajax({
        type: "POST",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: APP_URL + '/organization/create',
        data: {},
        success: function (msg) {
            $("#add_organization_partial").html(msg.html);
            create_custom_dropdowns()
            $('#add__popup').addClass('show');
        }
    });


}

