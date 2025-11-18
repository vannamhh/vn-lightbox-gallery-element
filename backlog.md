Kế hoạch Phát triển (Backlog) - VN Gallery v4.0

Product Owner: Sarah (👩‍💼 Bmad PO)

Tài liệu này chuyển đổi bản Phân tích Kỹ thuật (bổ sung PRD) thành một kế hoạch phát triển có cấu trúc, bao gồm các Epic và User Story, sẵn sàng để đưa vào sprint.

Epic 1: Tích hợp Lõi Plugin & UX Builder

Mục tiêu: Thiết lập nền tảng plugin và đăng ký element "VN Gallery" vào Flatsome UX Builder.

Story 1.1: Thiết lập Cấu trúc Plugin

Là một Dev, tôi muốn có cấu trúc tệp plugin cơ bản (scaffolding) để đảm bảo tuân thủ các tiêu chuẩn của WordPress và dễ dàng quản lý code.

AC 1: Tạo tệp plugin chính với header (Plugin Name, Version, Author...).

AC 2: Tạo cấu trúc thư mục (ví dụ: /includes, /assets/js, /assets/css).

AC 3: Tạo các tệp class chính (ví dụ: class-vn-gallery.php, class-vn-ux-builder.php, class-vn-shortcode.php, class-vn-assets.php) và require chúng vào tệp chính.

Story 1.2: Đăng ký Element "VN Gallery" vào UX Builder

Là một Dev, tôi muốn triển khai Class_VN_UX_Builder để đăng ký element "VN Gallery" một cách an toàn.

AC 1: Class chỉ hook vào ux_builder_setup nếu function_exists('ux_builder_add_map') trả về true.

AC 2: Element "VN Gallery" được đăng ký bằng ux_builder_add_map với tên (Name) "VN Gallery" và danh mục (Category) "Content".

AC 3: Element phải có 3 tùy chọn (options) trong UX Builder:

field: (Text) "MetaBox Field ID", default: ''.

filters: (Checkbox) "Hiển thị Lọc", default: 'true'.

post_id: (Text) "Post ID (Tùy chọn)", default: ''.

AC 4: Element được liên kết với shortcode [vn_gallery].

Story 1.3: (Task Kỹ thuật) Xác thực Tích hợp UX Builder

Là một Dev, tôi cần xác thực các giả định kỹ thuật về UX Builder (theo mục 4 trong PRD) để tránh lỗi không tương thích.

AC 1: Xác nhận ux_builder_add_map là hàm chính xác để đăng ký element (bên trong hook ux_builder_setup).

AC 2: Xác nhận cấu trúc mảng options (cung cấp trong PRD mục 3.1) là đầy đủ và hiển thị chính xác trong UX Builder. Báo cáo lại nếu cần thêm thuộc tính (ví dụ: section).

Epic 2: Logic Render Shortcode & Truy xuất Dữ liệu

Mục tiêu: Render shortcode [vn_gallery] thành HTML, lấy dữ liệu từ MetaBox một cách chính xác và xử lý lỗi an toàn.

Story 2.1: Phân tích Attributes & Xử lý Lỗi Cơ bản

Là một Dev, tôi muốn Class_VN_Shortcode phân tích các attributes của shortcode và xử lý các lỗi đầu vào cơ bản.

AC 1: Shortcode [vn_gallery] được đăng ký.

AC 2: Hàm render phân tích (parse) 3 attributes: field (default: ''), post_id (default: 0), filters (default: 'true').

AC 3: Nếu field bị trống, hiển thị lỗi cho admin (sử dụng render_error).

AC 4: Nếu function_exists('rwmb_get_value') trả về false (MetaBox không hoạt động), hiển thị lỗi cho admin.

AC 5: Hàm render_error được tạo, chỉ hiển thị lỗi chi tiết nếu current_user_can('manage_options').

Story 2.2: Truy xuất Dữ liệu MetaBox & Render HTML

Là một Dev, tôi muốn lấy dữ liệu từ MetaBox dựa trên field_id và post_id để render ra cấu trúc HTML của thư viện.

AC 1: post_id được xác định: nếu atts['post_id'] > 0, sử dụng nó; nếu không, sử dụng get_the_ID().

AC 2: Dữ liệu được lấy bằng rwmb_get_value( $field_id, [ 'object_id' => $post_id ] ).

AC 3: Nếu dữ liệu rỗng hoặc không phải là mảng, hiển thị lỗi (không public).

AC 4: Cấu trúc HTML vn-gallery-wrapper và vn-gallery-grid được render.

AC 5: Gọi Class_VN_Assets::enqueue_scripts() để báo hiệu cần tải JS/CSS (sẽ được triển khai ở Epic 3).

Story 2.3: Render Các Nút Lọc (Filter)

Là một Dev, tôi muốn render các nút lọc (filter) nếu show_filters là true.

AC 1: Hàm render_filters được gọi bên trong render_shortcode nếu $show_filters là true.

AC 2: render_filters tạo ra HTML cho các nút: "Tất cả" (data-filter="*"), "Hình ảnh" (data-filter=".vn-item-image"), "Video" (data-filter=".vn-item-video").

AC 3: Nút "Tất cả" có class active mặc định.

Story 2.4: Render Từng Item (Thẻ <a>)

Là một Dev, tôi muốn hàm render_item lặp qua dữ liệu MetaBox và render từng item (hình ảnh/video) với các thẻ <a> chứa đầy đủ data-attributes.

AC 1: Tuân thủ hợp đồng dữ liệu cứng nhắc (PRD 4.3): item_type, item_image, item_url, item_title, item_description.

AC 2: Xác định data-type là 'image' hoặc 'video' dựa trên item_type.

AC 3: Render thẻ <a> với class vn-gallery-item và class động (ví dụ: vn-item-image, vn-item-video).

AC 4: Thẻ <a> phải chứa các data attributes sau:

data-type (ví dụ: 'video')

data-title (lấy từ item_title)

data-description (lấy từ item_description)

AC 5: href của thẻ <a> là item_url (nếu là video) hoặc URL ảnh (nếu là ảnh, ví dụ: item_image['full_url']).

AC 6: Bên trong thẻ <a> là thumbnail của ảnh (ví dụ: item_image['thumbnail_url']).

Epic 3: Tương tác Frontend (JS/CSS) & Hoàn thiện

Mục tiêu: Kích hoạt Magnific Popup, làm cho bộ lọc (filter) hoạt động, và thêm CSS cơ bản.

Story 3.1: Tải Assets (JS/CSS)

Là một Dev, tôi muốn Class_VN_Assets chỉ tải JS/CSS khi shortcode [vn_gallery] được sử dụng trên trang.

AC 1: Class_VN_Assets có phương thức tĩnh (ví dụ: enqueue_scripts()) để bật cờ (flag).

AC 2: frontend-main.js và frontend-style.css được đăng ký (register) nhưng không enqueue.

AC 3: Hook vào wp_footer (hoặc tương đương), nếu cờ được bật, thì mới enqueue các scripts/styles đã đăng ký.

Story 3.2: Khởi tạo Magnific Popup

Là một Dev, tôi muốn frontend-main.js khởi tạo Magnific Popup cho tất cả gallery trên trang và xử lý nội dung hỗn hợp (mixed content).

AC 1: JS kiểm tra nếu $.fn.magnificPopup tồn tại.

AC 2: JS lặp qua mỗi .vn-gallery-grid và gọi initMagnificPopup.

AC 3: initMagnificPopup sử dụng delegate: 'a.vn-gallery-item:visible'.

AC 4: (PRD 4.6) Callback elementParse được sử dụng: nếu item.el.data('type') === 'video', thì item.type = 'iframe'; ngược lại item.type = 'image'.

AC 5: (PRD 2.1) Callback titleSrc (cho cả image và iframe) được sử dụng để hiển thị data-title và data-description (nếu có) theo định dạng "Title<small>Description</small>".

Story 3.3: Triển khai Logic Lọc (Filtering)

Là một Dev, tôi muốn logic lọc trong frontend-main.js hoạt động và khởi tạo lại Magnific Popup một cách chính xác.

AC 1: Bắt sự kiện click trên .vn-filter-btn.

AC 2: Cập nhật class active cho nút được click.

AC 3: Lấy data-filter và sử dụng .show() / .hide() trên các .vn-gallery-item tương ứng. (Nếu * thì .show() tất cả).

AC 4: (Quan trọng nhất) Sau khi lọc, JS phải gọi $galleryGrid.magnificPopup('destroy') và sau đó gọi lại initMagnificPopup($galleryGrid) để đảm bảo popup chỉ hoạt động trên các item đang hiển thị.

Story 3.4: Thêm CSS Cơ bản

Là một Dev, tôi muốn thêm CSS cơ bản (frontend-style.css) để thư viện ảnh hiển thị ở dạng lưới (grid) và các nút lọc (filter) hoạt động.

AC 1: CSS cho .vn-gallery-grid (sử dụng CSS Grid hoặc Flexbox) để hiển thị các item thành lưới.

AC 2: CSS cho các nút lọc (filter) và class .active.

AC 3: Đảm bảo .vn-gallery-item bị .hide() (từ JS) không chiếm không gian.