$(document).ready(function () {
    var organizationId = getSelectedOrganization();
    activeSideBar();
    var agentId = initDashboard();
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

        $('#dashboard-1').data('daterangepicker').setStartDate(startDate);
        $('#dashboard-1').data('daterangepicker').setEndDate(endDate);

        var id = $(this).val();

        $('#filter-agent-form').submit();
    });


    $('#dashboard-organization').on('change', function (e) {
        var organizationId = $(this).val();
        date = startDate + ' - ' + endDate;
        var data = {
            'organization_id': organizationId,
            'date': date
        };
        $.ajax({
            url: "dashboard/get-data",
            type: "GET",
            data: data,
            success: function (response) {
                if (response.status == true) {
                    $('#dashboard-container').html(response.data);
                    var agentId = initDashboard();
                    initFunctions(startDate, endDate, agentId, organizationId);
                    create_custom_dropdowns();
                } else {
                    $('#dashboard-container').html("No users");
                }
            }
        });
    });

    initFunctions(startDate, endDate, agentId, organizationId);

});

function getSelectedOrganization() {
    var organizationId = '';
    if ($('#dashboard-organization').length) {
        organizationId = $('#dashboard-organization').val();
    }
    return organizationId;
}

function initFunctions(startDate, endDate, agentId, organizationId) {
    showChatCountData(startDate, endDate, agentId, organizationId);
    showChatTerminationData(startDate, endDate, agentId, organizationId);
    showChatQueueData(startDate, endDate, agentId, organizationId);
    initSlick(); //for slick design 
}

//for slick design
function initSlick() {
    $('.responsive').slick({
        dots: true,
        prevArrow: $('.dprev'),
        nextArrow: $('.dnext'),
        infinite: false,
        speed: 300,
        slidesToShow: 5,
        slidesToScroll: 5,
        responsive: [
            {
                breakpoint: 1024,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 3,
                    infinite: true,
                    dots: true
                }
            },
            {
                breakpoint: 600,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 2
                }
            },
            {
                breakpoint: 480,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
            }
            // You can unslick at a given breakpoint now by adding:
            // settings: "unslick"
            // instead of a settings object
        ]
    });
}

function initDashboard() {
    $('#dashboard-1').daterangepicker({
        autoApply: true,
        locale: {
            format: 'DD-MM-YYYY',
        },
        maxDate: moment()
    });

    let params = (new URL(document.location)).searchParams;

    let agentId = params.get("agentIds");

    let date = params.get("date");
    if (date != null) {
        let splittedDate = date.split(" - ");
        startDate = splittedDate[0];
        endDate = splittedDate[1];
    }

    if (date == null) {
        date = new Date();
        date.setDate(date.getDate());
        endDate = date.toLocaleDateString("nl", {
            year: "numeric",
            month: "2-digit",
            day: "2-digit"
        });
        date.setDate(date.getDate() - 6);
        startDate = date.toLocaleDateString("nl", {
            year: "numeric",
            month: "2-digit",
            day: "2-digit"
        })
    }

    $('#dashboard-1').data('daterangepicker').setStartDate(startDate);
    $('#dashboard-1').data('daterangepicker').setEndDate(endDate);

    if (agentId == null) {
        agentId = $("#agent-id").val();
    }
    var organizationId = '';
    if (('#dashboard-organization').length) {
        organizationId = $('#dashboard-organization').val();
        if (typeof organizationId === "undefined") {
            organizationId = '';
        }
    }

    if ($('#dashboard-export').length) {

        var href = $('#dashboard-export').attr('href');
        href = href.slice(0, href.lastIndexOf('?'))
        href = href + '?agentIds=' + agentId;
        href = href + '&startDate=' + startDate;
        href = href + '&endDate=' + endDate;
        if (organizationId != '') {
            href = href + '&organizationId=' + organizationId;
        }
        $("#dashboard-export").attr('href', href);
    }

    return agentId;
}

/**
 * Function to load high chart.
 *
 * @param id
 * @param title
 * @param cats
 * @param data
 * @returns
 */
function openChart(id, title, cats, data, plotOptions) {
    if (!plotOptions) {
        plotOptions = {
            column: {
                pointPadding: 0,
                borderWidth: 0,
            }
        }
    }
    Highcharts.chart(id, {

        chart: {
            type: 'column'
        },

        title: {
            text: title
        },

        xAxis: {
            categories: cats
        },

        yAxis: {
            allowDecimals: false,
            min: 0,
            title: {
                text: ''
            }
        },

        tooltip: {
            formatter: function () {
                return '<b>' + this.x + '</b><br/>' +
                    this.series.name + ': ' + this.y;
            }
        },
        plotOptions: plotOptions,
        navigation: {
            buttonOptions: {
                enabled: false
            }
        },
        credits: {
            enabled: false
        },
        series: data
    });
}

/**
 * Function to show chat Count Data.
 *
 * @param days
 * @returns
 */
function showChatCountData(startDate, endDate, agentId, organizationId) {
    var data = {
        'organization_id': organizationId,
        'startDate': startDate,
        'endDate': endDate,
        'agentId': agentId,
        'parameter': 'chat_count'
    };

    $.ajax({
        dataType: 'json',
        type: 'GET',
        data: data,
        url: '/dashboard/chat-data',
        success: function (response) {
            id = 'container';
            title = dashbord_js_var.chat_report;
            categories = response.data.categories;
            data = [
                {
                    name: dashbord_js_var.no_of_chats,
                    data: response.data.data,
                    color: "rgba(90, 190, 255, 0.50)"
                }
            ];
            plotOptions = {
                column: {
                    pointPadding: 0,
                    borderWidth: 0,
                }
            };
            openChart(id, title, categories, data, plotOptions);
        }
    });
}

/**
 * Function to show chat termination data.
 *
 * @param days
 * @returns
 */
function showChatTerminationData(startDate, endDate, agentId, organizationId) {
    var data = {
        'organization_id': organizationId,
        'startDate': startDate,
        'endDate': endDate,
        'agentId': agentId,
        'parameter': 'termination_chat'
    };
    $.ajax({
        dataType: 'json',
        type: 'GET',
        data: data,
        url: '/dashboard/chat-data',
        success: function (response) {
            data = [
                {
                    name: dashbord_js_var.by_visitor,
                    data: response.data.terminationByVisitor.data,
                    color: "#418bca",
                    marker: {
                        enabled: true,
                    }
                },
                {
                    name: dashbord_js_var.by_agent,
                    data: response.data.terminationByAgent.data,
                    color: "#21c998"
                }];
            openChart('container-1', dashbord_js_var.chat_termination, response.data.terminationByAgent.categories, data);

        }
    });
}

/**
 * Function to draw queue data on dashboard.
 *
 * @param days
 * @returns
 */
function showChatQueueData(startDate, endDate, agentId, organizationId) {
    var data = {
        'organization_id': organizationId,
        'startDate': startDate,
        'endDate': endDate,
        'agentId': agentId,
        'parameter': 'queued_chat'
    };

    $.ajax({
        dataType: 'json',
        type: 'GET',
        data: data,
        url: '/dashboard/chat-data',
        success: function (response) {

            data = [{
                name: dashbord_js_var.queued_visitor,
                data: response.data.queuedVisitor.data,
                color: "#418bca"

            }, {
                name: dashbord_js_var.entered_chat,
                data: response.data.enteredChat.data,
                color: "#21c998"

            },
            {
                name: dashbord_js_var.left_the_queue,
                data: response.data.queuedLeft.data,
                color: "#fd7e15"

            }
            ];
            openChart('container-2', dashbord_js_var.chats_in_queue, response.data.enteredChat.categories, data);
        }
    });
}

function getChatCount() {
    $("#availability").hide();
    $("#awaitingchat").hide();
    $("#activechat").hide();
    $("#chat").show();
}

function getOnlineDuration() {
    $("#availability").show();
    $("#awaitingchat").hide();
    $("#activechat").hide();
    $("#chat").hide();
}

function getAwaitingChatCount() {
    $("#availability").hide();
    $("#awaitingchat").show();
    $("#activechat").hide();
    $("#chat").hide();
}

function getActiveChatCount() {
    $("#availability").hide();
    $("#awaitingchat").hide();
    $("#activechat").show();
    $("#chat").hide();
}

/**
 * to show timer.
 * 
 * @param callback
 * @returns
 */
function _timer(callback) {

    var time = 0;     //  The default time of the timer

    var mode = 1;     //    Mode: count up or count down

    var status = 0;    //    Status: timer is running or stopped

    var timer_id;    //    This is used by setInterval function



    // this will start the timer ex. start the timer with 1 second interval timer.start(1000)

    this.start = function (interval) {
        if (time == 0 || time == undefined || time == null) {
            // var time=100;
            // debugger
            console.log($('.second').html())
            console.log($('.minute').html())
            console.log($('.hour').html())
            // console.log("second :" +$('.second').html() +"Minute :"+ $('.minute').html() + "Hour :"+$('.hour').html());

        }

        interval = (typeof (interval) !== 'undefined') ? interval : 1000;

        if (status == 0) {
            status = 1;

            timer_id = setInterval(function () {
                switch (mode) {
                    default:
                        if (time) {
                            time--;
                            generateTime();
                            if (typeof (callback) === 'function') callback(time);
                        }
                        break;
                    case 1:
                        if (time < 86400) {
                            time++;
                            generateTime();
                            if (typeof (callback) === 'function') callback(time);
                        }
                        break;
                }
            }, interval);
        }
    }
    //  Same as the name, this will stop or pause the timer ex. timer.stop()
    this.stop = function () {

        if (status == 1) {

            status = 0;

            clearInterval(timer_id);

        }

    }

    // Reset the timer to zero or reset it to your own custom time ex. reset to zero second timer.reset(0)
    this.reset = function (sec) {

        sec = (typeof (sec) !== 'undefined') ? sec : 0;

        time = sec;

        generateTime(time);

    }



    // Change the mode of the timer, count-up (1) or countdown (0)

    this.mode = function (tmode) {

        mode = tmode;

    }



    // This methode return the current value of the timer

    this.getTime = function () {

        return time;

    }



    // This methode return the current mode of the timer count-up (1) or countdown (0)

    this.getMode = function () {

        return mode;

    }



    // This methode return the status of the timer running (1) or stoped (1)

    this.getStatus

    {

        return status;

    }



    // This methode will render the time variable to hour:minute:second format

    function generateTime() {
        // time=100;

        var second = time % 60;

        var minute = Math.floor(time / 60) % 60;

        var hour = Math.floor(time / 3600) % 60;



        second = (second < 10) ? '0' + second : second;

        minute = (minute < 10) ? '0' + minute : minute;

        hour = (hour < 10) ? '0' + hour : hour;



        $('div.timer span.second').html(second);

        $('div.timer span.minute').html(minute);

        $('div.timer span.hour').html(hour);

    }

}



// example use

var timer;



$(document).ready(function (e) {

    timer = new _timer

        (

            function (time) {

                if (time == 0) {

                    timer.stop();

                    alert('time out');

                }

            }

        );

    var s = $('.second').html();
    var m = $('.minute').html() * 60;
    var h = $('.hour').html() * 3600;
    time = parseInt(s) + parseInt(m) + parseInt(h);
    timer.reset(time);
    timer.mode(1);
    //
    // $.get("user/check-online", function(data, status){
    //     console.log(data);
    //     console.log(status);
    // });

    // alert("Data: " + data + "\nStatus: " + status);
    $.ajax({
        url: "dashboard/check-online",
        type: "GET",
        dataType: 'json',

        success: function (response) {
            var user = {
                'name': response.name,
                'role_id': response.role_id
            };

            localStorage.setItem('currentUserInfo', JSON.stringify(user));
            if (response.online_status) {
                timer.start(1000);
            }
        }
    });

});

function dateRangeKeyDown(event) {
    if (event.keyCode == 8) {
        $("#dashboard-1").val('');

        $('#dashboard-1').daterangepicker({
            autoApply: true,
            locale: {
                format: 'DD-MM-YYYY',
            },
            maxDate: moment()
        });
        $("#dashboard-1").val("dd-mm-yyyy - dd-mm-yyyy");
    }

    event.preventDefault();
}


function dateRangeKeyDownUserLogging(event) {
    if (event.keyCode == 8) {
        $("#dashboard-7").val('');

        $('#dashboard-7').daterangepicker({
            autoApply: true,
            locale: {
                format: 'DD-MM-YYYY',
            },
            maxDate: moment()
        });
        $("#dashboard-7").val("dd-mm-yyyy - dd-mm-yyyy");
    }

    event.preventDefault();
}



function dateRangeKeyDownUserHistory(event) {
    if (event.keyCode == 7) {
        $("#dashboard-7").val('');

        $('#dashboard-7').daterangepicker({
            autoApply: true,
            locale: {
                format: 'DD-MM-YYYY',
            },
            maxDate: moment()
        });
        $("#dashboard-7").val("dd-mm-yyyy - dd-mm-yyyy");
    }

    event.preventDefault();
}


