const $ = require('jquery');

import {
    showLoader,
    hideLoader,
    initSelect2,
    initNumbersRange,
} from '../app.js';

$(document).ready(function () {
    $(document)
        /** *********************************************
         * triggers initializing
         ***********************************************/
        .on('change', '.js-items-component .js-hidden-fields-container :input', function() {
            $(this)
                .closest('.js-items-component')
                .trigger('listRebuild');
        })
        .on('change', '.js-items-component .js-filter-container :input', function() {
            let $component = $(this).closest('.js-items-component');

            $component
                .find('.js-list-container :input')
                .val(1);
            $component
                .trigger('listRebuild');
        })
        .on('change', '.js-items-component .js-list-container :input', function() {
            $(this)
                .closest('.js-items-component')
                .trigger('listRebuild');
        })
        /** *********************************************
         * list rebuild
         ***********************************************/
        .on('listRebuild', '.js-items-component', function() {
            let
                $list       = $(this),
                formData    = $list
                    .find(':input')
                    .not('[disabled]')
                    .serializeArray();

            showLoader();
            $.ajax({
                type    : 'POST',
                url     : $list.attr('data-rebuild-list-url'),
                data    : formData,
                success : function(response) {
                    if (response.hasOwnProperty('itemsList')) {
                        $list
                            .find('.js-list-container')
                            .html(response.itemsList);
                    }
                    if (response.hasOwnProperty('url')) {
                        $(document).trigger('setUrl', response.url);
                    }

                    hideLoader();
                    initSelect2();
                    initNumbersRange();
                },
                error: function() {
                    hideLoader();
                }
            });
        });
});
