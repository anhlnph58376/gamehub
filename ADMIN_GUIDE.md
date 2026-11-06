# CẨM NANG QUẢN TRỊ VIÊN (ADMIN GUIDE) — GAMEHUB

Tài liệu này hướng dẫn chi tiết cách vận hành hệ thống **GameHub Admin Portal** dành cho các tài khoản có vai trò **Super Admin** và **Admin**.

---

## 1. Đăng nhập và Truy cập Khu vực Quản trị

1. Truy cập trang đăng nhập tại `/dang-nhap` (hoặc `/login`).
2. Sử dụng thông tin tài khoản Quản trị viên được cấu hình riêng trong tệp `.env` của máy chủ (tuyệt đối không lưu mật khẩu thực tế lên Git):
   - **Tổng Quản Trị (Super Admin)**: Khai báo qua `SEED_SUPER_ADMIN_EMAIL` và `SEED_SUPER_ADMIN_PASSWORD` trong `.env`.
   - **Biên Tập Viên (Admin)**: Khai báo qua `SEED_ADMIN_EMAIL` và `SEED_ADMIN_PASSWORD` trong `.env`.
   - **Người chơi (Player)**: Khai báo qua `SEED_USER_EMAIL` và `SEED_USER_PASSWORD` trong `.env`.
   *(Lưu ý: Nếu các biến mật khẩu để trống khi chạy `php artisan db:seed`, hệ thống sẽ tự động sinh mật khẩu ngẫu nhiên 24 ký tự và in một lần duy nhất trên giao diện dòng lệnh CLI).*
3. Sau khi đăng nhập, nhấn vào nút **"Quản trị"** trên thanh điều hướng góc phải hoặc truy cập trực tiếp đường dẫn `/admin`.

---

## 2. Bảng Điều khiển Thống kê (`/admin`)

Trang tổng quan cung cấp số liệu thời gian thực:
- **Tổng số Game** (kèm số lượng game đang phát hành trực tuyến).
- **Tổng số Người dùng** và trạng thái hoạt động.
- **Tổng Lượt chơi** toàn hệ thống và số lượt chơi phát sinh trong ngày hôm nay.
- **Cảnh báo Kiểm duyệt**: Số lượng báo lỗi từ người chơi đang chờ xử lý và đánh giá cần kiểm duyệt.
- **Top Game Thịnh hành**: Bảng xếp hạng các tựa game có lượt chơi và điểm đánh giá cao nhất.

---

## 3. Quy trình Đăng tải & Quản lý Game HTML5 (`/admin/games`)

### Bước 1: Tạo Hồ sơ Game mới (`/admin/games/create`)
1. Nhập **Tên trò chơi**, chọn **Danh mục chính**, các **Thẻ (Tags)** phụ.
2. Cấu hình thông số khung hình: Chiều rộng/chiều cao mặc định (ví dụ `800x600`), hướng màn hình (`Ngang - Landscape` hoặc `Dọc - Portrait`), hỗ trợ Mobile/Fullscreen.
3. Tải lên **Ảnh đại diện (Thumbnail)** và **Ảnh bìa (Banner)**.
4. Tải lên gói trò chơi HTML5 định dạng **`.zip`** (tối đa 150MB) và nhập số hiệu phiên bản (ví dụ: `1.0.0`).

### Bước 2: Kiểm tra Báo cáo Tương thích & Bảo mật tự động
Ngay khi bạn nhấn lưu, hệ thống sẽ tự động:
1. Kiểm tra chữ ký số PKZIP, quét ngăn chặn mã độc server-side (`.php`, `.sh`, `.exe`...), chống Zip Slip và ZIP Bomb.
2. Giải nén gói game vào thư mục cô lập `storage/app/public/games/{slug}/v{version}/`.
3. Phân tích file `index.html` và trả về **Báo cáo tương thích** (đầy đủ thẻ viewport, tài nguyên đường dẫn tương đối, không chứa frame-buster).

### Bước 3: Chơi thử (Preview), Phát hành (Publish) & Rollback 1-Click
Tại trang chỉnh sửa game (`/admin/games/{game}/edit`):
- **Chơi thử (Preview)**: Nhấn nút *"Preview"* ở bất kỳ phiên bản nào để kiểm tra game chạy thực tế trong khung Iframe trước khi công khai.
- **Phát hành (Publish)**: Nhấn *"Phát hành"* để đưa phiên bản đó lên làm phiên bản chính thức phục vụ người chơi ngoài trang chủ.
- **Khôi phục phiên bản (1-Click Rollback)**: Nếu phiên bản mới gặp lỗi phát sinh, bạn chỉ cần nhấn nút **"Rollback về bản này"** ở phiên bản ổn định trước đó. Hệ thống sẽ lập tức chuyển hướng người chơi về phiên bản cũ mà không làm gián đoạn dịch vụ.

---

## 4. Quản lý Danh mục & Thẻ (`/admin/categories`, `/admin/tags`)

- Thêm mới, chỉnh sửa tên, đường dẫn tĩnh (`slug`), biểu tượng (`icon`), thứ tự hiển thị và trạng thái Bật/Tắt của các danh mục trò chơi.
- Hệ thống tự động ngăn chặn việc xóa các danh mục đang chứa trò chơi để đảm bảo toàn vẹn dữ liệu.

---

## 5. Quản lý Người dùng & Phân quyền (`/admin/users`)

- Tra cứu người dùng theo tên, email hoặc lọc theo vai trò (`Super Admin`, `Admin`, `User`) và trạng thái (`Hoạt động`, `Tạm khóa`, `Cấm vĩnh viễn`).
- **Khóa / Mở khóa nhanh**: Nhấn nút khóa tài khoản đối với người dùng vi phạm; hệ thống sẽ lập tức hủy phiên đăng nhập của người dùng đó.
- **Thay đổi vai trò**: Bổ nhiệm hoặc thu hồi quyền Quản trị viên.

---

## 6. Kiểm duyệt Đánh giá & Xử lý Báo lỗi (`/admin/reviews`, `/admin/reports`)

- **Đánh giá (`/admin/reviews`)**: Duyệt hiển thị hoặc ẩn/xóa các bình luận spam, ngôn từ không phù hợp. Điểm sao trung bình của game sẽ tự động cập nhật lại.
- **Báo lỗi (`/admin/reports`)**: Xem chi tiết lỗi do người chơi gửi về (loại lỗi, mô tả, thông tin trình duyệt), cập nhật trạng thái xử lý (`Đang kiểm tra`, `Đã khắc phục`, `Từ chối`) và ghi chú phản hồi của Admin.

---

## 7. Quản trị Nội dung CMS & Nhật ký Kiểm toán (`/admin/cms`)

- **Quản lý Banner Trang chủ**: Tạo mới, bật/tắt hoặc xóa các chiến dịch banner quảng bá game nổi bật trên trang chủ.
- **Cấu hình Hệ thống (`Settings`)**: Cập nhật tên cổng game, khẩu hiệu, mô tả SEO chuẩn và thông tin liên hệ.
- **Nhật ký Kiểm toán (`Audit Logs`)**: Theo dõi minh bạch mọi hành động của đội ngũ quản trị (Ai đã thực hiện, vào thời gian nào, từ địa chỉ IP nào, thay đổi dữ liệu gì).

