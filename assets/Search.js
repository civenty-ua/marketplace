import $ from 'jquery';

/**
 *
 */
class Search {

    /**
     *
     */
    constructor() {
        this.$body = $('body');
        this.$searchInput = $('.search-input');
        this.$searchInputClear = $('.js-search-input__clear');

        /**
         *
         */
        function assembleTooltip() {
            $('.js-tooltip').tooltip();
        }

        /**
         *
         */
        function hideTooltip() {
            $('.js-tooltip').tooltip('hide');
        }

        /**
         *
         * @param element
         */
        function appendCross(element) {
            $.each(element, function () {
                let $this, crossHtml, crossTooltipText;

                $this = $(this);
                crossTooltipText = $this.closest('.js-search-input').find('.js-search-input__clear').attr(
                    'data-clear-tooltip-title')
                crossHtml = '<i class="js-tooltip fas fa-times" data-toggle="tooltip" data-placement="auto" title="' + crossTooltipText + '">' + '</i>';

                if ($this.val()) {
                    $this
                        .closest('.js-search-input')
                        .find('.js-search-input__clear')
                        .html(crossHtml);

                    assembleTooltip();
                } else {
                    $this
                        .closest('.js-search-input')
                        .find('.js-search-input__clear')
                        .html('');

                    hideTooltip();
                }
            });
        }

        /**
         *
         * @param element
         */
        function searchAll(element) {
            let $this, locale, query;

            $this = element;
            locale = $this.attr('data-locale');

            if ($this.find('.search-input').val()) {
                query = $this.find('.search-input').val();
            } else {
                query = '';
            }

            switch (locale) {
                case 'uk':
                    window.location.href = '/search-all?q=' + query;
                    break;
                case 'en':
                    window.location.href = '/en/search-all?q=' + query;
                    break;
            }
        }

        this.$body.on('submit', '.js-search-all', function (event) {
            event.preventDefault();

            hideTooltip();

            let $this;

            $this = $(this);

            searchAll($this);
        });

        this.$body.on('keyup', '.search-input', function (event) {
            event.preventDefault();

            hideTooltip();

            let $this;

            $this = $(this);

            appendCross($this);

            if (event.keyCode === 13) {
                $(this)
                    .closest('.search')
                    .find('.search__button')
                    .click();
            }
        });

        appendCross(this.$searchInput);

        this.$searchInputClear.on('click', function (event) {
            event.preventDefault();

            hideTooltip();

            let $this, $searchInput;

            $this = $(this);
            $searchInput = $this.closest('.js-search-input').find('.search-input');

            if ($searchInput.val()) {
                $searchInput
                    .val('')
                    .trigger('change');
                $this.closest('.js-search-input').find('.js-search-input__clear').html('');
                $searchInput.closest('.search').find('.search__button').submit();

                if ($searchInput.closest('.search').parent().hasClass('js-search-all')) {
                    searchAll($searchInput);
                }

                if ($searchInput.closest('.search').parent().hasClass('js-search-container')) {
                    $searchInput.closest('.search').find('.search__button').click();
                }
            }
        });
    }
}

export default Search;
