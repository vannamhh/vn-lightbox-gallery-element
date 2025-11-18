# VN Lightbox Gallery Element v4.0

Plugin WordPress tích hợp với Flatsome UX Builder để hiển thị gallery với lightbox từ dữ liệu MetaBox.

## Tính năng

- ✅ Tích hợp hoàn toàn với Flatsome UX Builder
- ✅ Sử dụng Magnific Popup có sẵn của Flatsome
- ✅ Hỗ trợ hình ảnh và video (YouTube, Vimeo)
- ✅ Bộ lọc (Filter) theo loại: Tất cả, Hình ảnh, Video
- ✅ Responsive grid layout
- ✅ Conditional loading (chỉ load assets khi cần)
- ✅ Tuân thủ WordPress Coding Standards

## Yêu cầu

- WordPress 5.8+
- PHP 7.4+
- Theme Flatsome (bất kỳ phiên bản nào có UX Builder)
- Plugin Meta Box (để quản lý dữ liệu)

## Cài đặt

1. Upload thư mục `vn-lightbox-gallery-element` vào `/wp-content/plugins/`
2. Kích hoạt plugin từ menu 'Plugins' trong WordPress
3. Đảm bảo theme Flatsome và plugin Meta Box đã được kích hoạt

## Cấu trúc Dữ liệu MetaBox

Plugin yêu cầu cấu trúc dữ liệu cụ thể từ MetaBox. Tạo một Group/Repeater field với các trường con sau:

```php
array(
    'id'     => 'my_gallery_field', // ID của field chính
    'type'   => 'group',
    'clone'  => true, // Để tạo repeater
    'fields' => array(
        array(
            'id'   => 'item_type',
            'type' => 'select',
            'options' => array(
                'image' => 'Hình ảnh',
                'video' => 'Video',
            ),
        ),
        array(
            'id'   => 'item_image',
            'type' => 'image_advanced',
            'max_file_uploads' => 1,
        ),
        array(
            'id'   => 'item_url',
            'name' => 'Video URL',
            'type' => 'url',
            'desc' => 'URL YouTube hoặc Vimeo',
        ),
        array(
            'id'   => 'item_title',
            'type' => 'text',
        ),
        array(
            'id'   => 'item_description',
            'type' => 'textarea',
        ),
    ),
)
```

### Tên trường bắt buộc (Không được thay đổi):

- `item_type` - Loại item ('image' hoặc 'video')
- `item_image` - Field hình ảnh (image_advanced)
- `item_url` - URL video (cho YouTube/Vimeo)
- `item_title` - Tiêu đề
- `item_description` - Mô tả

## Sử dụng

### 1. Trong UX Builder

1. Mở UX Builder
2. Thêm element "VN Gallery" từ danh mục "Content"
3. Cấu hình:
   - **MetaBox Field ID**: Nhập ID của Group/Repeater field (ví dụ: `my_gallery_field`)
   - **Post ID**: Bỏ trống để lấy trang hiện tại, hoặc nhập ID của trang/bài viết cụ thể
   - **Hiển thị Nút Lọc**: Bật/tắt các nút lọc

### 2. Sử dụng Shortcode

```
[vn_gallery field="my_gallery_field" filters="true"]
```

**Tham số:**

- `field` (bắt buộc) - ID của MetaBox field
- `post_id` (tùy chọn) - ID của trang/bài viết. Mặc định: trang hiện tại
- `filters` (tùy chọn) - Hiển thị nút lọc. Giá trị: 'true' hoặc 'false'. Mặc định: 'true'

**Ví dụ:**

```
[vn_gallery field="my_gallery_field" post_id="123" filters="false"]
```

## Cấu trúc Plugin

```
vn-lightbox-gallery-element/
├── vn-lightbox-gallery-element.php  # File plugin chính
├── includes/
│   ├── class-vn-ux-builder.php      # Tích hợp UX Builder
│   ├── class-vn-shortcode.php       # Xử lý shortcode
│   └── class-vn-assets.php          # Quản lý assets
├── assets/
│   ├── js/
│   │   └── frontend-main.js         # JavaScript chính
│   └── css/
│       └── frontend-style.css       # CSS chính
├── README.md
└── backlog.md
```

## Kiến trúc Kỹ thuật

### Class_VN_UX_Builder

- Kiểm tra `function_exists('add_ux_builder_shortcode')` trước khi đăng ký
- Hook vào `ux_builder_setup`
- Đăng ký element với 3 tùy chọn: field, post_id, filters

### Class_VN_Shortcode

- Parse và validate shortcode attributes
- Kiểm tra MetaBox availability
- Truy xuất dữ liệu từ `rwmb_get_value()`
- Render HTML structure với data-attributes đầy đủ
- Xử lý lỗi: chỉ hiển thị cho admin (`current_user_can('manage_options')`)

### Class_VN_Assets

- Conditional loading: chỉ load khi shortcode được sử dụng
- Register assets với `wp_register_style/script()`
- Enqueue trong `wp_footer` nếu flag được bật

### JavaScript (frontend-main.js)

- Khởi tạo Magnific Popup với `delegate: 'a.vn-gallery-item:visible'`
- Callback `elementParse`: phân biệt image vs video (iframe)
- Callback `image.titleSrc` và `markupParse`: hiển thị title + description
- Logic lọc: hide/show items và reinit Magnific Popup

### CSS (frontend-style.css)

- CSS Grid layout responsive
- Filter button styles với active state
- Gallery item hover effects
- Video play button overlay
- Mobile responsive breakpoints

## Xử lý Lỗi

Plugin hiển thị thông báo lỗi chi tiết cho admin khi:

- Field ID không được cung cấp
- MetaBox không được kích hoạt
- Không tìm thấy dữ liệu cho field
- Dữ liệu không hợp lệ

Người dùng thông thường không thấy thông báo lỗi (chỉ có HTML comment).

## Tương thích

- ✅ WordPress 5.8 - 6.x
- ✅ PHP 7.4 - 8.3
- ✅ Flatsome 3.x (tất cả các phiên bản có UX Builder)
- ✅ Meta Box 5.x

## Hỗ trợ

- Plugin URI: https://wpmasterynow.com/
- Author: VN

## Changelog

### 4.0.0 (2025-11-18)

- Release đầu tiên
- Tích hợp với Flatsome UX Builder
- Hỗ trợ hình ảnh và video
- Bộ lọc theo loại
- Conditional asset loading
- WordPress Coding Standards compliant

## License

Plugin được phát triển theo WordPress Plugin Development Best Practices.
