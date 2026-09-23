<?php
/**
 * Example MetaBox Configuration for VN Gallery
 *
 * Copy và chỉnh sửa code này vào file functions.php của theme hoặc tạo file riêng.
 *
 * @package VN_Lightbox_Gallery
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'rwmb_meta_boxes', 'vn_gallery_register_metabox' );

/**
 * Register MetaBox fields for VN Gallery.
 *
 * @param array $meta_boxes Existing metaboxes.
 * @return array Modified metaboxes.
 */
function vn_gallery_register_metabox( $meta_boxes ) {
	$meta_boxes[] = array(
		'id'         => 'vn_gallery_metabox',
		'title'      => __( 'VN Gallery', 'vn-lightbox-gallery' ),
		'post_types' => array( 'gallery' ), // Plugin chỉ đọc dữ liệu từ post type 'gallery'.
		'context'    => 'normal',
		'priority'   => 'high',
		'fields'     => array(
			array(
				'id'          => 'vn_gallery_items',
				'name'        => __( 'Gallery Items', 'vn-lightbox-gallery' ),
				'type'        => 'group',
				'clone'       => true,
				'sort_clone'  => true,
				'collapsible' => true,
				'group_title' => array( 'field' => 'item_title' ),
				'add_button'  => __( 'Thêm Item', 'vn-lightbox-gallery' ),
				'fields'      => array(
					// Item Type.
					array(
						'id'            => 'item_type',
						'name'          => __( 'Loại Item', 'vn-lightbox-gallery' ),
						'type'          => 'select',
						'options'       => array(
							'image' => __( 'Hình ảnh', 'vn-lightbox-gallery' ),
							'video' => __( 'Video', 'vn-lightbox-gallery' ),
						),
						'std'           => 'image',
						'admin_columns' => 'replace',
					),
					// Item Image.
					array(
						'id'               => 'item_image',
						'name'             => __( 'Hình ảnh', 'vn-lightbox-gallery' ),
						'type'             => 'image_advanced',
						'max_file_uploads' => 1,
						'max_status'       => false,
						'image_size'       => 'thumbnail',
						'visible'          => array( 'item_type', '=', 'image' ),
					),
					// Video URL.
					array(
						'id'      => 'item_video_url',
						'name'    => __( 'Video URL', 'vn-lightbox-gallery' ),
						'type'    => 'url',
						'desc'    => __( 'URL của YouTube hoặc Vimeo', 'vn-lightbox-gallery' ),
						'visible' => array( 'item_type', '=', 'video' ),
					),
					// Video Thumbnail (optional, fallback: YouTube/Vimeo thumbnail).
					array(
						'id'               => 'item_thumbnail',
						'name'             => __( 'Thumbnail video', 'vn-lightbox-gallery' ),
						'type'             => 'image_advanced',
						'max_file_uploads' => 1,
						'max_status'       => false,
						'visible'          => array( 'item_type', '=', 'video' ),
					),
					// Item Title.
					array(
						'id'   => 'item_title',
						'name' => __( 'Tiêu đề', 'vn-lightbox-gallery' ),
						'type' => 'text',
						'size' => 60,
					),
					// Item Description.
					array(
						'id'   => 'item_description',
						'name' => __( 'Mô tả', 'vn-lightbox-gallery' ),
						'type' => 'textarea',
						'rows' => 3,
					),
				),
			),
		),
	);

	return $meta_boxes;
}

/**
 * HƯỚNG DẪN SỬ DỤNG:
 *
 * 1. Đăng ký post type 'gallery' (MB Custom Post Type) nếu chưa có.
 *
 * 2. Copy code này vào functions.php của theme (hoặc một file riêng được require),
 *    hoặc tạo field group tương đương bằng MB Builder.
 *
 * 3. Tạo một Gallery mới và thêm item:
 *    - Hình ảnh: chọn loại "Hình ảnh" và upload ảnh.
 *    - Video: chọn loại "Video", nhập URL YouTube/Vimeo; thumbnail là tùy chọn
 *      (để trống sẽ tự lấy thumbnail từ YouTube/Vimeo).
 *
 * 4. Hiển thị: thêm element "VN Gallery" trong UX Builder và chọn gallery,
 *    hoặc dùng shortcode: [vn_gallery gallery_id="123"]
 *
 * QUAN TRỌNG:
 * - KHÔNG đổi ID của group (vn_gallery_items) và các field con:
 *   item_type, item_image, item_video_url, item_thumbnail, item_title, item_description
 *   Plugin phụ thuộc vào các ID này (xem hằng số trong VN_Shortcode).
 */
