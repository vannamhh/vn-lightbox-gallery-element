<?php
/**
 * Debug helper for VN Lightbox Gallery Element.
 *
 * Access: Add ?vn_gallery_debug=1 to any post/page URL to see debug info.
 *
 * @package VN_Lightbox_Gallery
 */

declare(strict_types=1);

add_action( 'template_redirect', 'vn_gallery_debug_output' );

/**
 * Output debug information about gallery data.
 */
function vn_gallery_debug_output(): void {
	if ( ! isset( $_GET['vn_gallery_debug'] ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$post_id = get_the_ID();
	if ( ! $post_id ) {
		wp_die( 'No post ID found. Please access this from a post/page URL.' );
	}

	$field_id = 'vn_gallery_items';
	
	// Try to get data using rwmb_get_value.
	$data = function_exists( 'rwmb_get_value' ) 
		? rwmb_get_value( $field_id, array( 'object_id' => $post_id ) ) 
		: null;

	?>
	<!DOCTYPE html>
	<html>
	<head>
		<meta charset="utf-8">
		<title>VN Gallery Debug</title>
		<style>
			body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 20px; max-width: 1200px; margin: 0 auto; }
			h1 { color: #333; border-bottom: 3px solid #0073aa; padding-bottom: 10px; }
			h2 { color: #555; margin-top: 30px; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
			.info { background: #f0f0f1; padding: 15px; border-left: 4px solid #0073aa; margin: 20px 0; }
			.info p { margin: 8px 0; }
			.info strong { display: inline-block; width: 200px; }
			.error { background: #f8d7da; border-left-color: #dc3545; color: #721c24; }
			.warning { background: #fff3cd; border-left-color: #ffc107; color: #856404; }
			pre { background: #f5f5f5; padding: 15px; border: 1px solid #ddd; overflow: auto; border-radius: 4px; }
		</style>
	</head>
	<body>
		<h1>VN Gallery Debug Info</h1>
		
		<div class="info">
			<p><strong>Post ID:</strong> <?php echo esc_html( $post_id ); ?></p>
			<p><strong>Post Title:</strong> <?php echo esc_html( get_the_title( $post_id ) ); ?></p>
			<p><strong>Field ID:</strong> <?php echo esc_html( $field_id ); ?></p>
			<p><strong>rwmb_get_value():</strong> <?php echo function_exists( 'rwmb_get_value' ) ? '✓ Available' : '✗ Not Available'; ?></p>
		</div>
		
		<?php if ( $data === null && ! function_exists( 'rwmb_get_value' ) ) : ?>
			<div class="info error">
				<p><strong>ERROR:</strong> MetaBox plugin not active or rwmb_get_value() function not available!</p>
			</div>
		<?php endif; ?>
		
		<div class="info">
			<p><strong>Data Type:</strong> <?php echo esc_html( gettype( $data ) ); ?></p>
			<p><strong>Is Empty:</strong> <?php echo empty( $data ) ? 'Yes' : 'No'; ?></p>
			<p><strong>Is Array:</strong> <?php echo is_array( $data ) ? 'Yes' : 'No'; ?></p>
			<p><strong>Item Count:</strong> <?php echo is_array( $data ) ? count( $data ) : 'N/A'; ?></p>
		</div>

		<h2>Raw Data Structure (var_dump)</h2>
		<pre><?php var_dump( $data ); ?></pre>

		<?php if ( is_array( $data ) && ! empty( $data ) ) : ?>
			<h2>First Item (print_r)</h2>
			<pre><?php print_r( $data[0] ); ?></pre>
			
			<?php if ( isset( $data[0]['item_image'] ) ) : ?>
				<h2>First Item - Image Field Structure</h2>
				<pre><?php print_r( $data[0]['item_image'] ); ?></pre>
			<?php endif; ?>
		<?php else : ?>
			<div class="info warning">
				<p><strong>Note:</strong> No gallery data found. Please add items using MetaBox Builder in WordPress admin.</p>
			</div>
		<?php endif; ?>
		
		<hr style="margin: 40px 0;">
		<p><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">&larr; Back to Post</a></p>
	</body>
	</html>
	<?php
	exit;
}
