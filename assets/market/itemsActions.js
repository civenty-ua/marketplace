const $             = require('jquery');
const {
          showLoader,
          hideLoader
      }             = require('../app');
const {PopupWindow} = require('../modals');

$(document).ready(function() {
    $(document)
        /** *********************************************
         * Favorites
         ***********************************************/
        .on('click', '.item-favorite-toggle', function() {
            let $toggle = $(this);

            showLoader();
            $.ajax({
                type    : 'POST',
                url     : $toggle.attr('data-action-url'),
                success : function(response) {
                    let result = Boolean(response['isAdded'] || false);

                    result
                        ? $toggle.addClass('added')
                        : $toggle.removeClass('added');

                    hideLoader();
                },
                error   : function() {
                    hideLoader();
                }
            });
        })
        .on('click', '.item-favorite-remove', function() {
            let $toggle = $(this);

            showLoader();
            $.ajax({
                type    : 'POST',
                url     : $toggle.attr('data-action-url'),
                success : function(response) {
                    window.location.reload();
                },
                error   : function() {
                    hideLoader();
                }
            });
        })
        /** *********************************************
         * Commodity activation
         ***********************************************/
        .on(
            'click',
            '.item-container .item .commodity-activation-bar .activation-button, ' +
            '.item-container .item .item-activity-toggle',
            function() {
                let
                    $toggle     = $(this),
                    $itemTablet = $toggle.closest('.item-container .item');

                showLoader();
                $.ajax({
                    type    : 'POST',
                    url     : $toggle.attr('data-action-url'),
                    success : function(response) {
                        let isSuccess = Boolean(response['success'] || false);

                        if (isSuccess) {
                            $itemTablet.html(response.item);
                            window.location.reload();
                        } else {
                            (new PopupWindow(null, 'warning', response.message ?? '')).show();
                            hideLoader();
                        }
                    },
                    error   : function() {
                        hideLoader();
                    }
                });
        })
        /** *********************************************
         * Kit leaving
         ***********************************************/
        .on('click', '.item-container .item .kit-leaving-toggle', function() {
            let
                $toggle     = $(this),
                title       = $toggle.attr('data-alert-title'),
                message     = $toggle.attr('data-alert-message'),
                cancelTitle = $toggle.attr('data-alert-cancel'),
                popup       = new PopupWindow(title, 'warning', message, {
                    cancelButtonText : cancelTitle,
                    showCancelButton : true,
                });

            popup.on('onConfirm', function() {
                showLoader();
                $.ajax({
                    type    : 'POST',
                    url     : $toggle.attr('data-action-url'),
                    success : function(response) {
                        let isSuccess = Boolean(response['success'] || false);

                        if (isSuccess) {
                            window.location.reload();
                        } else {
                            (new PopupWindow(null, 'warning', response.message ?? '')).show();
                            hideLoader();
                        }
                    },
                    error   : function() {
                        hideLoader();
                    }
                });
            });
            popup.show();
        });
});
