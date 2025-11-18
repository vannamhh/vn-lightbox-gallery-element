# Changelog

Tất cả thay đổi đáng chú ý của plugin VN Lightbox Gallery Element sẽ được ghi chép tại đây.

## [4.0.0] - 2025-11-18

### Added (Thêm mới)

#### Epic 1: Tích hợp Lõi Plugin & UX Builder
- ✅ **Story 1.1**: Thiết lập cấu trúc plugin cơ bản
  - File plugin chính với header đầy đủ
  - Cấu trúc thư mục: `/includes`, `/assets/js`, `/assets/css`
  - Class chính: `VN_Lightbox_Gallery_Element`, `VN_UX_Builder`, `VN_Shortcode`, `VN_Assets`
  - Singleton pattern cho tất cả các class

- ✅ **Story 1.2**: Đăng ký Element "VN Gallery" vào UX Builder
  - Class `VN_UX_Builder` với hook `ux_builder_setup`
  - Function check: `function_exists('add_ux_builder_shortcode')`
  - Element options:
    - `field` (textfield): MetaBox Field ID
    - `post_id` (textfield): Post ID tùy chọn
    - `filters` (checkbox): Hiển thị nút lọc
  - Category: "Content"

#### Epic 2: Logic Render Shortcode & Truy xuất Dữ liệu
- ✅ **Story 2.1**: Phân tích Attributes & Xử lý Lỗi
  - Shortcode `[vn_gallery]` được đăng ký
  - Parse 3 attributes: `field`, `post_id`, `filters`
  - Validation: field không được trống
  - Check MetaBox availability: `function_exists('rwmb_get_value')`
  - Error rendering chỉ cho admin (`current_user_can('manage_options')`)

- ✅ **Story 2.2**: Truy xuất Dữ liệu MetaBox & Render HTML
  - Xác định `post_id`: từ attribute hoặc `get_the_ID()`
  - Lấy dữ liệu: `rwmb_get_value($field_id, ['object_id' => $post_id])`
  - Validate dữ liệu: check empty và is_array
  - Render HTML structure: `.vn-gallery-wrapper` > `.vn-gallery-grid`
  - Signal assets loading: `VN_Assets::enqueue_scripts()`

- ✅ **Story 2.3**: Render Các Nút Lọc
  - HTML filter buttons trong `.vn-gallery-filters`
  - 3 nút: "Tất cả" (`*`), "Hình ảnh" (`.vn-item-image`), "Video" (`.vn-item-video`)
  - Class `active` mặc định cho nút "Tất cả"

- ✅ **Story 2.4**: Render Từng Item
  - Field name constants: `FIELD_ITEM_TYPE`, `FIELD_ITEM_IMAGE`, `FIELD_ITEM_URL`, etc.
  - Xác định `data-type`: 'image' hoặc 'video'
  - Thẻ `<a>` với class: `vn-gallery-item`, `vn-item-{type}`
  - Data attributes: `data-type`, `data-title`, `data-description`
  - `href`: video URL hoặc image full URL
  - Thumbnail: `item_image['thumbnail_url']`

#### Epic 3: Tương tác Frontend (JS/CSS) & Hoàn thiện
- ✅ **Story 3.1**: Tải Assets (JS/CSS)
  - Class `VN_Assets` với static flag `$should_enqueue`
  - Register assets: `wp_register_style/script()`
  - Conditional enqueue trong `wp_footer` hook
  - Chỉ load khi shortcode được sử dụng

- ✅ **Story 3.2**: Khởi tạo Magnific Popup
  - Check `$.fn.magnificPopup` exists
  - Init cho mỗi `.vn-gallery-grid`
  - Delegate: `'a.vn-gallery-item:visible'`
  - Callback `elementParse`: set `item.type` = 'iframe' hoặc 'image'
  - Callback `image.titleSrc`: hiển thị title + description
  - Callback `markupParse`: title cho iframe items
  - Support YouTube & Vimeo patterns

- ✅ **Story 3.3**: Triển khai Logic Lọc
  - Event handler: `.vn-filter-btn` click
  - Update active state
  - Show/hide items theo `data-filter`
  - Destroy và reinit Magnific Popup sau khi lọc
  - Chỉ popup items visible

- ✅ **Story 3.4**: Thêm CSS Cơ bản
  - CSS Grid layout: `repeat(auto-fill, minmax(250px, 1fr))`
  - Responsive breakpoints: 768px, 480px
  - Filter button styles với active state
  - Gallery item hover effects
  - Video play button overlay (::before)
  - Aspect ratio 1:1 cho items
  - Error message styles

### Technical Details

#### Architecture
- **Design Pattern**: Singleton cho tất cả class
- **Strict Typing**: `declare(strict_types=1);` 
- **Constants**: Field names là constants để dễ maintain
- **Conditional Loading**: Assets chỉ load khi cần

#### Security
- Input sanitization: `sanitize_text_field()`, `intval()`
- Output escaping: `esc_html()`, `esc_attr()`, `esc_url()`
- Capability check: `current_user_can('manage_options')`
- Nonce verification: Không cần (read-only)

#### Performance
- Lazy loading images: `loading="lazy"`
- Conditional asset loading
- CSS Grid thay vì JavaScript layout
- Magnific Popup delegate pattern

#### Compatibility
- WordPress 5.8+
- PHP 7.4+ với typed properties
- Flatsome theme (all versions với UX Builder)
- Meta Box 5.x

### Documentation
- README.md với hướng dẫn chi tiết
- example-metabox-config.php với code mẫu
- Inline comments trong code
- PHPDoc blocks cho tất cả methods

### Files Created
```
vn-lightbox-gallery-element/
├── vn-lightbox-gallery-element.php
├── includes/
│   ├── class-vn-ux-builder.php
│   ├── class-vn-shortcode.php
│   └── class-vn-assets.php
├── assets/
│   ├── js/
│   │   └── frontend-main.js
│   └── css/
│       └── frontend-style.css
├── README.md
├── CHANGELOG.md
├── example-metabox-config.php
├── backlog.md
├── prd.md
└── .gitignore
```

### Verified Against Backlog

Tất cả 10 Epic/Story trong backlog.md đã được triển khai đầy đủ:

- [x] Epic 1.1: Thiết lập Cấu trúc Plugin
- [x] Epic 1.2: Đăng ký Element vào UX Builder
- [x] Epic 2.1: Phân tích Attributes & Xử lý Lỗi
- [x] Epic 2.2: Truy xuất Dữ liệu MetaBox
- [x] Epic 2.3: Render Filter Buttons
- [x] Epic 2.4: Render Gallery Items
- [x] Epic 3.1: Tải Assets JS/CSS
- [x] Epic 3.2: Khởi tạo Magnific Popup
- [x] Epic 3.3: Implement Filter Logic
- [x] Epic 3.4: Thêm CSS cơ bản

## [Unreleased]

### Planned for Future Versions

#### v4.1.0 (Future)
- [ ] Thêm tùy chọn số cột trong UX Builder
- [ ] Hỗ trợ lazy loading cho gallery lớn
- [ ] Thêm animation transitions cho filter

#### v4.2.0 (Future)
- [ ] Hỗ trợ custom field mapping trong settings
- [ ] Admin settings page
- [ ] Thêm gallery layouts (masonry, justified)

#### v5.0.0 (Future)
- [ ] Tích hợp với Gutenberg blocks
- [ ] Elementor widget support
- [ ] Advanced filtering (tags, categories)
