const $ = require('jquery');

$(document).ready(function () {
    /** *********************************************
     * commodity active to date control
     ***********************************************/
    $('form[data-commodity-form]')
        .on('change', ':input[name$="[user]"]', function() {
            $(this)
                .closest('form')
                .trigger('commodityFromSetActiveToDate');
        })
        .on('commodityFromSetActiveToDate', function() {
            let
                $form           = $(this),
                $userField      = $form.find(':input[name$="[user]"]'),
                $activeToField  = $form.find(':input[name$="[activeTo]"]'),
                currentUrl      = new URL(window.location.href);

            if (!$userField.val()) {
                return;
            }

            $.ajax({
                type    : 'POST',
                url     : $form.attr('data-get-active-to-date-url'),
                data    : {
                    user        : $userField.val(),
                    commodity   : currentUrl.searchParams.get('entityId'),
                    controller  : currentUrl.searchParams.get('crudControllerFqcn'),
                },
                success : function(response) {
                    let
                        date            = new Date(response.date.date),
                        dateFormatted   = date.toISOString().slice(0, 19);

                    $activeToField
                        .attr('value', dateFormatted)
                        .val(dateFormatted);
                },
                error: function() {

                }
            });
        })
        .trigger('commodityFromSetActiveToDate');
    /** *********************************************
     * product allowed types control
     ***********************************************/
    $('form[data-commodity-product-form]')
        .on('change', ':input[name$="[user]"]', function() {
            $(this)
                .closest('form')
                .trigger('productFromTypesControl');
        })
        .on('productFromTypesControl', function() {
            let
                $form       = $(this),
                $userField  = $form.find(':input[name$="[user]"]'),
                $typeField  = $form.find(':input[name$="[type]"]');

            $typeField
                .trigger('prepareTypeField')
                .trigger('setTypeFieldAllowedValues');

            if (!$userField.val()) {
                return;
            }

            $.ajax({
                type    : 'POST',
                url     : $form.attr('data-get-product-allowed-types-url'),
                data    : {
                    user : $userField.val(),
                },
                success : function(response) {
                    $typeField.trigger('setTypeFieldAllowedValues', {
                        allowedValues : response.types,
                    });

                    if (!response.types.includes($typeField.val())) {
                        $typeField.trigger('setTypeFieldDefaultValue');
                    }

                    $typeField.trigger('closeTypeField');
                },
                error: function() {

                }
            });
        })
        .on('prepareTypeField', ':input', function() {
            let
                $typeFieldFullBlock = $(this).parent(),
                $typeFieldLabel     = $typeFieldFullBlock.find('[role="combobox"]'),
                $typeFieldList      = $typeFieldFullBlock.find('[role="listbox"]');

            if ($typeFieldList.children().length === 0) {
                $typeFieldLabel.click().click();
            }
        })
        .on('setTypeFieldAllowedValues', ':input', function(event, data) {
            let
                $typeFieldFullBlock = $(this).parent(),
                $typeFieldList      = $typeFieldFullBlock.find('[role="listbox"]'),
                allowedValues       = typeof data !== 'undefined' && data.hasOwnProperty('allowedValues')
                    ? data.allowedValues
                    : [];

            $typeFieldList.find('.option').each(function() {
                let value = $(this).attr('data-value');

                allowedValues.length > 0 && !allowedValues.includes(value)
                    ? $(this).removeAttr('data-selectable')
                    : $(this).attr('data-selectable', true);
            });
        })
        .on('setTypeFieldDefaultValue', ':input', function() {
            let
                $typeFieldFullBlock = $(this).parent(),
                $typeFieldList      = $typeFieldFullBlock.find('[role="listbox"]');

            $typeFieldList
                .find('.option')
                .filter('[data-selectable]')
                .first()
                .click();
        })
        .on('closeTypeField', ':input', function() {
            let
                $typeFieldFullBlock = $(this).parent(),
                $typeFieldLabel     = $typeFieldFullBlock.find('[role="combobox"]'),
                $typeFieldList      = $typeFieldFullBlock.find('[role="listbox"]');

            if ($typeFieldList.is(':visible')) {
                $typeFieldLabel.click();
            }
        })
        .trigger('productFromTypesControl');
    /** *********************************************
     * kit commodities activity visualisation.
     ***********************************************/
    $('form[data-commodity-kit-form]')
        .on('change', ':input[name$="[commodities][]"]', function() {
            $(this)
                .closest('form')
                .trigger('kitFromCommoditiesActivityControl');
        })
        .on('kitFromCommoditiesActivityControl', function() {
            let
                $form                       = $(this),
                $commoditiesField           = $form.find(':input[name$="[commodities][]"]'),
                $commoditiesList            = $commoditiesField.parent().find('[role="listbox"]'),
                updateItemActivityDisplay   = function() {
                    let
                        $item   = $(this),
                        value   = $item.attr('data-value'),
                        $option = $commoditiesField.find(`option[value="${value}"]`);

                    $option.attr('data-activity') === 'N'
                        ? $item.addClass('inactive')
                        : $item.removeClass('inactive');
                };

            $commoditiesField
                .parent()
                .find('[role="combobox"] .item')
                .each(updateItemActivityDisplay);
            $commoditiesList
                .not('.list-updated')
                .on('DOMNodeInserted', function() {
                    $commoditiesList
                        .addClass('list-updated')
                        .off('DOMNodeInserted')
                        .children()
                        .each(updateItemActivityDisplay);
                });
        })
        .trigger('kitFromCommoditiesActivityControl');
});
