HCMUS Auto Homepage 2.2.0

Cài đặt: Plugin -> Cài mới -> Tải plugin lên -> chọn file zip (nếu đã có bản cũ, chọn "Thay thế bản hiện tại") -> Kích hoạt.
Sau khi cài, vào Trang quản trị một lần: plugin tự tạo 2 trang "Dòng xe" và "Dự toán chi phí".

Cấu trúc nhiều trang:
- Trang chủ: giới thiệu chung (mục tiêu & sứ mệnh, triết lý kinh doanh, showroom). Sửa nội dung tại Cài đặt -> HCMUS Auto – Nội dung.
- Dòng xe (/dong-xe/): thanh tìm kiếm (tên xe/từ khóa, hãng xe, phân khúc giá) + danh sách xe theo loại hình. Trang Cửa hàng và danh mục của WooCommerce tự chuyển về trang này.
- Dự toán chi phí (/du-toan-chi-phi/): chi phí lăn bánh + trả góp. Mức thu chỉnh tại Cài đặt -> HCMUS Auto – Chi phí.
- Chi tiết xe: chia thành 5 mục có địa chỉ riêng: Tổng quan, Mô tả, Thông số, Chi phí lăn bánh, Đánh giá (?hcmus_tab=...).
- Header chỉ còn nút MENU và logo. Mọi liên kết (Dòng xe, Dự toán chi phí, Giỏ hàng, Thanh toán, Tài khoản) nằm trong menu trượt.
- Xe điện: Cài đặt -> HCMUS Auto – Chi phí có ngày hết hạn ưu đãi trước bạ và mức tính sau ngày đó.
- Danh sách xe bỏ qua sản phẩm ẩn khỏi danh mục (và hết hàng nếu WooCommerce bật ẩn hết hàng); kết quả tìm kiếm có phân trang.
- Hiệu ứng chuyển cảnh: màn trượt khi đổi trang, các khối hiện dần khi cuộn (tự tắt nếu thiết bị bật giảm chuyển động).

Hãng xe: nếu WooCommerce có sẵn mục Thương hiệu (Brands) thì dùng mục đó; nếu không, plugin tạo mục Sản phẩm -> Hãng xe.
Shortcode: [hcmus_auto_home] [hcmus_auto_cars per_category="4"] [hcmus_auto_cost]
Không xóa/sửa sản phẩm, đơn hàng, tài khoản hay trang giỏ hàng/thanh toán.
Ảnh banner lấy từ Unsplash, cần Internet.

- v2.2.0: Giỏ hàng, Thanh toán (kèm trang đặt hàng thành công) và Tài khoản dùng chung header, footer, hiệu ứng chuyển cảnh với phần còn lại. Có thanh các bước Giỏ hàng > Thanh toán > Hoàn tất. Hỗ trợ cả giao diện WooCommerce cổ điển (shortcode) và dạng khối (Blocks).
