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

Plugin hoạt động với **MetaBox Builder** hoặc code thủ công. Tạo một Group/Repeater field với field ID mặc định là `vn_gallery_items`.

### Cấu hình trong MetaBox Builder (Khuyến nghị):

1. Vào **Meta Box → Custom Fields → Add New**
2. Tạo field group với cấu trúc:
   - **Field ID**: `vn_gallery_items`
   - **Type**: Group
   - **Cloneable**: Yes (để tạo repeater)
   - **Collapsible**: Yes (tùy chọn)

3. Thêm các sub-fields sau:

| Field ID | Field Type | Options | Bắt buộc | Ghi chú |
|----------|-----------|---------|----------|---------|
| `item_type` | Select | `image` / `video` | ✅ | Loại item |
| `item_image` | Image Advanced | max_file_uploads = 1 | ✅ | Hình ảnh (hoặc thumbnail cho video) |
| `item_video_url` | URL | - | ⚠️ | Bắt buộc nếu type = video |
| `item_thumbnail` | Image Advanced | max_file_uploads = 1 | ❌ | Thumbnail tùy chỉnh cho video (tùy chọn) |
| `item_title` | Text | - | ❌ | Tiêu đề hiển thị |
| `item_description` | Textarea | - | ❌ | Mô tả hiển thị |

### Cấu hình thủ công (Advanced):

Xem file `example-metabox-config.php` trong thư mục plugin để có ví dụ cấu hình đầy đủ.

### ⚠️ Tên trường KHÔNG được thay đổi:

Plugin phụ thuộc vào các tên field sau:

- ✅ `item_type` - Loại item ('image' hoặc 'video')
- ✅ `item_image` - Field hình ảnh (type: image_advanced)
- ✅ `item_video_url` - URL video YouTube/Vimeo (type: url)
- ✅ `item_thumbnail` - Thumbnail tùy chỉnh cho video (type: image_advanced)
- ✅ `item_title` - Tiêu đề (type: text)
- ✅ `item_description` - Mô tả (type: textarea)

### 📝 Cấu trúc dữ liệu trả về từ MetaBox:

```php
// MetaBox Builder trả về mảng như sau:
array(
    [0] => array(
        'item_type' => 'image',           // hoặc 'video'
        'item_image' => array(
            [0] => '1837'                 // Attachment ID dạng string
        ),
        'item_title' => 'Tiêu đề',
        'item_description' => 'Mô tả',
    ),
    [1] => array(
        'item_type' => 'video',
        'item_video_url' => 'https://youtube.com/watch?v=...',
        'item_thumbnail' => array(        // Tùy chọn
            [0] => '398'
        ),
        'item_title' => 'Video title',
    ),
)
```

Plugin tự động xử lý:
- ✅ Attachment ID dạng string từ MetaBox Builder
- ✅ Lấy URL hình ảnh từ attachment ID
- ✅ Tự động lấy thumbnail từ YouTube/Vimeo nếu không có `item_thumbnail`
- ✅ Fallback sizes: full → large → medium → thumbnail

## Sử dụng

### 1. Thêm Gallery Data trong WordPress Admin

1. Edit Page/Post trong WordPress admin
2. Tìm meta box **"VN Gallery"** (hoặc tên bạn đã đặt)
3. Click **"Thêm Item"** để thêm hình ảnh hoặc video:
   - Chọn **Loại**: Hình ảnh hoặc Video
   - Upload **Hình ảnh** (bắt buộc - dùng làm thumbnail)
   - Nếu chọn Video: Nhập **Video URL** (YouTube/Vimeo)
   - Nhập **Tiêu đề** và **Mô tả** (tùy chọn)
4. Click **Update** để lưu

### 2. Hiển thị trong UX Builder

1. Mở UX Builder
2. Thêm element **"VN Gallery"** từ danh mục **"Content"**
3. Cấu hình:
   - **Post ID**: Bỏ trống (lấy trang hiện tại) hoặc nhập ID cụ thể
   - **Hiển thị Filter**: Bật/tắt nút lọc Tất cả/Hình ảnh/Video

**⚠️ Lưu ý**: Field ID đã được hardcode là `vn_gallery_items`, không cần nhập thủ công.

### 3. Sử dụng Shortcode

```
[vn_gallery]
```

**Tham số tùy chọn:**

- `field` - ID của MetaBox field. Mặc định: `vn_gallery_items`
- `post_id` - ID của trang/bài viết. Mặc định: trang hiện tại
- `filters` - Hiển thị nút lọc. Giá trị: `true` hoặc `false`. Mặc định: `true`

**Ví dụ:**

```
[vn_gallery post_id="123" filters="false"]
[vn_gallery field="custom_gallery_field" filters="true"]
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

## Debug & Troubleshooting

### Debug Mode

Truy cập `?vn_gallery_debug=1` trong URL của post/page để xem thông tin debug:

```
https://yoursite.com/page-slug/?vn_gallery_debug=1
```

Debug info sẽ hiển thị:
- Post ID và Title
- Field ID đang sử dụng
- Data type và structure
- Raw data từ MetaBox
- Image field structure

### Xử lý Lỗi

Plugin hiển thị thông báo lỗi chi tiết cho admin (`manage_options` capability) khi:

- ❌ Field ID không tồn tại
- ❌ MetaBox plugin không được kích hoạt
- ❌ Không tìm thấy dữ liệu cho field
- ❌ Dữ liệu không đúng format array
- ❌ Item thiếu image hoặc video URL

Thông báo debug hiển thị:
- Field ID và Post ID đang query
- Data type (array, null, false...)
- Số lượng items
- Hint truy cập debug mode

Người dùng thông thường chỉ thấy HTML comment hoặc không hiển thị gì.

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

**✅ Release đầu tiên - Hoàn chỉnh**

**Features:**
- ✅ Tích hợp với Flatsome UX Builder
- ✅ Hỗ trợ hình ảnh và video (YouTube, Vimeo)
- ✅ Bộ lọc theo loại (Tất cả / Hình ảnh / Video)
- ✅ Conditional asset loading (chỉ load khi cần)
- ✅ Magnific Popup integration với lazy loading
- ✅ Responsive CSS Grid layout
- ✅ Debug mode (`?vn_gallery_debug=1`)

**Technical:**
- ✅ WordPress Coding Standards compliant
- ✅ Singleton pattern cho tất cả classes
- ✅ Strict typing (PHP 7.4+)
- ✅ MetaBox Builder compatibility
- ✅ Xử lý attachment ID dạng string từ MetaBox
- ✅ Auto thumbnail cho YouTube/Vimeo
- ✅ Fallback image sizes (full → large → medium → thumbnail)

**Fixed Issues:**
- 🔧 UX Builder element không xuất hiện → Fixed hook to `ux_builder_setup`
- 🔧 MetaBox field ID phải nhập thủ công → Hardcoded default `vn_gallery_items`
- 🔧 Magnific Popup không load → Added dynamic loading support
- 🔧 Images không render → Fixed MetaBox Builder data structure handling
- 🔧 Video URL field mismatch → Updated to `item_video_url`
- 🔧 Attachment ID string format → Converted to int for `wp_get_attachment_image_url()`

## License

Plugin được phát triển theo WordPress Plugin Development Best Practices.
