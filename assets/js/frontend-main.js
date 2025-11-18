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

	/**
	 * Configuration Constants
	 */
	var CONFIG = {
		FLATSOME_INIT_DELAY: 500,
		VIDEO_AUTOPLAY_PARAMS: 'autoplay=1&mute=1&rel=0',
		SELECTORS: {
			galleryGrid: '.vn-gallery-grid',
			galleryItem: 'a.vn-gallery-item',
			filterBtn: '.vn-filter-btn',
			galleryWrapper: '.vn-gallery-wrapper'
		}
	};

	/**
	 * Magnific Popup Configuration
	 */
	var MAGNIFICPOPUP_CONFIG = {
		baseConfig: {
			type: 'image',
			mainClass: 'mfp-fade mfp-img-mobile',
			removalDelay: 300,
			closeOnContentClick: false,
			closeBtnInside: false,
			fixedContentPos: true
		},
		galleryConfig: {
			enabled: true,
			navigateByImgClick: true,
			preload: [0, 1],
			tPrev: 'Trước (Left arrow)',
			tNext: 'Tiếp (Right arrow)',
			tCounter: '%curr% / %total%',
			arrowMarkup: '<button title="%title%" type="button" class="mfp-arrow mfp-arrow-%dir%"></button>'
		},
		imageConfig: {
			verticalFit: true,
			tError: '<a href="%url%">Hình ảnh</a> không thể tải.'
		},
		markup: {
			image: '<div class="mfp-figure">' +
				'<div class="mfp-close"></div>' +
				'<div class="mfp-img"></div>' +
				'<div class="mfp-bottom-bar">' +
				'<div class="mfp-title"></div>' +
				'<div class="mfp-counter"></div>' +
				'</div>' +
				'</div>',
			iframe: '<div class="mfp-iframe-scaler">' +
				'<div class="mfp-close"></div>' +
				'<iframe class="mfp-iframe" frameborder="0" allowfullscreen></iframe>' +
				'<div class="mfp-bottom-bar">' +
				'<div class="mfp-title"></div>' +
				'<div class="mfp-counter"></div>' +
				'</div>' +
				'</div>'
		}
	};

	/**
	 * Utility Functions
	 */
	var Utils = {
		/**
		 * Escape HTML for safe output.
		 * @param {string} text - Text to escape.
		 * @return {string} Escaped text.
		 */
		escapeHtml: function(text) {
			if (!text) return '';
			return text.toString()
				.replace(/&/g, '&amp;')
				.replace(/</g, '&lt;')
				.replace(/>/g, '&gt;')
				.replace(/"/g, '&quot;')
				.replace(/'/g, '&#039;');
		},

		/**
		 * Build title markup from item data.
		 * @param {Object} item - Item with title and description.
		 * @return {string} HTML markup.
		 */
		buildTitleMarkup: function(item) {
			var title = item.title || '';
			var desc = item.description || '';
			var output = '';
			
			if (title) {
				output = '<h3>' + this.escapeHtml(title) + '</h3>';
			}
			if (desc) {
				output += '<p>' + this.escapeHtml(desc) + '</p>';
			}
			
			return output;
		},

		/**
		 * Extract video ID from URL.
		 * @param {string} url - Video URL.
		 * @param {RegExp} pattern - Regex pattern to match.
		 * @return {string|null} Video ID or null.
		 */
		extractVideoId: function(url, pattern) {
			var match = url.match(pattern);
			return (match && match[1]) ? match[1] : null;
		}
	};

	/**
	 * Video Platform Patterns
	 */
	var VIDEO_PATTERNS = {
		youtube: {
			index: 'youtube.com/',
			id: function(url) {
				return Utils.extractVideoId(url, /[\?&]v=([^\?&]+)/);
			},
			src: '//www.youtube.com/embed/%id%?' + CONFIG.VIDEO_AUTOPLAY_PARAMS
		},
		youtu_be: {
			index: 'youtu.be/',
			id: function(url) {
				return Utils.extractVideoId(url, /youtu\.be\/([^\?&]+)/);
			},
			src: '//www.youtube.com/embed/%id%?' + CONFIG.VIDEO_AUTOPLAY_PARAMS
		},
		vimeo: {
			index: 'vimeo.com/',
			id: function(url) {
				return Utils.extractVideoId(url, /vimeo\.com\/(\d+)/);
			},
			src: '//player.vimeo.com/video/%id%?autoplay=1'
		}
	};

	/**
	 * Gallery Module
	 */
	var Gallery = {
		/**
		 * Build items array from gallery element.
		 * @param {jQuery} $gallery - Gallery element.
		 * @return {Array} Items array.
		 */
		buildItemsArray: function($gallery) {
			var items = [];
			$gallery.find(CONFIG.SELECTORS.galleryItem + ':visible').each(function() {
				var $item = $(this);
				items.push({
					src: $item.attr('href'),
					type: $item.data('type') === 'video' ? 'iframe' : 'image',
					title: $item.data('title') || '',
					description: $item.data('description') || ''
				});
			});
			return items;
		},

		/**
		 * Clean up existing instances.
		 * @param {jQuery} $gallery - Gallery element.
		 */
		cleanup: function($gallery) {
			$gallery.magnificPopup('close');
			if ($gallery.data('magnificPopup')) {
				$gallery.magnificPopup('destroy');
			}
			$gallery.find(CONFIG.SELECTORS.galleryItem).off('click');
			$gallery.find(CONFIG.SELECTORS.galleryItem).off('click.flatsomeLightbox');
			$gallery.off('click.flatsomeLightbox');
		},

		/**
		 * Get Magnific Popup configuration.
		 * @param {Array} items - Gallery items.
		 * @return {Object} Configuration object.
		 */
		getMagnificConfig: function(items) {
			var config = $.extend({}, MAGNIFICPOPUP_CONFIG.baseConfig, {
				items: items,
				gallery: MAGNIFICPOPUP_CONFIG.galleryConfig,
				image: $.extend({}, MAGNIFICPOPUP_CONFIG.imageConfig, {
					markup: MAGNIFICPOPUP_CONFIG.markup.image
				}),
				iframe: {
					markup: MAGNIFICPOPUP_CONFIG.markup.iframe,
					patterns: VIDEO_PATTERNS
				},
				callbacks: {
					markupParse: function(template, values, item) {
						values.title = Utils.buildTitleMarkup(item);
					}
				}
			});
			return config;
		},

		/**
		 * Initialize Magnific Popup.
		 * @param {jQuery} $gallery - Gallery element.
		 */
		init: function($gallery) {
			var self = this;

			// Check if Magnific Popup is available
			if (!$.fn.magnificPopup) {
				if (typeof jQuery.loadMagnificPopup === 'function') {
					jQuery.loadMagnificPopup().then(function() {
						self.init($gallery);
					});
				}
				return;
			}

			// Cleanup existing instances
			this.cleanup($gallery);

			// Build items array
			var items = this.buildItemsArray($gallery);

			// Bind click handler
			var config = this.getMagnificConfig(items);
			$gallery.on('click.vnGallery', CONFIG.SELECTORS.galleryItem, function(e) {
				e.preventDefault();
				e.stopPropagation();
				e.stopImmediatePropagation();
				
				var index = $gallery.find(CONFIG.SELECTORS.galleryItem + ':visible').index($(this));
				
				$.magnificPopup.open(config, index);
			});
		}
	};

	/**
	 * Filter Module
	 */
	var Filter = {
		/**
		 * Apply filter to gallery.
		 * @param {jQuery} $gallery - Gallery element.
		 * @param {string} filterValue - Filter selector or '*' for all.
		 */
		apply: function($gallery, filterValue) {
			var $items = $gallery.find(CONFIG.SELECTORS.galleryItem);
			
			// Remove active class and hide all
			$items.removeClass('vn-item-visible');
			
			setTimeout(function() {
				if (filterValue === '*') {
					$items.show().addClass('vn-item-visible');
				} else {
					$items.hide();
					$gallery.find(filterValue).show().addClass('vn-item-visible');
				}
			}, 50);
		},

		/**
		 * Initialize filter buttons.
		 */
		init: function() {
			$(document).on('click', CONFIG.SELECTORS.filterBtn, function(e) {
				e.preventDefault();

				var $btn = $(this);
				var $wrapper = $btn.closest(CONFIG.SELECTORS.galleryWrapper);
				var $gallery = $wrapper.find(CONFIG.SELECTORS.galleryGrid);
				var filterValue = $btn.data('filter');

				// Update active state
				$btn.siblings().removeClass('active');
				$btn.addClass('active');

				// Apply filter
				Filter.apply($gallery, filterValue);

				// Reinitialize gallery
				if ($.fn.magnificPopup) {
					$gallery.magnificPopup('destroy');
					Gallery.init($gallery);
				}
			});
		}
	};

	/**
	 * Initialize on document ready
	 */
	$(document).ready(function() {
		// Initialize filters
		Filter.init();

		// Wait for Flatsome, then initialize galleries
		setTimeout(function() {
			$(CONFIG.SELECTORS.galleryGrid).each(function() {
				Gallery.init($(this));
			});
		}, CONFIG.FLATSOME_INIT_DELAY);
	});

})(jQuery);
