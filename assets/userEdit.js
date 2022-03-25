const $ = require("jquery");

document.addEventListener('DOMContentLoaded', function() {

    $(document).on('change','select[data-entity="region"]', function() {
        let $region = $('select[data-entity="region"]');
        let $district = $('select[data-entity="district"]');
        let $locality = $('select[data-entity="locality"]');
        let data = {};
        data['name'] = $region.attr('name');
        if (!$region.val().length) {
            $district.empty()
            $locality.empty()
            return true;
        }
        $.ajax({
            url: '/ajax/get-districts/' + $region.val(),
            type: 'POST',
            data: data,
            success: function (html) {
                $district.parent().html(html.select);
                $locality.parent().html(html.selectLocalities);
            },
            error: function (res) {
                console.log(res)
            }
        });
    });

    $(document).on('change','select[data-entity="district"]', function() {
        let $district = $('select[data-entity="district"]');
        let $locality = $('select[data-entity="locality"]');
        let data = {};
        data['name'] = $district.attr('name');
        if (!$district.val().length) {
            $locality.empty();
            return true;
        }
        $.ajax({
            url: '/ajax/get-localities/' + $district.val(),
            type: 'POST',
            data: data,
            success: function (html) {
                $locality.parent().html(html.select);
            },
            error: function (res) {
                console.log(res)
            }
        });
    });
});
