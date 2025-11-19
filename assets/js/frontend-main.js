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
			galleryItem: '.gallery-item-wrapper',
			filterBtn: '.vn-filter-btn',
			galleryWrapper: '.vn-gallery-wrapper'
		}
	};

	/**
	 * Magnific Popup Configuration
	 */
	var MAGNIFICPOPUP_CONFIG = {
		baseConfig: {
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
			arrowMarkup: '<button title="%title%" type="button" class="vn-arrow vn-prev fb-icon mfp-arrow mfp-arrow-%dir%" id="vnMenuPrev"><span class="fb-icon-arrow"></span></button></button>'
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
		 * Only shows description (no title), returns empty string if description is empty.
		 * @param {Object} item - Item with description.
		 * @return {string} HTML markup or empty string.
		 */
		buildTitleMarkup: function(item) {
			var desc = item.description || '';
			
			if (desc) {
				return '<div class="mfp-description">' + this.escapeHtml(desc) + '</div>';
			}
			
			return '';
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
				var $wrapper = $(this);
				var $link = $wrapper.find('a.vn-gallery-item');
				
				// Get data-type attribute value
				var dataType = $link.attr('data-type');
				var itemType = (dataType === 'video') ? 'iframe' : 'image';
				
				var title = $link.attr('data-title') || '';
				var description = $link.attr('data-description') || '';
				
				items.push({
					src: $link.attr('href'),
					type: itemType,
					title: title,
					description: description
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
			// Unbind all possible Flatsome and plugin events
			$gallery.off('click.vnGallery');
			$gallery.off('click.flatsomeLightbox');
			$gallery.find('a.vn-gallery-item').off('click');
			$gallery.find('a.vn-gallery-item').off('click.flatsomeLightbox');
			$gallery.find('.border-image').off('click');
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
					},
					change: function() {
						var index = this.index;
						var item = items[index];
						var $content = this.contentContainer;
						var $bottomBar = $content.find('.mfp-bottom-bar');
						var $title = $bottomBar.find('.mfp-title');
						
						if (item && item.description) {
							var titleHtml = Utils.buildTitleMarkup(item);
							$title.html(titleHtml);
							$bottomBar.show();
						} else {
							$bottomBar.hide();
						}
					},
					imageLoadComplete: function() {
						var index = this.index;
						var item = items[index];
						var $content = this.contentContainer;
						var $bottomBar = $content.find('.mfp-bottom-bar');
						var $title = $bottomBar.find('.mfp-title');
						
						if (item && item.description) {
							var titleHtml = Utils.buildTitleMarkup(item);
							$title.html(titleHtml);
							$bottomBar.show();
						} else {
							$bottomBar.hide();
						}
					},
					open: function() {
						var self = this;
						var index = this.index;
						var item = items[index];
						
						// Wait for DOM to be ready
						setTimeout(function() {
							var $content = self.contentContainer;
							var $bottomBar = $content.find('.mfp-bottom-bar');
							var $title = $bottomBar.find('.mfp-title');
							
							if (item && item.description) {
								var titleHtml = Utils.buildTitleMarkup(item);
								$title.html(titleHtml);
								$bottomBar.show();
							} else {
								$bottomBar.hide();
							}
						}, 50);
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

			// Cleanup existing instances including Flatsome handlers
			this.cleanup($gallery);

			// Build items array
			var items = this.buildItemsArray($gallery);
			var config = this.getMagnificConfig(items);

			// Bind click handler with capture phase to override Flatsome
			// Use both event delegation and direct binding for maximum compatibility
			$gallery.find(CONFIG.SELECTORS.galleryItem + ':visible a.vn-gallery-item').each(function(i) {
				var $link = $(this);
				
				// Remove all existing handlers
				$link.off('click');
				
				// Bind new handler with high priority
				$link.on('click.vnGallery', function(e) {
					e.preventDefault();
					e.stopPropagation();
					e.stopImmediatePropagation();
					
					// Open gallery at correct index
					var $wrapper = $(this).closest(CONFIG.SELECTORS.galleryItem);
					var index = $gallery.find(CONFIG.SELECTORS.galleryItem + ':visible').index($wrapper);
					
					$.magnificPopup.open(config, index);
					
					return false;
				});
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
					// Hide all items first
					$items.hide();
					// Show only wrappers that contain matching items
					$items.each(function() {
						if ($(this).find(filterValue).length > 0) {
							$(this).show().addClass('vn-item-visible');
						}
					});
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
