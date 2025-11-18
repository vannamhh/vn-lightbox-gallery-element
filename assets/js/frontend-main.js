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

		// Destroy existing instance if any
		$galleryElement.magnificPopup('close');
		if ($galleryElement.data('magnificPopup')) {
			$galleryElement.magnificPopup('destroy');
		}
		
		// Remove ALL click handlers from items (including Flatsome's)
		$galleryElement.find('a.vn-gallery-item').off('click');

		// Build items array to ensure gallery mode works
		var items = [];
		$galleryElement.find('a.vn-gallery-item:visible').each(function() {
			var $this = $(this);
			var itemType = $this.data('type') === 'video' ? 'iframe' : 'image';
			items.push({
				src: $this.attr('href'),
				type: itemType,
				title: $this.data('title') || '',
				description: $this.data('description') || ''
			});
		});

		console.log('VN Gallery: Initializing with', items.length, 'items');

		// Bind click handler manually to override Flatsome
		$galleryElement.on('click.vnGallery', 'a.vn-gallery-item', function(e) {
			e.preventDefault();
			e.stopPropagation();
			e.stopImmediatePropagation();
			
			var $clicked = $(this);
			var index = $galleryElement.find('a.vn-gallery-item:visible').index($clicked);
			
			console.log('VN Gallery: Clicked item', index);
			
			// Open magnificPopup at clicked index
			$.magnificPopup.open({
				items: items,
				type: 'image',
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
				arrowMarkup: '<button title="%title%" type="button" class="mfp-arrow mfp-arrow-%dir%"></button>'
			},
			image: {
				titleSrc: function(item) {
					var title = item.el.data('title') || '';
					var desc = item.el.data('description') || '';
					var output = '';
					if (title) {
						output = '<h3>' + escHtml(title) + '</h3>';
					}
					if (desc) {
						output += '<p>' + escHtml(desc) + '</p>';
					}
					return output;
				},
				verticalFit: true,
				tError: '<a href="%url%">Hình ảnh</a> không thể tải.',
				markup: '<div class="mfp-figure">'+
						'<div class="mfp-close"></div>'+
						'<div class="mfp-img"></div>'+
						'<div class="mfp-bottom-bar">'+
						'<div class="mfp-title"></div>'+
						'<div class="mfp-counter"></div>'+
						'</div>'+
						'</div>'
			},
			iframe: {
				markup: '<div class="mfp-iframe-scaler">'+
						'<div class="mfp-close"></div>'+
						'<iframe class="mfp-iframe" frameborder="0" allowfullscreen></iframe>'+
						'<div class="mfp-bottom-bar">'+
						'<div class="mfp-title"></div>'+
						'<div class="mfp-counter"></div>'+
						'</div>'+
						'</div>',
				patterns: {
					youtube: {
						index: 'youtube.com/',
						id: function(url) {
							var m = url.match(/[\?&]v=([^\?&]+)/);
							if (!m || !m[1]) return null;
							return m[1];
						},
						src: '//www.youtube.com/embed/%id%?autoplay=1&rel=0'
					},
					youtu_be: {
						index: 'youtu.be/',
						id: function(url) {
							var m = url.match(/youtu\.be\/([^\?&]+)/);
							if (!m || !m[1]) return null;
							return m[1];
						},
						src: '//www.youtube.com/embed/%id%?autoplay=1&rel=0'
					},
					vimeo: {
						index: 'vimeo.com/',
						id: function(url) {
							var m = url.match(/vimeo\.com\/(\d+)/);
							if (!m || !m[1]) return null;
							return m[1];
						},
						src: '//player.vimeo.com/video/%id%?autoplay=1'
					}
				}
			},
			callbacks: {
				elementParse: function(item) {
					// Item type already set in items array
					// Just use it directly, no need to parse el
					console.log('Parsing item:', item.src, 'Type:', item.type);
				},
				markupParse: function(template, values, item) {
					// Get title and description from item data (already stored in items array)
					var title = item.title || '';
					var desc = item.description || '';
					var output = '';
					
					if (title) {
						output = '<h3>' + escHtml(title) + '</h3>';
					}
					if (desc) {
						output += '<p>' + escHtml(desc) + '</p>';
					}
					
					values.title = output;
				},
				open: function() {
					console.log('VN Gallery: Opened', this.index + 1, '/', this.items.length);
				},
				change: function() {
					console.log('VN Gallery: Changed to', this.index + 1, '/', this.items.length);
				}
			}
			}, index); // Pass starting index
		});

		console.log('VN Gallery: Initialized successfully');
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

		// Wait for Flatsome to finish loading, then override
		setTimeout(function() {
			$('.vn-gallery-grid').each(function() {
				var $gallery = $(this);
				
				// Remove Flatsome's lightbox handlers
				$gallery.find('a.vn-gallery-item').off('click.flatsomeLightbox');
				$gallery.off('click.flatsomeLightbox');
				
				// Destroy any existing magnificPopup
				if ($gallery.data('magnificPopup')) {
					$gallery.magnificPopup('destroy');
				}
				
				// Initialize our gallery
				initMagnificPopup($gallery);
			});
		}, 500);
	});

})(jQuery);
