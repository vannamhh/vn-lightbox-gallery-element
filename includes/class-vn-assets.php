<?php
declare(strict_types=1);

/**
 * Assets Manager Class
 *
 * Handles conditional loading of CSS and JavaScript assets.
 *
 * @package VN_Lightbox_Gallery
 * @since 4.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class VN_Assets
 *
 * Manages plugin assets with conditional loading.
 */
class VN_Assets {

	/**
	 * Instance of this class.
	 *
	 * @var VN_Assets
	 */
	private static $instance = null;

	/**
	 * Flag to track if scripts should be enqueued.
	 *
	 * @var bool
	 */
	private static $should_enqueue = false;

	/**
	 * Get the singleton instance.
	 *
	 * @return VN_Assets
	 */
	public static function get_instance(): VN_Assets {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		// Register assets.
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		
		// Conditionally enqueue assets.
		add_action( 'wp_footer', array( $this, 'conditional_enqueue' ), 5 );
	}

	/**
	 * Signal that scripts should be enqueued (called from shortcode).
	 */
	public static function enqueue_scripts(): void {
		self::$should_enqueue = true;
	}

	/**
	 * Register CSS and JavaScript assets.
	 */
	public function register_assets(): void {
		// Register CSS.
		wp_register_style(
			'vn-lightbox-gallery',
			VN_LIGHTBOX_GALLERY_PLUGIN_URL . 'assets/css/frontend-style.css',
			array(),
			VN_LIGHTBOX_GALLERY_VERSION,
			'all'
		);

		// Register JavaScript.
		wp_register_script(
			'vn-lightbox-gallery',
			VN_LIGHTBOX_GALLERY_PLUGIN_URL . 'assets/js/frontend-main.js',
			array( 'jquery' ),
			VN_LIGHTBOX_GALLERY_VERSION,
			true
		);
	}

	/**
	 * Conditionally enqueue assets if shortcode was used.
	 */
	public function conditional_enqueue(): void {
		if ( self::$should_enqueue ) {
			wp_enqueue_style( 'vn-lightbox-gallery' );
			wp_enqueue_script( 'vn-lightbox-gallery' );
		}
	}
}
