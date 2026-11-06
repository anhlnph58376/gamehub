# HƯỚNG DẪN SỬ DỤNG DÀNH CHO NGƯỜI CHƠI (USER GUIDE) — GAMEHUB

Chào mừng bạn đến với **GameHub** — Cổng trò chơi HTML5 trực tuyến tốc độ cao, nơi bạn có thể trải nghiệm hàng loạt tựa game hấp dẫn ngay trên trình duyệt máy tính và điện thoại mà không cần cài đặt.

---

## 1. Khám phá và Tìm kiếm Trò chơi

1. **Trang chủ (`/`)**:
   - Hiển thị **Banner nổi bật**, danh sách **Game Nổi Bật (Featured)**, **Game Đang Thịnh Hành (Trending)**, **Game Mới Ra Mắt**, và **Top Điểm Cao Toàn Hệ Thống**.
2. **Tìm kiếm tức thì (Instant Search)**:
   - Nhấp vào thanh tìm kiếm ở đầu trang (hoặc nhấn phím tắt `/`), gõ từ khóa tên game (ví dụ: `Pixel`, `Space`, `Puzzle`).
   - Hệ thống sẽ tự động gợi ý các tựa game phù hợp kèm ảnh đại diện, danh mục và điểm đánh giá chỉ sau 0.25 giây.
3. **Bộ lọc Khám phá (`/games`)**:
   - Lọc theo **Danh mục** (Hành động, Giải đố, Arcade, Thể thao, Chiến thuật, Phiêu lưu).
   - Lọc theo **Thẻ (Tag)** hoặc **Thiết bị hỗ trợ** (Máy tính, Điện thoại).
   - Sắp xếp theo: *Phổ biến nhất*, *Mới nhất*, hoặc *Đánh giá cao nhất*.

---

## 2. Trải nghiệm Chơi Game (`/games/{slug}/play`)

Khi nhấn nút **"Chơi Ngay"** trên bất kỳ tựa game nào:
1. **Màn hình chơi game chuyên dụng (Cinema Mode)**:
   - Giao diện tối màu giúp tập trung hoàn toàn vào khung hình trò chơi.
2. **Thanh công cụ điều khiển bên dưới khung game**:
   - **Yêu thích**: Lưu game vào bộ sưu tập cá nhân của bạn để truy cập nhanh lần sau.
   - **Tắt/Bật tiếng**: Điều khiển trạng thái âm thanh.
   - **Chơi lại (Reload)**: Khởi động lại màn chơi tức thì nếu bạn muốn thử lại từ đầu.
   - **Toàn màn hình (Fullscreen)**: Mở rộng khung hình game ra toàn bộ màn hình máy tính hoặc điện thoại.
   - **Báo lỗi**: Gửi thông báo trực tiếp cho Quản trị viên nếu bạn phát hiện lỗi âm thanh, hình ảnh hoặc không tải được game.

---

## 3. Lưu Điểm Cao (Leaderboard) & Mở khóa Thành tựu

1. **Tự động ghi nhận kỷ lục**:
   - Khi bạn đăng nhập tài khoản và chơi game, mỗi khi kết thúc màn chơi (`GAME_OVER`) hoặc cập nhật điểm số (`SCORE_UPDATE`), hệ thống sẽ tự động lưu lại kỷ lục cao nhất của bạn vào **Bảng Xếp Hạng (Leaderboard)** của game đó.
   - Nếu bạn chưa đăng nhập, giao diện sẽ hiển thị lời nhắc đăng nhập để lưu điểm số lên bảng vàng.
2. **Hệ thống Thành tựu (Achievements)**:
   - Mỗi tựa game có các mốc thành tựu riêng (ví dụ: đạt mốc điểm kỷ lục, hoàn thành màn chơi). Khi bạn đạt điều kiện, thành tựu sẽ tự động được mở khóa và cộng điểm thưởng vào hồ sơ cá nhân.

---

## 4. Quản lý Tài khoản Cá nhân

Sau khi đăng ký hoặc đăng nhập tại `/login`:
- **Hồ sơ cá nhân (`/user/profile`)**:
  - Cập nhật Tên hiển thị, Tiểu sử (Bio), Ảnh đại diện (Avatar) và đổi mật khẩu bảo mật.
  - Xem thống kê tổng lượt chơi, số game yêu thích và danh sách thành tựu đã mở khóa.
- **Game Yêu thích (`/user/favorites`)**:
  - Danh sách tất cả các trò chơi bạn đã thả tim.
- **Lịch sử Chơi game (`/user/history`)**:
  - Xem lại các tựa game bạn đã chơi gần đây cùng điểm số đạt được và thời lượng trải nghiệm.

---

## 5. Đánh giá & Bình luận Trò chơi

Tại trang chi tiết của mỗi game (`/games/{slug}`):
- Bạn có thể chọn số sao (từ **1 sao** đến **5 sao**) và viết nhận xét chia sẻ cảm nhận, mẹo chơi cho cộng đồng.
- Điểm đánh giá trung bình của game sẽ được hệ thống tự động tính toán lại ngay lập tức.

