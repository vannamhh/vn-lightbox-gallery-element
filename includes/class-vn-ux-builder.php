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
					'post_id' => array(
						'type'        => 'textfield',
						'heading'     => __( 'Post ID (Tùy chọn)', 'vn-lightbox-gallery' ),
						'description' => __( 'Bỏ trống để lấy trang hiện tại', 'vn-lightbox-gallery' ),
						'default'     => '',
					),
					'filters' => array(
						'type'    => 'checkbox',
						'heading' => __( 'Hiển thị Nút Lọc', 'vn-lightbox-gallery' ),
						'default' => 'true',
					),
					'show_title' => array(
						'type'    => 'checkbox',
						'heading' => __( 'Hiển thị Tiêu đề', 'vn-lightbox-gallery' ),
						'default' => 'false',
					),
					'class'   => array(
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
		return '[vn_gallery post_id="{{post_id}}" filters="{{filters}}" show_title="{{show_title}}" class="{{class}}"]';
	}
}
