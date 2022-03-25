import { getScript } from 'jquery';
import svg4everybody from 'svg4everybody';
//require('smoothscroll-for-websites');
//require('./jqueryScrollbar/jquery.scrollbar.js');
//require('./select2/select2.min.js');
require('./slick/slick.min.js');
require('./gsap/gsap.min.js');
require('./gsap/ScrollTrigger.min.js');
require('./gsap/DrawSVGPlugin3.6.0.min.js');
require('./jquery.smatEqualItemsHeight.js');
require('jquery-mask-plugin');

//const $ = require('jquery');


gsap.registerPlugin(ScrollTrigger, DrawSVGPlugin);


if ( !!(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) ) {
	document.body.classList.add('body-mobile-device');
} else {
	document.body.classList.add('body-desktop-device');
}


svg4everybody({ polyfill: true });


$('.js-scrollbar').scrollbar();


/* input[data-phone-mask]
 ******************************************************************************/
$('input[data-phone-mask]').each(function() {
	let mask = $(this).attr('data-phone-mask');
	$(this).mask(mask);
});


/* .field-avatar
 ******************************************************************************/
$('.field-avatar').each(function() {
	let field = this,
		img = this.querySelector('.field-avatar__image'),
		inputFile = this.querySelector('input[type="file"]');

	if (!! img.getAttribute('src')) {
		field.classList.add('fill');
	}

	inputFile.addEventListener('change', function(evt) {
		let reader = new FileReader();
		reader.onload = function(){
			img.src = reader.result;
			if (!! img.getAttribute('src')) {
				field.classList.add('fill');
			}
		};
		reader.readAsDataURL(evt.target.files[0]);
	})
});


/* выравнивание высоты заголовков .knowledge-chart__title
 ******************************************************************************/
if (typeof($.fn.smatEqualItemsHeight) === 'function') {
	$('.knowledge-chart').smatEqualItemsHeight({
		innerEqualElems: ['.knowledge-chart__title']
	});
}


/* .knowledge-chart.chart-lines (графики с цветными линиями)
 ******************************************************************************/
$('.knowledge-chart.chart-lines').each(function() {
	let $knowledgeChart = $(this),
		lineColorbar = $(this)[0].querySelectorAll('.knowledge-chart__line-colorbar'),
		$bottomStrong   = $('.knowledge-chart__bottom-info strong', $knowledgeChart);

	$knowledgeChart.css('color', $knowledgeChart.data('color') ?? '');
	$bottomStrong.css('color', $knowledgeChart.data('color') ?? '');

	ScrollTrigger.create({
		trigger: $knowledgeChart[0],
		start: '50% 100%',
		onEnter: function() {
			gsap.to(lineColorbar, {
				width: function(index, target, targets) {
					return target.getAttribute('data-percent') + '%';
				},
				duration: 1,
				ease: 'power3.out',
				overwrite: 'auto'
			});
		},
		onLeaveBack: function() {
			gsap.to(lineColorbar, {
				width: 0,
				duration: 1,
				ease: 'power3.out',
				overwrite: 'auto'
			});
		}
	});
});


/* .chart-circle-simple (простой круглый график)
 ******************************************************************************/
$('.chart-circle-simple').each(function() {
	let $chart   = $(this),
	    color    = $('.chart-circle-simple__content', $chart).data('color'),
	    value    = parseInt($('.chart-circle-simple__content', $chart).data('percent')),
		percent  = $('.chart-circle-simple__percent', $chart)[0],
	    circle   = $('.chart-circle-simple__svg-circle', $chart)[0];

	$(percent).text(value + '%').css('color', color);

	gsap.set(circle, {
		stroke: color,
		drawSVG: '0%',
		rotation: -90,
		transformOrigin: 'center center'
	});

	gsap.set(percent, {
		scale: 0
	});

	ScrollTrigger.create({
		trigger: circle,
		start: 'bottom 100%',
		onEnter: function() {
			gsap.to(circle, {
				drawSVG: '0% ' + value + '%',
				duration: 1,
				ease: 'power3.out',
				overwrite: 'auto'
			});
			gsap.to(percent, {
				scale: 1,
				duration: 1,
				ease: 'power3.out',
				overwrite: 'auto'
			});
		},
		onLeaveBack: function() {
			gsap.to(circle, {
				drawSVG: '0%',
				duration: 1,
				ease: 'power3.out',
				overwrite: 'auto'
			});
			gsap.to(percent, {
				scale: 0,
				duration: 1,
				ease: 'power3.out',
				overwrite: 'auto'
			});
		}
	});
});


/* .knowledge-chart.chart-map-country (график с картой Украины)
 ******************************************************************************/
$('.knowledge-chart.chart-map-country').each(function() {
	let $chartCountry = $(this),
		path = $chartCountry[0].querySelectorAll('.knowledge-chart__country-svg path[id]');

	path.forEach(function(elm) {
		let id = elm.getAttribute('id'),
			bgr = $chartCountry.find('[class = knowledge-chart__' + id + ']').data('background') || '';

		elm.setAttribute('old-fill', elm.getAttribute('fill'));
		elm.setAttribute('new-fill', bgr);
	});

	gsap.set(path, {scale: 0, transformOrigin: 'center'});

	ScrollTrigger.batch(path, {
		start: "50% bottom",
		onEnter: function(batch) {
			gsap.to(batch, {
				fill: function(index, target, targets) {
					return target.getAttribute('new-fill');
				},
				scale: 1,
				duration: 0.5,
				stagger: 0.15,
				ease: 'back.out(2)',
				overwrite: 'auto'
			});
		},
		onLeaveBack: function(batch) {
			gsap.to(batch, {
				fill: function(index, target, targets) {
					return target.getAttribute('old-fill');
				},
				scale: 0,
				duration: 0.5,
				stagger: 0,
				overwrite: 'auto'
			});
		}
	});
});


/* .business-toolset__slider
 ******************************************************************************/
$('.business-toolset__slider').on('init reInit', function() {
	$(this).find('.business-toolset__item').smatEqualItemsHeight();
}).slick({
	infinite: true,
	slidesToShow: 3,
	slidesToScroll: 3,
	arrows: false,
	dots: true,
	responsive: [
		{
			breakpoint: 768,
			settings: {
				slidesToShow: 2,
				slidesToScroll: 2
			}
		},
		{
			breakpoint: 640,
			settings: {
				slidesToShow: 1,
				slidesToScroll: 1,
				adaptiveHeight: true,
			}
		},
	]
});


/* .partner-item
 ******************************************************************************/
$('.partner-item').each(function() {
	let color = $(this).data('color') || '',
		$link = $('.partner-icon-text-li a', this);

	$(this).css('color', color);
	$link.css('color', color);
});


/* .chart-circle-donut (круглый график с сегментами регионов)
 ******************************************************************************/
$('.chart-circle-donut').each(function() {
	let dataRegions  = $(this).data('regions'),
		otherRegion  = $(this).data('other-region'),
		otherPercent = parseInt($(this).data('other-percent')),
	    otherColor   = $(this).data('other-color'),
		otherСount   = $(this).data('other-count'),
		$descript    = $('.chart-circle-donut__description-list', this),
	    chartCircle  = this.querySelector('.chart-circle-donut__circle'),
		descrItems   = null,
		circles      = chartCircle.querySelectorAll('circle'),
	    text         = chartCircle.querySelectorAll('text'),
		textJump     = 170,
	    starts       = [],
	    ends         = [],
	    startSum     = 0,
	    startDig     = 0,
	    endDig       = 0,
	    dig          = 0,
	    distanceX    = 0,
	    distanceY    = 0;

	dataRegions.forEach(({color, percent}, i) => {
		circles[i].setAttribute('stroke', color);
		circles[i].setAttribute('data-start', startSum);

		starts.push(parseInt(startSum));
		startSum = parseInt(startSum) + parseInt(percent);
		ends.push(parseInt(startSum));
		circles[i].setAttribute('data-end', parseInt(startSum));

		circles[circles.length-1].setAttribute('stroke', otherColor);
		circles[circles.length-1].setAttribute('data-start', parseInt(startSum));
		circles[circles.length-1].setAttribute('data-end', 100);

		if (i == dataRegions.length-1) {
			starts.push(parseInt(startSum));
			ends.push(100);
		}

		gsap.set(circles, {
			drawSVG: 0,
			opacity: 0,
			rotation: -90,
			transformOrigin: "center center"
		});

		startDig  = starts[i] * 360 / 100,
		endDig    = ends[i] * 360 / 100,
		dig       = startDig + (endDig - startDig) / 2,
		distanceX = Math.cos(dig*Math.PI/180)*textJump,
		distanceY = Math.sin(dig*Math.PI/180)*textJump;

		text[i].setAttribute('distanceX', distanceX);
		text[i].setAttribute('distanceY', distanceY);
		text[i].innerHTML = percent + '%';

		if (i == dataRegions.length-1) {
			startDig  = endDig;
			endDig    = 360;
			dig       = startDig + (endDig - startDig) / 2,
			distanceX = Math.cos(dig*Math.PI/180)*textJump,
			distanceY = Math.sin(dig*Math.PI/180)*textJump;
			text[text.length-1].setAttribute('distanceX', distanceX);
			text[text.length-1].setAttribute('distanceY', distanceY);
			text[text.length-1].innerHTML = otherPercent + '%';
		}

		gsap.set(text, {
			rotation: 90,
			transformOrigin: "center",
		})
	});

	for (let i = 0, len = dataRegions.length; i <= len; i++) {
		let curRegion = '',
		    curColor  = '',
		    curCount  = '',
		    block     = '';

		if (i == len) {
			curRegion = otherRegion;
			curColor = otherColor;
			curCount = otherСount;
		} else {
			curRegion = dataRegions[i]['region'];
			curColor = dataRegions[i]['color'];
			curCount = dataRegions[i]['count'];
		}

		block = `<div class="chart-circle-donut__description-item" style="color: ${curColor}">
					<div class="chart-circle-donut__description-title">${curRegion}</div>
					<div class="chart-circle-donut__description-count">${curCount}</div>
				</div>`;

		$descript.append(block);
	}

	descrItems = $descript[0].querySelectorAll('.chart-circle-donut__description-item');

	gsap.set(descrItems, {
		scale: 0
	});

	ScrollTrigger.create({
		trigger: chartCircle,
		start: '50% bottom',
		onEnter: function() {
			gsap.to(circles, {
				drawSVG: function(index, target, targets) {
					let start = target.getAttribute('data-start'),
						end   = target.getAttribute('data-end');
					return start + "% " + end + "%";
				},
				opacity: 1,
				duration: 1,
				ease: 'power3.out',
				overwrite: 'auto'
			});
			gsap.to(text, {
				x: function(index, target, targets) {
					return target.getAttribute('distanceX');
				},
				y: function(index, target, targets) {
					return target.getAttribute('distanceY');
				},
				duration: 0.5,
				delay: 0.5,
				ease: 'back.out(2)',
				overwrite: 'auto'
			});
		},
		onLeaveBack: function() {
			gsap.to(circles, {
				drawSVG: 0,
				opacity: 0,
				duration: 1,
				ease: 'power3.out',
				overwrite: 'auto'
			});
			gsap.to(text, {
				x: 0,
				y: 0,
				duration: 0.5,
				delay: 0,
				overwrite: 'auto'
			});
		}
	});

	ScrollTrigger.batch(descrItems, {
		start: "50% bottom",
		onEnter: function(batch) {
			gsap.to(batch, {
				scale: 1,
				duration: 0.5,
				delay: 0.5,
				stagger: 0.15,
				ease: 'back.out(2)',
				overwrite: 'auto'
			});
		},
		onLeaveBack: function(batch) {
			gsap.to(batch, {
				scale: 0,
				duration: 0.25,
				delay: 0,
				stagger: 0,
				overwrite: 'auto'
			});
		}
	});
});


/* .js-btn-sidebar
 ******************************************************************************/
let
	currentUrl 			= document.URL,
	urlParts   			= currentUrl.split('#'),
	currentUrlAnchor	= urlParts.length > 1 ? urlParts[1] : null;

$('.js-btn-sidebar')
	.on('click', function(event) {
		let
			$button = $(this),
			link	= $button.attr('href'),
			$target	= $(`.js-tabs-wrapper ${link}`);

		event.preventDefault();
		event.stopPropagation();
		if ( $target.length ) {
			$target
				.show()
				.siblings(['id'])
				.hide();
			$button
				.addClass('active')
				.siblings('.js-btn-sidebar')
				.removeClass('active');
		}
	})
	.filter(`[href="#${currentUrlAnchor}"]`)
	.click();

/* .about-activity-manufacturers__products
 ******************************************************************************/
$('.about-activity-manufacturers__products').each(function() {
	let productCountMaxWidth = 0;
	$('.about-activity-manufacturers__product-count').each(function() {
		productCountMaxWidth = $(this).outerWidth(true) > productCountMaxWidth ?  $(this).outerWidth(true) : productCountMaxWidth;
	});
	$('.about-activity-manufacturers__product-count').css('width', productCountMaxWidth);
});


/* .about-activity-players
 ******************************************************************************/
$('.about-activity-players').each(function() {
	let $columnLarge = $('.about-activity-players-sex__column.large', this),
		$columnSmall = $('.about-activity-players-sex__column.small', this);

	$columnLarge.each(function() {
		let totalCount      = parseInt($(this).data('total-count')),
			percentWoman    = parseInt($(this).data('percent-woman')),
			percentMan      = parseInt($(this).data('percent-man')),
			delta           = { woman: 0, man: 0, total: 0 },
			svgWoman        = this.querySelector('.about-activity-players-sex__total-woman .svg .circle-value'),
			svgMan          = this.querySelector('.about-activity-players-sex__total-man .svg .circle-value'),
			blockPrcWoman   = this.querySelector('.percent-total-woman strong'),
			blockPrcMan     = this.querySelector('.percent-total-man strong'),
			blockTotalCount = this.querySelector('.about-activity-players-sex__total-count');

		blockPrcWoman.innerText = '0';
		blockPrcMan.innerText = '0';
		blockTotalCount.innerText = '0';

		gsap.set([svgWoman, svgMan], {
			drawSVG: '0%',
			rotation: -90,
			transformOrigin: 'center center'
		});

		ScrollTrigger.create({
			trigger: svgWoman,
			start: 'bottom 100%',
			onEnter: function() {
				gsap.to(svgWoman, { drawSVG: '0% ' + percentWoman + '%', duration: 1, ease: 'power3.out', overwrite: 'auto' });
				gsap.to(delta, {
					woman: percentWoman,
					duration: 1,
					onUpdate: function() {
						blockPrcWoman.innerText = delta.woman.toFixed();
					}
				});
			},
			onLeaveBack: function() {
				gsap.to(svgWoman, { drawSVG: '0%', duration: 1, ease: 'none', overwrite: 'auto' });
				gsap.to(delta, {
					woman: 0,
					duration: 1,
					onUpdate: function() {
						blockPrcWoman.innerText = delta.woman.toFixed();
					}
				});
			},
		});

		ScrollTrigger.create({
			trigger: svgMan,
			start: 'bottom 100%',
			onEnter: function() {
				gsap.to(svgMan, { drawSVG: '0% ' + percentMan + '%', duration: 1, ease: 'power3.out', overwrite: 'auto' });
				gsap.to(delta, {
					man: percentMan,
					duration: 1,
					onUpdate: function() {
						blockPrcMan.innerText = delta.man.toFixed();
					}
				});
			},
			onLeaveBack: function() {
				gsap.to(svgMan, { drawSVG: '0%', duration: 0.5, ease: 'none', overwrite: 'auto' });
				gsap.to(delta, {
					man: 0,
					duration: 1,
					onUpdate: function() {
						blockPrcMan.innerText = delta.man.toFixed();
					}
				});
			}
		});

		ScrollTrigger.create({
			trigger: blockTotalCount,
			start: 'bottom 100%-=50px',
			onEnter: function() {
				gsap.to(delta, {
					total: totalCount,
					duration: 1,
					overwrite: 'auto',
					onUpdate: function() {
						blockTotalCount.innerText = delta.total.toFixed().replace(/(\d)(?=(\d{3})+$)/g, '$1 ');
					}
				});
			},
			onLeaveBack: function() {
				gsap.to(delta, {
					total: 0,
					duration: 1,
					overwrite: 'auto',
					onUpdate: function() {
						blockTotalCount.innerText = delta.total.toFixed().replace(/(\d)(?=(\d{3})+$)/g, '$1 ');
					}
				});
			}
		});
	});

	$columnSmall.each(function() {
		let percentWoman  = parseInt($(this).data('percent-woman')),
			percentMan    = parseInt($(this).data('percent-man')),
			delta         = { woman: 0, man: 0 },
			svgWoman      = this.querySelector('.about-activity-players-sex__total-woman .svg .circle-value'),
			svgMan        = this.querySelector('.about-activity-players-sex__total-man .svg .circle-value'),
			blockPrcWoman = this.querySelector('.percent-total-woman strong'),
			blockPrcMan   = this.querySelector('.percent-total-man strong');

		blockPrcWoman.innerText = '0';
		blockPrcMan.innerText = '0';

		gsap.set([svgWoman, svgMan], {
			drawSVG: '0%',
			rotation: -90,
			transformOrigin: 'center center'
		});

		ScrollTrigger.create({
			trigger: svgWoman,
			start: 'bottom 100%',
			onEnter: function() {
				gsap.to(svgWoman, { drawSVG: '0% ' + percentWoman + '%', duration: 1, ease: 'power3.out', overwrite: 'auto' });
				gsap.to(delta, {
					woman: percentWoman,
					duration: 1,
					onUpdate: function() {
						blockPrcWoman.innerText = delta.woman.toFixed();
					}
				});
			},
			onLeaveBack: function() {
				gsap.to(svgWoman, { drawSVG: '0%', duration: 0.5, ease: 'none', overwrite: 'auto' });
				gsap.to(delta, {
					woman: 0,
					duration: 1,
					onUpdate: function() {
						blockPrcWoman.innerText = delta.woman.toFixed();
					}
				});
			}
		});

		ScrollTrigger.create({
			trigger: svgMan,
			start: 'bottom 100%',
			onEnter: function() {
				gsap.to(svgMan, { drawSVG: '0% ' + percentMan + '%', duration: 1, ease: 'power3.out', overwrite: 'auto' });
				gsap.to(delta, {
					man: percentMan,
					duration: 1,
					onUpdate: function() {
						blockPrcMan.innerText = delta.man.toFixed();
					}
				});
			},
			onLeaveBack: function() {
				gsap.to(svgMan, { drawSVG: '0%', duration: 0.5, ease: 'none', overwrite: 'auto' });
				gsap.to(delta, {
					man: 0,
					duration: 1,
					onUpdate: function() {
						blockPrcMan.innerText = delta.man.toFixed();
					}
				});
			}
		});
	});
});


/* .about-activity-players-village__line-list
 ******************************************************************************/
$('.about-activity-players-village__line-list').each(function() {
	let lineColorbar = this.querySelectorAll('.about-activity-players-village__line-colorbar');

	ScrollTrigger.batch(lineColorbar, {
		start: 'bottom 100%-=20px',
		onEnter: function(batch) {
			gsap.to(batch, {
				width: function(index, target, targets) {
					return target.getAttribute('data-percent') + '%';
				},
				duration: 1,
				ease: 'power3.out',
				overwrite: 'auto'
			});
		},
		onLeaveBack: function(batch) {
			gsap.to(batch, {
				width: 0,
				duration: 1,
				ease: 'power3.out',
				overwrite: 'auto'
			});
		}
	});
});


/* .about-activity-manufacturers
 ******************************************************************************/
$('.about-activity-manufacturers').each(function() {
	let $productItem = $('.about-activity-manufacturers__product-item', this),
		lineColorbar = this.querySelectorAll('.about-activity-manufacturers__product-chart-colorbar'),
		maxCount = 0;

	$productItem.each(function() {
		let curCount = parseInt($('.about-activity-manufacturers__product-count', this).text().replace(/\D/g, ''));
		maxCount = curCount > maxCount ? curCount : maxCount;
	});

	$(lineColorbar).each(function() {
		let curVal = parseInt($(this).closest('.about-activity-manufacturers__product-item').find('.about-activity-manufacturers__product-count').text().replace(/\D/g, '')),
			curPercent = Math.round(curVal * 100 / maxCount);
		$(this).attr('data-percent', curPercent);
	});

	ScrollTrigger.batch(lineColorbar, {
		start: 'bottom 100%-=20px',
		onEnter: function(batch) {
			gsap.to(batch, {
				width: function(index, target, targets) {
					return target.getAttribute('data-percent') + '%';
				},
				duration: 1,
				ease: 'power3.out',
				overwrite: 'auto'
			});
		},
		onLeaveBack: function(batch) {
			gsap.to(batch, {
				width: 0,
				duration: 1,
				ease: 'power3.out',
				overwrite: 'auto'
			});
		}
	});
});


/* .about-activity-players-village__registry-percent-chart
 ******************************************************************************/
$('.about-activity-players-village__registry-percent-chart').each(function() {
	let percent = parseInt($(this).data('percent')),
		svg = this.querySelector('.svg .circle-value');

	gsap.set(svg, {
		drawSVG: '0%',
		rotation: -90,
		transformOrigin: 'center center'
	});

	ScrollTrigger.create({
		trigger: svg,
		start: 'bottom 100%',
		onEnter: function() {
			gsap.to(svg, { drawSVG: '0% ' + percent + '%', duration: 1, ease: 'power3.out', overwrite: 'auto' });
		},
		onLeaveBack: function() {
			gsap.to(svg, { drawSVG: '0%', duration: 0.5, ease: 'none', overwrite: 'auto' });
		}
	});
});


/* .about-activity-service__simple-info-list
 ******************************************************************************/
$('.about-activity-service__simple-info-list').each(function() {
	let $circleChart = $('.about-activity-service__simple-info-value[data-percent]');

	$circleChart.each(function() {
		let percent = parseInt($(this).data('percent')),
			svg = this.querySelector('.circle .circle-value');

		gsap.set(svg, {
			drawSVG: '0%',
			rotation: -90,
			transformOrigin: 'center center'
		});

		ScrollTrigger.create({
			trigger: svg,
			start: 'bottom 100%-=20px',
			onEnter: function() {
				gsap.to(svg, { drawSVG: '0% ' + percent + '%', duration: 1, ease: 'power3.out', overwrite: 'auto' });
			},
			onLeaveBack: function() {
				gsap.to(svg, { drawSVG: '0%', duration: 0.5, ease: 'none', overwrite: 'auto' });
			}
		});
	});
});


/* .about-activity-service__grey-chart-columns-group
 ******************************************************************************/
$('.about-activity-service__grey-chart-columns-group').each(function() {
	let colorbar = this.querySelectorAll('.about-activity-service__grey-chart-column-colorbar'),
		maxPercent = 0;

	colorbar.forEach(function(elm) {
		let curPercent = +elm.getAttribute('data-percent');
		maxPercent = curPercent > maxPercent ? curPercent : maxPercent;
	});

	ScrollTrigger.batch(colorbar, {
		start: 'bottom bottom',
		onEnter: function(batch) {
			gsap.to(batch, {
				height: function(index, target, targets) {
					let cur = +target.getAttribute('data-percent');
					return (cur * 100 / maxPercent) + '%';
				},
				duration: 1,
				stagger: 0.05,
				ease: 'power3.out',
				overwrite: 'auto'
			});
		},
		onLeaveBack: function(batch) {
			gsap.to(batch, {
				height: '0%',
				duration: 0.5,
				ease: 'none',
				overwrite: 'auto'
			});
		}
	});
});


/* .calendar-events
 ******************************************************************************/
$('.calendar-events').each(function() {
	let $btnCalendarPeriod = $('.js-btn-calendar-period', this),
	    $calendarPeriod    = $('.calendar-period', this);

	$btnCalendarPeriod.on('click', function(evt) {
		evt.preventDefault();
		let id = $(this).data('target-id');
		$(this).addClass('active').siblings('.js-btn-calendar-period').removeClass('active');
		$calendarPeriod.filter(id).slideDown().siblings('.calendar-period').slideUp();
	});

	$(document).on('click', '.js-btn-calendar-category', function(evt) {
		evt.preventDefault();
		let category = $(this).data('category');
		$(this).toggleClass('active');
		if ( $(this).is('.active') ) {
			$(this).closest('.calendar-period').find('.calendar-content').find('[data-category = "' + category + '"]').addClass('active');
		} else {
			$(this).closest('.calendar-period').find('.calendar-content').find('[data-category = "' + category + '"]').removeClass('active');
			if ( ! $(this).siblings('.active').length ) {
				$(this).closest('.calendar-filters').find('.js-btn-calendar-all-category').removeClass('active');
			}
		}
	});

	$(document).on('click', '.js-btn-calendar-all-category', function(evt) {
		evt.preventDefault();
		$(this).toggleClass('active');
		if ( $(this).is('.active') ) {
			$(this).closest('.calendar-filters').find('.js-btn-calendar-category').removeClass('active').trigger('click');
		} else {
			$(this).closest('.calendar-filters').find('.js-btn-calendar-category').addClass('active').trigger('click');
		}
	});

	$(document).on('click', '.js-calendar-filters-show', function(evt) {
		$('body').toggleClass('body-calendar-filters-show');
	});

	$(document).on('click', '.js-calendar-filters-hide', function(evt) {
		$('body').removeClass('body-calendar-filters-show');
	});
});




