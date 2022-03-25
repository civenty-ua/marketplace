const $ = require('jquery');

import {
    showLoader,
    hideLoader,
    initSelect2,
} from '../app.js';

import 'jquery-steps/build/jquery.steps';
import 'jquery-validation';

$(document).ready(function () {
    $('.commodity-creating-form form')
        /** *********************************************
         * steps form initialization
         ***********************************************/
        .each(function() {
            let
                $form           = $(this),
                $formWrapper    = $form.parent(),
                titleNext       = $formWrapper.attr('data-title-next'),
                titlePrevious   = $formWrapper.attr('data-title-previous'),
                titleFinish     = $formWrapper.attr('data-title-finish'),
                $nextButton     =
                    `<span class="marginRight10px">${titleNext}</span>`+
                    '<i class="fas fa-angle-right"></i>',
                $previousButton =
                    '<i class="fas fa-angle-left"></i>'+
                    `<span class="marginLeft10px">${titlePrevious}</span>`;

            $form
                /** *********************************************
                 * form inputs rebuild
                 ***********************************************/
                .on('formInputsRebuild', function() {
                    $(this).find('.js-steps-form-select').each(function() {
                        let trueClass = $(this).attr('data-class');

                        $(this).attr('class', trueClass);
                    });
                    initSelect2();
                })
                /** *********************************************
                 * tab activating
                 ***********************************************/
                .on('setActiveTab', function(event, data) {
                    switch (data) {
                        case 'withErrors':
                            $(this).trigger('setActiveTabWithErrors');
                            break;
                        default:
                            $(this)
                                .find('[role="tab"]')
                                .eq(data)
                                .find('a')
                                .click();
                    }
                })
                .on('setActiveTabWithErrors', function() {
                    let
                        $form = $(this),
                        needTabId;

                    $form.find('section').each(function() {
                        let hasErrors = $(this).find('.validation-error').length > 0;

                        if (hasErrors && !needTabId) {
                            needTabId = $(this).attr('id').replace('-p-', '-t-');
                        }
                    });

                    if (needTabId) {
                        $form.find(`#${needTabId}`).click();
                    } else {
                        $form.trigger('setActiveTab', 0);
                    }
                })
                /** *********************************************
                 * steps form creating
                 ***********************************************/
                .steps({
                    headerTag           : 'h6',
                    bodyTag             : 'section',
                    transitionEffect    : 'fade',
                    titleTemplate       :
                        '<span class="step">#index#</span>'+
                        '<span class="step-title">#title#</span>',
                    labels              : {
                        finish              : titleFinish,
                        next                : $nextButton,
                        previous            : $previousButton,
                    },
                    enableAllSteps      : $formWrapper.attr('data-is-new') === 'N',
                    onInit              : function() {
                        $form.trigger('formInputsRebuild');
                    },
                    onStepChanging      : function(event, currentIndex, newIndex) {
                        let
                            $formInputs = $form.find('section').eq(currentIndex).find(':input'),
                            backStep    = currentIndex > newIndex,
                            formIsValid = $formInputs.length > 0 ? $formInputs.valid() : true;

                        if (formIsValid || backStep) {
                            $form.trigger('beforeStepChanged');
                            return true;
                        }

                        return false;
                    },
                    onFinishing         : function(event, currentIndex) {
                        let
                            $formInputs     = $form.find(':input'),
                            $subformInputs  = $form.find('section').eq(currentIndex).find(':input'),
                            subformIsValid  = $subformInputs.length > 0 ? $subformInputs.valid()    : true,
                            fullFormIsValid = $formInputs.length    > 0 ? $formInputs.valid()       : true;

                        if (subformIsValid && fullFormIsValid) {
                            showLoader();
                            $form.find(':submit').click();
                            return true;
                        } else if (subformIsValid && !fullFormIsValid) {
                            $form.trigger('setActiveTab', 'withErrors');
                            return false;
                        }

                        return false;
                    },
                })
                /** *********************************************
                 * validation
                 ***********************************************/
                .validate({
                    ignore      : 'input[type="hidden"], input[disabled]',
                    showErrors  : function(errorMap) {
                        let
                            errorExist  = Object.keys(errorMap).length !== 0,
                            errorClass  = 'validation-error';

                        $.each(this.currentElements, function(index, input) {
                            let $field = $(input).closest('.field');

                            errorExist ? $field.addClass(errorClass) : $field.removeClass(errorClass);
                        });
                    },
                });
        })
        /** *********************************************
         * localities fields
         ***********************************************/
        .on('change', '.localities-fields select', function() {
            let
                $form                   = $(this).closest('form'),
                $localitiesFieldsBlock  = $(this).closest('.localities-fields');

            showLoader();
            $.ajax({
                type    : 'POST',
                url     : $localitiesFieldsBlock.attr('data-rebuild-fields-url'),
                data    : {
                    'region'    : $localitiesFieldsBlock.find(`:input[data-region-field]`).val(),
                    'district'  : $localitiesFieldsBlock.find(`:input[data-district-field]`).val(),
                    'locality'  : $localitiesFieldsBlock.find(`:input[data-locality-field]`).val(),
                },
                success : function(response) {
                    $localitiesFieldsBlock
                        .after(response)
                        .remove();
                    $form.trigger('formInputsRebuild');
                    hideLoader();
                },
                error   : function() {
                    hideLoader();
                }
            });
        })
        /** *********************************************
         * category attributes
         ***********************************************/
        .on('change', '[data-category-field]', function() {
            let
                $form               = $(this).closest('form'),
                $categoriesBlock    = $form.find('.js-categories-fields'),
                $attributesBlock    = $form.find('.js-attributes-fields');

            showLoader();
            $.ajax({
                type    : 'POST',
                url     : $attributesBlock.attr('data-rebuild-attributes-url'),
                data    : {
                    'category' : $(this).val(),
                },
                success : function(response) {
                    $categoriesBlock
                        .after(response.category)
                        .remove();
                    $attributesBlock
                        .after(response.attributes)
                        .remove();
                    $form.trigger('formInputsRebuild');
                    hideLoader();
                },
                error   : function() {
                    hideLoader();
                }
            });
        })
        /** *********************************************
         * negotiated price
         ***********************************************/
        .on('change', '[data-price-negotiated-checker]', function() {
            let
                $form                   = $(this).closest('form'),
                $priceRow               = $form.find('[data-price-row]'),
                $priceInput             = $form.find('[data-price-input]'),
                $priceNegotiatedChecker = $form.find('[data-price-negotiated-checker]');

            if ($priceNegotiatedChecker.is(':checked')) {
                $priceRow
                    .addClass('hidden');
                $priceInput
                    .attr('data-old-value', $priceInput.val())
                    .attr('data-old-min', $priceInput.attr('min'))
                    .attr('min', 0)
                    .val(0);
            } else {
                $priceRow
                    .removeClass('hidden');
                $priceInput
                    .val($priceInput.attr('data-old-value') ?? 1)
                    .removeAttr('data-old-value')
                    .attr('min', $priceInput.attr('data-old-min') ?? 1)
                    .removeAttr('data-old-min');
            }
        });
});
