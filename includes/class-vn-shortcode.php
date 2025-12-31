<?php
declare(strict_types=1);

/**
 * Shortcode Handler Class
 *
 * Handles rendering of [vn_gallery] shortcode.
 *
 * @package VN_Lightbox_Gallery
 * @since 4.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class VN_Shortcode
 *
 * Processes and renders the VN Gallery shortcode.
 */
class VN_Shortcode {

	/**
	 * Instance of this class.
	 *
	 * @var VN_Shortcode
	 */
	private static $instance = null;

	/**
	 * MetaBox field name constants.
	 */
	const METABOX_FIELD_ID       = 'vn_gallery_items';
	const FIELD_ITEM_TYPE        = 'item_type';
	const FIELD_ITEM_IMAGE       = 'item_image';
	const FIELD_ITEM_VIDEO_URL   = 'item_video_url';
	const FIELD_ITEM_THUMBNAIL   = 'item_thumbnail';
	const FIELD_ITEM_TITLE       = 'item_title';
	const FIELD_ITEM_DESCRIPTION = 'item_description';

	/**
	 * Get the singleton instance.
	 *
	 * @return VN_Shortcode
	 */
	public static function get_instance(): VN_Shortcode {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_shortcode( 'vn_gallery', array( $this, 'render_shortcode' ) );
	}

	/**
	 * Render the shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_shortcode( $atts ): string {
		// Parse attributes with responsive column defaults.
		$atts = shortcode_atts(
			array(
				'field'       => self::METABOX_FIELD_ID,
				'gallery_id'  => 0,
				'filters'     => 'true',
				'show_title'  => 'false',
				'col_spacing' => 'normal',
				'columns'     => '4',
				'columns__md' => '',
				'columns__sm' => '',
				'class'       => '',
			),
			$atts,
			'vn_gallery'
		);

		$field_id     = sanitize_key( $atts['field'] );
		$gallery_id   = absint( $atts['gallery_id'] );
		$show_filters = rest_sanitize_boolean( $atts['filters'] );
		$show_title   = rest_sanitize_boolean( $atts['show_title'] );

		// Check for UX Builder preview context.
		$is_ux_builder = $this->is_ux_builder_context();

		// Validate gallery ID - show placeholder in UX Builder, error on frontend.
		if ( $gallery_id <= 0 ) {
			if ( $is_ux_builder ) {
				return $this->render_ux_builder_placeholder( $atts );
			}
			return $this->render_error( __( 'Lỗi VN Gallery: Vui lòng chọn gallery cần hiển thị.', 'vn-lightbox-gallery' ) );
		}

		// Verify post type is 'gallery'.
		if ( get_post_type( $gallery_id ) !== 'gallery' ) {
			return $this->render_error(
				sprintf(
					/* translators: %d: Post ID */
					__( 'Lỗi VN Gallery: Post ID %d không phải là gallery post type.', 'vn-lightbox-gallery' ),
					$gallery_id
				)
			);
		}

		// Parse layout attributes.
		$layout_config = $this->parse_layout_attributes( $atts );

		// Sanitize multiple classes separated by spaces.
		$custom_classes = array();
		if ( ! empty( $atts['class'] ) ) {
			$classes = explode( ' ', $atts['class'] );
			foreach ( $classes as $class ) {
				$sanitized = sanitize_html_class( $class );
				if ( ! empty( $sanitized ) ) {
					$custom_classes[] = $sanitized;
				}
			}
		}

		// Check if MetaBox is available.
		if ( ! function_exists( 'rwmb_get_value' ) ) {
			return $this->render_error( __( 'Lỗi VN Gallery: MetaBox.io không được kích hoạt.', 'vn-lightbox-gallery' ) );
		}

		// Get gallery data from MetaBox (fixed post_type='gallery').
		$gallery_data = rwmb_get_value( $field_id, array( 'object_id' => $gallery_id ), $gallery_id );

		// Debug for admins: Show data info if invalid.
		if ( $this->should_show_debug( $gallery_data ) ) {
			return $this->render_error( $this->build_debug_info( $field_id, $gallery_id, $gallery_data ) );
		}

		// Validate gallery data.
		if ( ! $this->is_valid_gallery_data( $gallery_data ) ) {
			return $this->render_error(
				sprintf(
					/* translators: %s: Field ID */
					__( 'Lỗi VN Gallery: Không tìm thấy dữ liệu cho trường "%s" hoặc dữ liệu không hợp lệ.', 'vn-lightbox-gallery' ),
					$field_id
				),
				false
			);
		}

		// Signal assets need to be loaded.
		VN_Assets::enqueue_scripts();

		// Start output buffering.
		ob_start();

		// Build wrapper classes.
		$wrapper_classes = array( 'vn-gallery-wrapper' );
		if ( ! empty( $custom_classes ) ) {
			$wrapper_classes = array_merge( $wrapper_classes, $custom_classes );
		}

		printf( '<div class="%s">', esc_attr( implode( ' ', $wrapper_classes ) ) );

		// Render filter buttons if enabled.
		if ( $show_filters ) {
			$this->render_filters();
		}

		// Build grid classes with Flatsome responsive columns.
		$grid_classes = $this->build_grid_classes( $layout_config );

		// Render gallery grid.
		$gallery_dom_id = 'vn-gallery-' . esc_attr( $gallery_id . '-' . $field_id );
		printf(
			'<div class="%s" id="%s">',
			esc_attr( implode( ' ', $grid_classes ) ),
			esc_attr( $gallery_dom_id )
		);

		// Debug: Log gallery data for admins.
		$this->log_gallery_data( $gallery_id, $field_id, $gallery_data );

		foreach ( $gallery_data as $item ) {
			$this->render_item( $item, $show_title );
		}

		echo '</div>'; // .vn-gallery-grid.
		echo '</div>'; // .vn-gallery-wrapper.

		return ob_get_clean();
	}

	/**
	 * Parse layout attributes from shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return array Parsed layout configuration.
	 */
	private function parse_layout_attributes( array $atts ): array {
		$columns    = absint( $atts['columns'] ) > 0 ? absint( $atts['columns'] ) : 4;
		$columns_md = ! empty( $atts['columns__md'] ) ? absint( $atts['columns__md'] ) : 0;
		$columns_sm = ! empty( $atts['columns__sm'] ) ? absint( $atts['columns__sm'] ) : 0;

		// Apply Flatsome fallback logic for responsive columns.
		if ( 0 === $columns_md && $columns > 3 ) {
			$columns_md = 3;
		} elseif ( 0 === $columns_md ) {
			$columns_md = $columns;
		}

		if ( 0 === $columns_sm && $columns > 2 ) {
			$columns_sm = 2;
		} elseif ( 0 === $columns_sm ) {
			$columns_sm = min( $columns, 2 );
		}

		// Sanitize col_spacing.
		$valid_spacings = array( 'collapse', 'xsmall', 'small', 'normal', 'large' );
		$col_spacing    = in_array( $atts['col_spacing'], $valid_spacings, true )
			? $atts['col_spacing']
			: 'normal';

		return array(
			'columns'     => $columns,
			'columns__md' => $columns_md,
			'columns__sm' => $columns_sm,
			'col_spacing' => $col_spacing,
		);
	}

	/**
	 * Build grid CSS classes based on layout configuration.
	 *
	 * Uses Flatsome's responsive column class pattern for consistency.
	 *
	 * @param array $config Layout configuration.
	 * @return array Array of CSS classes.
	 */
	private function build_grid_classes( array $config ): array {
		$classes = array( 'vn-gallery-grid', 'row' );

		// Add column spacing class (Flatsome pattern: row-{spacing}).
		if ( 'normal' !== $config['col_spacing'] ) {
			$classes[] = 'row-' . $config['col_spacing'];
		}

		// Add responsive column classes (Flatsome pattern).
		$classes[] = 'large-columns-' . $config['columns'];
		$classes[] = 'medium-columns-' . $config['columns__md'];
		$classes[] = 'small-columns-' . $config['columns__sm'];

		return $classes;
	}



	/**
	 * Render filter buttons.
	 */
	private function render_filters(): void {
		?>
		<div class="vn-gallery-filters">
			<button class="vn-filter-btn active" data-filter="*">
				<?php esc_html_e( 'Tất cả', 'vn-lightbox-gallery' ); ?>
			</button>
			<button class="vn-filter-btn" data-filter=".vn-item-image">
				<?php esc_html_e( 'Hình ảnh', 'vn-lightbox-gallery' ); ?>
			</button>
			<button class="vn-filter-btn" data-filter=".vn-item-video">
				<?php esc_html_e( 'Video', 'vn-lightbox-gallery' ); ?>
			</button>
		</div>
		<?php
	}

	/**
	 * Check if debug info should be shown.
	 *
	 * @param mixed $gallery_data Gallery data to check.
	 * @return bool True if should show debug.
	 */
	private function should_show_debug( $gallery_data ): bool {
		return current_user_can( 'manage_options' ) && ( empty( $gallery_data ) || ! is_array( $gallery_data ) );
	}

	/**
	 * Build debug info message.
	 *
	 * @param string $field_id Field ID.
	 * @param int    $post_id Post ID.
	 * @param mixed  $gallery_data Gallery data.
	 * @return string Debug info HTML.
	 */
	private function build_debug_info( string $field_id, int $post_id, $gallery_data ): string {
		return sprintf(
			'<strong>VN Gallery Debug Info:</strong><br>Field ID: <code>%s</code><br>Post ID: <code>%s</code><br>Data Type: <code>%s</code><br>Is Array: <code>%s</code><br>Is Empty: <code>%s</code><br>Count: <code>%s</code><hr>Hint: Access <code>?vn_gallery_debug=1</code> for full debug.',
			$field_id,
			$post_id,
			gettype( $gallery_data ),
			is_array( $gallery_data ) ? 'Yes' : 'No',
			empty( $gallery_data ) ? 'Yes' : 'No',
			is_array( $gallery_data ) ? count( $gallery_data ) : 'N/A'
		);
	}

	/**
	 * Validate gallery data.
	 *
	 * @param mixed $gallery_data Gallery data to validate.
	 * @return bool True if valid.
	 */
	private function is_valid_gallery_data( $gallery_data ): bool {
		return ! empty( $gallery_data ) && is_array( $gallery_data );
	}

	/**
	 * Log gallery data for debugging.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $field_id Field ID.
	 * @param array  $gallery_data Gallery data.
	 */
	private function log_gallery_data( int $post_id, string $field_id, array $gallery_data ): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$item_count = count( $gallery_data );
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( sprintf( 'VN Gallery Debug - Post ID: %d | Field: %s | Item Count: %d', $post_id, $field_id, $item_count ) );

		if ( $item_count > 0 ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log, WordPress.PHP.DevelopmentFunctions.error_log_print_r
			error_log( 'First Item Structure: ' . print_r( $gallery_data[0], true ) );
		}
	}

	/**
	 * Extract image URL from MetaBox data structure.
	 *
	 * MetaBox Builder returns:
	 * - Single image/file: ['0' => 'attachment_id_as_string']
	 * - Multiple images: ['0' => 'id1', '1' => 'id2', ...]
	 * - Or direct attachment ID
	 *
	 * @param mixed  $image_data Image data from MetaBox.
	 * @param string $size Image size to retrieve (full, large, medium, thumbnail).
	 * @return string Image URL or empty string.
	 */
	private function extract_image_url( $image_data, string $size = 'full' ): string {
		// Handle array with attachment IDs (MetaBox Builder format).
		if ( is_array( $image_data ) && isset( $image_data[0] ) ) {
			// Get first attachment ID.
			$attachment_id = $image_data[0];

			// MetaBox returns ID as string, convert to int.
			if ( is_numeric( $attachment_id ) ) {
				$attachment_id = (int) $attachment_id;
				$image_url     = wp_get_attachment_image_url( $attachment_id, $size );
				if ( $image_url ) {
					return $image_url;
				}
			}
		}

		// Handle direct attachment ID.
		if ( is_numeric( $image_data ) ) {
			$image_url = wp_get_attachment_image_url( (int) $image_data, $size );
			if ( $image_url ) {
				return $image_url;
			}
		}

		// Handle direct URL string (fallback).
		if ( is_string( $image_data ) && filter_var( $image_data, FILTER_VALIDATE_URL ) ) {
			return $image_data;
		}

		return '';
	}

	/**
	 * Get video thumbnail from YouTube or Vimeo URL.
	 *
	 * @param string $video_url Video URL.
	 * @return string Thumbnail URL or empty string.
	 */
	private function get_video_thumbnail( string $video_url ): string {
		// YouTube.
		if ( preg_match( '/(?:youtube\\.com\\/watch\\?v=|youtu\\.be\\/)([a-zA-Z0-9_-]+)/', $video_url, $matches ) ) {
			return 'https://img.youtube.com/vi/' . $matches[1] . '/hqdefault.jpg';
		}

		// Vimeo.
		if ( preg_match( '/vimeo\\.com\\/(\\d+)/', $video_url, $matches ) ) {
			$vimeo_data = wp_remote_get( 'https://vimeo.com/api/v2/video/' . $matches[1] . '.json' );
			if ( ! is_wp_error( $vimeo_data ) ) {
				$vimeo_data = json_decode( wp_remote_retrieve_body( $vimeo_data ), true );
				if ( isset( $vimeo_data[0]['thumbnail_large'] ) ) {
					return $vimeo_data[0]['thumbnail_large'];
				}
			}
		}

		return '';
	}

	/**
	 * Render a single gallery item.
	 *
	 * @param array $item Gallery item data from MetaBox.
	 * @param bool  $show_title Whether to show title below item.
	 */
	private function render_item( $item, bool $show_title = false ): void {
		if ( ! is_array( $item ) ) {
			return;
		}

		$item_data = $this->parse_item_data( $item );

		if ( ! $this->is_valid_item_data( $item_data ) ) {
			$this->log_skipped_item( $item_data );
			return;
		}

		$this->output_item_html( $item_data, $show_title );
	}

	/**
	 * Parse item data from MetaBox structure.
	 *
	 * @param array $item Raw item data.
	 * @return array Parsed item data with href, thumbnail, type, title, desc.
	 */
	private function parse_item_data( array $item ): array {
		$item_type = $item[ self::FIELD_ITEM_TYPE ] ?? 'image';
		$is_video  = ( 'video' === $item_type );
		$data_type = $is_video ? 'video' : 'image';

		// Extract URLs based on type.
		if ( $is_video ) {
			$video_url     = $item[ self::FIELD_ITEM_VIDEO_URL ] ?? '';
			$video_thumb   = $item[ self::FIELD_ITEM_THUMBNAIL ] ?? array();
			$href          = ! empty( $video_url ) ? esc_url( $video_url ) : '';
			$thumbnail_url = $this->get_video_thumbnail_url( $video_url, $video_thumb );
		} else {
			$item_image    = $item[ self::FIELD_ITEM_IMAGE ] ?? array();
			$href          = $this->extract_image_url( $item_image, 'full' );
			$thumbnail_url = $this->extract_image_url( $item_image, 'large' );
		}

		return array(
			'href'      => $href,
			'thumbnail' => $thumbnail_url,
			'type'      => $data_type,
			'title'     => $item[ self::FIELD_ITEM_TITLE ] ?? '',
			'desc'      => $item[ self::FIELD_ITEM_DESCRIPTION ] ?? '',
			'raw_data'  => $item[ self::FIELD_ITEM_IMAGE ] ?? array(),
		);
	}

	/**
	 * Get video thumbnail URL with fallback logic.
	 *
	 * @param string $video_url Video URL.
	 * @param mixed  $custom_thumbnail Custom thumbnail data.
	 * @return string Thumbnail URL.
	 */
	private function get_video_thumbnail_url( string $video_url, $custom_thumbnail ): string {
		// Try custom thumbnail first.
		if ( ! empty( $custom_thumbnail ) ) {
			$thumbnail = $this->extract_image_url( $custom_thumbnail, 'large' );
			if ( $thumbnail ) {
				return $thumbnail;
			}
		}

		// Fallback to auto-generated thumbnail.
		if ( ! empty( $video_url ) ) {
			return $this->get_video_thumbnail( $video_url );
		}

		return '';
	}

	/**
	 * Validate parsed item data.
	 *
	 * @param array $item_data Parsed item data.
	 * @return bool True if valid.
	 */
	private function is_valid_item_data( array $item_data ): bool {
		return ! empty( $item_data['href'] ) && ! empty( $item_data['thumbnail'] );
	}

	/**
	 * Log skipped item for debugging.
	 *
	 * @param array $item_data Parsed item data.
	 */
	private function log_skipped_item( array $item_data ): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log, WordPress.PHP.DevelopmentFunctions.error_log_print_r
		error_log(
			sprintf(
				'VN Gallery Item Skip - Type: %s | HREF: %s | Thumbnail: %s | Image Data: %s',
				$item_data['type'] ?? 'unknown',
				empty( $item_data['href'] ) ? 'EMPTY' : 'OK',
				empty( $item_data['thumbnail'] ) ? 'EMPTY' : 'OK',
				print_r( $item_data['raw_data'] ?? array(), true )
			)
		);
	}

	/**
	 * Output item HTML markup.
	 *
	 * @param array $item_data Parsed and validated item data.
	 * @param bool  $show_title Whether to show title below item.
	 */
	private function output_item_html( array $item_data, bool $show_title = false ): void {
		$classes = array(
			'vn-gallery-item',
			'vn-item-' . $item_data['type'],
			'border-image',
		);

		// Open wrapper.
		echo '<div class="gallery-item-wrapper">';

		// Output link and image.
		printf(
			'<a href="%s" class="%s" data-type="%s" data-title="%s" data-description="%s"><div class="image-inner"><img src="%s" alt="%s" loading="lazy" /><div class="vn-youtube-play-button">
					<button class="btn-icon circle is-xlarge"><i class="icon-play" aria-hidden="true"></i></button></div></div></a>',
			esc_url( $item_data['href'] ),
			esc_attr( implode( ' ', $classes ) ),
			esc_attr( $item_data['type'] ),
			esc_attr( $item_data['title'] ),
			esc_attr( $item_data['desc'] ),
			esc_url( $item_data['thumbnail'] ),
			esc_attr( $item_data['title'] )
		);

		// Output title if enabled.
		if ( $show_title && ! empty( $item_data['title'] ) ) {
			printf(
				'<h5 class="gallery-item-title">%s</h5>',
				esc_html( $item_data['title'] )
			);
		}

		// Close wrapper.
		echo '</div>';
	}

	/**
	 * Render error message (only visible to admins).
	 *
	 * @param string $message Error message.
	 * @param bool   $public_facing Whether error should be public facing.
	 * @return string Error HTML or empty string.
	 */
	private function render_error( string $message, bool $public_facing = true ): string {
		if ( current_user_can( 'manage_options' ) ) {
			return sprintf(
				'<div class="vn-gallery-error" style="color: #d63638; border: 1px solid #d63638; padding: 10px; background: #fff; margin: 10px 0;">%s</div>',
				wp_kses_post( $message )
			);
		}

		return $public_facing ? '' : '<!-- ' . esc_html( $message ) . ' -->';
	}

	/**
	 * Check if currently in UX Builder context.
	 *
	 * @return bool True if UX Builder is active.
	 */
	private function is_ux_builder_context(): bool {
		// Check for UX Builder AJAX request.
		if ( defined( 'UX_BUILDER_DOING_AJAX' ) && UX_BUILDER_DOING_AJAX ) {
			return true;
		}

		// Check for UX Builder iframe.
		if ( function_exists( 'ux_builder_is_active' ) && ux_builder_is_active() ) {
			return true;
		}

		return false;
	}

	/**
	 * Render placeholder preview for UX Builder when no gallery is selected.
	 *
	 * Displays a visual grid placeholder with sample items to show layout.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string Placeholder HTML.
	 */
	private function render_ux_builder_placeholder( array $atts ): string {
		// Parse layout for grid classes.
		$layout_config = $this->parse_layout_attributes( $atts );
		$grid_classes  = $this->build_grid_classes( $layout_config );

		// Signal assets need to be loaded.
		VN_Assets::enqueue_scripts();

		ob_start();
		?>
		<div class="vn-gallery-wrapper vn-gallery-placeholder">
			<div class="vn-gallery-placeholder-notice" style="padding: 12px 16px; background: #f0f6fc; border: 1px solid #c3c4c7; border-radius: 4px; margin-bottom: 16px;">
				<span style="display: flex; align-items: center; gap: 8px; color: #1d2327;">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z" fill="#2271b1"/>
					</svg>
					<?php esc_html_e( 'VN Gallery: Chọn một gallery từ panel bên trái để hiển thị', 'vn-lightbox-gallery' ); ?>
				</span>
			</div>
			<div class="<?php echo esc_attr( implode( ' ', $grid_classes ) ); ?>">
				<?php
				// Render placeholder items to show grid layout.
				$placeholder_count = absint( $layout_config['columns'] );
				for ( $i = 0; $i < $placeholder_count; $i++ ) :
					?>
					<div class="col">
						<div class="vn-gallery-item vn-gallery-item-placeholder" style="aspect-ratio: 4/3; background: linear-gradient(135deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); display: flex; align-items: center; justify-content: center; border-radius: 4px;">
							<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="opacity: 0.3;">
								<path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z" fill="#666"/>
							</svg>
						</div>
					</div>
				<?php endfor; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
