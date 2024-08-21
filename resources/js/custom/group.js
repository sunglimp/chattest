$(document).ready(function () {

    $(document).on('click', '#add_group_button', function (event) {
        addGroup();
    });

});
function addGroup() {
    var name = $("#add_group").val();
    var organization_id = $('#organization_list').val();
    // Request.
    $.ajax({
        type: "POST",
        url: "/group/store",
        data: { 'name': name, 'organization_id': organization_id },
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
    }).done(function (response) {
        if (response.status === 200) {
            $('#add_group').val('');
            $('#add_group').focus();

            $('#group_ul').append('<li class=tag-purple id=li' + response.data.id + '><span>' + response.data.name + '</span> <i class="fas fa-times" onclick=deleteGroup(' + response.data.id + ')></i></li>');
            resetFormErrors();
        }
    }).fail(function (response) {
        // Check for errors.
        if (response.status === 422) {
            var validation = $.parseJSON(response.responseText);
            if (validation.common === undefined) {
                errors = validation.errors;
                $.each(errors, function (field, message) {
                    var formGroup = $('[name=' + field + ']', '.popup__container').closest('.popup__permissions--addtags');
                    formGroup.next('.warning-text').text(message);
                });
            } else {
                if (validation.common === true) {
                    $('span.response-message').addClass('text-red').text(validation.errors).fadeIn().fadeOut(3000);
                }
            }

        }
    });
}
function deleteGroup(id) {
        $.ajax({
            type: "get",
            dataType: 'json',
            url: '/group/delete/' + id,
            data: []
        }).done(function (response) {
            if (response.status === 200) {
                $('#li' + id).remove();
            } else {
                showNotification('warning', response.message);
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
 * @todo remove after adding in common
 */
/*function resetFormErrors() {
    $('.popup__permissions--addtags').next('.warning-text').empty();
}*/
