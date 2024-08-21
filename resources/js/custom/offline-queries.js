$(document).ready(function () {

    var startDate;
    var endDate;
    var status = "";

    $('#dashboard-7').daterangepicker({
        startDate: moment().subtract('days', 14),

        autoApply: true,
        locale: {
            format: 'DD-MM-YYYY',
        },
        maxDate: moment().subtract(0, "days")
    });
    selectedDateRange();
    $(".custom-input-search").val("");
    //Click on 7, 15, 30 day tab
    $('.select-days').click(function () {
        var id = $(this).val();
        date = new Date();
        endDate = date.toLocaleDateString("nl", {
            year: "numeric",
            month: "2-digit",
            day: "2-digit"
        });

        date.setDate(date.getDate() - id + 1);
        startDate = date.toLocaleDateString("nl", {
            year: "numeric",
            month: "2-digit",
            day: "2-digit"
        })

        $('#dashboard-7').data('daterangepicker').setStartDate(startDate);
        $('#dashboard-7').data('daterangepicker').setEndDate(endDate);
        $('.custom-input-searchtable').val('');
        // resetStatus();
        selectedDateRange();
    });
});

/**
 * Function to show User table.
 *
 * @returns
 */
function showOfflineQueriesTable() {

    if ($.fn.DataTable.isDataTable('#offline-query-table')) {
        $('#offline-query-table').DataTable().destroy();
    }
    // disable datatables error prompt
    $.fn.dataTable.ext.errMode = 'none';

    var obj = $.parseJSON(table_columns);
    var columns = [];
    $.each(obj, function (i, item) {
        columns.push(item);
    });

    var datatable = $('#offline-query-table').DataTable({
        fixedHeader: true,
        info: false,
        processData: true,
        serverSide: true,
        'oLanguage': {
            sEmptyTable: offline_queries_js_var.no_data_available,
            sZeroRecords: offline_queries_js_var.no_matching_records_found,
            oPaginate: {
                sFirst: offline_queries_js_var.first_page, // This is the link to the first page
                sPrevious: offline_queries_js_var.previous_page, // This is the link to the previous page
                sNext: offline_queries_js_var.next_page, // This is the link to the next page
                sLast: offline_queries_js_var.last_page // This is the link to the last page
            }
        },
        ajax: {
            url: '/chat/get-offline-queries',
            data: function (d) {
                d.start_date = startDate;
                d.end_date = endDate;
                d.status = status;
            }
        },
        columns: columns,
        /*columns: [
            {data: 'group_id', name: 'group_id', class: ''},
            {data: 'source_type', name: 'source_type', class: ''},
            {data: 'mobile', name: 'mobile', class: ''},
            {data: 'client_query', name: 'client_query', class: ''},
            {data: 'created_at', name: 'created_at', class: ''},
            {data: 'action', name: 'action', class: '', searchable: false},
        ],*/
        columnDefs: [

        ],
        "order": [[5, "desc"]]
    });



    $('#select-organization').on('change', function (e) {
        datatable.draw();
    });


    $('#datatable-search').keyup(function () {
        datatable.search($(this).val()).draw();
    });

    $('#datatable-length').change(function () {
        datatable.page.len($(this).val()).draw();
    });
}

$(document).on('click', '#confirm-offline-action', function (event) {
    var request_id = $(this).data('id');
    var url = $(this).data('url');
    var template_id = $('select#whatsapp-template').val();
    $.ajax({
        type: "post",
        url: url,
        data: { 'request_id': request_id, 'template_id': template_id ? template_id.split('_').join(' ') : template_id, 'is_free_push': is_free_push },
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
    }).done(function (response) {
        if (response.status === 200) {
            showOfflineQueriesTable();
            $('#confirm-offline-popup').removeClass('show');
            $('body').removeClass('overflow-hidden');
            showNotification('success', response.message);
        }
    }).fail(function (response) {
        // Check for errors.
        if (response.status === 422) {
            showOfflineQueriesTable();
            $('#confirm-offline-popup').removeClass('show');
            $('body').removeClass('overflow-hidden');
            showNotification('warning', response.responseJSON.message);
        }
    });
});

$(document).on('click', '.offline-query-action', function (event) {
    var request_id = ($(this).attr('data-id'));
    var url = $(this).data('url');
    var text = $(this).data('text');
    openOfflineConfirmPopup(text, request_id, url);
});

function openOfflineConfirmPopup(text, request_id, url) {
    $('#confirm-offline-action').data('id', request_id);
    $('#confirm-offline-action').data('url', url);
    $('#popup-cinfirm-text').text(text);
    $('#popup__wrapper').focus();
    $('body').addClass('overflow-hidden');
    $('#confirm-offline-popup').addClass('show');
}

var is_free_push;

function getTemplates(id) {
    $('#whatsapp-template-container').removeClass('hide');
    $.ajax({
        type: "get",
        url: "whatsapp-templates",
        data: { 'request_id': id },
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
    }).done(function (response) {
        if (response.status === 200) {
            is_free_push = response.is_free_push;

            if (response.is_free_push_time_over === 1) {
                $('#is_free_push_time_over_old').show();
                $('#is_free_push_time_over_new').hide();
            } else {
                $('#is_free_push_time_over_old').hide();
                $('#is_free_push_time_over_new').show();
            }

            var wp_template_option = '';
            if (response.templates && response.templates.length > 0) {
                response.templates.forEach(templateId => {
                    wp_template_option += '<option value="' + templateId.split('\n').join(' ').split(' ').join('_') + '">' + templateId + '</option>';
                });
            } else {
                wp_template_option = '<option selected="true" disabled="disabled" value="">No data</option>';
            }
            $('#whatsapp-template').html(wp_template_option);
            return;
        }
    }).fail(function (response) {
        // Check for errors.
        if (response.status === 422) {
            $('#confirm-offline-popup').removeClass('show');
            $('body').removeClass('overflow-hidden');
            showNotification('warning', response.responseJSON.message);
        }
    });
}

function getAddclassHide() {
    $('#whatsapp-template-container').addClass('hide');
    $('select#whatsapp-template').val('');
    $('#is_free_push_time_over_new').show();
    is_free_push = undefined;
}



function selectedDateRange() {
    startDate = $('#dashboard-7').data('daterangepicker').startDate.format('DD-MM-YYYY');
    endDate = $('#dashboard-7').data('daterangepicker').endDate.format('DD-MM-YYYY');
    status = !$('.current-val').val() ? '' : $('.current-val').val();
    $('.custom-input-searchtable').val('');
    showOfflineQueriesTable();
}

function openDropdown(event) {

    if (!$('.dropdown').hasClass('open')) {
        $('.dropdown').addClass('open');
    }
}

$(document).on('click', '.dropdown ul li', function (event) {
    $('.current-val').val($(this).data('val'));
    status = $(this).data('val');
    $('.dropdown span.current').text($(this).text());
    if ($('.dropdown').hasClass('open')) {
        $('.dropdown').removeClass('open');
    }
});

$(document).on('click', '.content__wrapper, header, .submit-btn-ctr, .input-ctr, .right-filter ', function (event) {
    if ($('.dropdown').hasClass('open')) {
        $('.dropdown').removeClass('open');
    }
});

function resetStatus() {
    $('.current-val').val('');
    $('.custom-input-searchtable').val('');
    $('.dropdown span.current').text($('.dropdown ul li:first-child').text());
}


$(document).on('click', '#offline-export', function (event) {
    event.preventDefault();
    const url = "/api/v1/offlineQueries/download?start_date=" + startDate + "&end_date=" + endDate + "&status=" + status;

    $.ajax({
        type: "GET",
        headers: {
            'Authorization': 'Bearer ' + accessToken
        },
        url: url,
        dataType: 'json',
    }).done(function (response) {
        if (response.status) {
            showNotification('success', response.message);
        } else {
            showNotification('warning', response.message);
        }
    }).fail(function (response) {
        console.log(res);
        if (response.status) {
            showNotification('success', response.message);
        } else {
            showNotification('warning', response.message);
        }
    });
});