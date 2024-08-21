$(document).ready(function () {
    $(".menu_nav_translator").hide();

    $('#nav li').hover(
        function (event) {
            $('ul', this).slideDown("fast");
        }, function () {
            $('ul', this).slideUp("fast");
        });

    $("select").change(function () {
        if (this.value == '' || this.value == "null") {
            $(".menu_nav_translator").hide();
        } else {
            $(".menu_nav_translator").show();
            getOrgLanguageList();
        }
    }).change();

});