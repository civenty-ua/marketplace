/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

import './popper.min';
// start the Stimulus application
import './bootstrap';

const $ = require('jquery');
require('bootstrap');

import 'jquery-steps/build/jquery.steps';
import 'jquery-validation';
import Inputmask from 'inputmask';
import Glide from '@glidejs/glide';
import Swal from 'sweetalert2'
import 'raty-js';
import 'select2';
import 'jquery.scrollbar';
import shareon from 'shareon';
import {Calendar} from '@fullcalendar/core';
import {formatDate} from '@fullcalendar/core'
import interactionPlugin from '@fullcalendar/interaction';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import uk from '@fullcalendar/core/locales/uk';
import en from '@fullcalendar/core/locales/en-gb';
import locales from '@fullcalendar/core/locales-all';
import Cookies from 'js-cookie';
import noUiSlider from 'nouislider';
import {Fancybox} from '@fancyapps/ui';
import Filter from './Filter';
import Search from './Search';
import {PopupWindow, PopupFlashes, PopupNotification} from './modals';

Fancybox.bind('[data-fancybox]');
new Filter();
new Search();

let $body, $window, $document, $header;

$body = $('body');
$window = $(window);
$document = $(document);
$header = $('header');

/**
 *  LOADER
 */

export function showLoader($element) {
    if (!$element) {
        $element = $body;
    }
    $element.addClass('js-loading');
}

export function hideLoader($element) {
    if ($element === undefined) {
        $element = $body;
    }
    $element.removeClass('js-loading');
}

/**
 * Select2
 */
export function initSelect2() {
    $('.js-select2').each(function () {
        let dropdownCssClass, closeOnSelect;
        dropdownCssClass = !!$(this).attr('multiple') ? 'multiple smat' : 'smat';
        closeOnSelect = !$(this).attr('multiple');

        $(this).select2({
            dropdownCssClass: dropdownCssClass,
            closeOnSelect: closeOnSelect,
            minimumResultsForSearch: Infinity
        })
            .on('select2:open', function () {
                $body.find('.select2-results__options').scrollbar();
                $body.find('.select2-dropdown.smat').css('opacity', 1);
            })
            .on('select2SetData', function (event, data) {
                $(this)
                    .val(data)
                    .trigger('change.select2');
            });
    });
}
export function closeAllSelect2() {
    $('.js-select2').each(function () {
        $(this).select2('close');
    });
}

/**
 * Inputs range
 */
export function initNumbersRange() {
    $('.js-price-slider')
        .not('[data-initialized]')
        .each(function () {
            let
                mainNode = $(this)[0],
                $inputs = $(this).parent().find('input'),
                $inputFrom = $inputs.first(),
                $inputTo = $inputs.last(),
                maxValue = parseInt($(this).attr('data-max-value') || 100000);

            $(this).attr('data-initialized', 'Y');

            noUiSlider.create(mainNode, {
                start: [
                    $inputFrom.val(),
                    $inputTo.val(),
                ],
                range: {
                    'min'   : [0],
                    'max'   : [maxValue]
                }
            });

            mainNode.noUiSlider.on('update', function (values, handle) {
                let
                    valueRound  = Math.round(values[handle] ?? null),
                    $needInput  = handle === 1 ? $inputTo : $inputFrom;

                $needInput
                    .val(valueRound > 0 ? valueRound : null)
                    .attr('value', valueRound);
            });
            $inputFrom.add($inputTo).on('input', function(event) {
                let
                    value           = $(this).val(),
                    valueCleared    = value.replace(/[^0-9.]/g, '');

                $(this).val(valueCleared);
                if (event.keyCode === 13) {
                    $inputFrom.trigger('change');
                }
            });
            $inputFrom.on('change', function() {
                if ($inputFrom.val() && !$inputTo.val() && maxValue > 0) {
                    $inputTo.val(maxValue);
                }
            });
            mainNode.noUiSlider.on('change', function () {
                $inputFrom.trigger('change');
            });
        });
}



$document.ready(function () {
    window.options = window.options ?? [];

    let flashes = document.querySelector('#notifications').content.querySelectorAll('div.modal');
    if (flashes) {
        let popupService = new PopupFlashes();
        flashes.forEach((item) => {
            /*
        $userRoles = $this->getUser()->getRoles();
        $renderedMessage = $this->render('modal/welcome-modal.html.twig', ['roles' => $userRoles])->getContent();
        $this->addFlash('html', $renderedMessage);
        $this->addFlash('success', 'wefq');
        $this->addFlash('info', 'info');
        $this->addFlash('error', 'error');
        $this->addFlash('warning', 'warning');
        $this->addFlash('question', 'question');
        $this->addFlash('notification.success', 'notification');
             */
            let itemAttr = item.getAttribute('data-type').split('.');
            switch (itemAttr[0]) {
                case 'html': {
                    let modalParams = {showCloseButton: true, html: item.textContent};
                    let Popup = new PopupWindow('', itemAttr[1], false, modalParams);
                    popupService.addToQueue(Popup);
                }
                    break;
                case 'notification': {
                    let notifier = new PopupNotification('', itemAttr[1], item.textContent);
                    popupService.addToQueue(notifier);
                }
                    break;
                default: {
                    let Popup = new PopupWindow('', itemAttr[0], item.textContent);
                    popupService.addToQueue(Popup);
                }
                    break;
            }
        });

        popupService.show();
    }

    $('body').on('input', '.js-textarea', function () {
        let
            $textarea   = $(this),
            $counter    = $textarea
                .parent()
                .find('.js-textarea-count__current-count');

        $counter.text($textarea.val().length);
    });

    $('.js-demo-add ').on('click', function (event) {
        event.preventDefault();

        $('.js-demo-add-block').hide();
        $('.wizard-content').show();
    });

    /**
     * SELECT ALL
     */

    let $selectAllList, $selectAllDeleteTrigger;
    $selectAllList = $('.js-select-all__list');
    $selectAllDeleteTrigger = $('.js-select-all__delete-trigger');

    $document.on('change', '.js-select-all :checkbox', function (event) {
        event.preventDefault();

        let $this;
        $this = $(this);

        if ($this.is(':checked')) {
            $selectAllList
                .each(function () {
                    let $this = $(this);

                    $this.find('.checkbox input')
                        .prop('checked', true);
                });
            $selectAllDeleteTrigger.addClass('select-all__delete-trigger--active');
        } else {
            $selectAllList
                .each(function () {
                    let $this = $(this);

                    $this.find('.checkbox input')
                        .prop('checked', false);
                });
            $selectAllDeleteTrigger.removeClass('select-all__delete-trigger--active');
        }
    });

    /**
     * EDIT ROW
     */

    $document.on('click', '.js-edit-row', function (event) {
        event.preventDefault();

        let $this;
        $this = $(this);

        $this.hide();
        $this.parent().find('.js-row').hide();
        $this.parent().find('.js-edit-row__container').addClass('edit-row__container--visible');
    });

    $document.on('click', '.js-edit-row__close-trigger', function (event) {
        event.preventDefault();

        let $this;
        $this = $(this);

        $this.parent().removeClass('edit-row__container--visible');
        $this.parent().parent().find('.js-row').show();
        $this.parent().parent().find('.js-edit-row').show();
    })

    /**
     * ADD PHONE ROW
     */

    $document.on('click', '.js-add-phone-row', function (event) {
        event.preventDefault();

        let $phoneRowItem = $('.js-phone-row-item');

        $phoneRowItem
            .eq(0)
            .clone()
            .find('input').val('').end()
            .show()
            .insertAfter('.js-phone-row-item:last');

        $('input[data-phone-mask]').each(function () {
            let mask = $(this).attr('data-phone-mask');
            $(this).mask(mask);
        });
    })

    /**
     * WIZARD FORM WITH VALIDATION
     */

    let form = $('.validation-wizard').show();

    $('.validation-wizard').steps({
        headerTag: 'h6',
        bodyTag: 'section',
        transitionEffect: 'fade',
        titleTemplate: '<span class="step">#index#</span> #title#',
        labels: {
            finish: 'Опублікувати',
            next: "<span class=\"marginRight10px\">Далі</span><i class=\"fas fa-angle-right\"></i>",
            previous: "<i class=\"fas fa-angle-left\"></i><span class=\"marginLeft10px\">Назад</span>",
        },
        onStepChanging: function (event, currentIndex, newIndex) {
            return currentIndex > newIndex || !(3 === newIndex && Number(
                $('#age-2').val()) < 18) && (currentIndex < newIndex && (form.find(
                '.body:eq(' + newIndex + ') label.error').remove(), form.find(
                '.body:eq(' + newIndex + ') .error').removeClass(
                'error')), form.validate().settings.ignore = ':disabled,:hidden', form.valid())
        },
        onFinishing: function (event, currentIndex) {
            return form.validate().settings.ignore = ':disabled', form.valid()
        },
        onFinished: function (event, currentIndex) {
            form.hide();
            $('.js-demo-list').show();
        }
    }), $('.validation-wizard').validate({
        ignore: 'input[type=hidden]',
        errorClass: 'text-danger',
        successClass: 'text-success',
        highlight: function (element, errorClass) {
            $(element).removeClass(errorClass)
        },
        unhighlight: function (element, errorClass) {
            $(element).removeClass(errorClass)
        },
        errorPlacement: function (error, element) {
            error.insertAfter(element)
        },
        rules: {
            email: {
                email: !0
            }
        }
    });

    window.options = window.options ?? [];

    $('.js-offer-form__open').click(function (event) {
        event.preventDefault();
        event.stopPropagation();

        $.ajax({
            type: 'GET',
            url: $(this).attr('href'),
            data: {},
            beforeSend: function () {
                showLoader();
            },
            success: function (data) {
                Swal.fire({
                    html: data.form,
                    showConfirmButton: false,
                    showCancelButton: false,
                    showCloseButton: true,
                    showClass: {
                        popup: 'animate__animated animate__fadeIn animate__faster'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOut animate__faster'
                    }
                })
                $('[data-toggle="tooltip"]').tooltip();
                $('input[data-phone-mask]').each(function () {
                    let mask = $(this).attr('data-phone-mask');
                    $(this).mask(mask);
                });
            },
            error: function (data) {

            },
            complete: function () {
                hideLoader();
            }
        });
    });

    /**
     * RANGE SLIDER
     */
    initNumbersRange();
    // hide loader when the page has loaded
    hideLoader();

    /**
     * SHARE SOCIAL LINKS
     */

    shareon();

    /**
     * TOOLTIP
     */

    $('[data-toggle="tooltip"]').tooltip();

    /**
     * CALENDAR
     */

    /**
     *
     * @param calendarCatList
     */
    function assembleCalendar(calendarCatList = null) {
        let jsCalendar = document.querySelector('#js-calendar');
        let locale, calendar, calendarEventList, eventList;
        if (jsCalendar) {
            locale = jsCalendar.getAttribute('data-locale') === 'uk' ? 'uk' : 'en';
            calendarEventList = document.querySelectorAll('.js-calendar-event-list .calendar-event-item');

            eventList = [];
            window.calendarEvents = {};

            calendarEventList.forEach(function (element) {
                let date = element.getAttribute('date-day');
                if (!window.calendarEvents.hasOwnProperty(date)) {
                    window.calendarEvents[date] = [];
                }

                window.calendarEvents[date].push({
                    id: element.getAttribute('data-id'),
                    title: element.getAttribute('data-title'),
                    start: element.getAttribute('data-start'),
                    url: element.getAttribute('data-url'),
                    extendedProps: {
                        category: element.getAttribute('data-category'),
                        partners: element.getAttribute('data-partners')
                    }
                });

                eventList.push({
                    id: element.getAttribute('data-id'),
                    title: element.getAttribute('data-title'),
                    start: element.getAttribute('data-start'),
                    url: element.getAttribute('data-url'),
                    extendedProps: {
                        category: element.getAttribute('data-category'),
                        partners: element.getAttribute('data-partners')
                    }
                });
            });

            if (eventList.length > 0) {
                eventList.forEach(function (key, value) {
                    let partnerStrings = key.extendedProps.partners.split(' ')
                    let partnersIds = [];

                    if (partnersIds) {
                        partnerStrings.forEach(function (value) {
                            partnersIds.push(value)
                        })
                    }

                    if (calendarCatList) {
                        if (calendarCatList.length > 0) {
                            if (calendarCatList.some(r => partnersIds.includes(r))) {
                                key.display = 'auto';
                            } else {
                                key.display = 'none';
                            }
                        } else {
                            key.display = 'auto';
                        }
                    }
                });
            }
            calendar = new Calendar(jsCalendar, {
                plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
                eventTimeFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    meridiem: false
                },
                initialView: 'dayGridMonth',
                locales: locales,
                locale: locale,
                events: eventList,
                datesSet: function(dateInfo) {
                    responsiveCalendar()
                },
                windowResize: function(view) {
                    responsiveCalendar()
                },
                dateClick: function(dateInfo) {
                    let date = dateInfo.dateStr;
                    let htmlEvents = '';
                    let format = {
                        // weekday: 'long',
                        month: 'long',
                        day: 'numeric',
                        year: 'numeric',
                        locale: locale
                    };

                    let dateString = formatDate(date, format);

                    if (window.calendarEvents.hasOwnProperty(date)) {
                        window.calendarEvents[date].forEach(function (singleEvent) {
                            htmlEvents += '<div class="mb-2 text-left"><span class="calendar-event-dot mr-2"></span><a href="' + singleEvent.url + '">' + singleEvent.title + '</a></div>'
                        });
                    }

                    if (window.innerWidth < 768 && htmlEvents) {
                        Swal.fire({
                            title: dateString,
                            html: htmlEvents,
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#007a33',
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: 'swal2-oun-style-button'
                            },
                            showClass: {
                                popup: 'animate__animated animate__fadeIn animate__faster'
                            },
                            hideClass: {
                                popup: 'animate__animated animate__fadeOut animate__faster'
                            }
                        })
                    }
                },
                dayCellDidMount: function (dayCellArg) {
                    let dateString = dayCellArg.el.dataset.date;

                    if (window.calendarEvents.hasOwnProperty(dateString)) {
                        dayCellArg.el.classList.add('has-events');
                    } else {
                        dayCellArg.el.classList.add('no-events');
                    }
                },
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listWeek'
                }
            });

            window.calendar = calendar;

            calendar.render();
        }
    }

    function responsiveCalendar() {
        /* for next refactoring :) */
    }

    /**
     *
     * @param categoryItemList
     */
    function updateCalendar(categoryItemList) {
        let calendarCatList = [];

        $(categoryItemList).filter('.active').each(function (key, value) {
            if ($(value).hasClass('active')) {
                if (!calendarCatList.includes(value.getAttribute('data-category'))) {
                    calendarCatList.push(value.getAttribute('data-category'));
                }
            }
        })

        assembleCalendar(calendarCatList);
    }

    assembleCalendar();

    $body.on('click', '.js-btn-calendar-category', function (event) {
        event.preventDefault();

        let $this, category, $categoryItemList;
        $this = $(this);
        category = $(this).data('category');
        $categoryItemList = $(this).parent().find('.js-btn-calendar-category');

        $this.toggleClass('active');
        if ($this.is('.active')) {
            $this.closest('.calendar-period').find('.calendar-content').find(
                '[data-category = "' + category + '"]').addClass('active');
        } else {
            $this.closest('.calendar-period').find('.calendar-content').find(
                '[data-category = "' + category + '"]').removeClass('active');
            if (!$this.siblings('.active').length) {
                $this.closest('.calendar-filters').find('.js-btn-calendar-all-category').removeClass('active');
            }
        }

        updateCalendar($categoryItemList);
    });

    $body.on('click', '.js-btn-calendar-all-category', function (event) {
        event.preventDefault();

        let $this, $categoryItemList;

        $this = $(this)
        $categoryItemList = $this.parent().parent().find('.calendar-filters__category').find(
            '.js-btn-calendar-category');

        if ($this.hasClass('selected')) {
            $this.removeClass('selected');
            $this.find('span').text($this.data('select-all-text'))
            $categoryItemList.removeClass('active');
        } else {
            $this.addClass('selected');
            $this.find('span').text($this.data('clear-all-text'))
            $categoryItemList.addClass('active');
        }

        updateCalendar($categoryItemList);
    });

    /**
     *
     */

    if (window.options.authorized !== undefined && window.options.authorized) {
        $(document).on('click', 'a', function (e) {
            let link = $(this).attr('href');
            if (isFileLink(link)) {
                e.preventDefault();
                let linkTitle = $(this).attr('title');
                let linkText = $(this).text();
                let text = null;

                if (linkText) {
                    text = linkText;
                } else if (linkTitle) {
                    text = linkTitle;
                }
                let ext = isFileLink(link, true);
                $.ajax({
                    type: 'POST',
                    url: window.options.ajaxLinks.downloadFile,
                    dataType: 'json',
                    data: {
                        text: text,
                        host: window.options.home,
                        link: link
                    },
                    success: function (response) {
                        if (response.success === true) {
                            if (ext === 'pdf' ||
                                ext === 'png' ||
                                ext === 'jpeg' ||
                                ext === 'jpg') {
                                download_file(link, text);
                            } else {
                                window.location.href = link;
                            }
                        } else {
                            window.location.reload();
                        }
                    }
                });
            }
        });
    }

    function download_file(fileURL, fileName) {
        // for non-IE
        if (!window.ActiveXObject) {
            var save = document.createElement('a');
            save.href = fileURL;
            save.target = '_blank';
            var filename = fileURL.substring(fileURL.lastIndexOf('/') + 1);
            save.download = decodeURI(filename);
            if (navigator.userAgent.toLowerCase().match(/(ipad|iphone|safari)/) && navigator.userAgent.search("Chrome") < 0) {
                document.location = save.href;
                // window event not working here
            } else {
                var evt = new MouseEvent('click', {
                    'view': window,
                    'bubbles': true,
                    'cancelable': false
                });
                save.dispatchEvent(evt);
                (window.URL || window.webkitURL).revokeObjectURL(save.href);
            }
        }

        // for IE < 11
        else if (!!window.ActiveXObject && document.execCommand) {
            var _window = window.open(fileURL, '_blank');
            _window.document.close();
            _window.document.execCommand('SaveAs', true, fileName || fileURL)
            _window.close();
        }
    }

    function isFileLink(link, checkRealExtension) {
        if (link) {
            let ext = link.split('.').pop();
            let host = window.location.hostname;

            if (link.indexOf(host) < 0 && link.indexOf('/') !== 0) {
                return false;
            }
            if (checkRealExtension !== undefined && checkRealExtension === true) {
                return ext;
            }
            return ext === 'doc' ||
                ext === 'docx' ||
                ext === 'xls' ||
                ext === 'xlsx' ||
                ext === 'pdf' ||
                ext === 'png' ||
                ext === 'jpeg' ||
                ext === 'jpg' ||
                ext === 'csv';
        }
    }

    /**
     *
     * @param title
     * @param text
     * @param type
     * @param color
     */
    let showPopup = function (title, text, type = 'info', color = '#007a33') {
        Swal.fire({
            title: title,
            text: text,
            icon: type,
            confirmButtonText: 'OK',
            confirmButtonColor: color,
            buttonsStyling: false,
            customClass: {
                confirmButton: 'swal2-oun-style-button'
            },
            showClass: {
                popup: 'animate__animated animate__fadeIn animate__faster'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOut animate__faster'
            }
        })
    };

    if (window.flashes) {
        flashes.forEach(function (message) {
            showPopup('', message, 'success', '#007a33')
        });
    }

    /**
     * DOWNLOAD FILE
     */

    $body.on('click', '.file-link', function (event) {
        event.preventDefault();

        let fileLink = $(this).attr('href');
        let fileLinkTitle, fileLinkCancelBtnTxt, fileLinkConfBtnTxt;

        if (document.documentElement.lang === 'en') {
            fileLinkTitle = 'To download files you have to be logged in.'
            fileLinkConfBtnTxt = 'Login';
            fileLinkCancelBtnTxt = 'Cancel';
        } else {
            fileLinkTitle = 'Щоб завантажувати файли вам потрібно авторизуватись.'
            fileLinkConfBtnTxt = 'Авторизація';
            fileLinkCancelBtnTxt = 'Скасувати';
        }
        if (fileLink === '#') {
            Swal.fire({
                title: '',
                text: fileLinkTitle,
                icon: 'warning',
                confirmButtonText: '<a href="/login" style="color: #ffffff">'.concat(fileLinkConfBtnTxt).concat('</a>'),
                confirmButtonColor: '#007a33',
                cancelButtonText: fileLinkCancelBtnTxt,
                buttonsStyling: true,
                showCancelButton: true,
                customClass: {
                    confirmButton: 'swal2-oun-style-button',
                },
                showClass: {
                    popup: 'animate__animated animate__fadeIn animate__faster'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOut animate__faster'
                }
            })
        }
    });

    /**
     * INPUT MASK
     */


    addPhoneMask();

    function addPhoneMask() {
        let $phoneInput = $('.js-phone-mask');

        $phoneInput.each(function() {
            Inputmask({'mask': '+38 (999) 999 99 99'}).mask($(this));
        })
    }

    /**
     * RATING STARS
     */
    $('.js-rating-readonly').raty({
        half: true,
        starType: 'i',
        readOnly: true,
    });
    $('.js-rating').each(function () {
        $(this).raty({
            half: $(this).attr('data-half') === 'y',
            starType: 'i',
            readOnly: false,
            beforeSend: function () {
                showLoader();
            },
            click: function (score, evt) {
                $.ajax({
                    type: 'POST',
                    url: $(this).attr('data-action'),
                    dataType: 'json',
                    data: JSON.stringify(score),
                    success: function (data) {
                        Swal.fire({
                            title: data.title,
                            icon: 'success',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#007a33',
                            buttonsStyling: false,
                            allowOutsideClick: false,
                            customClass: {
                                confirmButton: 'swal2-oun-style-button'
                            },
                            showClass: {
                                popup: 'animate__animated animate__fadeIn animate__faster'
                            },
                            hideClass: {
                                popup: 'animate__animated animate__fadeOut animate__faster'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                document.location.reload();
                            }
                        })
                    },
                    error: function (response) {
                        if (response.status === 401) {
                            Swal.fire({
                                title: response.responseJSON.title,
                                icon: 'info',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#007a33',
                                buttonsStyling: false,
                                allowOutsideClick: false,
                                showCancelButton: true,
                                customClass: {
                                    confirmButton: 'swal2-oun-style-button',
                                    cancelButton: 'swal2-oun-style-button'
                                },
                                showClass: {
                                    popup: 'animate__animated animate__fadeIn animate__faster'
                                },
                                hideClass: {
                                    popup: 'animate__animated animate__fadeOut animate__faster'
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = response.responseJSON.login;
                                }
                            })
                        }
                    }
                });
            }
        });
    });


    /**
     * DROPDOWN
     */

    let $dropdown, $dropdownTrigger, $dropdownContent;

    $dropdown = $('.js-dropdown');
    $dropdownTrigger = $('.js-dropdown__trigger');
    $dropdownContent = $('.js-dropdown__content');

    $dropdownTrigger.on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();

        $(this).closest($dropdown).find($dropdownContent).slideToggle('fast', 'swing');
    })

    $body.click(function (event) {
        if (!$(event.target).closest('.js-dropdown__content').length) {
            $dropdownContent.fadeOut();
        }
    });

    /**
     * GLIDE
     */

    if ($('.js-glide-catalog').length) {
        let catalog = new Glide('.js-glide-catalog', {
            type: 'carousel',
            gap: 20,
            perView: 3,
            breakpoints: {
                768: {
                    perView: 3,
                },
                650: {
                    perView: 3,
                },
                460: {
                    perView: 2,
                }
            }
        });
        catalog.mount();
    }

    if ($('.js-glide-knowledge-base').length) {
        let knowledgeBase = new Glide('.js-glide-knowledge-base', {
                type: 'slider',
                perView: 3,
                breakpoints: {
                    768: {
                        perView: 3,
                    },
                    650: {
                        perView: 2,
                    },
                    460: {
                        perView: 1,
                    }
                }
            }
        );
        knowledgeBase.mount();
    }

    if ($('.js-glide-landing-catalog').length) {
        let landingCatalog = new Glide('.js-glide-landing-catalog', {
            gap: 0,
            peek: {
                before: 0,
                after: 100
            },
            perView: 2,
            breakpoints: {
                460: {
                    perView: 1,
                }
            }
        });
        landingCatalog.mount();
    }

    if ($('.js-glide-single').length) {
        let glideSingle = new Glide('.js-glide-single', {
            type: 'slider',
        })
        glideSingle.mount();

        $(window).scroll(function () {
            glideSingle.update();
        });
    }

    /**
     *
     */
    function assembleGlide() {
        let $glideList = $('.js-glide');
        if ($glideList.length) {
            $glideList.each(function (i, element) {
                let $element, glide;
                $element = $(element);

                if ($element.hasClass('js-glide--assembled')) {
                    return;
                }

                let perView = $element.attr('data-max-items-per-slide') ? $element.attr('data-max-items-per-slide') : 5;

                glide = new Glide(element, {
                    type: 'slider',
                    gap: 40,
                    perView: perView,
                    breakpoints: {
                        1199: {
                            perView: 3,
                        },
                        992: {
                            perView: 3,
                        },
                        768: {
                            perView: 2,
                        },
                        460: {
                            perView: 1,
                        }
                    }
                });

                glide.on('build.after', function () {
                    $element.removeClass('js-glide--not-loaded');
                });

                glide.mount();

                $element.addClass('js-glide--assembled');
            });
        }
    }

    assembleGlide();

    /**
     * MOBILE MENU
     */

    let $mobileMenu, $mobileMenuOpen, $mobileMenuClose, $shade;

    $mobileMenu = $('.js-mobile-menu');
    $mobileMenuOpen = $('.js-mobile-menu__open');
    $mobileMenuClose = $('.js-mobile-menu__close');
    $shade = $('.js-shade');

    $mobileMenuOpen.click(function (event) {
        event.preventDefault();

        $mobileMenu.addClass('mobile-menu--active');
        $shade.addClass('shade--active');
        $body.addClass('no-scroll');
    });

    function closeMobileMenu() {
        $mobileMenu.removeClass('mobile-menu--active');
        $shade.removeClass('shade--active');
        $body.removeClass('no-scroll');
    }

    $shade.click(closeMobileMenu);
    $mobileMenuClose.click(closeMobileMenu);

    /**
     * MOBILE FILTER
     */

    let $mobileFilter, $mobileFilterClose;

    $mobileFilter = $('.js-mobile-filter');
    $mobileFilterClose = $('.js-mobile-filter__close');

    function closeMobileFilter() {
        $mobileFilter.removeClass('mobile-filter--active');
        $shade.removeClass('shade--active');
        $body.removeClass('no-scroll');
    }

    $body.on('click', '.js-mobile-filter__open', function (event) {
        event.preventDefault();

        $mobileFilter.addClass('mobile-filter--active');
        $shade.addClass('shade--active');
        $body.addClass('no-scroll');
    });

    $shade.click(closeMobileFilter);
    $mobileFilterClose.click(closeMobileFilter);

    /**
     * ACCORDION
     */

    $document.on('click', '.js-accordion-block__open', function (event) {
        let $block = $(this).next('.js-accordion-block__content');

        event.preventDefault();

        if ($block.is(':visible')) {
            $(this)
                .removeClass('active');
            $block
                .hide()
                .trigger('accordion-block-state-changed', [false]);
        } else {
            $(this)
                .addClass('active');
            $block
                .show()
                .trigger('accordion-block-state-changed', [true]);
        }
    });
    // TODO: replace with trigger "accordion-block-change-state"; In current state sims like unworkable
    $document.on('accordion-block-show', function () {
        $(this)
            .next('.js-accordion-block__content')
            .slideDown('fast', 'swing');
        $(this)
            .addClass('active');
    })
    $document.on('accordion-block-hide', function () {
        $(this)
            .next('.js-accordion-block__content')
            .slideUp('fast', 'swing');
        $(this)
            .removeClass('active');
    })
    $document.on('accordion-block-change-state', '.js-accordion-block__open', function (event, data) {
        let
            $accordionHead = $(this),
            $accordionBody = $accordionHead.next('.js-accordion-block__content');

        if (data) {
            $accordionHead.addClass('active');
            $accordionBody.slideDown('fast', 'swing');
        } else {
            $accordionHead.removeClass('active');
            $accordionBody.slideUp('fast', 'swing');
        }
    });

    initSelect2();

    /**
     * FORMS
     */

    $(document).on('submit', 'form.js-form', function (e) {
        e.preventDefault();
        let $form = $(this);
        if (Number($form.data('ajax')) === 1) {
            ajaxFormHandler($form);
        } else {
            $form.submit();
        }
    });

    let ajaxFormHandler = function ($form, successCallback, errorCallback) {
        $.ajax({
            type: 'POST',
            url: $form.attr('action'),
            dataType: 'json',
            data: new FormData($form[0]),
            processData: false,
            contentType: false,
            beforeSend: function () {
                showLoader($form);
            },
            success: function (response) {
                if (response.success === true) {
                    if (typeof successCallback === 'function') {
                        successCallback($form, response);
                    } else if ($form.data('success')) {
                        showPopup($form.data('success'));
                    }
                } else {
                    if (typeof errorCallback === 'function') {
                        errorCallback($form, response);
                    } else if ($form.data('error')) {
                        showPopup($form.data('error'));
                    }
                }

                let $formContainer = $form.closest('.js-form-container');
                $formContainer.html(response.html);
                initSelect2();
            },
            error: function (xhr) {
                console.warn(xhr.responseText);
            },
            complete: function () {
                hideLoader($form);
            }
        });
    }

    /**
     * TABS
     */

    let $tabs;
    $tabs = $('.tabs');

    if ($tabs.length) {
        let nav = $tabs.find('.tabs__navigation');
        let tabs = $tabs.find('.tabs__content');

        if (nav.children('.selected').length > 0) {
            $(nav.children('.selected').attr('href')).show();
        } else {
            nav.children().first().addClass('selected');
            tabs.children().first().show();
        }

        $('.tabs__navigation a').click(function (event) {
            event.preventDefault();
            event.stopPropagation();

            if ($(this).hasClass('selected')) {
                return true;
            }

            tabs.children().hide();
            nav.children().removeClass('selected');
            $(this).addClass('selected');
            $($(this).attr('href')).fadeIn(200);
        });

        if (nav.hasClass('has-active-tab')) {
            $('html, body').animate({
                scrollTop: nav.offset().top
            }, 1000);
        }
    }

    /**
     * PASSWORD INPUT TOGGLE
     */

    $body.on('click', '.js-password-input__trigger', function (event) {
        event.preventDefault();
        event.stopPropagation();

        let $passwordInput;
        $passwordInput = $(this).closest('.password-input').find('input');

        if ($passwordInput.attr('type') === 'password') {
            $passwordInput.attr('type', 'text');
        } else {
            $passwordInput.attr('type', 'password')
        }
    })

    /**
     * LANGUAGE SWITCHER
     */

    $body.on('change', '.js-language-switcher', function () {
        let tmp, date, local;
        tmp = window.location.pathname.replace('/en/', '/');
        window.location = $(this).val() + tmp + window.location.search + window.location.hash;
        switch ($(this).val()) {
            case '/en':
                local = 'en';
                break;
            case '/':
                local = 'uk';
                break;
            default:
                local = 'uk';
        }
        date = new Date(new Date().getTime() + 30 * 24 * 60 * 60 * 1000);
        document.cookie = 'local=' + local + '; path=/; domain=' + window.location.hostname + ';expires=' + date.toUTCString();
    });

    /**
     * COURSE REGISTER
     */
    $document.ready(function (event) {
        let tabLinks, tabContentId;
        if (window.localStorage.getItem('go-to-program-tab') === 'y') {
            tabLinks = document.getElementsByClassName('tabs__link');
            tabLinks.forEach(function (item) {
                if ($(item).hasClass('selected') && $(item).attr('href') !== '#program') {
                    $(item).removeClass('selected');
                    tabContentId = $(item).attr('href');
                    $(tabContentId).css('display', 'none');
                }
                if ($(item).attr('href') === '#program') {
                    $(item).addClass('selected');
                    $('#program').css('display', 'block');
                }
            })
            window.localStorage.removeItem('go-to-program-tab');
        }

    });

    $('.js-course-register').click(function (event) {
        event.preventDefault();
        event.stopPropagation();

        let url, loginUrl;

        url = $(this).data('url');
        loginUrl = $(this).data('login-url');

        $.ajax({
            type: 'GET',
            url: url,
            data: {},
            beforeSend: function () {
                showLoader();
            },
            success: function (data) {
                if (data.status === 401) {
                    Swal.fire({
                        title: data.title,
                        text: data.message,
                        icon: 'info',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#007a33',
                        buttonsStyling: false,
                        allowOutsideClick: false,
                        customClass: {
                            confirmButton: 'swal2-oun-style-button'
                        },
                        showClass: {
                            popup: 'animate__animated animate__fadeIn animate__faster'
                        },
                        hideClass: {
                            popup: 'animate__animated animate__fadeOut animate__faster'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = loginUrl;
                        }
                    })
                } else {
                    Swal.fire({
                        title: data.title,
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#007a33',
                        buttonsStyling: false,
                        allowOutsideClick: false,
                        customClass: {
                            confirmButton: 'swal2-oun-style-button'
                        },
                        showClass: {
                            popup: 'animate__animated animate__fadeIn animate__faster'
                        },
                        hideClass: {
                            popup: 'animate__animated animate__fadeOut animate__faster'
                        }
                    }).then((result) => {
                        if (result.isConfirmed && data.url == '') {
                            window.localStorage.setItem('go-to-program-tab', 'y');
                            document.location.reload();
                        } else {
                            window.location.href = data.url;
                        }
                    })
                }
            },
            error: function (data) {
                showPopup(data.title, data.message);
            },
            complete: function () {
                hideLoader();
            }
        });
    });
    /**
     * Comments block.
     */
    $body.on('submit', '.comment-block .comment-form form', function (event) {
        let
            $form = $(this),
            $commentsBlock = $form.closest('.comment-block'),
            $ckEditor = CKEDITOR.instances[$commentsBlock.attr('data-ckeditor-id')],
            $submitButton = $form.find(':submit'),
            formData = {};

        event.preventDefault();
        if ($ckEditor.getData().length === 0) {
            return false;
        }

        $form.serializeArray().forEach(function (item) {
            formData[item.name] = item.value;
        });

        $submitButton.attr('disabled', true);
        $.ajax({
            type: 'POST',
            url: $commentsBlock.attr('data-comment-post-route'),
            data: formData,
            success: function (data) {
                $commentsBlock.trigger('comment-add', {...data, ...{adding: 'UP'}});
                $submitButton.attr('disabled', false);
                $ckEditor.setData('');
            },
            error: function () {
                $submitButton.attr('disabled', false);
            }
        });
        return false;
    });
    $body.on('click', '.comment-block .show-more', function () {
        let
            $showMore = $(this),
            $commentsBlock = $showMore.closest('.comment-block'),
            pageSize = $commentsBlock.attr('data-page-size'),
            existCommentsCount = $commentsBlock
                .find('.comment-item')
                .not('.template')
                .length;

        $.ajax({
            type: 'GET',
            url: $commentsBlock.attr('data-comment-get-route'),
            data: {
                limit: pageSize,
                offset: existCommentsCount
            },
            success: function (data) {
                data.comments.forEach(function (item) {
                    $commentsBlock.trigger('comment-add', {...item, ...{adding: 'DOWN'}});
                });

                if (data.comments.length < pageSize) {
                    $showMore.hide();
                }
            }
        });
    });
    $body.on('comment-add', '.comment-block', function (event, data) {
        let
            $commentsBlock = $(this),
            $existCommentsBar = $commentsBlock.find('.comment-list'),
            $newComment = $existCommentsBar
                .find('.template')
                .clone()
                .removeClass('template'),
            imagesFolder = $commentsBlock.attr('data-user-image-folder');

        if (data.userAvatar) {
            $newComment
                .find('.comment-avatar')
                .attr('src', `${imagesFolder}/${data.userAvatar}`);
        }
        $newComment
            .find('.comment-item__author')
            .html(data.userTitle);
        $newComment
            .find('.comment-item__comment')
            .html(data.message);
        $newComment
            .find('.comment-item__date span')
            .html(data.createdAt);

        if (data.adding === 'UP') {
            $newComment.prependTo($existCommentsBar);
        } else {
            $newComment.appendTo($existCommentsBar);
        }
    });
    /**
     * History of success
     */
    let
        $historyOfSuccessBlock = $('.section.success-stories'),
        $historyOfSuccessMobileFilter = $('.mobile-filter__inner-wrapper.success-stories');

    $historyOfSuccessBlock.on('keyup', '.js-search-container :text', function (event) {
        if (event.keyCode === 13) {
            $(this)
                .closest('.js-search-container')
                .find('.search__button')
                .click();
        }
    });
    $historyOfSuccessBlock.on('click', '.js-search-container .search__button', function () {
        $historyOfSuccessBlock.trigger('page-rebuild-asked', {
            type: 'search'
        });
    });

    $historyOfSuccessBlock.on('change', '.filter-block .checkbox :checkbox', function () {
        $historyOfSuccessBlock.trigger('page-rebuild-asked', {
            type: 'filter'
        });
    });
    $historyOfSuccessMobileFilter.on('change', '.checkbox :checkbox', function () {
        $historyOfSuccessBlock.trigger('page-rebuild-asked', {
            type: 'filter'
        });
    });

    $historyOfSuccessBlock.on(
        'click',
        '.filter-block .js-filter-select-all, ' +
        '.filter-block .js-filter-reset-all',
        function () {
            setTimeout(function () {
                $historyOfSuccessBlock.trigger('page-rebuild-asked', {
                    type: 'filter'
                });
            }, 500);
        }
    );
    $historyOfSuccessMobileFilter.on(
        'click',
        '.js-filter-select-all, ' +
        '.js-filter-reset-all',
        function () {
            setTimeout(function () {
                $historyOfSuccessBlock.trigger('page-rebuild-asked', {
                    type: 'filter'
                });
            }, 500);
        }
    );

    $historyOfSuccessBlock.on('change', '.sorter', function () {
        $historyOfSuccessBlock.trigger('page-rebuild-asked', {
            type: 'sort'
        });
    });

    $historyOfSuccessBlock.on('click', '.items-bar-container .pagination a', function (event) {
        event.preventDefault();

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

        $historyOfSuccessBlock.trigger('page-rebuild-asked', {
            type: 'pagination',
            value: pageValueInt
        });
    });

    $historyOfSuccessBlock.on('page-rebuild-asked', function (event, data) {
        let
            requestUrl = $historyOfSuccessBlock.attr('data-list-ajax-route'),
            searchValue = $historyOfSuccessBlock.find('.js-search-container input').val(),
            sortValue = $historyOfSuccessBlock.find('.sorter').val(),
            pageValue = data.type === 'pagination' ? data.value : 1,
            checkedRegions = [];

        $historyOfSuccessBlock
            .find('.filter-block .checkbox :checkbox')
            .filter(':checked')
            .each(function () {
                let value = $(this).val();

                checkedRegions.push(value);
            });

        requestUrl = requestUrl
            .replace('SEARCH_VALUE', searchValue)
            .replace('REGIONS_VALUE', checkedRegions.join(','))
            .replace('SORT_BY_VALUE', sortValue)
            .replace('PAGE_VALUE', pageValue);

        showLoader();
        $.ajax({
            type: 'GET',
            url: requestUrl + '&' + Math.random(),
            success: function (data) {
                $historyOfSuccessBlock
                    .find('.items-bar-container')
                    .html(data.content);
                window.history.pushState(
                    {},
                    $document.find('title').text(),
                    data.url
                );
                hideLoader();
            }
        });
    });
    /**
     * Subscribe
     */
    $body.on('click', '.subscribe-block .action-button', function () {
        let $subscribeBlock = $(this).closest('.subscribe-block');

        showLoader();
        $.ajax({
            type: 'POST',
            url: $subscribeBlock.attr('data-link'),
            success: function () {
                let message = $subscribeBlock.attr('data-message-success');

                hideLoader();
                $subscribeBlock.remove();
                showPopup($subscribeBlock.find('.action-button').text().trim(), message, 'success')
            },
            error: function () {
                let message = $subscribeBlock.attr('data-message-failed');

                hideLoader();
                showPopup($subscribeBlock.find('.action-button').text().trim(), message, 'error');
            }
        });
    });
    /**
     * Video block.
     */
    window.YT.ready(function () {
        let
            $videoBlocks = $('.video-block'),
            videosWatchControlData = {},
            postingSequenceInMinutes = 30;

        $videoBlocks
            .each(function () {
                let
                    $videoBlock = $(this),
                    videoId = $videoBlock.attr('data-video-id'),
                    videoTagId = $videoBlock.find('.video-item').attr('id'),
                    widthValue = $videoBlock.attr('data-width'),
                    width = parseInt(widthValue),
                    heightValue = $videoBlock.attr('data-height'),
                    height = parseInt(heightValue);

                videosWatchControlData[videoId] = {
                    lastOperationTime: null,
                    lastOperationType: null,
                    duration: 0
                };

                new YT.Player(videoTagId, {
                    width: width > 0 ? width : 640,
                    height: height > 0 ? height : 360,
                    videoId: videoId,
                    playerVars: {
                        controls: 1
                    },
                    events: {
                        'onStateChange': function (event) {
                            $videoBlock.trigger('video-interaction', {
                                type: event.data
                            });
                        }
                    }
                })
            })
            .on('video-interaction', function (event, data) {
                let
                    $videoBlock = $(this),
                    videoId = $videoBlock.attr('data-video-id'),
                    videoData = videosWatchControlData[videoId],
                    eventType = data.type,
                    eventTypesAllowedValues = [
                        YT.PlayerState.UNSTARTED,
                        YT.PlayerState.ENDED,
                        YT.PlayerState.PLAYING,
                        YT.PlayerState.PAUSED,
                        YT.PlayerState.BUFFERING,
                        YT.PlayerState.CUED
                    ];

                if (!videoData) {
                    return;
                }
                if (!eventTypesAllowedValues.includes(eventType)) {
                    throw Error(`unknown event type ${eventType}`);
                }

                if (
                    [
                        YT.PlayerState.PAUSED,
                        YT.PlayerState.ENDED
                    ].includes(eventType) &&
                    videoData.lastOperationType === YT.PlayerState.PLAYING &&
                    videoData.lastOperationTime
                ) {
                    videoData.duration += Date.now() - videoData.lastOperationTime;
                }

                videoData.lastOperationTime = Date.now();
                videoData.lastOperationType = eventType;

                videosWatchControlData[videoId] = videoData;
            })
            .on('video-watching-post', function () {
                let
                    $videoBlock = $(this),
                    videoId = $videoBlock.attr('data-video-id'),
                    duration = videosWatchControlData[videoId].duration;

                if (duration <= 0) {
                    return;
                }

                videosWatchControlData[videoId].duration = 0;
                $.ajax({
                    type: 'POST',
                    url: $videoBlock.attr('data-interaction-log-route'),
                    data: JSON.stringify({
                        duration: duration / 1000
                    }),
                    dataType: 'json',
                    contentType: 'application/json'
                });
            });

        setInterval(function () {
            $videoBlocks.trigger('video-watching-post');
        }, postingSequenceInMinutes * 1000);

        window.addEventListener('beforeunload', function () {
            $videoBlocks
                .trigger('video-interaction', {
                    type: YT.PlayerState.PAUSED
                })
                .trigger('video-watching-post');
        });
    });
    let $ajaxBlock = $('.section.ajax');

    $ajaxBlock.on('keyup', '.js-search-container :text', function (event) {
        if (event.keyCode === 13) {
            $(this)
                .closest('.js-search-container')
                .find('.search__button')
                .click();
        }
    });
    $ajaxBlock.on('click', '.js-search-container .search__button', function () {
        $ajaxBlock.trigger('page-rebuild-asked', {
            type: 'search'
        });
    });

    $ajaxBlock.on('change', '.sorter', function () {
        $ajaxBlock.trigger('page-rebuild-asked', {
            type: 'sort'
        });
    });

    $ajaxBlock.on('click', '.items-bar-container .pagination a', function (event) {
        event.preventDefault();

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

        $ajaxBlock.trigger('page-rebuild-asked', {
            type: 'pagination',
            value: pageValueInt
        });
    });

    $ajaxBlock.on('page-rebuild-asked', function (event, data) {
        let
            requestUrl = $ajaxBlock.attr('data-list-ajax-route'),
            searchValue = $ajaxBlock.find('.js-search-container input').val(),
            sortValue = $ajaxBlock.find('.sorter').val(),
            pageValue = data.type === 'pagination' ? data.value : 1;

        requestUrl = requestUrl
            .replace('SEARCH_VALUE', searchValue)
            .replace('SORT_BY_VALUE', sortValue)
            .replace('PAGE_VALUE', pageValue);

        showLoader();
        $.ajax({
            type: 'GET',
            url: requestUrl + '&' + Math.random(),
            success: function (data) {
                $ajaxBlock
                    .find('.items-bar-container')
                    .html(data.content);
                window.history.pushState(
                    {},
                    $document.find('title').text(),
                    data.url
                );
                hideLoader();
            }
        });
    });

    $body.on('click', '.js-filter-select-all', function (event) {
        let $accordionBlock = $(this)
            .parent('.button-container')
            .parent()
            .find('.accordion-block');

        event.preventDefault();

        $accordionBlock
            .find('.js-accordion-block__open')
            .trigger('accordion-block-show');
        $accordionBlock
            .find('.accordion-block__content .checkbox input')
            .prop('checked', true);
    });

    $body.on('click', '.js-filter-reset-all', function (event) {
        let $accordionBlock = $(this)
            .parent('.button-container')
            .parent()
            .find('.accordion-block');

        event.preventDefault();

        $accordionBlock
            .find('.js-accordion-block__open')
            .trigger('accordion-block-hide');
        $accordionBlock
            .find('.accordion-block__content .checkbox input')
            .prop('checked', false);
        $('.active-filter-container')
            .html('');
    });
    /**
     * Form required elements.
     */
    let checkPhoneValueValid = function (value) {
        let
            valueSplit = value.trim().match(/\d/g),
            valuePrepared = valueSplit ? valueSplit.join('') : '';

        return valuePrepared.length <= 20;
    };

    $body
        .on('click', '.form-required-control form [type="submit"]', function (event) {
            let
                $form = $(this).closest('form'),
                $fields = $form.find('input[required], textarea[required], select[required]'),
                fieldsByName = {},
                alertsExist = false;

            $fields.each(function () {
                let
                    $field = $(this),
                    name = $field.attr('name');

                if (!fieldsByName.hasOwnProperty(name)) {
                    fieldsByName[name] = {
                        type: $field.prop('tagName') === 'INPUT'
                            ? $field.attr('type')
                            : $field.prop('tagName'),
                        fields: []
                    };
                }

                fieldsByName[name].fields.push($field);
            });

            for (let fieldName in fieldsByName) {
                let
                    fieldsBandAlerted = true,
                    fieldsBandType = fieldsByName[fieldName].type;

                switch (fieldsByName[fieldName].type) {
                    case 'checkbox':
                    case 'radio':
                        fieldsByName[fieldName].fields.forEach(function ($field) {
                            if ($field.is(':checked')) {
                                fieldsBandAlerted = false;
                            }
                        });
                        break;
                    case 'email':
                        fieldsByName[fieldName].fields.forEach(function ($field) {
                            let
                                expression = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
                                value = $field.val(),
                                valuePrepared = String(value).toLowerCase();

                            if (expression.test(valuePrepared)) {
                                fieldsBandAlerted = false;
                            }
                        });
                        break;
                    case 'tel':
                        fieldsByName[fieldName].fields.forEach(function ($field) {
                            let
                                value = $field.val(),
                                needVerification = $field.hasClass('js-phone-mask'),
                                $verificationField = $field
                                    .closest('.form-block')
                                    .next('.form-block')
                                    .find('.js-verification-phone__code');

                            if (
                                checkPhoneValueValid(value) && (
                                    !needVerification ||
                                    $verificationField.is(':visible') ||
                                    $verificationField.hasClass('verified')
                                )
                            ) {
                                fieldsBandAlerted = false;
                            }
                        });
                        break;
                    default:
                        fieldsByName[fieldName].fields.forEach(function ($field) {
                            if ($field.val().trim().length > 0) {
                                fieldsBandAlerted = false;
                            }
                        });
                }

                fieldsByName[fieldName].fields[0]
                    .closest('.form-block')
                    .attr('data-alert-field-type', fieldsBandType)
                    .attr('data-alert-state', fieldsBandAlerted ? 'Y' : 'N');

                if (fieldsBandAlerted) {
                    alertsExist = true;
                }
            }

            if (alertsExist) {
                event.preventDefault();
            }
        })
        .on(
            'change input',
            '.form-block[data-alert-state="Y"] input, ' +
            '.form-block[data-alert-state="Y"] select, ' +
            '.form-block[data-alert-state="Y"] textarea',
            function () {
                $(this)
                    .closest('.form-block')
                    .attr('data-alert-state', 'N');
            }
        );
    /**
     * Phone verification.
     */
    $body
        .on('input', '.js-user-phone-input', function () {
            $(this).trigger('phone-code-field-control', {
                operation: 'hide'
            });
        })
        .on('click', '.js-verification-phone__button', function () {
            let
                $form = $(this).closest('form'),
                $phoneInput = $form.find('.js-user-phone-input'),
                phoneValue = $phoneInput.val();

            event.preventDefault();

            if (!checkPhoneValueValid(phoneValue)) {
                $phoneInput
                    .closest('.form-block')
                    .attr('data-alert-field-type', 'tel')
                    .attr('data-alert-state', 'Y');
                return;
            }

            $.ajax({
                type: 'POST',
                url: '/verify/send-code',
                data: {
                    phone: phoneValue
                },
                dataType: 'json',
                success: function success() {
                    $phoneInput.trigger('phone-code-field-control', {
                        operation: 'show'
                    });
                },
                error: function error(response) {

                }
            });
        })
        .on('phone-code-field-control', '.js-user-phone-input', function (event, data) {
            let
                $form = $(this).closest('form'),
                $phoneVerifyButton = $form.find('.js-verification-phone__button'),
                $phoneVerifyInput = $form.find('.js-verification-phone__code');

            if (data.operation === 'hide') {
                $phoneVerifyInput
                    .hide()
                    .find('input')
                    .removeAttr('required')
                    .val('');
                $phoneVerifyButton
                    .show();
            } else if (data.operation === 'show') {
                $phoneVerifyInput
                    .show()
                    .find('input')
                    .attr('required', 'required');
                $phoneVerifyButton
                    .hide();
            }
        });

    /**
     * Password verification.
     */
    let pswd, pswdFullValid;
    $body.on('focusout', '.new-password', function (event) {
        event.preventDefault();
        pswd = $('.new-password').val();
        pswdFullValid = /^(?=.*\d)([()*_\-!#$@%^&,.+"\\'\][])*(?=.*[a-z])(?=.*[A-Z]).{8,255}$/
        if (pswdFullValid.test(pswd) === true) {
            $('.new-password').css('border-color', 'grey');
            document.getElementsByClassName('personal-area-form-help-wrapper help-text')[0].style.display = 'none';
        } else {
            $('.new-password').css('border-color', 'red');
            document.getElementsByClassName('personal-area-form-help-wrapper help-text')[0].style.display = 'block';
        }
    });
    $body.on('focusin', '.new-password', function (event) {
        $('.new-password').css('border-color', 'green');
    });

    /**
     * Lesson button redirect.
     */
    $body.on('click', '.js-lesson-unauthorized-button', function (event) {
        event.preventDefault();
        let lessonButtonLink = $(this).attr('href');
        let lessonButtonTitle, lessonButtonCancelTxt, lessonButtonConfTxt;

        if (document.documentElement.lang === 'en') {
            lessonButtonTitle = 'To watch lesson content you have to be logged in.'
            lessonButtonConfTxt = 'Login';
            lessonButtonCancelTxt = 'Cancel';
        } else {
            lessonButtonTitle = 'Щоб подивитись урок вам потрібно авторизуватись.'
            lessonButtonConfTxt = 'Авторизація';
            lessonButtonCancelTxt = 'Скасувати';
        }
        if (lessonButtonLink === '#') {
            Swal.fire({
                title: '',
                text: lessonButtonTitle,
                icon: 'warning',
                confirmButtonText: '<a href="/login" style="color: #ffffff">'.concat(lessonButtonConfTxt).concat(
                    '</a>'),
                confirmButtonColor: '#007a33',
                cancelButtonText: lessonButtonCancelTxt,
                buttonsStyling: true,
                showCancelButton: true,
                customClass: {
                    confirmButton: 'swal2-oun-style-button',
                },
                showClass: {
                    popup: 'animate__animated animate__fadeIn animate__faster'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOut animate__faster'
                }
            })
        }
    });

    /**
     * Scroll to Lesson Section
     */
    $document.ready(function () {
        let lessonBlock = $('.single-lesson')[0];
        if(lessonBlock)
        {
            lessonBlock.scrollIntoView();
        }
    });

    /**
     * Redirect to course program tab
     */
    $body.on('click', '.go-to-course-program', function (event) {
        event.preventDefault();
        let courseProgramButton = $('.go-to-course-program');
        let programTab = $('#program');
        $.ajax({
            type: 'GET',
            url: courseProgramButton.attr('href'),
            success: function (response) {
                programTab.html(response);
            }
        });

    });


    /**
     * Cookies
     */

    let cookieButton, cookieBanner;

    cookieButton = document.getElementById('cookies-button');
    cookieBanner = document.getElementById('cookie-banner');

    cookieButton.onclick = function () {
        Cookies.set('isCookieBar', '1', {expires: 3000});
        cookieBanner.style.display = 'none';
    }

    if (!Cookies.get('isCookieBar')) {
        cookieBanner.style.display = 'block';
    }

    /**
     * SHOW ROW
     */

    $body.on('click', '.js-show-row', function (event) {
        event.preventDefault();
        event.stopPropagation();

        let $this, url, type;
        $this = $(this);
        url = $this.data('url');

        switch ($this.data) {
            case 'email':
                type = 'email'
                break;
            case 'phone':
                type = 'phone'
                break;
        }

        showLoader();
        $.ajax({
            type: 'GET',
            url: url,
            data: {
                type: type,
            },
            success: function (data) {
                switch (data.type) {
                    case 'email':
                        $this.hide();
                        $this.parent().find('.email').text(data.row);
                        break;
                    case 'phone':
                        $this.hide();
                        $this.parent().find('.phone').text(data.row);
                        break;
                }
                hideLoader();
            }
        });
    })

    $body.on('click', '.add-another-collection-widget', function (e) {
        e.preventDefault();
        var list = $($(this).attr('data-list'));
        var counter = list.data('widget-counter') | list.children().length;
        if (!counter) {
            counter = list.children().length;
        }
        var newWidget = list.attr('data-prototype');
        newWidget = newWidget.replace(/__name__/g, counter);
        counter++;
        list.data('widget-counter', counter);
        var newElem = $(list.attr('data-widget-tags')).html(newWidget);
        newElem.appendTo(list.closest(".field.collection-field"));

        addPhoneMask();
    });

    $body.on('click', '.phone-delete', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).closest('div.js-phone-row-item.phone-row-item').remove()
    })

    $body.on('click', '.add-certificate-collection-widget', function (e) {
        e.preventDefault();
        var list = $($(this).attr('data-list'));
        var counter2 = list.data('widget-counter') | list.children().length;
        if (!counter2) {
            counter2 = list.children().length;
        }
        var newWidget = list.attr('data-prototype');
        newWidget = newWidget.replace(/__name__/g, counter2);
        counter2++;
        list.data('widget-counter', counter2);
        var newElem = $(list.attr('data-widget-tags')).html(newWidget);
        newElem.appendTo(list.closest(".field.collection-field"));
    });

    $body.on('click', '.certificate-delete', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var blockDelete = $(this).closest('div.js-certificate-row-item.certificate-row-item')
        blockDelete.remove();
    })

    $body.on('click', '.add-certificate-collection', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $('div.form-added-certificate-in-page.hide').show()
    })

    $body.on('click', '.save-certificate-collection', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $('form[name="user_certificate_form"]').submit()
    })

    $body.on('click', '.delete-button-role', function (e) {
        e.preventDefault();
        e.stopPropagation();
        Swal.fire({
            title: '',
            text: 'Ви дійсно бажаєте видалити свою роль?',
            icon: 'warning',
            confirmButtonText: '<a href="' + $(this).data('url') + '" style="color: #ffffff">Так</a>',
            confirmButtonColor: '#007a33',
            cancelButtonText: 'Ні',
            buttonsStyling: true,
            showCancelButton: true,
            customClass: {
                confirmButton: 'swal2-oun-style-button',
            },
            showClass: {
                popup: 'animate__animated animate__fadeIn animate__faster'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOut animate__faster'
            }
        })
    })

});
