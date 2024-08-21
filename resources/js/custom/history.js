$(document).ready(function () {
    var startDate;
    var endDate;
    var startDateUser;
    var endDateUser;

        $('#dashboard-7').daterangepicker({
            startDate: moment().subtract('days', 14),

            autoApply : true,
        locale: {
            format: 'DD-MM-YYYY',
        },
        maxDate : moment().subtract(0, "days")
    });

        startDateUser = $.session.get("startDate", startDate);
        endDateUser =  $.session.get("endDate", endDate);
        $('#dashboard-8').daterangepicker({
            autoApply : true,
            locale: {
                format: 'DD-MM-YYYY',
            },
            maxDate : moment().subtract(0, "days"),
            startDate: startDateUser, endDate: endDateUser
        });


    selectedDateRange();
    getDateVal();

	$('#select-agents').change(function () {

	    $('#filter-agent-form').submit();
    });
    showUsersTable();
    showUserTable();
    $(".custom-input-search").val("");
    
    

});
$('body').delegate(".archive_chat",'click',function(){        
        var startDate = $(this).attr('data-startdate');
        var endDate   = $(this).attr('data-enddate');
        
        $.session.set("startDate", startDate);
        $.session.set("endDate", endDate); 
        
        $.session.set("startTime", $(this).attr('data-starttime'));
        $.session.set("endTime", $(this).attr('data-endtime')); 
        
        window.location = "/chat/archive";
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
function showUsersTable() {
    if ($.fn.DataTable.isDataTable('#loginHistoryTable')) {
        $('#loginHistoryTable').DataTable().ajax.reload(); 
    }
    length = $('div.select-custom-length span.current').text();
    // disable datatables error prompt
    $.fn.dataTable.ext.errMode = 'none';

    var datatable = $('#loginHistoryTable').DataTable({
        fixedHeader: true,
        info: false,
        processData: true,
        serverSide: true,
        'oLanguage': {
            sEmptyTable: history_js_var.no_data_available,
            sZeroRecords : history_js_var.no_matching_records_found,
            oPaginate : {
                sFirst : history_js_var.first_page, // This is the link to the first page
                sPrevious : history_js_var.previous_page, // This is the link to the previous page
                sNext : history_js_var.next_page, // This is the link to the next page
                sLast : history_js_var.last_page // This is the link to the last page
            }
        },
        ajax: {
            url: '/history/get-users-login-history',
            data: function (d) {
                d.organization_id = $('#select-organization').val();
                d.startDate = startDate;
                d.endDate = endDate;
                d.length = length;
                
                
            }
        },
        columns: [
            {data: 'name', name: 'name', class: 'prevent-overflow col-width-15'},
            {data: 'role_name', name: 'role_name', class: 'prevent-overflow col-width-10'},
            {data: 'login_count', name: 'login_count', class: 'prevent-overflow col-width-10'},
            {data: 'duration', name: 'duration', class: 'prevent-overflow col-width-10'},
            {data: 'chat_count', name: 'chat_count', class: 'prevent-overflow col-width-10'},
            {data: 'blocked_clients', name: 'blocked_clients', class: 'prevent-overflow col-width-10'},
            {data: 'last_login', name: 'last_login', class: 'prevent-overflow col-width-20'},
        ],
        "order": []
    });
    $('#select-organization').on('change', function (e) {
        datatable.draw();
    });


    $('#datatable-search').keyup(function () {
        datatable.search($(this).val()).draw();
    });

    $('#datatable-length').change(function () {
        length = $('div.select-custom-length span.current').text();
        datatable.page.len(length).draw();
    });
    
}

/**
 * Function to show User table.
 * 
 * @returns
 */
function showUserTable() {

    if ($.fn.DataTable.isDataTable('#userLoginHistoryTable')) {
        $('#userLoginHistoryTable').DataTable().ajax.reload(); 
    }

    length = $('div.select-custom-length span.current').text();
    // disable datatables error prompt
    $.fn.dataTable.ext.errMode = 'none';

    var datatable = $('#userLoginHistoryTable').DataTable({
        fixedHeader: true,
        info: false,
        processData: true,
        serverSide: true,
        'oLanguage': {
            sEmptyTable: history_js_var.no_data_available,
            oPaginate : {
                sFirst : history_js_var.first_page, // This is the link to the first page
                sPrevious : history_js_var.previous_page, // This is the link to the previous page
                sNext : history_js_var.next_page, // This is the link to the next page
                sLast : history_js_var.last_page // This is the link to the last page
            }
        },
        ajax: {
            url: '/history/get-user-login-history',
            data: function (d) {
                 d.user_id = $('input[name=user_id]').val();
                d.startDate = startDateUser;
                d.endDate = endDateUser;
                d.length = length;
            }
        },

        columns: [
            {data: 'ip_detail', name: 'ip_detail', class: 'prevent-overflow col-width-10'},
            {data: 'device_detail', name: 'device_detail', class: 'prevent-overflow col-width-10'},
            {data: 'login_time', name: 'login_time', class: 'prevent-overflow col-width-20'},
            {data: 'logout_time', name: 'logout_time', class: 'prevent-overflow col-width-20'},
            {data: 'duration', name: 'duration', class: 'prevent-overflow col-width-10'},
            {data: 'chat_count', name: 'chat_count', class: 'prevent-overflow col-width-10',searchable: false,orderable: false},
            {data: 'blocked_clients', name: 'blocked_clients', class: 'prevent-overflow col-width-10',searchable: false},
        ],
        "order": [3,"desc"]
    }); 
    $('#datatable-search').keyup(function () {
        datatable.search($(this).val()).draw();
    });

    $('#datatable-length').change(function () {
        length = $('div.select-custom-length span.current').text();
        datatable.page.len(length).draw();
    });
    
}

function dateRangeKeyDownUserLogging(event) {
    if (event.keyCode == 8) {
        $("#dashboard-7").val('');

        $('#dashboard-7').daterangepicker({
            autoApply : true,
            locale: {
                format: 'DD-MM-YYYY',
            },
            maxDate : moment()
        });
        $("#dashboard-7").val("dd-mm-yyyy - dd-mm-yyyy");
    }

    event.preventDefault();
}



function dateRangeKeyDownUserHistory(event) {
    if (event.keyCode == 7) {
        $("#dashboard-8").val('');

        $('#dashboard-8').daterangepicker({
            autoApply : true,
            locale: {
                format: 'DD-MM-YYYY',
            },
            maxDate : moment()
        });
        $("#dashboard-8").val("dd-mm-yyyy - dd-mm-yyyy");
    }

    event.preventDefault();
}

function selectedDateRange(){
    if(window.location.pathname == "/history"){
        startDate = $('#dashboard-7').data('daterangepicker').startDate.format('DD-MM-YYYY');
        endDate = $('#dashboard-7').data('daterangepicker').endDate.format('DD-MM-YYYY');
        $.session.set("startDate", startDate);
        $.session.set("endDate", endDate);
        showUsersTable();
    }else{
        startDateUser = $('#dashboard-8').data('daterangepicker').startDate.format('DD-MM-YYYY');
        endDateUser = $('#dashboard-8').data('daterangepicker').endDate.format('DD-MM-YYYY');
        showUserTable();
        console.log(startDate);
        console.log(endDate);
    }

}

function getDateVal(){
    if(startDateUser &&  endDateUser){
        startDateUser = $.session.get("startDate", startDate);
        endDateUser =  $.session.get("endDate", endDate);
        showUserTable();
    }
}
