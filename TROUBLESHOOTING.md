# Hướng dẫn Kiểm tra & Khắc phục - VN Gallery không xuất hiện trong UX Builder

## Bước 1: Kiểm tra Plugin đã Active

1. Vào **WordPress Admin > Plugins**
2. Tìm "VN Lightbox Gallery Element"
3. Đảm bảo nó đã được **Activate** (màu xanh)

## Bước 2: Kiểm tra Theme Flatsome

1. Vào **Appearance > Themes**
2. Đảm bảo theme **Flatsome** đang active
3. UX Builder phải có sẵn trong Flatsome

## Bước 3: Thử Test Shortcode Trước

Trước khi test UX Builder, hãy test shortcode để đảm bảo plugin hoạt động:

1. Tạo một page test
2. Thêm shortcode: `[vn_gallery field="test"]`
3. View page
4. Nếu thấy error message (màu đỏ) → Plugin hoạt động ✓
5. Nếu không có gì → Plugin có vấn đề

## Bước 4: Clear Cache

1. **Disable tất cả cache plugins** tạm thời:
   - WP Rocket
   - W3 Total Cache
   - WP Super Cache
   - etc.

2. **Clear browser cache**: Ctrl+Shift+Delete (hoặc Cmd+Shift+Delete trên Mac)

3. **Hard refresh**: Ctrl+F5 (hoặc Cmd+Shift+R trên Mac)

## Bước 5: Kiểm tra trong UX Builder

1. Vào **Pages > Edit any page**
2. Click **UX Builder** button
3. Click **Add Element**
4. Tìm trong category **"Content"**
5. Scroll xuống, tìm **"VN Gallery"**

**Nếu KHÔNG thấy:**

### Fix 1: Deactivate & Reactivate Plugin

```
1. Plugins > Deactivate "VN Lightbox Gallery Element"
2. Activate lại
3. Refresh UX Builder
```

### Fix 2: Thêm Code Vào functions.php

Nếu vẫn không hiện, thêm code này vào `functions.php` của theme:

```php
add_action( 'init', 'vn_force_register_gallery', 99 );
function vn_force_register_gallery() {
    if ( ! function_exists( 'add_ux_builder_shortcode' ) ) {
        return;
    }
    
    add_ux_builder_shortcode(
        'vn_gallery',
        array(
            'name'     => 'VN Gallery',
            'category' => 'Content',
            'info'     => '{{ field }}',
            'options'  => array(
                'field' => array(
                    'type'        => 'textfield',
                    'heading'     => 'MetaBox Field ID',
                    'description' => 'Nhập ID của trường từ MetaBox',
                    'default'     => '',
                ),
                'post_id' => array(
                    'type'    => 'textfield',
                    'heading' => 'Post ID (optional)',
                    'default' => '',
                ),
                'filters' => array(
                    'type'    => 'checkbox',
                    'heading' => 'Show Filters',
                    'default' => 'true',
                ),
            ),
        )
    );
}
```

### Fix 3: Debug Mode

Thêm code debug vào `wp-config.php`:

```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

Sau đó kiểm tra file `wp-content/debug.log` để xem có error nào không.

### Fix 4: Kiểm tra Priority Hook

File: `/wp-content/plugins/vn-lightbox-gallery-element/includes/class-vn-ux-builder.php`

Dòng 48, đảm bảo là:
```php
add_action( 'init', array( $this, 'register_element' ), 20 );
```

Thử thay đổi priority:
- Priority 20 → 99
- Hoặc thử priority 10

## Bước 6: Kiểm tra File Permissions

```bash
# Trên terminal
cd /path/to/wp-content/plugins/vn-lightbox-gallery-element
chmod -R 755 .
chown -R www-data:www-data . # Hoặc user của web server
```

## Bước 7: Kiểm tra PHP Version

Plugin yêu cầu PHP 7.4+. Kiểm tra:

1. Vào **Tools > Site Health**
2. Click **Info** tab
3. Xem **Server** section
4. PHP version phải >= 7.4

## Bước 8: Test với Theme Flatsome Mặc định

1. Tạm thời switch sang theme **Twenty Twenty-Three**
2. Switch lại **Flatsome**
3. Mở UX Builder

## Các Lỗi Thường Gặp

### Lỗi 1: "Fatal error: Cannot redeclare..."
**Nguyên nhân:** Class bị khai báo 2 lần  
**Giải pháp:** Deactivate plugin, xóa cache, activate lại

### Lỗi 2: Element xuất hiện nhưng không có options
**Nguyên nhân:** Lỗi trong array options  
**Giải pháp:** Kiểm tra file `class-vn-ux-builder.php` dòng 70-90

### Lỗi 3: "Call to undefined function add_ux_builder_shortcode"
**Nguyên nhân:** Theme Flatsome chưa load đầy đủ  
**Giải pháp:** Tăng priority hook lên 99 hoặc 999

## Test Nhanh

Paste code này vào `functions.php` để test xem UX Builder có hoạt động không:

```php
add_action( 'wp_footer', 'vn_debug_check' );
function vn_debug_check() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    
    echo '<!-- VN Debug -->';
    echo '<!-- add_ux_builder_shortcode: ' . ( function_exists( 'add_ux_builder_shortcode' ) ? 'YES' : 'NO' ) . ' -->';
    echo '<!-- VN_UX_Builder class: ' . ( class_exists( 'VN_UX_Builder' ) ? 'YES' : 'NO' ) . ' -->';
    
    if ( function_exists( 'ux_builder_shortcodes' ) ) {
        $elements = ux_builder_shortcodes();
        echo '<!-- Total elements: ' . count( $elements ) . ' -->';
        echo '<!-- VN Gallery: ' . ( isset( $elements['vn_gallery'] ) ? 'REGISTERED' : 'NOT FOUND' ) . ' -->';
    }
}
```

Sau đó **View Page Source** (Ctrl+U), kéo xuống cuối, xem các comment HTML.

## Liên hệ

Nếu vẫn không được, cung cấp thông tin:

1. WordPress version: ____
2. PHP version: ____
3. Flatsome version: ____
4. Active plugins: ____
5. Error trong debug.log: ____
