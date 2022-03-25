const $ = require('jquery');

import {
    showLoader,
    hideLoader,
    initSelect2,
    closeAllSelect2,
    initNumbersRange,
} from '../app.js';

$(document).ready(function () {
    $('.market-commodities-list-page').each(function() {
        let
            $list               = $(this),
            $filters            = $list.find('.js-commodities-list-filter'),
            $searchBar          = $list.find('.js-commodities-search-bar'),
            $appliedFiltersBar  = $list.find('.js-commodities-list-applied-filters-bar'),
            $itemsContainer     = $list.find('.js-commodities-list-items-container');
        /** *********************************************
         * on filter input change
         ***********************************************/
        $filters.on('change', '.field :input', function() {
            let $filter = $(this).closest('.js-commodities-list-filter');

            $list
                .trigger('setPage', 1)
                .trigger('pageRebuild', {initiator : $filter});
        });
        /** *********************************************
         * on filter reset all
         ***********************************************/
        $filters.on('click', '.js-reset-all', function() {
            let
                $filter                 = $(this).closest('.js-commodities-list-filter'),
                $subFiltersAdditional   = $list.find('.js-commodities-list-sub-filters-additional-data');

            $appliedFiltersBar
                .find('.js-applied-filter-item')
                .remove();
            $filter
                .find('.js-commodities-list-sub-filter')
                .remove();
            $subFiltersAdditional
                .find('input')
                .remove();
            $list
                .trigger('setSearch', '')
                .trigger('setPage', 1)
                .trigger('pageRebuild', {initiator : $('')});
        });
        /** *********************************************
         * KITS LIST: on add sub-filter
         ***********************************************/
        $filters.on('click', '.js-commodities-list-add-sub-filter', function() {
            let
                $subFiltersAdditional   = $list.find('.js-commodities-list-sub-filters-additional-data'),
                commodityType           = $(this).attr('data-commodity-type'),
                lastIndex               = $list
                    .find('.js-commodities-list-sub-filter')
                    .last()
                    .attr('data-filter-index'),
                needIndex               = lastIndex
                    ? parseInt(lastIndex) + 1
                    : 0,
                commodityTypeInputName  = $subFiltersAdditional
                    .attr('data-input-name-template')
                    .replace('#INDEX#',     needIndex)
                    .replace('#ACTION#',    'commodityType'),
                filterIsCloseInputName  = $subFiltersAdditional
                    .attr('data-input-name-template')
                    .replace('#INDEX#',     needIndex)
                    .replace('#ACTION#',    'filterIsClosed');

            $subFiltersAdditional
                .append(`<input type="hidden" name="${commodityTypeInputName}" value="${commodityType}">`)
                .append(`<input type="hidden" name="${filterIsCloseInputName}" value="0">`);
            $list
                .trigger('pageRebuild');
        });
        /** *********************************************
         * KITS LIST: on sub-filter visibility change
         ***********************************************/
        $filters.on('accordion-block-state-changed', '.js-commodities-list-sub-filter', function(event, data) {
            let
                $subFiltersAdditional   = $list.find('.js-commodities-list-sub-filters-additional-data'),
                filterIndex             = $(this).attr('data-filter-index'),
                inputName               = $subFiltersAdditional
                    .attr('data-input-name-template')
                    .replace('#INDEX#',     filterIndex)
                    .replace('#ACTION#',    'filterIsClosed');

            $subFiltersAdditional
                .find(`:input[name="${inputName}"]`)
                .val(data ? 0 : 1);

            $list.trigger('pageRebuild', {
                silent : true,
            });
        });
        /** *********************************************
         * on applied filters choice remove
         ***********************************************/
        $appliedFiltersBar.on('click', '.js-applied-filter-item .js-delete', function() {
            let $item = $(this).closest('.js-applied-filter-item');

            $item
                .remove();
            $list
                .trigger('setPage', 1)
                .trigger('pageRebuild', {initiator : $appliedFiltersBar});
        });
        /** *********************************************
         * on search/sort/page change
         ***********************************************/
        $searchBar.on('change', ':input', function() {
            $list
                .trigger('setPage', 1)
                .trigger('pageRebuild');
        });
        $itemsContainer.on('change', ':input', function() {
            $list.trigger('pageRebuild');
        });
        /** *********************************************
         * triggers
         ***********************************************/
        $list.on('setPage', function(event, data) {
            $itemsContainer
                .find(':input')
                .val(data);
        });
        $list.on('setSearch', function(event, data) {
            $searchBar
                .find('input[type="text"]')
                .val(data);
        });
        /** *********************************************
         * page rebuild process
         ***********************************************/
        $list.on('pageRebuild', function(event, data) {
            let
                requestUrl      = $(this).attr('data-rebuild-page-url'),
                $initiator      = typeof data !== 'undefined' && data.hasOwnProperty('initiator')
                    ? data.initiator
                    : $filters.first(),
                isSilentMode    = typeof data !== 'undefined' && data.hasOwnProperty('silent')
                    ? data.silent
                    : false,
                formData        = $searchBar
                    .add($itemsContainer.find('.navigation'))
                    .add($initiator)
                    .find(':input')
                    .not('[disabled]')
                    .serializeArray();

            if (!isSilentMode) {
                showLoader();
            }

            $.ajax({
                type    : 'POST',
                url     : requestUrl,
                data    : formData,
                success : function(response) {
                    if (response.hasOwnProperty('filter') && !isSilentMode) {
                        closeAllSelect2();
                        $filters
                            .find('.js-commodities-list-filter-fields')
                            .html(response.filter);
                        initSelect2();
                        initNumbersRange();
                    }
                    if (response.hasOwnProperty('appliedFiltersBar') && !isSilentMode) {
                        $appliedFiltersBar.html(response.appliedFiltersBar);
                    }
                    if (response.hasOwnProperty('itemsList') && !isSilentMode) {
                        $itemsContainer.html(response.itemsList);
                    }
                    if (response.hasOwnProperty('url')) {
                        $(document).trigger('setUrl', response.url);
                    }

                    hideLoader();
                },
                error: function() {
                    hideLoader();
                }
            });
        });
    });
});
