# VN Lightbox Gallery Element

![Version](https://img.shields.io/badge/version-4.0.0-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)
![License](https://img.shields.io/badge/license-GPL--2.0%2B-green.svg)

A powerful WordPress plugin that integrates with Flatsome UX Builder to display beautiful lightbox galleries powered by MetaBox data.

## ✨ Features

- **🎨 Flatsome UX Builder Integration** - Seamlessly works with Flatsome's visual page builder
- **🖼️ Multi-Media Support** - Display images and videos (YouTube, Vimeo) in one gallery
- **🔍 Smart Filtering** - Filter content by type (All, Images, Videos)
- **📱 Fully Responsive** - Beautiful CSS Grid layout that adapts to all devices
- **⚡ Performance Optimized** - Conditional asset loading (only loads when needed)
- **🎭 Magnific Popup** - Uses Flatsome's built-in Magnific Popup for smooth lightbox experience
- **🧰 MetaBox Powered** - Easy content management through MetaBox Builder
- **🎯 Cross-Page Gallery** - Display any page's gallery on different pages using post_id
- **📝 Title Display Toggle** - Option to show/hide gallery item titles
- **🎨 Custom CSS Classes** - Add custom classes for advanced styling
- **✅ WordPress Standards** - Follows WordPress Coding Standards and best practices

## 📋 Requirements

- **WordPress:** 5.8 or higher
- **PHP:** 7.4 or higher
- **Theme:** Flatsome (any version with UX Builder)
- **Plugin:** Meta Box (for content management)

## 🚀 Installation

1. Upload the `vn-lightbox-gallery-element` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Ensure Flatsome theme and Meta Box plugin are activated
4. Create your gallery field using MetaBox Builder (see configuration below)

## 📦 MetaBox Configuration

The plugin works with **MetaBox Builder** or manual code configuration. Create a Group/Repeater field with the default field ID `vn_gallery_items`.

### Using MetaBox Builder (Recommended):

1. Go to **Meta Box → Custom Fields → Add New**
2. Create a field group with the following structure:
   - **Field ID**: `vn_gallery_items`
   - **Type**: Group
   - **Cloneable**: Yes (to create repeater)
   - **Collapsible**: Yes (optional)

3. Add the following sub-fields:

| Field ID | Field Type | Options | Required | Notes |
|----------|-----------|---------|----------|-------|
| `item_type` | Select | `image` / `video` | ✅ | Item type |
| `item_image` | Image Advanced | max_file_uploads = 1 | ✅ | Image (or video thumbnail) |
| `item_video_url` | URL | - | ⚠️ | Required if type = video |
| `item_thumbnail` | Image Advanced | max_file_uploads = 1 | ❌ | Custom video thumbnail (optional) |
| `item_title` | Text | - | ❌ | Display title |
| `item_description` | Textarea | - | ❌ | Display description |

### Manual Configuration (Advanced):

See `example-metabox-config.php` in the plugin directory for a complete configuration example.

### ⚠️ Field Names Must Not Be Changed:

The plugin depends on these exact field names:

- ✅ `item_type` - Item type ('image' or 'video')
- ✅ `item_image` - Image field (type: image_advanced)
- ✅ `item_video_url` - YouTube/Vimeo video URL (type: url)
- ✅ `item_thumbnail` - Custom video thumbnail (type: image_advanced)
- ✅ `item_title` - Title (type: text)
- ✅ `item_description` - Description (type: textarea)

### 📝 MetaBox Data Structure:

```php
// MetaBox Builder returns array structure:
array(
    [0] => array(
        'item_type' => 'image',           // or 'video'
        'item_image' => array(
            [0] => '1837'                 // Attachment ID as string
        ),
        'item_title' => 'Image Title',
        'item_description' => 'Image Description',
    ),
    [1] => array(
        'item_type' => 'video',
        'item_video_url' => 'https://youtube.com/watch?v=...',
        'item_thumbnail' => array(        // Optional
            [0] => '398'
        ),
        'item_title' => 'Video Title',
    ),
)
```

The plugin automatically handles:
- ✅ String attachment IDs from MetaBox Builder
- ✅ Image URL extraction from attachment IDs
- ✅ Auto-fetch YouTube/Vimeo thumbnails if `item_thumbnail` is empty
- ✅ Fallback image sizes: full → large → medium → thumbnail

## 📖 Usage

### 1. Adding Gallery Content in WordPress Admin

1. Edit a Page/Post in WordPress admin
2. Find the meta box **"VN Gallery"** (or your custom name)
3. Click **"Add Item"** to add images or videos:
   - Select **Type**: Image or Video
   - Upload **Image** (required - used as thumbnail)
   - If Video: Enter **Video URL** (YouTube/Vimeo)
   - Enter **Title** and **Description** (optional)
4. Click **Update** to save

### 2. Display in UX Builder

1. Open UX Builder
2. Add the **"VN Gallery"** element from the **"Content"** category
3. Configure options:
   - **Select Gallery Page**: Choose a page with gallery data from dropdown
   - **Show Filter Buttons**: Enable/disable All/Images/Videos filter buttons
   - **Show Title**: Toggle title display below gallery items
   - **Custom Class**: Add custom CSS classes for styling

**💡 Note**: The field ID is hardcoded as `vn_gallery_items` - no manual input needed.

### 3. Using Shortcode

```
[vn_gallery]
```

**Optional Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `field` | string | `vn_gallery_items` | MetaBox field ID |
| `post_id` | integer | current page | ID of page/post with gallery data |
| `filters` | boolean | `true` | Show filter buttons (All/Images/Videos) |
| `show_title` | boolean | `false` | Display item titles below thumbnails |
| `class` | string | - | Custom CSS classes (space-separated) |

**Examples:**

```
[vn_gallery post_id="123" filters="false"]
[vn_gallery post_id="456" show_title="true" class="my-custom-gallery"]
[vn_gallery field="custom_gallery_field" filters="true"]
```

## 📁 Plugin Structure

```
vn-lightbox-gallery-element/
├── vn-lightbox-gallery-element.php  # Main plugin file
├── includes/
│   ├── class-vn-ux-builder.php      # UX Builder integration
│   ├── class-vn-shortcode.php       # Shortcode handler
│   └── class-vn-assets.php          # Asset management
├── assets/
│   ├── js/
│   │   └── frontend-main.js         # Main JavaScript
│   └── css/
│       └── frontend-style.css       # Main CSS
├── README.md
└── backlog.md
```

## 🏗️ Technical Architecture

### Class: VN_UX_Builder

- Checks `function_exists('add_ux_builder_shortcode')` before registration
- Hooks into `ux_builder_setup` action
- Registers element with page dropdown selector
- Dynamic template generation with conditional attributes

**Key Features:**
- Page selection dropdown instead of manual ID input
- Conditional shortcode attribute rendering
- WP_Query integration for page list

### Class: VN_Shortcode

- Parses and validates shortcode attributes
- Checks MetaBox availability
- Retrieves data using `rwmb_get_value()` with proper post type handling
- Renders HTML structure with complete data-attributes
- Error handling: admin-only visibility (`current_user_can('manage_options')`)

**Key Features:**
- Multi-class support (space-separated)
- Cross-page gallery loading with post_id parameter
- Gallery item wrapper for title display
- MetaBox attachment ID handling (string to int conversion)

### Class: VN_Assets

- Conditional loading: only loads when shortcode is used
- Registers assets with `wp_register_style/script()`
- Enqueues in `wp_footer` when flag is enabled

**Key Features:**
- Flatsome Magnific Popup dependency check
- Lazy loading support

### JavaScript (frontend-main.js)

- Modular architecture (CONFIG, Utils, Gallery, Filter)
- Magnific Popup initialization with `delegate` pattern
- Video platform detection (YouTube, Vimeo)
- Title and description markup building
- Filter logic with wrapper-aware visibility

**Key Features:**
- Cleans up existing Magnific Popup instances before reinit
- Supports `.gallery-item-wrapper` for title display
- Filter by child element classes
- Production-ready (no console logs)

### CSS (frontend-style.css)

- Responsive CSS Grid layout
- Filter button styles with active states
- Gallery item hover effects and animations
- Video play button overlay
- Mobile-first responsive breakpoints
- CSS variables for design tokens

**Key Features:**
- Fade-in animations with staggered delays
- Image load shimmer effect
- Flexbox wrapper for title positioning

## 🐛 Debug & Troubleshooting

### Debug Mode

Access `?vn_gallery_debug=1` in your post/page URL to view debug information:

```
https://yoursite.com/page-slug/?vn_gallery_debug=1
```

Debug information displays:
- Post ID, Title, Type, and Status
- Field ID being queried
- Data type and structure
- Related meta keys found
- Raw data from MetaBox
- Item count

### Error Handling

The plugin displays detailed error messages for admins (`manage_options` capability) when:

- ❌ Field ID doesn't exist
- ❌ MetaBox plugin is not activated
- ❌ No data found for field
- ❌ Data is not in array format
- ❌ Items missing image or video URL

Debug messages show:
- Field ID and Post ID being queried
- Data type (array, null, false, etc.)
- Item count
- Hint to access debug mode

Regular users see HTML comments or no output.

### Common Issues

**Gallery not showing:**
- Check if MetaBox field ID is `vn_gallery_items`
- Verify gallery data exists in WordPress admin
- Enable debug mode to see detailed information

**Images not loading:**
- Ensure images are uploaded through MetaBox Builder
- Check attachment IDs are valid
- Verify image URLs in debug output

**Videos not playing:**
- Confirm video URL format is correct (YouTube/Vimeo)
- Check Magnific Popup is loaded (Flatsome dependency)

**Cross-page gallery not working:**
- Verify post_id parameter is correct
- Ensure target page has gallery data
- Check page post type matches MetaBox configuration

## 🔄 Compatibility

- ✅ **WordPress:** 5.8 - 6.x
- ✅ **PHP:** 7.4 - 8.3
- ✅ **Flatsome:** 3.x (all versions with UX Builder)
- ✅ **Meta Box:** 5.x

## 📞 Support

- **Plugin URI:** https://wpmasterynow.com/
- **Author:** VN
- **Documentation:** See this README
- **Issues:** Check debug mode first

## 📝 Changelog

### 4.0.0 (2025-11-18)

**🎉 Initial Release - Production Ready**

**New Features:**
- ✅ Flatsome UX Builder integration with visual element
- ✅ Image and video support (YouTube, Vimeo)
- ✅ Smart filtering by type (All / Images / Videos)
- ✅ Conditional asset loading (loads only when needed)
- ✅ Magnific Popup integration with lazy loading
- ✅ Responsive CSS Grid layout with animations
- ✅ Debug mode (`?vn_gallery_debug=1`)
- ✅ Cross-page gallery display with post_id parameter
- ✅ Title display toggle (show_title parameter)
- ✅ Custom CSS class support (space-separated multi-class)
- ✅ Page dropdown selector in UX Builder

**Technical Improvements:**
- ✅ WordPress Coding Standards compliant
- ✅ Singleton pattern for all classes
- ✅ Strict typing (PHP 7.4+)
- ✅ MetaBox Builder compatibility
- ✅ String attachment ID handling from MetaBox
- ✅ Auto thumbnail fetching for YouTube/Vimeo
- ✅ Fallback image sizes (full → large → medium → thumbnail)
- ✅ Modular JavaScript architecture (no console logs)
- ✅ CSS animations with staggered delays
- ✅ Gallery item wrapper for title positioning

**Bug Fixes:**
- 🔧 UX Builder element not appearing → Fixed hook to `ux_builder_setup`
- 🔧 MetaBox field ID manual input required → Hardcoded default `vn_gallery_items`
- 🔧 Magnific Popup not loading → Added dynamic loading support
- 🔧 Images not rendering → Fixed MetaBox Builder data structure handling
- 🔧 Video URL field mismatch → Updated to `item_video_url`
- 🔧 Attachment ID string format → Converted to int for `wp_get_attachment_image_url()`
- 🔧 Cross-page post_id not working → Added proper post type parameter to `rwmb_get_value()`
- 🔧 Filter showing orphaned titles → Updated to filter `.gallery-item-wrapper` parent
- 🔧 Custom class not displaying in UX Builder → Fixed template with conditional attributes
- 🔧 Page selection difficult → Replaced textfield with dropdown selector

## 📄 License

This plugin is licensed under the GPL v2 or later.

```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

---

**Made with ❤️ for WordPress and Flatsome**
