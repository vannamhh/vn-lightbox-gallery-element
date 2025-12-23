# Hướng dẫn sử dụng VN Lightbox Gallery Element v4.2.0

## Tính năng mới: Hỗ trợ Custom Post Type

Từ phiên bản 4.2.0, plugin hỗ trợ lấy dữ liệu gallery từ cả **Page** và **Custom Post Type**.

## Cách sử dụng trong UX Builder

### 1. Thêm element VN Gallery

1. Mở trang/post trong UX Builder
2. Tìm element **VN Gallery** trong category "Content"
3. Kéo thả element vào vị trí mong muốn

### 2. Cấu hình nguồn dữ liệu

#### Option 1: Lấy từ Page (mặc định)

1. **Nguồn Gallery**: Chọn "Page"
2. **Chọn trang Gallery**: Chọn page có chứa dữ liệu gallery
   - Hoặc để trống để lấy từ trang hiện tại

#### Option 2: Lấy từ Custom Post Type

1. **Nguồn Gallery**: Chọn "Custom Post Type"
2. **Loại Custom Post**: Chọn loại custom post type (ví dụ: Product, Portfolio, etc.)
3. **Chọn Custom Post**: Chọn custom post cụ thể có chứa dữ liệu gallery

### 3. Các tùy chọn khác

- **Hiển thị Nút Lọc**: Bật/tắt nút lọc theo loại (Tất cả/Hình ảnh/Video)
- **Hiển thị Tiêu đề**: Bật/tắt hiển thị tiêu đề của từng item
- **Class**: Thêm custom CSS class cho wrapper

## Sử dụng Shortcode trực tiếp

### Với Page

```php
// Lấy từ page cụ thể
[vn_gallery source_type="page" page_id="123" filters="true" show_title="true"]

// Lấy từ page hiện tại
[vn_gallery source_type="page" filters="true"]
```

### Với Custom Post Type

```php
// Lấy từ custom post cụ thể
[vn_gallery source_type="custom_post" custom_post_type="product" custom_post_id="456" filters="true" show_title="true"]

// Với class tùy chỉnh
[vn_gallery source_type="custom_post" custom_post_id="456" class="my-custom-gallery"]
```

### Tương thích ngược

Plugin vẫn hỗ trợ cú pháp cũ:

```php
[vn_gallery post_id="123"]
[vn_gallery post_id="123" filters="false"]
```

## Các tham số shortcode

| Tham số | Kiểu | Mặc định | Mô tả |
|---------|------|----------|-------|
| `source_type` | string | 'page' | Nguồn dữ liệu: 'page' hoặc 'custom_post' |
| `page_id` | int | 0 | ID của page (dùng khi source_type='page') |
| `custom_post_type` | string | '' | Loại custom post type |
| `custom_post_id` | int | 0 | ID của custom post (dùng khi source_type='custom_post') |
| `post_id` | int | 0 | (Deprecated) ID của post/page - dùng page_id hoặc custom_post_id |
| `filters` | boolean | true | Hiển thị nút lọc |
| `show_title` | boolean | false | Hiển thị tiêu đề item |
| `class` | string | '' | Custom CSS class |

## Lưu ý quan trọng

1. **Dữ liệu MetaBox**: Đảm bảo page hoặc custom post đã có dữ liệu gallery được tạo bằng MetaBox
2. **Custom Post Type**: Custom post type phải được đăng ký với `'public' => true`
3. **Field ID**: Mặc định plugin sử dụng field ID `vn_gallery_items` của MetaBox
4. **Flatsome Theme**: Plugin yêu cầu Flatsome Theme để hoạt động với UX Builder

## Ví dụ thực tế

### Ví dụ 1: Gallery sản phẩm từ WooCommerce

```php
[vn_gallery source_type="custom_post" custom_post_type="product" custom_post_id="789" filters="true" show_title="true" class="product-gallery"]
```

### Ví dụ 2: Portfolio gallery từ custom post type

```php
[vn_gallery source_type="custom_post" custom_post_type="portfolio" custom_post_id="234" filters="true" class="portfolio-grid"]
```

### Ví dụ 3: Gallery trên trang giới thiệu

```php
[vn_gallery source_type="page" page_id="56" filters="true" show_title="false"]
```

## Troubleshooting

### Không hiển thị dữ liệu

1. Kiểm tra MetaBox plugin đã được kích hoạt
2. Kiểm tra dữ liệu gallery đã được nhập trong page/post
3. Kiểm tra field ID có đúng không (mặc định: `vn_gallery_items`)
4. Nếu là custom post type, kiểm tra xem nó có được đăng ký public không

### Dropdown không hiển thị custom post types

- Đảm bảo custom post type được đăng ký với:
  ```php
  'public' => true,
  '_builtin' => false
  ```

### Lỗi hiển thị trong admin

- Bật WP_DEBUG để xem thông tin chi tiết
- Kiểm tra console browser để xem lỗi JavaScript
- Kiểm tra file error log của WordPress

## Hỗ trợ

Nếu gặp vấn đề, vui lòng liên hệ:
- Email: support@wpmasterynow.com
- Website: https://wpmasterynow.com/
