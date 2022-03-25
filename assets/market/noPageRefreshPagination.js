const $ = require('jquery');

$(document).ready(function () {
    $(document).on('click', '.js-no-page-refresh-navigation a', function(event) {
        let
            $link       = $(this),
            $navigation = $link.closest('.js-no-page-refresh-navigation'),
            currentPage = parseInt($navigation.find('.current').html()),
            needPage;

        event.preventDefault();
        event.stopPropagation();

        if ($link.prop('rel') === 'next') {
            needPage = currentPage + 1;
        } else if ($link.prop('rel') === 'prev') {
            needPage = currentPage - 1;
        } else {
            needPage = parseInt($link.html());
        }

        $navigation
            .find(':input')
            .val(needPage)
            .trigger('change');
    });
});
