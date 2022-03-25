const $ = require('jquery');

$(document).ready(function () {
    $(document)
        .on('keydown', '.market-search input', function () {
            let
                $input  = $(this),
                $search = $input.closest('.market-search');

            $input.val().length > 0
                ? $search.removeClass('empty')
                : $search.addClass('empty');
        })
        .on('click', '.market-search .clear-button', function () {
            let
                $search = $(this).closest('.market-search'),
                $input  = $search.find(':input');

            $input
                .val('')
                .trigger('keydown')
                .trigger('change');
        })
        .on('click', '.market-search .search-button', function () {
            $(this)
                .closest('.market-search')
                .find(':input')
                .trigger('change');
        })
        .on('keydown', '.market-search :input', function (event) {
            if (event.keyCode === 13) {
                event.preventDefault();
                $(this)
                    .closest('.market-search')
                    .find(':input')
                    .trigger('change')
                    .blur();
            }
        });
});
