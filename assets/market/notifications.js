import Swal from 'sweetalert2';

const $ = require('jquery');

import {
    showLoader,
    hideLoader,
} from '../app.js';
import {PopupWindowForm} from "../modals";

$(document).ready(function () {

    window.checkedCheckboxes = null;

    /**
     * Single notification click to detail action
     */
    $(document).on('click', '.notification-item', function (event) {
        event.preventDefault();
        window.location.href = $(event.currentTarget).attr('data-url');
    });

    $(document).on('click', '.notification-checkbox', function (event) {
        event.stopPropagation();
    });

    assembleNotificationFilter();

    function assembleNotificationFilter() {
        let $notificationListBlock, $itemBlock, searchValue;
        $notificationListBlock = $('.js-notification-block');
        $itemBlock = $('.personal-area-center__tab');
        searchValue = $itemBlock.find('.js-notification-search').val();

        $notificationListBlock.on('change', '.notification-sorter', function(event) {
            event.preventDefault();

            $notificationListBlock.trigger('notification-page-rebuild-asked', {
                type: 'filter'
            });
        });

        $notificationListBlock.on('click', '.notification-search', function(event) {
            event.preventDefault();
            $notificationListBlock.trigger('notification-page-rebuild-asked', {
                type: 'search'
            });
        });

        $notificationListBlock.on('notification-page-rebuild-asked', function(event, data) {
            event.preventDefault();

            let listAjaxUrl,
                typeValue,
                searchValue,
                pageValue;

            listAjaxUrl = $notificationListBlock.attr('data-list-ajax-url');
            searchValue = $itemBlock.find('.js-notification-search').val();
            typeValue = $itemBlock.find('.notification-sorter').val();
            pageValue = data.type === 'pagination' ? data.value : 1;


            listAjaxUrl = listAjaxUrl
                .replace('SEARCH_VALUE', searchValue)
                .replace('TYPE_VALUES', typeValue)
                .replace('PAGE_VALUE', pageValue);

            showLoader();

            $(document).trigger('setUrl', listAjaxUrl);

            $.ajax({
                type: 'GET',
                url: listAjaxUrl + '&' + Math.random(),
                success: function(data) {
                    $notificationListBlock
                        .find('.notification-list')
                        .html(data.content);
                    hideLoader();
                }.bind(this)
            });
        }.bind(this));
    }

    assembleNotificationBatchDelete();

    function assembleNotificationBatchDelete() {
        let $notificationListBlock;
        $notificationListBlock = $('.js-notification-block');

        $notificationListBlock.on('click', '.notification-delete-trigger', function(event) {
            event.preventDefault();
            let notificationsId = [];
            let batchDeleteUrl = $('.notification-delete-trigger').attr('data-url');

            $('.notification-checkbox :checkbox:checked').each(function (index, element) {
                notificationsId.push(element.getAttribute('data-id'));
            });

            $.ajax({
                type: 'GET',
                url: batchDeleteUrl,
                data: {},
                beforeSend: function() {
                    showLoader();
                },
                success: function(data) {
                    hideLoader();
                    let params = {
                        confirmButtonText: data.confirmButtonText,
                        cancelButtonText: data.cancelButtonText,
                    };
                    let Popup = new PopupWindowForm(
                        '',
                        'warning',
                        data.title,
                        params
                    );
                    Popup.on('onConfirm', () =>{
                        $.ajax({
                            type: 'POST',
                            url: batchDeleteUrl,
                            data: {
                                notificationsIds: notificationsId
                            },
                            beforeSend: function() {
                                showLoader();
                            },
                            success: function(data) {
                                hideLoader();
                                window.location.reload();
                            }
                        });
                    }).show();
                }
            });
        });
    }

    assembleNotificationBucketClear();

    function assembleNotificationBucketClear() {
        let $notificationListBlock = $('.js-notification-block');

        $notificationListBlock.on('click', '.js-notification-bucket-clear', function(event) {
            event.preventDefault();
            let $notificationBucketClearButton = $('.js-notification-bucket-clear');

            let params = {
                confirmButtonText: 'Так',
                cancelButtonText: 'Відміна',
            };
            let Popup = new PopupWindowForm(
                '',
                'warning',
                'Очистити кошик? Відновити ці данні буде неможливо.',
                params
            );
            Popup.on('onConfirm', () =>{
                $.ajax({
                    type: 'GET',
                    url: $notificationBucketClearButton.attr('data-url'),
                    data: {},
                    beforeSend: function() {
                        showLoader();
                    },
                    success: function() {
                        hideLoader();
                        window.location.reload();
                    }
                });
            }).show();
        });
    }

    checkboxDeleteAllState();

    function checkboxDeleteAllState(){
        let $selectAllDeleteTrigger = $('.js-select-all__delete-trigger');

        if ($('.notification-checkbox :checkbox:checked').length > 0) {
            $selectAllDeleteTrigger.addClass('select-all__delete-trigger--active');
        } else {
            $selectAllDeleteTrigger.removeClass('select-all__delete-trigger--active');
        }
    }
    /**
     * Single notification checkbox trigger trash can
     */
    $(document).on('change', '.notification-checkbox :checkbox', function (event) {
        event.preventDefault();

        checkboxDeleteAllState()
    });
});
