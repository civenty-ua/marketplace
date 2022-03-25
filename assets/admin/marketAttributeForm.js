const $                 = require('jquery');
const {PopupWindowForm} = require('../modals');

$(document).ready(function() {
    $('.ea-edit-form[name="Attribute"]')
        .on('submit', function(event) {
            let
                $form               = $(this),
                $usageBar           = $form.parent().find('.market-attribute-edit-usage-bar'),
                submitAllowed       = $form.attr('data-submit-allowed') === 'Y',
                needConfirmPopup    = $usageBar.length > 0,
                popupConfirmMessage = $usageBar.attr('data-submit-confirm-message'),
                $popup              = new PopupWindowForm('', 'warning', popupConfirmMessage, false, {
                    showConfirmButton   : true,
                    showCancelButton    : true,
                });

            if (submitAllowed || !needConfirmPopup) {
                return;
            }

            event.preventDefault();
            $form.trigger('rejectSubmit');

            $popup.show();
            $popup.on('onConfirm', function() {
                let
                    $actionButton   = $(event.originalEvent.submitter),
                    $actionInput    = $('<input>')
                        .attr('type', 'hidden')
                        .attr('name', $actionButton.attr('name'))
                        .attr('value', $actionButton.attr('value'));

                $form
                    .append($actionInput)
                    .trigger('allowSubmit')
                    .trigger('submit');
            });
            $popup.on('onDismiss', function() {
                $form.trigger('normalizeActionsButtons');
            });
            $popup.on('onHide', function() {
                $form.trigger('normalizeActionsButtons');
            });
        })
        .on('allowSubmit', function() {
            $(this).attr('data-submit-allowed', 'Y');
        })
        .on('rejectSubmit', function() {
            $(this).removeAttr('data-submit-allowed');
        })
        .on('normalizeActionsButtons', function() {
            $(document)
                .find('.page-actions button[disabled]')
                .removeAttr('disabled');
        });
});
