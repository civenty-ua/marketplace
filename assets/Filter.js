import $ from 'jquery';

/**
 *
 */
class Filter {

    /**
     *
     */
    constructor() {
        this.$body = $('body');

        this.assembleItemFilter();
        this.assembleExpertFilter();
        this.assembleDetailPartnerFilter();
    }

    /**
     *
     */
    assembleItemFilter() {
        let $itemBlock;
        $itemBlock = $('.js-item');

        $itemBlock.on('keyup', '.search-container :text', function (event) {
            if (event.keyCode === 13) {
                $(this)
                    .closest('.search-container')
                    .find('.search__button')
                    .click();
            }
        });
        $itemBlock.on('click', '.search-container .search__button', function () {
            $itemBlock.trigger('item-page-rebuild-asked', {
                type: 'search'
            });
        });

        $itemBlock.on('click', '.tag-item', function (event) {
            event.preventDefault();

            let $this, $filterToChange, tagName;
            $this = $(this);
            tagName = $this.attr('data-tag-name');


            $filterToChange = $this.closest('.mobile-tag').length
                ? $('.desktop-tag')
                : $('.mobile-tag');

            $this.hasClass('active') ? $this.removeClass('active') : $this.addClass('active');

            $filterToChange.find('[data-tag-name="' + tagName + '"]').hasClass('active') ?
                $filterToChange.find('[data-tag-name="' + tagName + '"]').removeClass('active') :
                $filterToChange.find('[data-tag-name="' + tagName + '"]').addClass('active');

            $itemBlock.trigger('item-page-rebuild-asked', {
                type: 'filter'
            });
        });

        $itemBlock.on('click', '.js-select-all', function (event) {
            event.preventDefault();

            this.selectAllCheckbox();

            $itemBlock.trigger('item-page-rebuild-asked', {
                type: 'filter'
            });
        }.bind(this));

        $itemBlock.on('click', '.js-reset-all', function (event) {
            event.preventDefault();

            this.resetAllCheckbox();

            $itemBlock.trigger('item-page-rebuild-asked', {
                type: 'filter'
            });
        }.bind(this));

        $itemBlock.on('change', '.checkbox :checkbox', function (event) {
            event.preventDefault();

            let $this, checkboxId, $filterToChange;
            $this = $(this);
            checkboxId = $this.attr('data-checkbox-id');

            $filterToChange = $this.closest('.js-mobile-item-filter').length
                ? $('.js-desktop-filter')
                : $('.js-mobile-item-filter');

            $filterToChange
                .find('.checkbox :checkbox[data-checkbox-id="' + checkboxId + '"]')
                .prop('checked', $this.prop('checked'));

            $itemBlock.trigger('item-page-rebuild-asked', {
                type: 'filter'
            });
        });

        $itemBlock.on('click', '.active-filter__item', function (event) {
            event.preventDefault();

            let activeCheckboxId, $this;
            $this = $(this);
            activeCheckboxId = $this.attr('data-active-checkbox-id');

            $itemBlock
                .find('.checkbox :checkbox[data-checkbox-id=' + activeCheckboxId + ']')
                .prop('checked', false);

            $itemBlock.trigger('item-page-rebuild-asked', {
                type: 'filter'
            });
        });

        $itemBlock.on('change', '.sorter', function (event) {
            event.preventDefault();

            $itemBlock.trigger('item-page-rebuild-asked', {
                type: 'sort'
            });
        });

        $itemBlock.on('click', '.js-item-list-block .pagination a', function (event) {
            event.preventDefault();
            event.stopPropagation();

            let pagePropRel, pageValue, pageValueInt, currentPage, currentPageInt;

            pagePropRel = $(this).prop('rel');
            pageValue = $(this).html();
            pageValueInt = parseInt(pageValue);
            currentPage = $(this).closest('.pagination').find('.current').html();
            currentPageInt = parseInt(currentPage);

            if (pagePropRel === 'next') {
                pageValueInt = currentPageInt + 1;
            }

            if (pagePropRel === 'prev') {
                pageValueInt = currentPageInt - 1;
            }

            $itemBlock.trigger('item-page-rebuild-asked', {
                type: 'pagination',
                value: pageValueInt
            });
            window.scrollTo({top:500,behavior:"smooth"});
        });

        $itemBlock.on('item-page-rebuild-asked', function (event, data) {
            event.preventDefault();

            let listAjaxUrl,
                searchValue,
                categoryList,
                cropList,
                partnerList,
                expertList,
                typeList,
                sortValue,
                pageValue,
                $activeFilterContainer,
                activeFilterList;

            listAjaxUrl = $itemBlock.attr('data-list-ajax-url');
            searchValue = $itemBlock.find('.search-container input').val();
            categoryList = [];
            cropList = [];
            partnerList = [];
            expertList = [];
            typeList = [];
            sortValue = $itemBlock.find('.sorter').val();
            pageValue = data.type === 'pagination' ? data.value : 1;
            $activeFilterContainer = $('.active-filter-container');
            activeFilterList = [];
            /**
             *
             * @param item
             */
            function makeFilter(item) {
                let $this, value, name;
                $this = $(item);

                value = $this.val();
                name = $this.attr('data-name');

                activeFilterList.push({
                    id: name + '-' + value,
                    name: $this.siblings('.checkbox__title').html().trim(),
                });

                if (name === 'category') {
                    categoryList.push(value);
                }

                if (name === 'crop') {
                    cropList.push(value);
                }

                if (name === 'partner') {
                    partnerList.push(value);
                }

                if (name === 'expert') {
                    expertList.push(value);
                }
            }

            /**
             *
             */
            function buildFilter() {
                $itemBlock
                    .find('.js-desktop-filter .checkbox :checkbox')
                    .filter(':checked')
                    .each(function () {
                        makeFilter(this);
                    });

                $itemBlock
                    .find('.desktop-tag .tag-item-container .tag-item')
                    .each(function () {
                        let $this, name;
                        $this = $(this);

                        name = $this.attr('data-tag-name');

                        if ($this.hasClass('active')) {
                            typeList.push(name);
                        }
                    });
            }

            buildFilter();

            listAjaxUrl = listAjaxUrl
                .replace('SEARCH_VALUE', searchValue)
                .replace('CATEGORY_VALUES', categoryList.join(','))
                .replace('CROP_VALUES', cropList.join(','))
                .replace('PARTNER_VALUES', partnerList.join(','))
                .replace('EXPERT_VALUES', expertList.join(','))
                .replace('TYPE_VALUES', typeList.join(','))
                .replace('SORT_VALUE', sortValue)
                .replace('PAGE_VALUE', pageValue);

            this.showLoader();

            window.history.pushState(
                {},
                $(document).find('title').text(),
                listAjaxUrl
            );

            $.ajax({
                type: 'GET',
                url: listAjaxUrl + '&' + Math.random(),
                success: function (data) {
                    $itemBlock
                        .find('.js-item-list-block')
                        .html(data.content).promise().done(function () {
                        if ($activeFilterContainer.length) {
                            $activeFilterContainer.html('');
                            if (activeFilterList.length) {
                                for (let i = 0; i < activeFilterList.length; i++) {
                                    $activeFilterContainer.append(
                                        '<div data-active-checkbox-id="' + activeFilterList[i].id + '" class="active-filter__item"><span>' + activeFilterList[i].name + '</span><svg><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#cross"></use></svg></div>');
                                }
                            }
                        }
                    });
                    $itemBlock
                        .find('.js-item-filter-block')
                        .html(data.filter)
                    ;
                    this.hideLoader();
                }.bind(this)
            });
        }.bind(this));
    }

    /**
     *
     */
    assembleExpertFilter() {
        let $expertBlock;
        $expertBlock = $('.js-expert');

        $expertBlock.on('click', '.tag-item', function (event) {
            event.preventDefault();

            let $this, $filterToChange, tagName;
            $this = $(this);
            tagName = $this.attr('data-tag-name');

            $filterToChange = $this.closest('.mobile-tag').length
                ? $('.desktop-tag')
                : $('.mobile-tag');

            $this.hasClass('active') ? $this.removeClass('active') : $this.addClass('active');

            $filterToChange.find('[data-tag-name="' + tagName + '"]').hasClass('active') ?
                $filterToChange.find('[data-tag-name="' + tagName + '"]').removeClass('active') :
                $filterToChange.find('[data-tag-name="' + tagName + '"]').addClass('active');

            $expertBlock.trigger('expert-page-rebuild-asked', {
                type: 'filter'
            });
        });

        $expertBlock.on('click', '.js-expert-list-block .pagination a', function (event) {
            event.preventDefault();
            event.stopPropagation();

            let pagePropRel, pageValue, pageValueInt, currentPage, currentPageInt;

            pagePropRel = $(this).prop('rel');
            pageValue = $(this).html();
            pageValueInt = parseInt(pageValue);
            currentPage = $(this).closest('.pagination').find('.current').html();
            currentPageInt = parseInt(currentPage);

            if (pagePropRel === 'next') {
                pageValueInt = currentPageInt + 1;
            }

            if (pagePropRel === 'prev') {
                pageValueInt = currentPageInt - 1;
            }

            $expertBlock.trigger('expert-page-rebuild-asked', {
                type: 'pagination',
                value: pageValueInt
            });
        });

        $expertBlock.on('expert-page-rebuild-asked', function (event, data) {
            event.preventDefault();

            let listAjaxUrl,
                tagList,
                pageValue;

            listAjaxUrl = $expertBlock.attr('data-list-ajax-url');
            tagList = [];
            pageValue = data.type === 'pagination' ? data.value : 1;

            /**
             *
             */
            function buildFilter() {
                $expertBlock
                    .find('.desktop-tag .tag-item-container .tag-item')
                    .each(function () {
                        let $this, name;
                        $this = $(this);

                        name = $this.attr('data-tag-name');

                        if ($this.hasClass('active')) {
                            tagList.push(name);
                        }
                    });
            }

            buildFilter();

            listAjaxUrl = listAjaxUrl
                .replace('TAGS_VALUES', tagList.join(','))
                .replace('PAGE_VALUE', pageValue);

            this.showLoader();

            window.history.pushState(
                {},
                $(document).find('title').text(),
                listAjaxUrl
            );

            $.ajax({
                type: 'GET',
                url: listAjaxUrl + '&' + Math.random(),
                success: function (data) {
                    $expertBlock
                        .find('.js-expert-list-block')
                        .html(data.content);
                    this.hideLoader();
                }.bind(this)
            });
        }.bind(this));
    }

    /**
     * Partner filter
     */
    /**
     *
     */
    assembleDetailPartnerFilter() {
        let $detailPartnerBlock;
        $detailPartnerBlock = $('.js-detail-partner');

        $detailPartnerBlock.on('click', '.tag-item', function (event) {
            event.preventDefault();

            let $this, $filterToChange, tagName;
            $this = $(this);
            tagName = $this.attr('data-tag-name');

            $filterToChange = $this.closest('.mobile-tag').length
                ? $('.desktop-tag')
                : $('.mobile-tag');

            $this.hasClass('active') ? $this.removeClass('active') : $this.addClass('active');

            $filterToChange.find('[data-tag-name="' + tagName + '"]').hasClass('active') ?
                $filterToChange.find('[data-tag-name="' + tagName + '"]').removeClass('active') :
                $filterToChange.find('[data-tag-name="' + tagName + '"]').addClass('active');

            $detailPartnerBlock.trigger('detail-partner-page-rebuild-asked', {
                type: 'filter'
            });
        });

        $detailPartnerBlock.on('click', '.js-detail-partner-list-block .pagination a', function (event) {
            event.preventDefault();
            event.stopPropagation();

            let pagePropRel, pageValue, pageValueInt, currentPage, currentPageInt;

            pagePropRel = $(this).prop('rel');
            pageValue = $(this).html();
            pageValueInt = parseInt(pageValue);
            currentPage = $(this).closest('.pagination').find('.current').html();
            currentPageInt = parseInt(currentPage);

            if (pagePropRel === 'next') {
                pageValueInt = currentPageInt + 1;
            }

            if (pagePropRel === 'prev') {
                pageValueInt = currentPageInt - 1;
            }

            $detailPartnerBlock.trigger('detail-partner-page-rebuild-asked', {
                type: 'pagination',
                value: pageValueInt
            });
        });

        $detailPartnerBlock.on('detail-partner-page-rebuild-asked', function (event, data) {
            event.preventDefault();

            let listAjaxUrl,
                tagList,
                pageValue;

            listAjaxUrl = $detailPartnerBlock.attr('data-list-ajax-url');
            tagList = [];
            pageValue = data.type === 'pagination' ? data.value : 1;

            /**
             *
             */
            function buildFilter() {
                $detailPartnerBlock
                    .find('.desktop-tag .tag-item-container .tag-item')
                    .each(function () {
                        let $this, name;
                        $this = $(this);

                        name = $this.attr('data-tag-name');

                        if ($this.hasClass('active')) {
                            tagList.push(name);
                        }
                    });
            }

            buildFilter();

            listAjaxUrl = listAjaxUrl
                .replace('TAGS_VALUES', tagList.join(','))
                .replace('PAGE_VALUE', pageValue);

            this.showLoader();

            window.history.pushState(
                {},
                $(document).find('title').text(),
                listAjaxUrl
            );

            $.ajax({
                type: 'GET',
                url: listAjaxUrl + '&' + Math.random(),
                success: function (data) {
                    $detailPartnerBlock
                        .find('.js-detail-partner-list-block')
                        .html(data.content);
                    this.hideLoader();
                }.bind(this)
            });
        }.bind(this));
    }

    /**
     * Select all checkbox
     */
    selectAllCheckbox() {
        let $mobileAndDesktopCheckboxes;

        $mobileAndDesktopCheckboxes = $('.js-desktop-filter, .js-mobile-item-filter')
            .find('.accordion-block');

        $mobileAndDesktopCheckboxes
            .find('.js-accordion-block__open')
            .trigger('accordion-block-show');
        $mobileAndDesktopCheckboxes
            .find('.accordion-block__content .checkbox input')
            .prop('checked', true);
    }

    /**
     * Reset all checkbox
     */
    resetAllCheckbox() {
        let $mobileAndDesktopCheckboxes;

        $mobileAndDesktopCheckboxes = $('.js-desktop-filter, .js-mobile-item-filter')
            .find('.accordion-block');

        $mobileAndDesktopCheckboxes
            .find('.js-accordion-block__open')
            .trigger('accordion-block-hide');
        $mobileAndDesktopCheckboxes
            .find('.accordion-block__content .checkbox input')
            .prop('checked', false);
        $('.active-filter-container')
            .html('');
    }

    /**
     *
     * @param $element
     */
    showLoader($element) {
        if (!$element) {
            $element = this.$body;
        }
        $element.addClass('js-loading');
    }

    /**
     *
     * @param $element
     */
    hideLoader($element) {
        if ($element === undefined) {
            $element = this.$body;
        }
        $element.removeClass('js-loading');
    }
}

export default Filter;
