$(document).ready(function () {
    // for admin
    var organization_id = $('#select-organization').val();
    if (organization_id) {
        $(".custom-input-search").val("");
    }
    
});

function showCustomizeFieldTable(language_feature, language_type) {
    if ($.fn.DataTable.isDataTable('#customize-field-table')) {
        $('#customize-field-table').DataTable().destroy();
    }
    // disable datatables error prompt
    $.fn.dataTable.ext.errMode = 'none';
    
    var columns = [
        {data: 'default', name: 'default', class: 'prevent-overflow col-width-20',searchable:true}
    ];
    var obj = $.parseJSON(lang_table_columns);
    $.each(obj, function (i, item) {
        columns.push(item);
    });
    columns.push({data: 'action', name: 'action', class: 'prevent-overflow col-width-20', searchable: false});
    
    var datatable = $('#customize-field-table').DataTable({
        fixedHeader: true,
        info: false,
        processData: true,
        serverSide: true,
        ajax: {
            url: 'get-lan-data',
            data: function (d) {
                d.organization_id = $('#select-organization').val();
                d.language_feature = language_feature;
                d.language_type = language_type;
            }
        },
        columns: columns,
        columnDefs: [
            {orderable: false, targets: [-1]}
        ],
        "order": [],
    });
    datatable.draw();
    $('#select-organization').on('change', function (e) {
        datatable.draw();
        e.preventDefault();
    });


    $('#datatable-search').keyup(function () {
        datatable.search($(this).val()).draw();
    });

    $('#datatable-length').change(function () {
        datatable.page.len($(this).val()).draw();
    });

}

$(document).on('click', '#translator-language-popup', function (event) {
    
    var language_type = $(this).data('language-type');
    var language_file = $(this).data('language-file');
    var language_slug = $(this).data('slug');
    var organization_id = $('#select-organization').val();

    $.ajax({
        type: "get",
        url: "get-translator-data",
        data: {
            "language_type" : language_type,
            "language_file" : language_file,
            "language_slug" : language_slug,
            "organization_id" : organization_id
        },
        dataType: 'json',
        //processData: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
    }).done(function (response) {
        if (response.status === true) {

            $("#translator_edit_partial").html(response.html);
            $('#translator_edit__popup').addClass('show');
            $(".permission_error").addClass('hidden');
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

$(document).on('submit', '#translator_edit_form', function (event) {
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
    
    var language_feature = data.get("feature");
    var language_type = data.get("type");
    
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
            $('#translator_edit__popup').removeClass('show');
            $('body').removeClass('overflow-hidden');
            showNotification('success', response.message);
            refreshCustomizeFieldTable();
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


$(document).on('click', '.translator-features', function (event) {
    var language_feature = $(this).data('feature');
    var language_type = $(this).data('type');
    getOrgLanguageList(language_feature, language_type);
    
});


function getOrgLanguageList(language_feature, language_type)
{
    var organization_id = $('#select-organization').val();
    if (organization_id==null || organization_id=='' || organization_id=='null') { return false;}
    $.ajax({
        type: "get",
        url: "get-org-languages",
        data: {
            "organization_id" : organization_id
        },
        dataType: 'json',
        //processData: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
    }).done(function (response) {
        if (response.status === true) {
            $("#language-list-table").html(response.html);
        }
        showCustomizeFieldTable(language_feature, language_type);
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

function refreshCustomizeFieldTable()
{
    setTimeout(function(){
        $('#customize-field-table').DataTable().ajax.reload(null, false);
    }, 3000); 
}
