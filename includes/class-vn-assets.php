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
	 * Script & style handle.
	 */
	const HANDLE = 'vn-lightbox-gallery';

	/**
	 * Instance of this class.
	 *
	 * @var VN_Assets
	 */
	private static $instance = null;

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
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ), 5 );
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_in_head' ), 20 );
	}

	/**
	 * Register CSS and JavaScript assets.
	 */
	public function register_assets(): void {
		wp_register_style(
			self::HANDLE,
			VN_LIGHTBOX_GALLERY_PLUGIN_URL . 'assets/css/frontend-style.css',
			array(),
			VN_LIGHTBOX_GALLERY_VERSION
		);

		wp_register_script(
			self::HANDLE,
			VN_LIGHTBOX_GALLERY_PLUGIN_URL . 'assets/js/frontend-main.js',
			array( 'jquery' ),
			VN_LIGHTBOX_GALLERY_VERSION,
			true
		);
	}

	/**
	 * Enqueue in <head> when the gallery is known ahead of rendering,
	 * so the stylesheet is not printed late in the footer (avoids FOUC).
	 */
	public function maybe_enqueue_in_head(): void {
		if ( VN_Lightbox_Gallery_Element::is_ux_builder()
			|| ( is_singular() && has_shortcode( (string) get_post_field( 'post_content', get_queried_object_id() ), 'vn_gallery' ) )
		) {
			self::enqueue_scripts();
		}
	}

	/**
	 * Enqueue assets (called from the shortcode; late calls are printed in the footer).
	 */
	public static function enqueue_scripts(): void {
		if ( ! wp_script_is( self::HANDLE, 'registered' ) ) {
			self::get_instance()->register_assets();
		}

		wp_enqueue_style( self::HANDLE );
		wp_enqueue_script( self::HANDLE );
	}
}
