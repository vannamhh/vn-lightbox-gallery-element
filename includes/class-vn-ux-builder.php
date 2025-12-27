<?php
declare(strict_types=1);

/**
 * UX Builder Integration Class
 *
 * Handles registration of VN Gallery element with Flatsome UX Builder.
 *
 * @package VN_Lightbox_Gallery
 * @since 4.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class VN_UX_Builder
 *
 * Registers custom element with Flatsome UX Builder.
 */
class VN_UX_Builder {

	/**
	 * Instance of this class.
	 *
	 * @var VN_UX_Builder
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return VN_UX_Builder
	 */
	public static function get_instance(): VN_UX_Builder {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		// Hook into UX Builder setup.
		add_action( 'ux_builder_setup', array( $this, 'register_element' ) );
	}

	/**
	 * Register VN Gallery element with UX Builder.
	 */
	public function register_element(): void {
		// Verify UX Builder function exists.
		if ( ! function_exists( 'add_ux_builder_shortcode' ) ) {
			return;
		}

		add_ux_builder_shortcode(
			'vn_gallery',
			array(
				'name'     => __( 'VN Gallery', 'vn-lightbox-gallery' ),
				'category' => __( 'Content' ),
				'icon'     => 'text',
				'template' => $this->get_shortcode_template(),
				'options'  => array(
					'gallery_id' => array(
						'type'        => 'select',
						'heading'     => __( 'Chọn Gallery', 'vn-lightbox-gallery' ),
						'description' => __( 'Chọn gallery bạn muốn hiển thị', 'vn-lightbox-gallery' ),
						'default'     => '',
						'options'     => $this->get_gallery_list(),
					),
					'filters'    => array(
						'type'    => 'checkbox',
						'heading' => __( 'Hiển thị Nút Lọc', 'vn-lightbox-gallery' ),
						'default' => 'true',
					),
					'show_title' => array(
						'type'    => 'checkbox',
						'heading' => __( 'Hiển thị Tiêu đề', 'vn-lightbox-gallery' ),
						'default' => '',
					),
					'class'      => array(
						'type'        => 'textfield',
						'heading'     => __( 'Class', 'vn-lightbox-gallery' ),
						'description' => __( 'Thêm custom CSS class', 'vn-lightbox-gallery' ),
						'default'     => '',
					),
				),
			)
		);
	}

	/**
	 * Get shortcode template with proper attribute mapping.
	 *
	 * This ensures UX Builder passes all attributes to the shortcode.
	 *
	 * @return string Shortcode template.
	 */
	private function get_shortcode_template(): string {
		return '[vn_gallery{{gallery_id ? \' gallery_id="\' + gallery_id + \'"\' : \'\'}}{{filters ? \' filters="\' + filters + \'"\' : \'\'}}{{show_title ? \' show_title="\' + show_title + \'"\' : \'\'}}{{class ? \' class="\' + class + \'"\' : \'\'}}]';
	}

	/**
	 * Get list of galleries for dropdown.
	 *
	 * Fixed post_type='gallery' for optimal performance and UX.
	 *
	 * @return array Galleries list with ID => Title format.
	 */
	private function get_gallery_list(): array {
		$galleries = array(
			'' => __( '-- Gallery hiện tại --', 'vn-lightbox-gallery' ),
		);

		// Query directly from 'gallery' post type - optimized & clean.
		$query = new WP_Query(
			array(
				'post_type'              => 'gallery',
				'posts_per_page'         => 100,
				'orderby'                => 'title',
				'order'                  => 'ASC',
				'post_status'            => 'publish',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				// Use raw title to prevent WordPress from converting -- to em dash (&#8211;).
				$raw_title                 = get_post_field( 'post_title', get_the_ID(), 'raw' );
				$galleries[ get_the_ID() ] = $raw_title . ' (ID: ' . get_the_ID() . ')';
			}
			wp_reset_postdata();
		}

		return $galleries;
	}
}
