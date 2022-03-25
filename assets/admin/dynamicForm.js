const $ = require('jquery');

$(document).ready(function () {
    $('.ea-new-form, .ea-edit-form').on('change', '[data-rebuild-form]', function () {
        let
            $form       = $(this).closest('form'),
            formData    = $(this)
                .closest('form')
                .find(':input')
                .not('[disabled]')
                .serializeArray();

        $.ajax({
            type    : 'POST',
            url     : $form.attr('data-refresh-form-url'),
            data    : formData,
            success : function(response) {
                let $newForm = $(response.form).find('.ea-new-form, .ea-edit-form, .ea-refreshForm-form');

                $form.html($newForm.children());
            },
            error: function() {

            }
        });
    });
});
