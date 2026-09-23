/**
 * VN Lightbox Gallery Frontend JavaScript
 *
 * Handles Magnific Popup initialization and filtering logic.
 *
 * @package VN_Lightbox_Gallery
 * @since 4.0.0
 */

(function($) {
	'use strict';

	var SELECTORS = {
		grid: '.vn-gallery-grid',
		item: '.gallery-item-wrapper',
		link: 'a.vn-gallery-item',
		filterBtn: '.vn-filter-btn',
		wrapper: '.vn-gallery-wrapper'
	};

	var YOUTUBE_EMBED = 'https://www.youtube.com/embed/%id%?autoplay=1&mute=1&rel=0';

	/**
	 * Escape HTML for safe output.
	 * @param {string} text - Text to escape.
	 * @return {string} Escaped text.
	 */
	function escapeHtml(text) {
		return String(text || '')
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');
	}

	/**
	 * Build an iframe id resolver from a regex (first capture group = video ID).
	 * Falls back to the raw URL so Magnific Popup never embeds "null".
	 * @param {RegExp} pattern - Regex pattern.
	 * @return {Function}
	 */
	function videoId(pattern) {
		return function(url) {
			var match = url.match(pattern);
			return match ? match[1] : url;
		};
	}

	var BOTTOM_BAR = '<div class="mfp-bottom-bar"><div class="mfp-title"></div><div class="mfp-counter"></div></div>';

	/**
	 * Magnific Popup configuration (items are added at open time).
	 */
	var MFP_CONFIG = {
		mainClass: 'mfp-fade mfp-img-mobile',
		removalDelay: 300,
		closeOnContentClick: false,
		closeBtnInside: false,
		fixedContentPos: true,
		gallery: {
			enabled: true,
			navigateByImgClick: true,
			preload: [0, 1],
			tPrev: 'Trước (Left arrow)',
			tNext: 'Tiếp (Right arrow)',
			tCounter: '%curr% / %total%',
			arrowMarkup: '<button title="%title%" type="button" class="vn-arrow fb-icon mfp-arrow mfp-arrow-%dir%"><span class="fb-icon-arrow"></span></button>'
		},
		image: {
			verticalFit: true,
			tError: '<a href="%url%">Hình ảnh</a> không thể tải.',
			markup: '<div class="mfp-figure"><div class="mfp-close"></div><div class="mfp-img"></div>' + BOTTOM_BAR + '</div>'
		},
		iframe: {
			markup: '<div class="mfp-iframe-scaler"><div class="mfp-close"></div><iframe class="mfp-iframe" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>' + BOTTOM_BAR + '</div>',
			patterns: {
				youtube: {
					index: 'youtube.com/',
					id: videoId(/(?:[?&]v=|\/(?:embed|shorts|live)\/)([\w-]{11})/),
					src: YOUTUBE_EMBED
				},
				youtu_be: {
					index: 'youtu.be/',
					id: videoId(/youtu\.be\/([\w-]{11})/),
					src: YOUTUBE_EMBED
				},
				vimeo: {
					index: 'vimeo.com/',
					id: videoId(/vimeo\.com\/(?:video\/)?(\d+)/),
					src: 'https://player.vimeo.com/video/%id%?autoplay=1'
				}
			}
		},
		callbacks: {
			/**
			 * Runs for every item (image & iframe) before it is shown:
			 * show the description, hide the bottom bar when there is none.
			 */
			markupParse: function(template, values, item) {
				var desc = (item.data && item.data.description) || '';
				values.title = desc ? '<div class="mfp-description">' + escapeHtml(desc) + '</div>' : '';
				template.find('.mfp-bottom-bar').toggle(!!desc);
			}
		}
	};

	/**
	 * Open the lightbox for a gallery, starting at the clicked item.
	 * Items are read at click time so filtering never needs a re-init.
	 * @param {jQuery} $grid - Gallery grid.
	 * @param {jQuery} $current - Clicked item wrapper.
	 */
	function openGallery($grid, $current) {
		if (!$.fn.magnificPopup) {
			// Flatsome lazy-loads Magnific Popup.
			if (typeof $.loadMagnificPopup === 'function') {
				$.loadMagnificPopup().then(function() {
					openGallery($grid, $current);
				});
			}
			return;
		}

		var $items = $grid.find(SELECTORS.item).filter(':visible');
		var items = $items.map(function() {
			var $link = $(this).find(SELECTORS.link);
			return {
				src: $link.attr('href'),
				type: $link.attr('data-type') === 'video' ? 'iframe' : 'image',
				description: $link.attr('data-description') || ''
			};
		}).get();

		$.magnificPopup.open($.extend({}, MFP_CONFIG, { items: items }), Math.max(0, $items.index($current)));
	}

	/**
	 * Capture-phase listener: runs before any handler bound on the links
	 * (e.g. Flatsome's own lightbox), regardless of when those were bound.
	 */
	document.addEventListener('click', function(e) {
		// Keep ctrl/cmd/shift-click = open in new tab.
		if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || !(e.target instanceof Element)) {
			return;
		}

		var link = e.target.closest(SELECTORS.link);
		var grid = link && link.closest(SELECTORS.grid);
		if (!grid) {
			return;
		}

		e.preventDefault();
		e.stopPropagation();
		openGallery($(grid), $(link).closest(SELECTORS.item));
	}, true);

	/**
	 * Filter buttons.
	 */
	$(document).on('click', SELECTORS.filterBtn, function(e) {
		e.preventDefault();

		var $btn = $(this);
		var filter = $btn.attr('data-filter');

		$btn.addClass('active').attr('aria-pressed', 'true')
			.siblings(SELECTORS.filterBtn).removeClass('active').attr('aria-pressed', 'false');

		$btn.closest(SELECTORS.wrapper).find(SELECTORS.item).each(function() {
			var $item = $(this);
			$item.toggle(filter === '*' || $item.find(SELECTORS.link).is(filter));
		});
	});

})(jQuery);
