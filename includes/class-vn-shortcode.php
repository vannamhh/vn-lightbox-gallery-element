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
		// Parse attributes.
		$atts = shortcode_atts(
			array(
				'field'   => self::METABOX_FIELD_ID,
				'post_id' => 0,
				'filters' => 'true',
			),
			$atts,
			'vn_gallery'
		);

		$field_id     = sanitize_text_field( $atts['field'] );
		$post_id      = ( $atts['post_id'] > 0 ) ? intval( $atts['post_id'] ) : get_the_ID();
		$show_filters = filter_var( $atts['filters'], FILTER_VALIDATE_BOOLEAN );

		// Check if MetaBox is available.
		if ( ! function_exists( 'rwmb_get_value' ) ) {
			return $this->render_error( __( 'Lỗi VN Gallery: MetaBox.io không được kích hoạt.', 'vn-lightbox-gallery' ) );
		}

		// Get gallery data from MetaBox.
		$gallery_data = rwmb_get_value( $field_id, array( 'object_id' => $post_id ) );

		// Debug for admins: Always show data info if user is admin.
		if ( current_user_can( 'manage_options' ) && ( empty( $gallery_data ) || ! is_array( $gallery_data ) ) ) {
			$debug_info = sprintf(
				'<strong>VN Gallery Debug Info:</strong><br>Field ID: <code>%s</code><br>Post ID: <code>%s</code><br>Data Type: <code>%s</code><br>Is Array: <code>%s</code><br>Is Empty: <code>%s</code><br>Count: <code>%s</code><hr>Hint: Access <code>?vn_gallery_debug=1</code> for full debug.',
				$field_id,
				$post_id,
				gettype( $gallery_data ),
				is_array( $gallery_data ) ? 'Yes' : 'No',
				empty( $gallery_data ) ? 'Yes' : 'No',
				is_array( $gallery_data ) ? count( $gallery_data ) : 'N/A'
			);
			return $this->render_error( $debug_info );
		}

		// Validate gallery data.
		if ( empty( $gallery_data ) || ! is_array( $gallery_data ) ) {
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

		echo '<div class="vn-gallery-wrapper">';

		// Render filter buttons if enabled.
		if ( $show_filters ) {
			$this->render_filters();
		}

		// Render gallery grid.
		$gallery_id = 'vn-gallery-' . esc_attr( $post_id . '-' . $field_id );
		echo '<div class="vn-gallery-grid" id="' . esc_attr( $gallery_id ) . '">';

		// Debug: Log gallery data count for admins.
		if ( current_user_can( 'manage_options' ) ) {
			$item_count = is_array( $gallery_data ) ? count( $gallery_data ) : 0;
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'VN Gallery Debug - Post ID: ' . $post_id . ' | Field: ' . $field_id . ' | Item Count: ' . $item_count );
			if ( $item_count > 0 ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log, WordPress.PHP.DevelopmentFunctions.error_log_print_r
				error_log( 'First Item Structure: ' . print_r( $gallery_data[0], true ) );
			}
		}

		foreach ( $gallery_data as $item ) {
			$this->render_item( $item );
		}

		echo '</div>'; // .vn-gallery-grid.
		echo '</div>'; // .vn-gallery-wrapper.

		return ob_get_clean();
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
				$image_url = wp_get_attachment_image_url( $attachment_id, $size );
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
	 */
	private function render_item( $item ): void {
		// Validate item data structure.
		if ( ! is_array( $item ) ) {
			return;
		}

		// Get item data using constants.
		$item_type     = $item[ self::FIELD_ITEM_TYPE ] ?? 'image';
		$item_image    = $item[ self::FIELD_ITEM_IMAGE ] ?? array();
		$video_url     = $item[ self::FIELD_ITEM_VIDEO_URL ] ?? '';
		$video_thumb   = $item[ self::FIELD_ITEM_THUMBNAIL ] ?? array();
		$title         = $item[ self::FIELD_ITEM_TITLE ] ?? '';
		$desc          = $item[ self::FIELD_ITEM_DESCRIPTION ] ?? '';

		// Determine if item is video.
		$is_video  = ( 'video' === $item_type );
		$data_type = $is_video ? 'video' : 'image';

		// Set href and thumbnail based on type.
		if ( $is_video ) {
			// Video: href = video URL, thumbnail = custom thumbnail or video screenshot.
			$href = ! empty( $video_url ) ? esc_url( $video_url ) : '';
			// Use custom thumbnail if provided, otherwise try to extract from video URL.
			$thumbnail_url = ! empty( $video_thumb ) ? $this->extract_image_url( $video_thumb, 'large' ) : '';
			// Fallback: extract video ID and use default thumbnail (YouTube/Vimeo).
			if ( empty( $thumbnail_url ) && ! empty( $video_url ) ) {
				$thumbnail_url = $this->get_video_thumbnail( $video_url );
			}
		} else {
			// Image: both href and thumbnail from same field.
			$href          = $this->extract_image_url( $item_image, 'full' );
			$thumbnail_url = $this->extract_image_url( $item_image, 'large' );
		}

		// Skip if no valid href or thumbnail.
		if ( empty( $href ) || empty( $thumbnail_url ) ) {
			// Debug for admins.
			if ( current_user_can( 'manage_options' ) ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log, WordPress.PHP.DevelopmentFunctions.error_log_print_r
				error_log(
					sprintf(
						'VN Gallery Item Skip - Type: %s | HREF: %s | Thumbnail: %s | Image Data: %s',
						$item_type,
						empty( $href ) ? 'EMPTY' : 'OK',
						empty( $thumbnail_url ) ? 'EMPTY' : 'OK',
						print_r( $item_image, true )
					)
				);
			}
			return;
		}

		// Build CSS classes.
		$classes = array(
			'vn-gallery-item',
			'vn-item-' . $data_type,
		);

		?>
		<a 
			href="<?php echo esc_url( $href ); ?>" 
			class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
			data-type="<?php echo esc_attr( $data_type ); ?>"
			data-title="<?php echo esc_attr( $title ); ?>"
			data-description="<?php echo esc_attr( $desc ); ?>"
		>
			<img src="<?php echo esc_url( $thumbnail_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy" />
		</a>
		<?php
	}

	/**
	 * Render error message (only visible to admins).
	 *
	 * @param string $message Error message.
	 * @param bool   $public_facing Whether error should be public facing.
	 * @return string Error HTML or empty string.
	 */
	private function render_error( string $message, bool $public_facing = true ): string {
		// Only show errors to administrators.
		if ( current_user_can( 'manage_options' ) ) {
			return sprintf(
				'<div class="vn-gallery-error" style="color: #d63638; border: 1px solid #d63638; padding: 10px; background: #fff; margin: 10px 0;">%s</div>',
				esc_html( $message )
			);
		}

		// Return HTML comment for non-admin users.
		return $public_facing ? '' : '<!-- ' . esc_html( $message ) . ' -->';
	}
}
