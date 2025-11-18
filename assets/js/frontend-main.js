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
	 * Escape HTML for safe output in JavaScript.
	 *
	 * @param {string} text - Text to escape.
	 * @return {string} Escaped text.
	 */
	function escHtml(text) {
		if (!text) return '';
		return text.toString()
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');
	}

	/**
	 * Initialize Magnific Popup for a gallery grid.
	 *
	 * @param {jQuery} $galleryElement - The gallery grid element.
	 */
	function initMagnificPopup($galleryElement) {
		// Check if Magnific Popup is available.
		if (!$.fn.magnificPopup) {
			// Flatsome loads Magnific Popup dynamically, wait for it.
			if (typeof jQuery.loadMagnificPopup === 'function') {
				jQuery.loadMagnificPopup().then(function() {
					initMagnificPopup($galleryElement);
				});
			} else {
				console.error('VN Gallery: Magnific Popup không được tải.');
			}
			return;
		}

		$galleryElement.magnificPopup({
			// Use delegate to handle dynamically shown/hidden items.
			delegate: 'a.vn-gallery-item:visible',
			type: 'image',
			gallery: {
				enabled: true,
				navigateByImgClick: true,
				preload: [0, 1]
			},
			image: {
				titleSrc: function(item) {
					var title = item.el.data('title') || '';
					var desc = item.el.data('description') || '';
					if (desc) {
						return escHtml(title) + '<small>' + escHtml(desc) + '</small>';
					}
					return escHtml(title);
				}
			},
			iframe: {
				patterns: {
					youtube: {
						index: 'youtube.com/',
						id: 'v=',
						src: '//www.youtube.com/embed/%id%?autoplay=1'
					},
					vimeo: {
						index: 'vimeo.com/',
						id: '/',
						src: '//player.vimeo.com/video/%id%?autoplay=1'
					}
				}
			},
			callbacks: {
				// Parse element type (image vs video).
				elementParse: function(item) {
					if (item.el.data('type') === 'video') {
						item.type = 'iframe';
					} else {
						item.type = 'image';
					}
				},
				// Add title for iframe (video) items.
				markupParse: function(template, values, item) {
					if (item.type === 'iframe') {
						var title = item.el.data('title') || '';
						var desc = item.el.data('description') || '';
						if (desc) {
							values.title = escHtml(title) + '<small>' + escHtml(desc) + '</small>';
						} else {
							values.title = escHtml(title);
						}
					}
				}
			}
		});
	}

	/**
	 * Handle filter button clicks.
	 */
	function initFilters() {
		$(document).on('click', '.vn-filter-btn', function(e) {
			e.preventDefault();

			var $btn = $(this);
			var $wrapper = $btn.closest('.vn-gallery-wrapper');
			var $galleryGrid = $wrapper.find('.vn-gallery-grid');
			var filterValue = $btn.data('filter');

			// Update active state.
			$btn.siblings().removeClass('active');
			$btn.addClass('active');

			// Filter items.
			if (filterValue === '*') {
				// Show all items.
				$galleryGrid.find('.vn-gallery-item').show();
			} else {
				// Hide all, then show filtered.
				$galleryGrid.find('.vn-gallery-item').hide();
				$galleryGrid.find(filterValue).show();
			}

			// Reinitialize Magnific Popup with visible items only.
			if ($.fn.magnificPopup) {
				$galleryGrid.magnificPopup('destroy');
				initMagnificPopup($galleryGrid);
			}
		});
	}

	/**
	 * Initialize on document ready.
	 */
	$(document).ready(function() {
		// Initialize filters.
		initFilters();

		// Initialize Magnific Popup for all galleries.
		$('.vn-gallery-grid').each(function() {
			initMagnificPopup($(this));
		});
	});

})(jQuery);
