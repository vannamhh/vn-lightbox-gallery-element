# VN Lightbox Gallery Element - Update Summary v4.2.0

## Tóm tắt cập nhật

Plugin đã được cập nhật để hỗ trợ lấy dữ liệu gallery từ cả **Page** và **Custom Post Type**, mang lại sự linh hoạt cao hơn trong việc quản lý và hiển thị gallery.

## Những thay đổi chính

### 1. File: class-vn-ux-builder.php

#### Thêm mới Options trong UX Builder

**`source_type`** - Chọn nguồn dữ liệu
- Type: select
- Default: 'page'
- Options: 'page' hoặc 'custom_post'

**`custom_post_type`** - Chọn loại Custom Post Type  
- Type: select
- Hiển thị khi: source_type === "custom_post"
- Dynamic options từ hàm `get_custom_post_types()`

**`page_id`** - Chọn Page
- Type: select
- Hiển thị khi: source_type === "page"
- Thay thế cho `post_id` cũ

**`custom_post_id`** - Chọn Custom Post
- Type: select
- Hiển thị khi: source_type === "custom_post"
- Dynamic options từ hàm `get_custom_posts_list()`

#### Hàm mới được thêm

**`get_custom_post_types(): array`**
- Lấy danh sách tất cả custom post types đã đăng ký
- Filter: public = true, _builtin = false
- Return: array với format ['post_type_name' => 'Label']

**`get_custom_posts_list(): array`**
- Lấy danh sách tất cả custom posts từ các custom post types
- Query tất cả custom posts (post_status = 'publish')
- Return: array với format [ID => 'Title (Post Type - ID: X)']

#### Cập nhật shortcode template
- Thêm tham số: source_type, page_id, custom_post_id, custom_post_type
- Template: `[vn_gallery{{source_type ? ' source_type="' + source_type + '"' : ''}}...]`

### 2. File: class-vn-shortcode.php

#### Cập nhật shortcode attributes
```php
'source_type'      => 'page',
'page_id'          => 0,
'custom_post_id'   => 0,
'custom_post_type' => '',
'post_id'          => 0, // Deprecated but still supported
```

#### Hàm mới: `determine_post_id()`
```php
private function determine_post_id( array $atts, string $source_type ): int
```
- Xác định post ID dựa trên source_type
- Ưu tiên: post_id (backward compat) > source-specific ID > current post ID
- Logic:
  - Nếu có `post_id` cũ → dùng `post_id`
  - Nếu `source_type = 'custom_post'` → dùng `custom_post_id`
  - Nếu `source_type = 'page'` → dùng `page_id`
  - Fallback: `get_the_ID()`

### 3. File: vn-lightbox-gallery-element.php

- Cập nhật version lên 4.2.0
- Cập nhật constant VN_LIGHTBOX_GALLERY_VERSION

## Cách hoạt động

### Luồng xử lý trong UX Builder

1. User chọn **Nguồn Gallery**: Page hoặc Custom Post Type
2. UX Builder hiển thị dropdown tương ứng dựa vào `conditions`
3. User chọn item cụ thể (page hoặc custom post)
4. Shortcode được generate với đúng tham số

### Luồng xử lý Shortcode

1. Parse attributes từ shortcode
2. Gọi `determine_post_id()` để xác định post ID
3. Validate post ID > 0
4. Lấy dữ liệu gallery từ MetaBox với post ID đã xác định
5. Render gallery như bình thường

## Backward Compatibility

✅ **Hoàn toàn tương thích ngược**

Các shortcode cũ vẫn hoạt động bình thường:
```php
[vn_gallery post_id="123"]
[vn_gallery post_id="123" filters="true"]
```

Hàm `determine_post_id()` sẽ ưu tiên `post_id` nếu có giá trị.

## Testing Checklist

- [ ] UX Builder hiển thị đúng dropdown khi chọn source_type
- [ ] Dropdown Page hiển thị đúng danh sách pages
- [ ] Dropdown Custom Post Type hiển thị đúng các CPT đã đăng ký
- [ ] Dropdown Custom Post hiển thị đúng danh sách posts
- [ ] Gallery hiển thị đúng khi chọn Page
- [ ] Gallery hiển thị đúng khi chọn Custom Post
- [ ] Shortcode cũ (với post_id) vẫn hoạt động
- [ ] Gallery hiển thị đúng trên trang hiện tại (không có ID)

## Yêu cầu hệ thống

- WordPress 5.8+
- PHP 7.4+
- Flatsome Theme (latest)
- MetaBox.io Plugin (latest)

## Known Issues

- Các lỗi coding standards (formatting only, không ảnh hưởng chức năng)
- File doc comments chưa theo đúng chuẩn WordPress (có thể fix sau)

## Next Steps

1. Test plugin trên môi trường thực tế
2. Tạo custom post type để test
3. Kiểm tra tương thích với các version khác nhau của WordPress/PHP
4. Cập nhật documentation nếu cần
5. Fix coding standards nếu cần thiết

## Files Changed

1. `/includes/class-vn-ux-builder.php` - Updated
2. `/includes/class-vn-shortcode.php` - Updated  
3. `/vn-lightbox-gallery-element.php` - Updated version
4. `/CHANGELOG.md` - Updated with v4.2.0 info
5. `/USAGE-GUIDE.md` - Created (New)
6. `/UPDATE-SUMMARY.md` - Created (This file)
