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
	 * DOM ids already rendered on this request (same gallery may appear twice on a page).
	 *
	 * @var array<string, int>
	 */
	private $dom_ids = array();

	/**
	 * Post type holding the galleries.
	 */
	const POST_TYPE = 'gallery';

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
	 * Allowed column spacing values (Flatsome row-{spacing} classes).
	 */
	const COL_SPACINGS = array( 'collapse', 'xsmall', 'small', 'normal', 'large' );

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
	 * @param array|string $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_shortcode( $atts ): string {
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

		$field_id   = sanitize_key( $atts['field'] );
		$gallery_id = absint( $atts['gallery_id'] );
		$layout     = $this->parse_layout_attributes( $atts );

		// No gallery selected: placeholder in UX Builder, admin-only error on frontend.
		if ( ! $gallery_id ) {
			return VN_Lightbox_Gallery_Element::is_ux_builder()
				? $this->render_ux_builder_placeholder( $layout )
				: $this->render_error( __( 'Lỗi VN Gallery: Vui lòng chọn gallery cần hiển thị.', 'vn-lightbox-gallery' ) );
		}

		$gallery = get_post( $gallery_id );
		if ( ! $gallery || self::POST_TYPE !== $gallery->post_type ) {
			return $this->render_error(
				sprintf(
					/* translators: %d: Post ID */
					__( 'Lỗi VN Gallery: Post ID %d không phải là gallery post type.', 'vn-lightbox-gallery' ),
					$gallery_id
				)
			);
		}

		// Never leak draft/private/password-protected galleries to visitors who cannot read them.
		if ( ( 'publish' !== $gallery->post_status && ! current_user_can( 'read_post', $gallery_id ) )
			|| post_password_required( $gallery )
		) {
			return '';
		}

		if ( ! function_exists( 'rwmb_get_value' ) ) {
			return $this->render_error( __( 'Lỗi VN Gallery: MetaBox.io không được kích hoạt.', 'vn-lightbox-gallery' ) );
		}

		$gallery_data = rwmb_get_value( $field_id, array( 'object_id' => $gallery_id ), $gallery_id );

		if ( empty( $gallery_data ) || ! is_array( $gallery_data ) ) {
			return $this->render_error(
				sprintf(
					/* translators: 1: Field ID, 2: Post ID, 3: PHP data type */
					__( 'Lỗi VN Gallery: Không tìm thấy dữ liệu cho trường "%1$s" (Post ID: %2$d, kiểu dữ liệu: %3$s).', 'vn-lightbox-gallery' ),
					$field_id,
					$gallery_id,
					gettype( $gallery_data )
				)
			);
		}

		VN_Assets::enqueue_scripts();

		$wrapper_classes = array_merge( array( 'vn-gallery-wrapper' ), $this->sanitize_classes( (string) $atts['class'] ) );
		$show_title      = rest_sanitize_boolean( $atts['show_title'] );
		$img_sizes       = $this->build_img_sizes( $layout );

		ob_start();

		printf( '<div class="%s">', esc_attr( implode( ' ', $wrapper_classes ) ) );

		if ( rest_sanitize_boolean( $atts['filters'] ) ) {
			$this->render_filters();
		}

		printf(
			'<div class="%s" id="%s">',
			esc_attr( implode( ' ', $this->build_grid_classes( $layout ) ) ),
			esc_attr( $this->unique_dom_id( 'vn-gallery-' . $gallery_id . '-' . $field_id ) )
		);

		foreach ( $gallery_data as $item ) {
			if ( is_array( $item ) ) {
				$this->render_item( $item, $show_title, $img_sizes );
			}
		}

		echo '</div></div>'; // .vn-gallery-grid, .vn-gallery-wrapper.

		return (string) ob_get_clean();
	}

	/**
	 * Parse layout attributes from shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return array Parsed layout configuration.
	 */
	private function parse_layout_attributes( array $atts ): array {
		$columns    = absint( $atts['columns'] ) ?: 4;
		$columns_md = absint( $atts['columns__md'] ) ?: min( $columns, 3 ); // Flatsome fallback.
		$columns_sm = absint( $atts['columns__sm'] ) ?: min( $columns, 2 ); // Flatsome fallback.

		return array(
			'columns'     => $columns,
			'columns__md' => $columns_md,
			'columns__sm' => $columns_sm,
			'col_spacing' => in_array( $atts['col_spacing'], self::COL_SPACINGS, true ) ? $atts['col_spacing'] : 'normal',
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

		if ( 'normal' !== $config['col_spacing'] ) {
			$classes[] = 'row-' . $config['col_spacing'];
		}

		$classes[] = 'large-columns-' . $config['columns'];
		$classes[] = 'medium-columns-' . $config['columns__md'];
		$classes[] = 'small-columns-' . $config['columns__sm'];

		return $classes;
	}

	/**
	 * Build the <img sizes> attribute so browsers pick the smallest srcset candidate for each breakpoint.
	 *
	 * @param array $config Layout configuration.
	 * @return string
	 */
	private function build_img_sizes( array $config ): string {
		return sprintf(
			'(max-width: 549px) %dvw, (max-width: 849px) %dvw, %dvw',
			(int) ceil( 100 / $config['columns__sm'] ),
			(int) ceil( 100 / $config['columns__md'] ),
			(int) ceil( 100 / $config['columns'] )
		);
	}

	/**
	 * Sanitize a space separated list of CSS classes.
	 *
	 * @param string $classes Raw classes.
	 * @return string[]
	 */
	private function sanitize_classes( string $classes ): array {
		$list = preg_split( '/\s+/', $classes, -1, PREG_SPLIT_NO_EMPTY );

		return array_values( array_filter( array_map( 'sanitize_html_class', $list ? $list : array() ) ) );
	}

	/**
	 * Return a DOM id that is unique within the current request.
	 *
	 * @param string $id Base id.
	 * @return string
	 */
	private function unique_dom_id( string $id ): string {
		$count                = $this->dom_ids[ $id ] ?? 0;
		$this->dom_ids[ $id ] = $count + 1;

		return $count ? $id . '-' . ( $count + 1 ) : $id;
	}

	/**
	 * Render filter buttons.
	 */
	private function render_filters(): void {
		$filters = array(
			'*'              => __( 'Tất cả', 'vn-lightbox-gallery' ),
			'.vn-item-image' => __( 'Hình ảnh', 'vn-lightbox-gallery' ),
			'.vn-item-video' => __( 'Video', 'vn-lightbox-gallery' ),
		);

		echo '<div class="vn-gallery-filters">';
		foreach ( $filters as $filter => $label ) {
			$is_active = '*' === $filter;
			printf(
				'<button type="button" class="vn-filter-btn%s" data-filter="%s" aria-pressed="%s">%s</button>',
				$is_active ? ' active' : '',
				esc_attr( $filter ),
				$is_active ? 'true' : 'false',
				esc_html( $label )
			);
		}
		echo '</div>';
	}

	/**
	 * Render a single gallery item.
	 *
	 * @param array  $item       Gallery item data from MetaBox.
	 * @param bool   $show_title Whether to show title below item.
	 * @param string $img_sizes  <img sizes> attribute value.
	 */
	private function render_item( array $item, bool $show_title, string $img_sizes ): void {
		$is_video    = 'video' === ( $item[ self::FIELD_ITEM_TYPE ] ?? 'image' );
		$type        = $is_video ? 'video' : 'image';
		$title       = (string) ( $item[ self::FIELD_ITEM_TITLE ] ?? '' );
		$description = (string) ( $item[ self::FIELD_ITEM_DESCRIPTION ] ?? '' );
		$img_attr    = array(
			'alt'      => $title,
			'loading'  => 'lazy',
			'decoding' => 'async',
			'sizes'    => $img_sizes,
		);

		if ( $is_video ) {
			$href      = esc_url_raw( (string) ( $item[ self::FIELD_ITEM_VIDEO_URL ] ?? '' ) );
			$thumb_id  = $this->get_attachment_id( $item[ self::FIELD_ITEM_THUMBNAIL ] ?? null );
			$thumbnail = $thumb_id ? wp_get_attachment_image( $thumb_id, 'large', false, $img_attr ) : '';

			// Fallback to the platform thumbnail (YouTube/Vimeo).
			$thumb_url = ( ! $thumbnail && $href ) ? $this->get_video_thumbnail( $href ) : '';
			if ( $thumb_url ) {
				$thumbnail = sprintf(
					'<img src="%s" alt="%s" loading="lazy" decoding="async" />',
					esc_url( $thumb_url ),
					esc_attr( $title )
				);
			}
		} else {
			$image_id  = $this->get_attachment_id( $item[ self::FIELD_ITEM_IMAGE ] ?? null );
			$href      = $image_id ? (string) wp_get_attachment_image_url( $image_id, 'full' ) : '';
			$thumbnail = $image_id ? wp_get_attachment_image( $image_id, 'large', false, $img_attr ) : '';
		}

		if ( ! $href || ! $thumbnail ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( sprintf( 'VN Gallery: skipped %s item "%s" (missing %s).', $type, $title, $href ? 'thumbnail' : 'URL' ) );
			}
			return;
		}

		$play_button = $is_video
			? '<div class="vn-youtube-play-button"><span class="btn-icon circle is-xlarge"><i class="icon-play" aria-hidden="true"></i></span></div>'
			: '';

		printf(
			'<div class="gallery-item-wrapper"><a href="%1$s" class="vn-gallery-item vn-item-%2$s border-image" data-type="%2$s" data-description="%3$s"><div class="image-inner">%4$s%5$s</div></a>%6$s</div>',
			esc_url( $href ),
			esc_attr( $type ),
			esc_attr( $description ),
			$thumbnail, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built by wp_get_attachment_image() or escaped above.
			$play_button, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static markup.
			( $show_title && '' !== $title ) ? '<h5 class="gallery-item-title">' . esc_html( $title ) . '</h5>' : '' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped inline.
		);
	}

	/**
	 * Extract the attachment ID from a MetaBox image field value.
	 *
	 * MetaBox returns image_advanced values as ['0' => 'id'] (raw) or [ ['ID' => id, ...] ] (formatted).
	 *
	 * @param mixed $image_data Image data from MetaBox.
	 * @return int Attachment ID or 0.
	 */
	private function get_attachment_id( $image_data ): int {
		if ( is_array( $image_data ) ) {
			$image_data = reset( $image_data );
		}
		if ( is_array( $image_data ) ) {
			$image_data = $image_data['ID'] ?? 0;
		}

		return is_numeric( $image_data ) ? absint( $image_data ) : 0;
	}

	/**
	 * Get video thumbnail from YouTube or Vimeo URL.
	 *
	 * @param string $video_url Video URL.
	 * @return string Thumbnail URL or empty string.
	 */
	private function get_video_thumbnail( string $video_url ): string {
		if ( preg_match( '~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/)|youtu\.be/)([\w-]{11})~i', $video_url, $matches ) ) {
			return 'https://img.youtube.com/vi/' . $matches[1] . '/hqdefault.jpg';
		}

		if ( preg_match( '~vimeo\.com/(?:video/)?(\d+)~i', $video_url, $matches ) ) {
			return $this->get_vimeo_thumbnail( $matches[1] );
		}

		return '';
	}

	/**
	 * Get (cached) Vimeo thumbnail via oEmbed.
	 *
	 * Cached in a transient so a remote HTTP request is not made on every page view.
	 *
	 * @param string $video_id Numeric Vimeo video ID.
	 * @return string Thumbnail URL or empty string.
	 */
	private function get_vimeo_thumbnail( string $video_id ): string {
		$cache_key = 'vn_gallery_vimeo_' . $video_id;
		$cached    = get_transient( $cache_key );
		if ( false !== $cached ) {
			return (string) $cached;
		}

		$thumbnail = '';
		$response  = wp_remote_get(
			'https://vimeo.com/api/oembed.json?width=1280&url=' . rawurlencode( 'https://vimeo.com/' . $video_id ),
			array( 'timeout' => 3 )
		);

		if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
			$data      = json_decode( wp_remote_retrieve_body( $response ), true );
			$thumbnail = is_array( $data ) && is_string( $data['thumbnail_url'] ?? null ) ? esc_url_raw( $data['thumbnail_url'] ) : '';
		}

		// Cache failures for a shorter time so a slow/down API does not block every page load.
		set_transient( $cache_key, $thumbnail, $thumbnail ? WEEK_IN_SECONDS : HOUR_IN_SECONDS );

		return $thumbnail;
	}

	/**
	 * Render error message (only visible to admins, empty for everyone else).
	 *
	 * @param string $message Error message.
	 * @return string Error HTML or empty string.
	 */
	private function render_error( string $message ): string {
		if ( ! current_user_can( 'manage_options' ) ) {
			return '';
		}

		VN_Assets::enqueue_scripts(); // .vn-gallery-error styles.

		return sprintf( '<div class="vn-gallery-error">%s</div>', esc_html( $message ) );
	}

	/**
	 * Render placeholder preview for UX Builder when no gallery is selected.
	 *
	 * Displays a visual grid placeholder with sample items to show layout.
	 *
	 * @param array $layout Parsed layout configuration.
	 * @return string Placeholder HTML.
	 */
	private function render_ux_builder_placeholder( array $layout ): string {
		VN_Assets::enqueue_scripts();

		ob_start();
		?>
		<div class="vn-gallery-wrapper vn-gallery-placeholder">
			<div class="vn-gallery-placeholder-notice">
				<svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z" fill="#2271b1"/></svg>
				<?php esc_html_e( 'VN Gallery: Chọn một gallery từ panel bên trái để hiển thị', 'vn-lightbox-gallery' ); ?>
			</div>
			<div class="<?php echo esc_attr( implode( ' ', $this->build_grid_classes( $layout ) ) ); ?>">
				<?php for ( $i = 0; $i < $layout['columns']; $i++ ) : ?>
					<div class="col">
						<div class="vn-gallery-item vn-gallery-item-placeholder">
							<svg width="48" height="48" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z" fill="#666"/></svg>
						</div>
					</div>
				<?php endfor; ?>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}
}
