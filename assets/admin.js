import {toInt} from '@glidejs/glide/src/utils/unit';

const $ = require('jquery');

import Chart from 'chart.js/dist/chart';
import PopupWindowAdmin from "./modals/PopupWindowAdmin";
import Inputmask from 'inputmask';
document.addEventListener('DOMContentLoaded', function() {
    if (typeof CKEDITOR != "undefined") {
        $.each(CKEDITOR.dtd.$removeEmpty, function (i, value) {
            CKEDITOR.dtd.$removeEmpty[i] = false;
        });

        for (let key in CKEDITOR.instances) {
            CKEDITOR.instances[key].on('instanceReady', function() {
                let
                    editor      = this,
                    $textarea   = $(this.element);

                // here is field data control!
                //editor.on('change', function() {
                //    console.log(editor.getData());
                //    editor.setData('!!!')
                //    editor.document.setData('!!!')
                //});
            });
        }
    }

    addPhoneMask();

    function addPhoneMask() {
        let $phoneInput = $('.js-phone-mask');

        $phoneInput.each(function() {
            Inputmask({'mask': '+38 (999) 999 99 99'}).mask($(this));
        })
    }

    document.addEventListener('ea.collection.item-added', addPhoneMask);

    /**
     * Dashboard charts.
     */
    $('.admin-dashboard .chart').each(function() {
        let
            $block          = $(this),
            canvasContext   = $block.find('canvas')[0].getContext('2d'),
            dataEncoded     = $block.attr('data-info'),
            dataDecoded     = JSON.parse(dataEncoded),
            labels          = [],
            data            = [],
            styles          = getComputedStyle(document.body);

        for (let key in dataDecoded) {
            if (dataDecoded.hasOwnProperty(key)) {
                labels.push(key);
                data.push(dataDecoded[key]);
            }
        }

        if (data.length === 0) {
            return;
        }

        if ($block.hasClass('horizontal-bar')) {
            new Chart(canvasContext, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{data: data}]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    indexAxis: 'y',
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                callback: function(value) {
                                    return value % 1 === 0 ? value : null;
                                }
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    borderColor: styles.getPropertyValue('--f-blue'),
                    backgroundColor: styles.getPropertyValue('--f-blue')
                }
            });
        } else {
            new Chart(canvasContext, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{data: data}]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                autoSkip: true,
                                maxRotation: 0
                            }
                        },
                        y: {
                            grid: {
                                display: true
                            },
                            ticks: {
                                callback: function(value) {
                                    return value % 1 === 0 && value >= 0 ? value : null;
                                }
                            }
                        }
                    },
                    tension: 0.1,
                    pointRadius: 2,
                    pointHoverRadius: 4,
                    borderWidth: 2,
                    borderColor: styles.getPropertyValue('--f-blue'),
                    backgroundColor: styles.getPropertyValue('--f-blue')
                }
            });
        }
    });
    /**
     * Course statistics: region split
     */
    $('.course-statistics-region-split').each(function() {
        let
            $block          = $(this),
            canvasContext   = $block.find('canvas')[0].getContext('2d'),
            dataEncoded     = $block.attr('data-info'),
            dataDecoded     = JSON.parse(dataEncoded),
            labels          = [],
            data            = [];

        dataDecoded.forEach(function(value) {
            labels.push(value.title);
            data.push(value.users);
        });

        if (data.length === 0) {
            return;
        }

        new Chart(canvasContext, {
            type: 'bar',

            data: {
                labels: labels,
                datasets: [{
                    label: '',
                    data: data,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            },
        });
    });
    /**
     * MARKET: category edit, attributes bar
     */
    $('.market-category-edit-attributes-bar')
        /** add attribute block */
        .on('click', '.add-attribute', function() {
            let
                $addButton          = $(this),
                $attributesBar      = $addButton.closest('.market-category-edit-attributes-bar'),
                $newAttributeBlock  = $attributesBar
                    .find('.attribute-block.template')
                    .clone()
                    .removeClass('template')
                    .insertBefore($addButton),
                namesPrefixTemplate = $attributesBar.attr('data-names-prefix'),
                currentIndex        = parseInt($newAttributeBlock.attr('data-index')),
                newIndex            = currentIndex + 1;

            while ($attributesBar.find(`.attribute-block[data-index="${newIndex}"]`).length > 0) {
                newIndex++;
            }

            $newAttributeBlock
                .attr('data-index', newIndex)
                .find('input, select, textarea')
                .each(function() {
                    let
                        $input  = $(this),
                        newName = $input
                            .attr('name')
                            .replace(
                                `${namesPrefixTemplate}[${currentIndex}]`,
                                `${namesPrefixTemplate}[${newIndex}]`
                            );

                    $input
                        .removeAttr('disabled')
                        .attr('name', newName);
                });
            $attributesBar
                .trigger('attributesSelectorsControl');
        })
        /** remove attribute block */
        .on('click', '.delete-attribute', function() {
            let
                $attributesBar  = $(this).closest('.market-category-edit-attributes-bar'),
                $attributeBlock = $(this).closest('.attribute-block');

            $attributeBlock.remove();
            $attributesBar.trigger('attributesSelectorsControl');
        })
        /** on change attribute */
        .on('change', '.field.attribute-type select', function() {
            $(this)
                .closest('.market-category-edit-attributes-bar')
                .trigger('attributesSelectorsControl');
        })
        /** attributes selectors control */
        .on('attributesSelectorsControl', function() {
            let
                $blocks                         = $(this).find('.attribute-block'),
                $selectorsAll                   = $blocks
                    .not('.template')
                    .find('.field.attribute-type select'),
                $selectorsEmpty                 = $(),
                $addButton                      = $(this).find('.add-attribute'),
                availableAttributesTotalCount   = $blocks
                    .filter('.template')
                    .find('.field.attribute-type select')
                    .find('option')
                    .length,
                activeAttributesId              = [];

            $selectorsAll.each(function() {
                let
                    $selector   = $(this),
                    value       = $selector.val();

                if ($selector.find('option[selected]').length === 0) {
                    $selectorsEmpty = $selectorsEmpty.add($selector);
                } else {
                    $selector
                        .find('option')
                        .removeAttr('selected')
                        .filter(`[value="${value}"]`)
                        .attr('selected', true);
                    activeAttributesId.push(value);
                }
            });

            $selectorsEmpty.each(function() {
                $(this)
                    .find('option')
                    .each(function() {
                        let
                            $option = $(this),
                            value   = $option.attr('value');

                        if (!activeAttributesId.includes(value)) {
                            $option.attr('selected', true);
                            activeAttributesId.push(value);
                            return false;
                        }
                    });
            });

            $selectorsAll
                .find('option')
                .removeAttr('disabled')
                .filter('[selected]')
                .each(function() {
                    let attributeType = $(this).attr('data-attribute-type');

                    $(this)
                        .closest('.attribute-block')
                        .attr('data-attribute-type', attributeType);
                });

            activeAttributesId.forEach(function(attributeId) {
                $selectorsAll
                    .find(`option[value="${attributeId}"]`)
                    .not('[selected]')
                    .attr('disabled', true);
            });

            if (availableAttributesTotalCount === activeAttributesId.length) {
                $addButton.addClass('disabled');
            } else {
                $addButton.removeClass('disabled');
            }
        })
        /** add attribute list value */
        .on('click', '.field-list-values .add-list-value', function() {
            let
                $valuesBar      = $(this).closest('.values-bar'),
                $lastValue      = $valuesBar.children().last(),
                currentIndex    = parseInt($lastValue.attr('data-index')),
                newIndex        = currentIndex + 1;

            $lastValue
                .clone()
                .appendTo($valuesBar)
                .attr('data-index', newIndex)
                .find('input')
                .each(function() {
                    let
                        $input              = $(this),
                        currentName         = $input.attr('name'),
                        currentNameParts    = currentName.split(/[[\]]{1,2}/),
                        currentNameLastPart = currentNameParts[currentNameParts.length - 2],
                        currentNamesPostfix = `[${currentIndex}][${currentNameLastPart}]`,
                        needNamesPostfix    = `[${newIndex}][${currentNameLastPart}]`,
                        newName             = currentName.replace(currentNamesPostfix, needNamesPostfix);

                    $input
                        .val('')
                        .attr('name', newName);
                });
        })
        /** remove attribute list value */
        .on('click', '.delete-list-value', function() {
            $(this)
                .parent()
                .remove();
        });
    /**
     * MARKET: commodity edit, attributes bar
     */
    $('.market-commodity-edit-attributes-values-bar').each(function() {
        let
            $bar                        = $(this),
            $categorySelector           = $bar.closest('form').find(':input[name$="[category]"]'),
            attributesData              = JSON.parse($bar.attr('data-attributes-data')),
            attributesExistValuesData   = JSON.parse($bar.attr('data-attributes-exist-values-data'));

        /** change category */
        $categorySelector.on('change', function() {
            let categoryId = $(this).val();

            $bar.trigger('categoryChanged', categoryId);
        });
        $bar
            /** category attributes rebuilding */
            .on('categoryChanged', function(event, categoryId) {
                let
                    categoryAttributesData  = attributesData[categoryId] || {},
                    $formColumn             = $bar.closest('[class^="col"]');

                $bar
                    .find('.item')
                    .not('.template')
                    .remove();

                $.each(categoryAttributesData, function(attributeIndex) {
                    let
                        $newItem            = $bar
                            .find('.item.template')
                            .clone()
                            .removeClass('template'),
                        $newItemField       = $newItem
                            .find(`.field [data-type="${categoryAttributesData[attributeIndex].type}"]`),
                        attributeListData   = categoryAttributesData[attributeIndex].listValues || {};

                    $newItem
                        .appendTo($bar)
                        .attr('data-attribute-id', categoryAttributesData[attributeIndex].id);
                    $newItem
                        .find('.title')
                        .text(categoryAttributesData[attributeIndex].title);
                    $newItem
                        .find('.field > *')
                        .not($newItemField)
                        .hide();
                    $newItemField
                        .find(':input')
                        .each(function() {
                            let name = $(this)
                                .attr('name')
                                .replace('#attribute-id#', categoryAttributesData[attributeIndex].id);

                            $(this)
                                .removeAttr('disabled')
                                .attr('name', name);

                            if (categoryAttributesData[attributeIndex].required) {
                                $(this).attr('required', true);
                            }
                        });

                    if (categoryAttributesData[attributeIndex].required) {
                        $newItem.addClass('item-required');
                    }

                    switch (categoryAttributesData[attributeIndex].type) {
                        case 'list':
                        case 'dictionary':
                            $.each(attributeListData, function(listValueId) {
                                let listValueTitle = attributeListData[listValueId];

                                $newItemField
                                    .find('select')
                                    .append(`<option value="${listValueId}">${listValueTitle}</option>`);
                            });
                            break;
                        case 'listMultiple':
                            let $checkboxTemplate = $newItemField.find('.list-value').first();

                            $.each(attributeListData, function(listValueId) {
                                let $checkbox = $checkboxTemplate.clone().appendTo($newItemField);

                                $checkbox
                                    .find(':checkbox')
                                    .attr('value', listValueId);
                                $checkbox
                                    .find('.list-value-title')
                                    .text(attributeListData[listValueId]);
                            });

                            $checkboxTemplate.remove();
                            break;
                        default:
                    }
                });

                categoryAttributesData.length > 0
                    ? $formColumn.show()
                    : $formColumn.hide();
            })
            /** category attributes values setting */
            .on('attributesValuesApplied', function() {
                $.each(attributesExistValuesData, function(attributeId) {
                    $bar
                        .find(`.item[data-attribute-id="${attributeId}"]`)
                        .find(':input')
                        .not('[disabled]')
                        .val(attributesExistValuesData[attributeId])
                        .first()
                        .trigger('change');
                });
            })
            /** checkboxes (multiple list) required attribute control */
            .on('change', '[data-type="listMultiple"] :checkbox', function() {
                let
                    $item           = $(this).closest('.item'),
                    fieldIsRequired = $item.hasClass('item-required'),
                    $checkboxes     = $item.find(':checkbox');

                if (!fieldIsRequired) {
                    return;
                }

                $checkboxes.filter(':checked').length > 0
                    ? $checkboxes.removeAttr('required')
                    : $checkboxes.attr('required', true);
            })
            .trigger('categoryChanged', $categorySelector.val())
            .trigger('attributesValuesApplied');
    });
    /**
     * MARKET: attribute edit, attribute type behavior.
     */
    $('.attribute-edit-attribute-type')
        .on('change', function() {
            let
                $typeSelector       = $(this),
                $dictionarySelector = $typeSelector
                    .closest('form')
                    .find('.attribute-edit-attribute-dictionary');

            if ($typeSelector.find('select').val() === 'dictionary') {
                $dictionarySelector.show();
            } else {
                $dictionarySelector.hide();
            }
        })
        .trigger('change');

    /**
     * Delete RequestRole popup
     */
    let $deleteRequestRole = $('.deleteRequestRole');
    let $deleteRequestRoleLink = $deleteRequestRole.attr('href');
    $deleteRequestRole.removeAttr('href');
    let $ajaxActionHref = $deleteRequestRole.attr('ajax-data-action');
    let $entityId = $deleteRequestRole.attr('entityid');

    $deleteRequestRole.on('click',function() {
        let additionalParameters = {
            input: 'textarea',
            inputLabel: 'Текст повідомлення користувачеві',
            inputPlaceholder: 'Сертифікат не пройшов перевірку адміном.',
            inputValidator: (value) => {
                if (!value) {
                    return 'Не може бути пустим'
                }
            },
            inputAttributes: {
                maxlength: 1000,
                autocapitalize: 'off',
                autocorrect: 'off'
            }
        }

        let $deleteRequestRolePopup =  new PopupWindowAdmin('Укажіть причину відмови',null,null,additionalParameters);
        $deleteRequestRolePopup.on('onConfirm',function(result) {
            $.ajax({
                type: "POST",
                url: $ajaxActionHref,
                data: {
                    message: result.value,
                    id: $entityId
                },
                cache: false,
                success: function(response) {
                    if(response.url){
                        window.location.href = response.url;
                    }
                },
                failure: function (response) {
                }
            });
        });

      $deleteRequestRolePopup.show();

    });
});
