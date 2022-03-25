const $ = require('jquery');

import {
    showLoader,
    hideLoader,
} from '../app.js';

$(document).ready(function () {
    $('.commodity-creating-form.kit form')
        .on('beforeStepChanged', function() {
            $(this).trigger('previewFormRefresh');
        })
        /** *********************************************
         * commodities tabs behavior
         ***********************************************/
        .on('click', '.tabs__navigation .tabs__link:not(.selected)', function() {
            let
                $tab            = $(this),
                value           = $tab.attr('data-tab'),
                $tabsContainer  = $tab.closest('section');

            $tabsContainer
                .find('.tabs__link')
                .removeClass('selected')
                .filter(`[data-tab="${value}"]`)
                .addClass('selected');
            $tabsContainer
                .find('.tab')
                .removeClass('visible')
                .filter(`[data-tab="${value}"]`)
                .addClass('visible');
        })
        /** *********************************************
         * commodities add/remove
         ***********************************************/
        .on('click', '.js-kit-creating-commodity-add', function() {
            let value = $(this).attr('data-id');

            $(this)
                .closest('form')
                .trigger('commodityAdded', value)
                .trigger('previewFormRefresh');
        })
        .on('click', '.js-kit-remove-commodity-row .js-kit-remove-commodity', function() {
            let
                value   = parseInt($(this).attr('data-value')),
                $form   = $(this).closest('form');

            $(this)
                .closest('.js-kit-remove-commodity-row')
                .remove();
            $form
                .trigger('commodityRemoved', value)
                .trigger('previewFormRefresh');
        })
        .on('click', '.js-kit-remove-user-row .js-kit-remove-user', function() {
            let
                value   = parseInt($(this).attr('data-value')),
                $form   = $(this).closest('form');

            $(this)
                .closest('.js-kit-remove-user-row')
                .remove();
            $form
                .trigger('userCommoditiesRemoved', value)
                .trigger('previewFormRefresh');
        })
        .on('click', '.js-kit-creating-user-add', function() {
            let value = $(this).attr('data-id');

            $(this)
                .closest('form')
                .trigger('userSelected', value);
        })
        .on('click', '.js-kit-creating-user-deselect', function() {
            $(this)
                .closest('form')
                .trigger('userSelected', 0);
        })
        /** *********************************************
         * name edition
         ***********************************************/
        .on('click', '.js-kit-title-edition .edit', function() {
            $(this)
                .closest('.js-kit-title-edition')
                .addClass('process');
        })
        .on('click', '.js-kit-title-edition .cancel', function() {
            let
                $titleEdition   = $(this).closest('.js-kit-title-edition'),
                $editionTitle   = $titleEdition.find('.title'),
                $editionInput   = $titleEdition.find(':input');

            $titleEdition.removeClass('process');
            $editionInput.val($editionTitle.text());
        })
        .on('click', '.js-kit-title-edition .apply', function() {
            let
                $titleEdition   = $(this).closest('.js-kit-title-edition'),
                $editionTitle   = $titleEdition.find('.title'),
                $editionInput   = $titleEdition.find(':input'),
                $form           = $(this).closest('form'),
                $titleInput     = $form.find('[data-title-input]');

            $titleEdition.removeClass('process');
            $editionTitle.text($editionInput.val());
            $titleInput.val($editionInput.val());
        })
        .on('keydown', '.js-kit-title-edition :input', function(event) {
            let
                $input          = $(this),
                $titleEdition   = $input.closest('.js-kit-title-edition');

            $input.val().length > 0
                ? $titleEdition.removeClass('empty')
                : $titleEdition.addClass('empty');

            if (event.keyCode === 13) {
                $titleEdition
                    .find('.apply')
                    .click();
                event.preventDefault();
            }
        })
        /** *********************************************
         * add commodity button
         ***********************************************/
        .on('click', '.js-kit-add-commodity-button', function() {
            $(this)
                .closest('form')
                .trigger('setActiveTab', 1);
        })
        /** *********************************************
         * preview form refresh
         ***********************************************/
        .on('previewFormRefresh', function() {
            let
                $form       = $(this),
                $formWrap   = $form.parent(),
                formData    = $form
                    .find(':input')
                    .not('[disabled]')
                    .serializeArray();

            showLoader();
            $.ajax({
                type    : 'POST',
                url     : $formWrap.attr('data-preview-url-rebuild'),
                data    : formData,
                success : function(response) {
                    if (response.success) {
                        $form
                            .find('.js-kit-preview-form')
                            .each(function() {
                                let
                                    $previewForm    = $(this),
                                    type            = $previewForm.attr('data-type');

                                $previewForm
                                    .after(response[type])
                                    .remove();
                            });
                    }

                    hideLoader();
                },
                error: function() {
                    hideLoader();
                }
            });
        })
        /** *********************************************
         * commodities add/remove triggers
         ***********************************************/
        .on('commodityAdded', function(event, data) {
            $(this).trigger('commoditiesSetChange', {
                value           : data,
                valueAttribute  : 'value',
                operation       : 'add',
            });
        })
        .on('commodityRemoved', function(event, data) {
            $(this).trigger('commoditiesSetChange', {
                value           : data,
                valueAttribute  : 'value',
                operation       : 'remove',
            });
        })
        .on('userCommoditiesRemoved', function(event, data) {
            $(this).trigger('commoditiesSetChange', {
                value           : data,
                valueAttribute  : 'data-user',
                operation       : 'remove',
            });
        })
        .on('userSelected', function(event, data) {
            let
                $usersList              = $(this).find('.users-list'),
                $userCommoditiesList    = $(this).find('.user-commodities-list');

            if (data === 0) {
                $usersList.show();
                $userCommoditiesList.hide();
            } else {
                $usersList.hide();
                $userCommoditiesList
                    .show()
                    .find('.js-user-commodities-items-container')
                    .html('');
                $(this)
                    .find('[name="selectedUser"]')
                    .val(data)
                    .trigger('change');
            }
        })
        .on('commoditiesSetChange', function(event, data) {
            let
                $form               = $(this),
                $commoditiesInput   = $form.find('.js-commodities-selector select'),
                dataNormalized      = {
                    value               : parseInt(data.value)  ?? 0,
                    valueAttribute      : data.valueAttribute   ?? 'value',
                    operation           : data.operation        ?? 'add',
                };

            $commoditiesInput.find('option').each(function() {
                let
                    $option     = $(this),
                    optionValue = parseInt($option.attr(dataNormalized.valueAttribute));

                if (optionValue === dataNormalized.value) {
                    dataNormalized.operation === 'add'
                        ? $option.attr('selected', true)
                        : $option.removeAttr('selected');
                }
            });

            $form
                .trigger('commoditiesValidation')
                .trigger('commoditiesSelectorsRebuild')
                .trigger('commoditiesSummaryPriceUpdate');
        })
        /** *********************************************
         * commodities validation
         ***********************************************/
        .on('commoditiesValidation', function() {
            let
                $commoditiesInput   = $(this).find('.js-commodities-selector select'),
                $container          = $commoditiesInput.parent().parent(),
                $minChecker         = $container.find('[name="commoditiesMinChecker"]'),
                $maxChecker         = $container.find('[name="commoditiesMaxChecker"]'),
                $ownerChecker       = $container.find('[name="commoditiesOwnerChecker"]'),
                values              = $commoditiesInput.val(),
                currentUserId       = parseInt($commoditiesInput.attr('data-current-user')),
                minValueIsValid     = values.length >= parseInt($minChecker.attr('data-value')),
                maxValueIsValid     = values.length <= parseInt($maxChecker.attr('data-value')),
                ownCommodityExist   = false;

            values.forEach(function(value) {
                let
                    commodityUser   = $commoditiesInput
                        .find(`option[value="${value}"]`)
                        .attr('data-user'),
                    commodityUserId = parseInt(commodityUser);

                if (commodityUserId === currentUserId) {
                    ownCommodityExist = true;
                }
            });

            $minChecker.prop('checked', minValueIsValid).valid();
            $maxChecker.prop('checked', maxValueIsValid).valid();
            $ownerChecker.prop('checked', ownCommodityExist).valid();
        })
        /** *********************************************
         * commodities selectors rebuilding
         ***********************************************/
        .on('commoditiesSelectorsRebuild', function() {
            let
                $form               = $(this),
                $commoditiesInput   = $form.find('.js-commodities-selector select'),
                values              = $commoditiesInput.val();

            $form.find('[name="selectedCommodities[]"]').each(function() {
                $(this)
                    .val(values)
                    .trigger('change');
            });
        })
        /** *********************************************
         * commodities summary price update
         ***********************************************/
        .on('commoditiesSummaryPriceUpdate', function() {
            let
                $form               = $(this),
                $commoditiesInput   = $form.find('.js-commodities-selector select'),
                $summaryPrice       = $form.find('.js-kit-commodities-price-summary'),
                value               = 0;

            $commoditiesInput
                .find('option[selected]')
                .each(function() {
                    let price = $(this).attr('data-price');

                    value += parseFloat(price);
                });
            $summaryPrice
                .find('.price b')
                .text(value);
        });
});
