<?php
/**
 * Example MetaBox Configuration for VN Gallery
 *
 * Copy và chỉnh sửa code này vào file functions.php của theme hoặc tạo file riêng.
 *
 * @package VN_Lightbox_Gallery
 */

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
		'post_types' => array( 'page', 'post' ), // Chỉnh sửa theo nhu cầu.
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
					),
					// Video URL.
					array(
						'id'      => 'item_url',
						'name'    => __( 'Video URL', 'vn-lightbox-gallery' ),
						'type'    => 'url',
						'desc'    => __( 'URL của YouTube hoặc Vimeo (chỉ hiển thị khi chọn loại Video)', 'vn-lightbox-gallery' ),
						'visible' => array(
							'when'     => array( array( 'item_type', '=', 'video' ) ),
							'relation' => 'or',
						),
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
 * 1. Copy toàn bộ code này vào file functions.php của theme
 *    HOẶC tạo file mới trong thư mục theme, ví dụ: inc/metabox-config.php
 *    và require nó trong functions.php:
 *    require_once get_template_directory() . '/inc/metabox-config.php';
 *
 * 2. Sau khi thêm code, vào trang "Edit Page" hoặc "Edit Post"
 *    Bạn sẽ thấy metabox "VN Gallery" xuất hiện
 *
 * 3. Thêm các item vào gallery:
 *    - Chọn loại: Hình ảnh hoặc Video
 *    - Upload hình ảnh (bắt buộc cho cả image và video - dùng làm thumbnail)
 *    - Nhập Video URL (nếu chọn loại Video)
 *    - Nhập tiêu đề và mô tả
 *
 * 4. Lưu trang/bài viết
 *
 * 5. Sử dụng trong UX Builder hoặc shortcode:
 *    - Field ID là: vn_gallery_items
 *    - Trong UX Builder: Thêm element "VN Gallery" và nhập "vn_gallery_items" vào ô "MetaBox Field ID"
 *    - Shortcode: [vn_gallery field="vn_gallery_items"]
 *
 * TÙY CHỈNH:
 *
 * - Thay đổi post types:
 *   Dòng 28: 'post_types' => array( 'page', 'post', 'your_custom_post_type' ),
 *
 * - Thay đổi Field ID chính:
 *   Dòng 32: 'id' => 'vn_gallery_items', // Đổi thành ID mong muốn
 *   LƯU Ý: Phải sử dụng cùng ID này trong UX Builder/shortcode
 *
 * - Thêm field mới:
 *   Thêm vào mảng 'fields' (sau dòng 37)
 *   VÍ DỤ:
 *   array(
 *       'id'   => 'item_custom_field',
 *       'name' => 'Custom Field',
 *       'type' => 'text',
 *   ),
 *
 * QUAN TRỌNG:
 * - KHÔNG được thay đổi tên các field con:
 *   item_type, item_image, item_url, item_title, item_description
 *   Plugin phụ thuộc vào các tên field này!
 */
