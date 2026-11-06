# 📋 PRD.md - Tài Liệu Đặc Tả Yêu Cầu Sản Phẩm (Product Requirements Document)

## 1. Mục Tiêu Sản Phẩm
Xây dựng nền tảng cổng game trực tuyến **GameHub** cho phép người dùng trải nghiệm tức thì hàng trăm tựa game HTML5 2D trên trình duyệt máy tính và điện thoại mà không cần cài đặt, đồng thời cung cấp cho Quản trị viên công cụ **Game Upload Center** để phát hành và cập nhật phiên bản game qua tệp `.zip` an toàn tuyệt đối.

## 2. Đối Tượng Người Dùng
1. **Khách truy cập (Guest):** Khám phá kho game, tìm kiếm tức thì, chơi game ngay trên trình duyệt, xem bảng xếp hạng và gửi báo cáo lỗi.
2. **Thành viên (Registered Player):** Lưu danh sách game yêu thích, lưu lịch sử chơi tự động, ghi danh bảng vàng điểm số cao, mở khóa huy hiệu thành tích (Achievements), chấm điểm 1-5 sao và viết nhận xét.
3. **Quản trị viên (Admin / Super Admin):** Quản lý kho game, tải lên tệp `.zip` phiên bản mới, kiểm tra báo cáo tương thích, chơi thử (Preview), xuất bản (Publish) hoặc khôi phục (Rollback), quản lý danh mục, người dùng, bình luận và CMS trang chủ.

## 3. Yêu Cầu Chức Năng Cốt Lõi
- **Trang chủ 11 khu vực động:** Thông báo hệ thống, Hero Banner, Danh mục chủ đề, Game nổi bật, Game phổ biến, Game mới cập nhật, Bảng xếp hạng chơi nhiều nhất, Game đánh giá cao, Đề xuất thông minh, Đã chơi gần đây và Banner giới thiệu.
- **Game Upload Center:** Kiểm tra bảo mật đa tầng (Magic Bytes, Zip Slip, ZIP Bomb, chặn tệp thực thi), tự động đọc `game.json`, kiểm tra tài nguyên tĩnh trong `index.html`.
- **Core Game Player:** Khung chơi game tối ưu diện tích, chế độ Toàn màn hình (Fullscreen API), nút tải lại game, bật/tắt âm thanh qua `postMessage`, cảnh báo xoay ngang màn hình di động.

## 4. Yêu Cầu Phi Chức Năng
- **Hiệu năng:** Sử dụng Eager Loading (`with()`) chống lỗi N+1 query, đánh chỉ mục (Index) đầy đủ các cột lọc/sắp xếp, lưu đệm (Cache) cấu hình và banner.
- **Tương thích:** Đáp ứng hoàn hảo trên các độ phân giải Desktop (`1920px`), Laptop (`1366px`), Tablet (`768px`) và Mobile (`375px` - `430px`).

