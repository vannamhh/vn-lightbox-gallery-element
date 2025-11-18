# Testing Guide - VN Lightbox Gallery Element v4.0

Hướng dẫn kiểm tra (testing) toàn diện cho plugin.

## Checklist Tổng quan

### ✅ Phase 1: Installation & Setup
- [ ] Plugin kích hoạt thành công
- [ ] Không có PHP errors/warnings
- [ ] MetaBox field hiển thị đúng
- [ ] UX Builder element xuất hiện

### ✅ Phase 2: Functionality Testing
- [ ] Shortcode render HTML đúng
- [ ] MetaBox data được lấy chính xác
- [ ] Filter buttons hoạt động
- [ ] Magnific Popup khởi tạo đúng
- [ ] Image/Video lightbox mở được

### ✅ Phase 3: Error Handling
- [ ] Error messages cho admin
- [ ] Graceful degradation cho user
- [ ] Missing MetaBox handling
- [ ] Invalid data handling

### ✅ Phase 4: Performance & Compatibility
- [ ] Assets chỉ load khi cần
- [ ] Responsive trên mobile
- [ ] Browser compatibility
- [ ] Conflict testing

---

## Phase 1: Installation & Setup Testing

### Test 1.1: Plugin Activation

**Steps:**
1. Upload plugin vào `/wp-content/plugins/`
2. Vào WordPress Admin > Plugins
3. Kích hoạt "VN Lightbox Gallery Element"

**Expected:**
- ✅ Plugin kích hoạt không có error
- ✅ Không có PHP notices/warnings
- ✅ Version 4.0.0 hiển thị

**Actual Result:**
```
[ ] Pass  [ ] Fail
Notes: _______________________________________________
```

### Test 1.2: MetaBox Field Registration

**Prerequisites:**
- Meta Box plugin đã kích hoạt
- Copy code từ `example-metabox-config.php` vào functions.php

**Steps:**
1. Tạo/edit một Page
2. Scroll xuống, tìm metabox "VN Gallery"

**Expected:**
- ✅ Metabox "VN Gallery" xuất hiện
- ✅ Nút "Thêm Item" hoạt động
- ✅ Tất cả fields hiển thị: Loại Item, Hình ảnh, Video URL, Tiêu đề, Mô tả

**Actual Result:**
```
[ ] Pass  [ ] Fail
Notes: _______________________________________________
```

### Test 1.3: UX Builder Element Registration

**Prerequisites:**
- Flatsome theme đã kích hoạt

**Steps:**
1. Mở một Page với UX Builder
2. Click "Add Element"
3. Tìm trong category "Content"

**Expected:**
- ✅ Element "VN Gallery" xuất hiện
- ✅ 3 options hiển thị:
  - MetaBox Field ID (textfield)
  - Post ID (textfield)
  - Hiển thị Nút Lọc (checkbox)

**Actual Result:**
```
[ ] Pass  [ ] Fail
Notes: _______________________________________________
```

---

## Phase 2: Functionality Testing

### Test 2.1: Add Gallery Items via MetaBox

**Steps:**
1. Edit một Page
2. Trong metabox "VN Gallery", click "Thêm Item"
3. **Item 1 (Image):**
   - Loại: Hình ảnh
   - Upload một ảnh
   - Tiêu đề: "Test Image 1"
   - Mô tả: "This is a test image"
4. **Item 2 (Video):**
   - Loại: Video
   - Upload một ảnh thumbnail
   - Video URL: `https://www.youtube.com/watch?v=dQw4w9WgXcQ`
   - Tiêu đề: "Test Video 1"
   - Mô tả: "This is a test video"
5. Thêm thêm 2-3 items nữa (mix image/video)
6. Save page

**Expected:**
- ✅ Items được save
- ✅ Thumbnail hiển thị trong admin
- ✅ Video URL field chỉ hiển thị khi chọn "Video"

**Actual Result:**
```
[ ] Pass  [ ] Fail
Notes: _______________________________________________
```

### Test 2.2: Render via UX Builder

**Steps:**
1. Mở Page vừa tạo với UX Builder
2. Thêm element "VN Gallery"
3. Nhập Field ID: `vn_gallery_items`
4. Bật "Hiển thị Nút Lọc"
5. Save và view page

**Expected:**
- ✅ Gallery hiển thị với grid layout
- ✅ 3 filter buttons hiển thị: Tất cả, Hình ảnh, Video
- ✅ Tất cả items hiển thị đúng thumbnail
- ✅ Video items có play icon overlay

**Actual Result:**
```
[ ] Pass  [ ] Fail
Notes: _______________________________________________
```

### Test 2.3: Render via Shortcode

**Steps:**
1. Tạo Page mới (không dùng UX Builder)
2. Thêm shortcode: `[vn_gallery field="vn_gallery_items"]`
3. Save và view page

**Expected:**
- ✅ Kết quả giống Test 2.2
- ✅ Gallery render đúng

**Actual Result:**
```
[ ] Pass  [ ] Fail
Notes: _______________________________________________
```

### Test 2.4: Filter Functionality

**Steps:**
1. View page có gallery
2. Click nút "Hình ảnh"
3. Click nút "Video"
4. Click nút "Tất cả"

**Expected:**
- ✅ Click "Hình ảnh": Chỉ image items hiển thị
- ✅ Click "Video": Chỉ video items hiển thị
- ✅ Click "Tất cả": Tất cả items hiển thị
- ✅ Active state highlight đúng nút

**Actual Result:**
```
[ ] Pass  [ ] Fail
Notes: _______________________________________________
```

### Test 2.5: Magnific Popup - Images

**Steps:**
1. View page có gallery
2. Click vào một image item

**Expected:**
- ✅ Lightbox mở
- ✅ Image full size hiển thị
- ✅ Title hiển thị
- ✅ Description hiển thị (nếu có)
- ✅ Navigation arrows hoạt động
- ✅ Keyboard navigation (←/→) hoạt động
- ✅ ESC để đóng

**Actual Result:**
```
[ ] Pass  [ ] Fail
Notes: _______________________________________________
```

### Test 2.6: Magnific Popup - Videos

**Steps:**
1. View page có gallery
2. Click vào một video item

**Expected:**
- ✅ Lightbox mở với iframe
- ✅ Video YouTube/Vimeo load và play
- ✅ Title hiển thị
- ✅ Description hiển thị (nếu có)
- ✅ Navigation arrows hoạt động

**Actual Result:**
```
[ ] Pass  [ ] Fail
Notes: _______________________________________________
```

### Test 2.7: Filter + Popup Integration

**Steps:**
1. View page có gallery
2. Click filter "Hình ảnh"
3. Click vào một image
4. Navigate bằng arrows

**Expected:**
- ✅ Lightbox chỉ navigate qua các images (không jump to videos)
- ✅ Không có lỗi console

**Repeat for "Video" filter:**
- ✅ Lightbox chỉ navigate qua videos

**Actual Result:**
```
[ ] Pass  [ ] Fail
Notes: _______________________________________________
```

---

## Phase 3: Error Handling Testing

### Test 3.1: Missing Field ID

**Steps:**
1. Thêm shortcode: `[vn_gallery]` (không có field)
2. View page as Admin
3. View page as logged-out user

**Expected:**
- ✅ **Admin sees:** Red error box với message "Lỗi VN Gallery: Vui lòng cung cấp field"
- ✅ **User sees:** Không có gì (hoặc HTML comment)

**Actual Result:**
```
[ ] Pass  [ ] Fail
Notes: _______________________________________________
```

### Test 3.2: MetaBox Not Active

**Steps:**
1. Deactivate Meta Box plugin
2. View page có shortcode
3. View as Admin

**Expected:**
- ✅ **Admin sees:** Error "MetaBox.io không được kích hoạt"
- ✅ Không có PHP fatal error

**Actual Result:**
```
[ ] Pass  [ ] Fail
Notes: _______________________________________________
```

### Test 3.3: Invalid Field ID

**Steps:**
1. Shortcode: `[vn_gallery field="nonexistent_field"]`
2. View as Admin

**Expected:**
- ✅ Error message: "Không tìm thấy dữ liệu cho trường..."

**Actual Result:**
```
[ ] Pass  [ ] Fail
Notes: _______________________________________________
```

### Test 3.4: Empty Gallery Data

**Steps:**
1. Tạo page mới với metabox "VN Gallery"
2. Không thêm items (để trống)
3. Thêm shortcode
4. View as Admin

**Expected:**
- ✅ Error message cho admin
- ✅ Không có PHP warnings

**Actual Result:**
```
[ ] Pass  [ ] Fail
Notes: _______________________________________________
```

### Test 3.5: Flatsome Not Active

**Steps:**
1. Switch sang theme khác (không phải Flatsome)
2. View page có shortcode

**Expected:**
- ✅ Shortcode vẫn render (gallery vẫn hiển thị)
- ✅ UX Builder element không xuất hiện (expected)
- ✅ Không có fatal errors

**Note:** Plugin phụ thuộc vào Magnific Popup của Flatsome. Nếu không có, cần manual load library.

**Actual Result:**
```
[ ] Pass  [ ] Fail
Notes: _______________________________________________
```

---

## Phase 4: Performance & Compatibility Testing

### Test 4.1: Conditional Asset Loading

**Steps:**
1. Tạo page KHÔNG có shortcode
2. View page source
3. Search cho `vn-lightbox-gallery`

**Expected:**
- ✅ CSS `frontend-style.css` KHÔNG được load
- ✅ JS `frontend-main.js` KHÔNG được load

**Steps:**
4. View page CÓ shortcode
5. View page source

**Expected:**
- ✅ CSS được load trong `<head>` hoặc footer
- ✅ JS được load trước `</body>`

**Actual Result:**
```
[ ] Pass  [ ] Fail
Notes: _______________________________________________
```

### Test 4.2: Responsive Design

**Devices to test:**
- [ ] Desktop (1920x1080)
- [ ] Tablet (768x1024)
- [ ] Mobile (375x667)

**Steps:**
1. View gallery page
2. Resize browser / use responsive mode

**Expected:**
- ✅ **Desktop:** 4-5 columns
- ✅ **Tablet:** 2-3 columns
- ✅ **Mobile:** 2 columns
- ✅ Filter buttons không vỡ layout
- ✅ Touch gestures hoạt động trên mobile

**Actual Result:**
```
[ ] Pass  [ ] Fail
Device: _______________
Notes: _______________________________________________
```

### Test 4.3: Browser Compatibility

**Browsers to test:**
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)

**Steps:**
1. View gallery page
2. Test all features (filter, popup)

**Expected:**
- ✅ Gallery render đúng
- ✅ Filter hoạt động
- ✅ Popup hoạt động
- ✅ Không có console errors

**Actual Result:**
```
Browser: _______________
[ ] Pass  [ ] Fail
Notes: _______________________________________________
```

### Test 4.4: Performance Metrics

**Tools:** Chrome DevTools > Performance

**Steps:**
1. View gallery page (10+ items)
2. Record performance
3. Click filters, open popups

**Expected:**
- ✅ First Contentful Paint < 2s
- ✅ No layout shifts (CLS < 0.1)
- ✅ Smooth animations (60fps)
- ✅ No memory leaks

**Actual Result:**
```
[ ] Pass  [ ] Fail
FCP: _______  CLS: _______
Notes: _______________________________________________
```

### Test 4.5: Plugin Conflict Testing

**Common plugins to test with:**
- [ ] WooCommerce
- [ ] Contact Form 7
- [ ] Yoast SEO
- [ ] Other gallery plugins

**Steps:**
1. Activate plugin
2. View gallery page
3. Check functionality

**Expected:**
- ✅ No JavaScript conflicts
- ✅ Gallery vẫn hoạt động
- ✅ No CSS conflicts

**Actual Result:**
```
Plugin: _______________
[ ] Pass  [ ] Fail
Notes: _______________________________________________
```

---

## Phase 5: Edge Cases & Stress Testing

### Test 5.1: Large Gallery (100+ Items)

**Steps:**
1. Tạo gallery với 100+ items
2. View page

**Expected:**
- ✅ Page load < 5s
- ✅ Filter không lag
- ✅ Popup navigation smooth

**Actual Result:**
```
[ ] Pass  [ ] Fail
Items: _______  Load time: _______
Notes: _______________________________________________
```

### Test 5.2: Special Characters in Titles

**Steps:**
1. Thêm item với title: `Test "Quote" & <Tag>`
2. Thêm item với title: `Tiếng Việt có dấu`
3. View page, open popup

**Expected:**
- ✅ Characters hiển thị đúng (escaped)
- ✅ Không có XSS vulnerabilities

**Actual Result:**
```
[ ] Pass  [ ] Fail
Notes: _______________________________________________
```

### Test 5.3: Post ID Override

**Steps:**
1. Tạo Page A có gallery data
2. Tạo Page B KHÔNG có gallery data
3. Trên Page B, thêm shortcode: `[vn_gallery field="vn_gallery_items" post_id="123"]` (ID của Page A)
4. View Page B

**Expected:**
- ✅ Gallery từ Page A hiển thị trên Page B

**Actual Result:**
```
[ ] Pass  [ ] Fail
Notes: _______________________________________________
```

---

## Bug Report Template

Nếu phát hiện bug, sử dụng template này:

```markdown
### Bug Report #___

**Severity:** [ ] Critical  [ ] High  [ ] Medium  [ ] Low

**Test:** Test X.X - [Test Name]

**Environment:**
- WordPress: _______
- PHP: _______
- Theme: _______
- Browser: _______

**Steps to Reproduce:**
1. 
2. 
3. 

**Expected Behavior:**

**Actual Behavior:**

**Screenshots/Console Errors:**

**Workaround (if any):**
```

---

## Final Checklist

Trước khi release:

- [ ] Tất cả Phase 1-4 tests pass
- [ ] Không có critical/high bugs
- [ ] Code review hoàn thành
- [ ] Documentation đầy đủ
- [ ] CHANGELOG.md updated
- [ ] Version number correct

**Tested by:** _______________
**Date:** _______________
**Signature:** _______________
