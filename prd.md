# PRD (Tài liệu Yêu cầu Sản phẩm)

- Phân tích Kỹ thuật & Bổ sung PRD
- Plugin: VN Gallery for Flatsome v4.0
- Kiến trúc sư: Winston (🧑‍🔧 Bmad Architect)

## 1. Tổng quan Phân tích

PRD v4.0 được cung cấp rất rõ ràng, có phạm vi (scope) tốt và khả thi về mặt kỹ thuật. Tài liệu này tập trung vào việc tận dụng các thành phần cốt lõi của theme Flatsome (Magnific Popup, UX Builder) và một nguồn dữ liệu cụ thể (MetaBox.io), đây là một chiến lược thông minh cho một plugin phụ trợ (add-on).

Tài liệu này không thay thế PRD mà **bổ sung** nó bằng các đặc tả kỹ thuật, chi tiết triển khai và các điểm cần lưu ý mà đội ngũ phát triển (dev) cần tuân thủ.

**Cập nhật 1 (Theo yêu cầu):** Đã thêm tùy chọn `post_id` vào UX Builder để tăng tính linh hoạt, cho phép hiển thị gallery từ một trang/bài viết cụ thể.

## 2. Đánh giá & Khuyến nghị

### 2.1. Điểm mạnh (Strengths)

- **Phạm vi rõ ràng (Clear Scope):** Việc phân định rạch ròi "In Scope" và "Out of Scope" (đặc biệt là "Không phải là Trình quản lý Dữ liệu") là rất tốt.
    
- **Tận dụng hệ sinh thái (Ecosystem Leverage):** Việc dựa vào Magnific Popup có sẵn của Flatsome giúp plugin cực kỳ nhẹ và tương thích 100%.
    
- **Nhận diện rủi ro (Risk Awareness):** PRD đã lường trước các vấn đề về phụ thuộc (dependency) và đề xuất các biện pháp kiểm tra (ví dụ: kiểm tra sự tồn tại của class/function UX Builder).
    

### 2.2. Rủi ro & Khuyến nghị (Risks & Recommendations)

1. **Rủi ro: Cấu trúc Dữ liệu MetaBox Cứng nhắc (Rigid MetaBox Data Contract)**
    
    - **Vấn đề:** PRD (mục 4.3) yêu cầu các tên trường con chính xác (`item_type`, `item_image`...). Nếu người dùng đặt tên khác, plugin sẽ thất bại.
        
    - **Khuyến nghị:** Đối với v4.0, **tuân thủ yêu cầu cứng nhắc này** như PRD đã nêu để giữ plugin đơn giản. Tuy nhiên, dev nên triển khai các tên trường này dưới dạng hằng số (constants) hoặc biến (variables) trong class, thay vì hardcode trực tiếp trong logic. Điều này cho phép mở rộng trong tương lai (ví dụ: thêm trang cài đặt để map trường) mà không cần viết lại (refactor) nhiều.
        
2. **Rủi ro: Phụ thuộc Hook của Flatsome (Flatsome Hook Dependency)**
    
    - **Vấn đề:** Các hook như `ux_builder_setup` có thể thay đổi trong các bản cập nhật Flatsome tương lai.
        
    - **Khuyến nghị:** PRD (mục 4.7, 5.2) đã xử lý tốt. Dev phải **tuân thủ nghiêm ngặt** việc kiểm tra `function_exists` hoặc `class_exists` trước khi gọi các hàm hoặc hook vào các action của UX Builder.
        

## 3. Bổ sung Kỹ thuật Chi tiết (Technical Supplementation)

Đây là các chi tiết kỹ thuật mà dev cần tuân theo.

### 3.1. Class: `Class_VN_UX_Builder` (MỚI)

Mục tiêu là đăng ký element "VN Gallery".

```
<?php
// Tóm tắt logic trong class-vn-ux-builder.php

class Class_VN_UX_Builder {

    public function __construct() {
        // Chỉ thêm action nếu function/class của UX Builder tồn tại
        if ( function_exists('ux_builder_add_map') ) {
            add_action( 'ux_builder_setup', [ $this, 'register_element' ] );
        }
    }

    public function register_element() {
        // Sử dụng hàm này để đăng ký element
        ux_builder_add_map( 'vn_gallery', [
            'name'      => __( 'VN Gallery', 'vn-gallery' ), // Tên hiển thị
            'category'  => __( 'Content', 'flatsome' ), // Danh mục (nên dùng của Flatsome)
            'options'   => [
                // 1. Tùy chọn 'field'
                'field' => [
                    'type'        => 'text',
                    'heading'     => __( 'MetaBox Field ID', 'vn-gallery' ),
                    'description' => __( 'ID của trường Group/Repeater (ví dụ: my_gallery_field)', 'vn-gallery' ),
                    'default'     => '',
                    'holder'      => 'h3', // Hiển thị ID field trên trình builder
                ],
                
                // 2. Tùy chọn 'filters'
                'filters' => [
                    'type'    => 'checkbox',
                    'heading' => __( 'Hiển thị Lọc', 'vn-gallery' ),
                    'default' => 'true',
                ],

                // 3. TÙY CHỌN 'post_id' (CẬP NHẬT THEO YÊU CẦU)
                'post_id' => [
                    'type'        => 'text',
                    'heading'     => __( 'Post ID (Tùy chọn)', 'vn-gallery' ),
                    'description' => __( 'Nhập ID của trang/bài viết chứa dữ liệu. Bỏ trống để lấy trang hiện tại.', 'vn-gallery' ),
                    'default'     => '',
                ],
            ],
            // QUAN TRỌNG: UX Builder dùng shortcode làm cơ sở
            'shortcode' => 'vn_gallery', 
        ] );
    }
}
```

### 3.2. Class: `Class_VN_Shortcode` (Cập nhật)

Mục tiêu là render `[vn_gallery]`.

```
<?php
// Tóm tắt logic trong class-vn-shortcode.php

class Class_VN_Shortcode {

    public function __construct() {
        // Đổi tên shortcode
        add_shortcode( 'vn_gallery', [ $this, 'render_shortcode' ] );
    }

```

### 3.2. Class: `Class_VN_Shortcode` (Cập nhật)

```
public function render_shortcode( $atts ) {
    // 1. Phân tích attributes
    $atts = shortcode_atts( [
        'field'   => '',
        'post_id' => 0, // 0 nghĩa là sẽ lấy trang hiện tại
        'filters' => 'true',
    ], $atts, 'vn_gallery' );

    $field_id = sanitize_text_field( $atts['field'] );
    $post_id  = ( $atts['post_id'] > 0 ) ? intval( $atts['post_id'] ) : get_the_ID();
    $show_filters = filter_var( $atts['filters'], FILTER_VALIDATE_BOOLEAN );

    // 2. Validate
    if ( empty( $field_id ) ) {
        return $this->render_error( __( 'Lỗi VN Gallery: Vui lòng cung cấp "field".', 'vn-gallery' ) );
    }
    if ( ! function_exists('rwmb_get_value') ) {
        return $this->render_error( __( 'Lỗi VN Gallery: MetaBox.io không được kích hoạt.', 'vn-gallery' ) );
    }

    // 3. Lấy dữ liệu
    $gallery_data = rwmb_get_value( $field_id, [ 'object_id' => $post_id ] );

    if ( empty( $gallery_data ) || ! is_array( $gallery_data ) ) {
        return $this->render_error( __( 'Lỗi VN Gallery: Không tìm thấy dữ liệu hoặc dữ liệu không hợp lệ.', 'vn-gallery' ), false );
    }

    // 4. Bật cờ (flag) để tải JS/CSS
    // Giả sử Class_VN_Assets có một phương thức tĩnh
    Class_VN_Assets::enqueue_scripts(); 

    // 5. Render HTML
    ob_start();
    
    echo '<div class="vn-gallery-wrapper">';

    // 5a. Render Filters (Nếu cần)
    if ( $show_filters ) {
        $this->render_filters();
    }

    // 5b. Render Gallery Grid
    echo '<div class="vn-gallery-grid" id="vn-gallery-' . esc_attr( $post_id . '-' . $field_id ) . '">';
    
    foreach ( $gallery_data as $item ) {
        // Logic render chi tiết cho từng item (thumbnail, <a>...)
        // Cần tuân thủ cấu trúc dữ liệu (mục 4.3 PRD)
        $this->render_item( $item );
    }
    
    echo '</div>'; // .vn-gallery-grid
    echo '</div>'; // .vn-gallery-wrapper

    return ob_get_clean();
}

private function render_item( $item ) {
    // Logic lấy item_type, item_image, item_url, item_title...
    // Render ra thẻ <a> với các class và data-attributes chính xác
    // VÍ DỤ:
    // $type = $item['item_type'] ?? 'image';
    // $is_video = ( $type === 'video' );
    // $href = $is_video ? $item['item_url'] : $image_url;
    // $data_type = $is_video ? 'video' : 'image';
    //
    // echo '<a href="' . $href . '" class="vn-gallery-item vn-item-' . $data_type . '" data-type="' . $data_type . '">';
    // ... render thumbnail ...
    // echo '</a>';
}

private function render_filters() {
    // HTML cho các nút lọc "Tất cả", "Hình ảnh", "Video"
}

private function render_error( $message, $public_facing = true ) {
    // Chỉ hiển thị lỗi cho admin
    if ( current_user_can( 'manage_options' ) ) {
        return '<div class="vn-gallery-error" style="color: red; border: 1px solid red; padding: 10px;">' . esc_html( $message ) . '</div>';
    }
    // Không hiển thị gì cho người dùng thông thường
    return $public_facing ? '' : '<!-- ' . esc_html( $message ) . ' -->';
}
```

}

````

### 3.3. `frontend-main.js` (Logic then chốt)

Dev cần tập trung vào 2 logic: Khởi tạo Magnific Popup và Lọc.

```javascript
// Tóm tắt logic trong frontend-main.js
jQuery(document).ready(function($) {

    // 1. Logic Lọc (Filtering)
    $('.vn-gallery-wrapper').on('click', '.vn-filter-btn', function(e) {
        e.preventDefault();
        
        var $wrapper = $(this).closest('.vn-gallery-wrapper');
        var $galleryGrid = $wrapper.find('.vn-gallery-grid');
        var filterValue = $(this).data('filter'); // ví dụ: '.vn-item-image', '.vn-item-video', '*'

        // Cập nhật class active cho nút
        $(this).siblings().removeClass('active');
        $(this).addClass('active');

        // Lọc (ẩn/hiện)
        if (filterValue === '*') {
            $galleryGrid.find('.vn-gallery-item').show();
        } else {
            $galleryGrid.find('.vn-gallery-item').hide();
            $galleryGrid.find(filterValue).show();
        }

        // 2. QUAN TRỌNG: Khởi tạo lại (Re-init) Magnific Popup
        // Sau khi lọc, chúng ta phải hủy instance cũ và tạo instance mới
        // chỉ với các item đang hiển thị.
        if ($.fn.magnificPopup) {
            // Hủy instance cũ gắn liền với grid này
            $galleryGrid.magnificPopup('destroy');
            
            // Khởi tạo lại với logic bên dưới
            initMagnificPopup($galleryGrid);
        }
    });

    // 3. Logic Khởi tạo (Initialization)
    function initMagnificPopup(galleryElement) {
        if (!$.fn.magnificPopup) {
            console.error('VN Gallery: Magnific Popup không được tải.');
            return;
        }

        galleryElement.magnificPopup({
            // Sử dụng delegate cho các item bên trong grid
            delegate: 'a.vn-gallery-item:visible', // QUAN TRỌNG: Chỉ chọn item đang hiển thị
            type: 'image', // Mặc định là 'image'
            gallery: {
                enabled: true
            },
            // Mục 4.6 PRD: Xử lý loại hỗn hợp
            callbacks: {
                elementParse: function(item) {
                    // 'item' là đối tượng của Magnific Popup
                    // 'item.el' là jQuery element (thẻ <a>)
                    if (item.el.data('type') === 'video') {
                        item.type = 'iframe'; // Ghi đè loại thành 'iframe' cho video
                    } else {
                        item.type = 'image'; // Đảm bảo các loại khác là 'image'
                    }
                },
                // Thêm tiêu đề & mô tả (Mục 2.1 PRD)
                image: {
                    titleSrc: function(item) {
                        // Lấy từ data attributes (dev cần thêm vào khi render_item)
                        var title = item.el.data('title') || '';
                        var desc = item.el.data('description') || '';
                        if (desc) {
                            return esc_html(title) + '<small>' + esc_html(desc) + '</small>';
                        }
                        return esc_html(title);
                    }
                },
                iframe: {
                     titleSrc: function(item) {
                        // Tương tự cho video
                        var title = item.el.data('title') || '';
                        var desc = item.el.data('description') || '';
                        if (desc) {
                            return esc_html(title) + '<small>' + esc_html(desc) + '</small>';
                        }
                        return esc_html(title);
                    }
                }
            }
        });
    }

    // Hàm escape HTML đơn giản cho JS
    function esc_html(text) {
        if (!text) return '';
        return text.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // 4. Khởi tạo lần đầu cho tất cả gallery trên trang
    $('.vn-gallery-grid').each(function() {
        initMagnificPopup($(this));
    });

});
````

## 4. Hạng mục Research cần xác nhận (Dev Validation)

PRD đã rất tốt, nhưng dev cần xác nhận 100% các điểm sau từ tài liệu của Flatsome (hoặc tự kiểm tra code của theme):

1. **Tên hàm đăng ký UX Builder:** PRD nói `ux_builder_setup`. Phân tích của tôi sử dụng `ux_builder_add_map`. Dev cần xác nhận `ux_builder_add_map` là hàm chính xác được gọi bên trong hook `ux_builder_setup`. (Khả năng cao là chính xác).
    
2. **Cấu trúc mảng Options:** Cấu trúc mảng `options` tôi cung cấp (mục 3.1) dựa trên các ví dụ phổ biến. Dev cần kiểm tra xem có cần thêm các thuộc tính khác (ví dụ: `section`, `priority`...) để tích hợp mượt mà nhất vào UX Builder hay không.