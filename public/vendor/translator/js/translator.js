$(document).ready(function () {
    $(".menu_nav_translator").hide();

    $('#nav li').hover(
        function (event) {
            $(this).addClass('active');
            // $('ul', this).slideDown("fast");
        }, function (event) {
            $(this).removeClass('active');
            // $('ul', this).slideUp("fast");
        });

    $("select").change(function () {
        if (this.value == '' || this.value == "null") {
            $(".menu_nav_translator").hide();
        } else {
            $(".menu_nav_translator").show();
            //getOrgLanguageList();
        }
    }).change();

});