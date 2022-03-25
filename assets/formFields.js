const $ = require('jquery');

import {PopupWindowForm} from './modals';

$(document).ready(function () {
    /** *********************************************
     * Image upload input.
     ***********************************************/
    $(document).on('change', '.js-image-upload-input input', function(event) {
        let
            $uploader               = $(this).closest('.js-image-upload-input'),
            $image                  = $uploader.find('img'),
            $input                  = $uploader.find('input'),
            sizeLimit               = parseInt($input.attr('data-size-limit') ?? 0),
            sizeLimitMessage        = $input.attr('data-size-limit-message'),
            allowMimeTypes          = $input.attr('data-allow-mime-types').split(','),
            mimeTypesLimitMessage   = $input.attr('data-allow-mime-type-message'),
            reader                  = new FileReader(),
            stopFileUploading       = function() {
                $input.val('');

                if ($uploader.hasClass('required')) {
                    $input.attr('required', true);
                }
            },
            fireAlertPopup          = function(message) {
                (new PopupWindowForm('', 'warning', message, false, {
                    showConfirmButton   : false,
                    showCancelButton    : false,
                })).show();
            };

        $uploader
            .removeClass('fill')
            .removeClass('pdf-uploaded');
        $input.removeAttr('required');
        $image.removeAttr('src');

        if (event.target.files.length === 0) {
            stopFileUploading();
            return;
        }

        if (sizeLimit > 0 && event.target.files[0].size > sizeLimit) {
            stopFileUploading();
            fireAlertPopup(sizeLimitMessage);
            return;
        }

        if (allowMimeTypes.length > 0 && !allowMimeTypes.includes(event.target.files[0].type)) {
            stopFileUploading();
            fireAlertPopup(mimeTypesLimitMessage);
            return;
        }

        reader.onload = function() {
            let mimeType = event.target.files[0].type.split('/')[1];

            $uploader.addClass('fill');

            if (mimeType === 'pdf' || mimeType === 'x-pdf')  {
                $uploader.addClass('pdf-uploaded');
            } else {
                $image.attr('src', reader.result);
            }
        };
        reader.readAsDataURL(event.target.files[0]);
    });
    $(document).on('click', '.js-image-upload-input img', function() {
        $(this)
            .closest('.js-image-upload-input')
            .find('input')
            .click();
    });
    /** *********************************************
     * Fake radio-buttons.
     ***********************************************/
    $(document).on('click', '.js-fake-radio-button', function() {
        let
            $input          = $(this).find('input'),
            value           = $input.attr('data-value'),
            name            = $input.attr('name'),
            $smaInputs      = $(`.js-fake-radio-button input[name="${name}"]`),
            $checkedInputs  = $smaInputs.filter(`[data-value="${value}"]`);

        $smaInputs
            .attr('value', '')
            .attr('disabled', true)
            .removeAttr('data-checked', '');
        $checkedInputs
            .attr('value', value)
            .removeAttr('disabled')
            .attr('data-checked', 'Y');
        $input.trigger('change');
    });
});
