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
		// Register assets early.
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ), 5 );
		
		// Conditionally enqueue assets.
		add_action( 'wp_footer', array( $this, 'conditional_enqueue' ), 5 );

		// Always load assets in UX Builder context for live preview.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_for_ux_builder' ), 100 );
		
		// Also hook into init for UX Builder AJAX requests.
		add_action( 'init', array( $this, 'maybe_enqueue_for_ajax' ) );
	}

	/**
	 * Signal that scripts should be enqueued (called from shortcode).
	 */
	public static function enqueue_scripts(): void {
		self::$should_enqueue = true;
		
		// In UX Builder AJAX context, enqueue immediately.
		if ( defined( 'UX_BUILDER_DOING_AJAX' ) && UX_BUILDER_DOING_AJAX ) {
			wp_enqueue_style( 'vn-lightbox-gallery' );
			wp_enqueue_script( 'vn-lightbox-gallery' );
		}
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
	 * Maybe enqueue assets for UX Builder AJAX request.
	 */
	public function maybe_enqueue_for_ajax(): void {
		if ( defined( 'UX_BUILDER_DOING_AJAX' ) && UX_BUILDER_DOING_AJAX ) {
			$this->register_assets();
			wp_enqueue_style( 'vn-lightbox-gallery' );
			wp_enqueue_script( 'vn-lightbox-gallery' );
		}
	}

	/**
	 * Enqueue assets when in UX Builder context.
	 */
	public function enqueue_for_ux_builder(): void {
		if ( $this->is_ux_builder_context() ) {
			wp_enqueue_style( 'vn-lightbox-gallery' );
			wp_enqueue_script( 'vn-lightbox-gallery' );
		}
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
