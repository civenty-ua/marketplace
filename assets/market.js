import window from "inputmask/lib/global/window";

const $ = require('jquery');

import {
    showLoader,
    hideLoader,
    initSelect2,
    initNumbersRange
} from './app.js';

import {PopupWindowForm} from './modals';

$(document).ready(function () {
    /** *********************************************
     * Set URL
     ***********************************************/
    $(document).on('setUrl', function (event, data) {
        window.history.pushState(
            {},
            $(document).find('title').text(),
            data
        );
    });
    /** *********************************************
     * User partly hidden field
     ***********************************************/
    $(document).on('click', '.market-user-partly-hidden-field', function () {
        let $field = $(this);

        showLoader();
        $.ajax({
            type: 'POST',
            url: $field.attr('data-url'),
            data: {
                'userId': $field.attr('data-user'),
                'field': $field.attr('data-field'),
                'value': $field.attr('data-value'),
            },
            success: function (response) {
                if (response.value) {
                    $field
                        .find('.value-cell')
                        .text(response.value);
                    $field
                        .find('.show-full-link')
                        .remove();
                }

                hideLoader();
            },
            error: function () {
                hideLoader();
            }
        });
    });
    /** *********************************************
     * User commodities tabs
     ***********************************************/
    $('.js-market-user-commodities-tabs')
        .on('click', '.tabs__link', function () {
            let
                $tab = $(this),
                commodityType = $tab.attr('data-commodity-type'),
                $tabsBlock = $tab.closest('.js-market-user-commodities-tabs');

            if ($tab.hasClass('selected')) {
                return;
            }

            showLoader();
            $.ajax({
                type: 'POST',
                url: $tabsBlock.attr('data-tab-rebuild-url'),
                data: {
                    commodityType: commodityType,
                },
                success: function (response) {
                    $tabsBlock
                        .find(`.tab[data-commodity-type="${commodityType}"]`)
                        .html(response.itemsList)
                        .trigger('activeTab', commodityType);

                    $(document).trigger('setUrl', response.url);

                    hideLoader();
                    initSelect2();
                    initNumbersRange();
                },
                error: function () {
                    hideLoader();
                }
            });
        })
        .on('activeTab', function (event, commodityType) {
            $(this)
                .find('.tabs__link')
                .removeClass('selected')
                .filter(`[data-commodity-type="${commodityType}"]`)
                .addClass('selected');
            $(this)
                .find('.tab')
                .hide()
                .filter(`[data-commodity-type="${commodityType}"]`)
                .show();
        });

    function showInputForm(data){
        let AdditionalProps = {
            cancelButtonText: data.cancelBtn,
            confirmButtonText: data.confirmBtn,
            html: data.form,
        }
        let Popup = new PopupWindowForm(
            '',
            '',
            '',
            AdditionalProps
        );
        Popup.on('onConfirm', (result) => {
            $('form[data-market-form]').submit();
        }).show();
    }
    /** *********************************************
     * Commodity buy
     ***********************************************/
    $(document).on('submit', 'form[data-market-form]', function (event) {
        event.preventDefault();
        event.stopPropagation();

        $.ajax({
            type: 'POST',
            url: $(this).attr('data-market-form'),
            data: $(this).serialize(),
            beforeSend: function () {
                showLoader();
            },
            success: function (data) {
                if (data.form) {
                    hideLoader();
                    showInputForm(data)
                }
                if (data.status) {
                    switch (data.status){
                        case "OK":
                            window.location.reload();
                            break;
                    }
                }
            },
            error: function (data) {
                console.log(data);
            },
            complete: function () {
            }
        });
        return false;
    });
    /** *********************************************
     * Tooltips
     ***********************************************/
    $(document).on('click', '[data-market-href]', function (event) {
        event.preventDefault();
        event.stopPropagation();

        $.ajax({
            type: 'GET',
            url: $(this).attr('data-market-href'),
            data: {},
            beforeSend: function () {
                showLoader();
            },
            success: function (data) {
                showInputForm(data)
            },
            error: function (data) {
                if(data.status !== null && data.status === 401)
                {
                    window.location.href = data.responseJSON.redirectUrl;
                }
            },
            complete: function () {
                hideLoader();
            }
        });
    });
});
