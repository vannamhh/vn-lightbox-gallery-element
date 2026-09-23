<?php
/**
 * Plugin Name: VN Lightbox Gallery Element
 * Plugin URI: https://wpmasterynow.com/
 * Description: Custom Flatsome UX Builder element để hiển thị gallery với lightbox từ dữ liệu MetaBox
 * Version: 4.6.0
 * Author: VN
 * Author URI: https://wpmasterynow.com/
 * Text Domain: vn-lightbox-gallery
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 *
 * @package VN_Lightbox_Gallery
 */

declare(strict_types=1);

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'VN_LIGHTBOX_GALLERY_VERSION', '4.6.0' );
define( 'VN_LIGHTBOX_GALLERY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'VN_LIGHTBOX_GALLERY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'VN_LIGHTBOX_GALLERY_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main Plugin Class
 */
class VN_Lightbox_Gallery_Element {

	/**
	 * Instance of this class.
	 *
	 * @var VN_Lightbox_Gallery_Element
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return VN_Lightbox_Gallery_Element
	 */
	public static function get_instance(): VN_Lightbox_Gallery_Element {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->load_dependencies();

		VN_UX_Builder::get_instance();
		VN_Shortcode::get_instance();
		VN_Assets::get_instance();

		// Translations must be loaded on `init` or later (WP 6.7+).
		add_action( 'init', array( $this, 'load_textdomain' ) );
	}

	/**
	 * Load required dependencies.
	 */
	private function load_dependencies(): void {
		require_once VN_LIGHTBOX_GALLERY_PLUGIN_DIR . 'includes/class-vn-ux-builder.php';
		require_once VN_LIGHTBOX_GALLERY_PLUGIN_DIR . 'includes/class-vn-shortcode.php';
		require_once VN_LIGHTBOX_GALLERY_PLUGIN_DIR . 'includes/class-vn-assets.php';

		// Debug helper (?vn_gallery_debug=1 for admins) is only available on development sites.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			require_once VN_LIGHTBOX_GALLERY_PLUGIN_DIR . 'debug.php';
		}
	}

	/**
	 * Check if the current request comes from Flatsome UX Builder (iframe, editor or AJAX preview).
	 *
	 * @return bool
	 */
	public static function is_ux_builder(): bool {
		if ( defined( 'UX_BUILDER_DOING_AJAX' ) && UX_BUILDER_DOING_AJAX ) {
			return true;
		}

		return function_exists( 'ux_builder_is_active' ) && ux_builder_is_active();
	}

	/**
	 * Load plugin text domain for translations.
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'vn-lightbox-gallery',
			false,
			dirname( VN_LIGHTBOX_GALLERY_PLUGIN_BASENAME ) . '/languages'
		);
	}
}

/**
 * Initialize the plugin
 */
function vn_lightbox_gallery_init() {
	return VN_Lightbox_Gallery_Element::get_instance();
}

// Start the plugin.
add_action( 'plugins_loaded', 'vn_lightbox_gallery_init' );
