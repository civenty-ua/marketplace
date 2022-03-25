const $ = require('jquery');

$(document).ready(function () {
    $('tab nav-item').click(function () {
        if($(this).hasClass('selected')) return;

        let tab = $(this).closest('tab');
        $(tab).find('nav-item').removeClass('selected');
        $(tab).find('tab-content').removeClass('active').eq($('tab nav-item').index(this)).addClass('active');
        $(this).addClass('selected');
    });
});