/*! jQuery.smatEqualItemsHeight | v1.4 */
/**
 * Назначение: Автоматическое выравнивание высоты блоков, и при необходимости - высоты внутренних элементов этих блоков
 * Работает и при ресайзе окна
 *
 * Все ругательства слать сюда: smat.dnepr@gmail.com
 *
 * Использование:
 * HTML:
 *   <div class="container">
 *       <div class="item"></div>
 *       <div class="item"></div>
 *       <div class="item"></div>
 *       <div class="item"></div>
 *   </div>
 * JS
 *   $(document).ready(function() {
 *        $('.item').smatEqualItemsHeight();
 *   });
 *
 * Если нужно задать выравнивание высоты еще и у внутренних элементов:
 *   $('.item').smatEqualItemsHeight({
 *       innerEqualElems: ['h2', 'p']
 *   });
 *
 * Если внутренние IMG нужно уменьшить по ширине и высоте в 2 раза от размера, заданного в CSS:
 *   $('.item').smatEqualItemsHeight({
 *       innerСompresElems: ['.img-1', '.img-2']
 *   });
 *
 *
 */

const jQuery = jQuery || require('jquery');

;(function ($) {
	var defaults = {
			'innerEqualElems': [],
			'innerСompresElems': []
		},
		methods = {
			init : function( options ) {
				var _this = this,
					timeout = false,
					$parent = this.parent(),
					settings = $.extend({}, defaults, options);

				methods.run( _this, settings );

				$(window).on("load", function() {
					methods.run( _this, settings );
				})
				.on('resize', function() {
					if (!!timeout) clearTimeout(timeout);
					timeout = setTimeout(function() {
						methods.run( _this, settings );
					}, 100);
				});

				return this;
			},
			run : function( elems, props ) {
				var rowEls = [],
					curElem = 0,
					curRowStart = 0,
					topPostion = 0,
					maxHeight = 0,
					equalElems = props.innerEqualElems,
					compresElems = props.innerСompresElems,
					maxEqualElemsHeight = [],
					elmsCompres = $(elems).find(compresElems.join(', ')),
					$cmpElem,
					setEqualHeightWidthCompresElems = function() {
						for ( var i = 0; i < elmsCompres.length; i++) {
							$cmpElem = $(elmsCompres[i]);

							if ( $cmpElem.is('img') ) {
								$cmpElem.css({ width: '', height: '' });

								if ( $cmpElem[0].complete && $cmpElem[0].naturalHeight !== 0 ) {
									$cmpElem.css({
										'width': $cmpElem.width() / 2,
										'height': $cmpElem.height() / 2
									});
									setEqualHeight();
								}
								else {
									$cmpElem.on('load', function() {
										$(this).css({
											'width': $(this).width() / 2,
											'height': $(this).height() / 2
										});
										setEqualHeight();
									});
								}
							}
						};
					},
					setEqualHeight = function() {
						for (var i = 0; i < equalElems.length; i++) {
							maxEqualElemsHeight[i] = 0;
						}
						$.each(elems, function() {
							var $el = $(this);
							for (var i = 0; i < equalElems.length; i++) {
								$el.find(equalElems[i]).height("auto");
							}
							$el.height("auto");
							topPostion = $el.offset().top;

							if (curRowStart != topPostion) {
								for (curElem = 0; curElem < rowEls.length; curElem++) {
									for (var i = 0; i < equalElems.length; i++) {
										rowEls[curElem].find(equalElems[i]).height(maxEqualElemsHeight[i]);
									}
									maxHeight = Math.max( maxHeight, rowEls[curElem].height() );
									rowEls[curElem].height(maxHeight);
								}
								rowEls.length = 0;
								curRowStart = topPostion;
								rowEls.push($el);
								for (var i = 0; i < equalElems.length; i++) {
									maxEqualElemsHeight[i] = $el.find(equalElems[i]).height();
								}
								maxHeight = $el.height();
							}
							else {
								rowEls.push($el);
								for (var i = 0; i < equalElems.length; i++) {
									maxEqualElemsHeight[i] = Math.max( maxEqualElemsHeight[i], $el.find(equalElems[i]).height() );
								};
								maxHeight = Math.max( maxHeight, $el.height() );
							}

							for (curElem = 0; curElem < rowEls.length; curElem++) {
								rowEls[curElem].height('auto');
								for (var i = 0; i < equalElems.length; i++) {
									rowEls[curElem].find(equalElems[i]).height(maxEqualElemsHeight[i]);
								}
								maxHeight = Math.max( maxHeight, rowEls[curElem].height() );
							}

							for (curElem = 0; curElem < rowEls.length; curElem++) {
								rowEls[curElem].height(maxHeight);
							}
						});
					};

				if ( !!compresElems.length ) {
					setEqualHeightWidthCompresElems();
				} else {
					setEqualHeight();
				}
			}
		};


	$.fn.smatEqualItemsHeight = function( method ) {
		if ( methods[ method ] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist on jQuery.smatEqualItemsHeight' );
		}
	};
})(jQuery);