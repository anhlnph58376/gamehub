# 🎮 GameHub - Nền Tảng Cổng Game HTML5 Trực Tuyến Thế Hệ Mới

**GameHub** là hệ thống cổng game HTML5 trực tuyến toàn diện (tương tự Poki, CrazyGames, Y8), được xây dựng trên nền tảng **Laravel 12**, **MySQL 8**, **Livewire 3**, **Alpine.js** và **Tailwind CSS** theo phong cách thiết kế **Modern Dark Gaming Portal (`#10121B`)**.

---

## ✨ Tính Năng Nổi Bật

1. **Game Upload Center & Quản Lý Phiên Bản (Version Control):**
   - Quản trị viên tải lên hàng trăm trò chơi HTML5 qua giao diện web chỉ bằng tệp nén `.zip`.
   - Tự động kiểm tra chữ ký **Magic Bytes**, chống **Zip Slip (Path Traversal)**, chống **ZIP Bomb**, và chặn tuyệt đối tệp thực thi phía máy chủ (`.php`, `.phar`, `.exe`, `.sh`, `.htaccess`...).
   - Tự động nhận diện `index.html`, đọc và làm sạch metadata từ `game.json`, kiểm tra tính tương thích (`CompatibilityCheckerService`).
   - Hỗ trợ **Preview (Chơi thử)** trước khi **Publish (Xuất bản)** và **Rollback về phiên bản cũ chỉ với 1 click**.
2. **Cơ Chế Cách Ly Bảo Mật Iframe Sandbox:**
   - Nhúng game trong `<iframe sandbox="allow-scripts allow-same-origin">`, ngăn chặn truy cập cookie hoặc điều hướng trang chính (`allow-top-navigation` bị cấm).
   - Hỗ trợ cấu hình `GAME_SANDBOX_DOMAIN` để phân phối static game từ subdomain/origin độc lập khi triển khai thực tế.
   - Giao tiếp `postMessage` kiểm tra chặt `event.origin` và schema dữ liệu để ghi nhận điểm số và mở khóa Huy hiệu Thành tích.
3. **Giao Diện Modern Dark Gaming UI (`#10121B`):**
   - Trang chủ 11 khu vực động từ cơ sở dữ liệu, thanh tìm kiếm tức thì **Livewire 3 Instant Search**, bộ lọc đa tiêu chí, bảng xếp hạng điểm cao, đánh giá 1-5 sao và báo cáo lỗi.
4. **Tích Hợp Sẵn 3 Tựa Game HTML5 Mẫu Chơi Được 100% (PC & Cảm Ứng Mobile):**
   - `games/sample-games/pixel-runner` (**Pixel Runner Siêu Tốc** - Endless Runner nhảy đôi).
   - `games/sample-games/space-shooter` (**Chiến Cơ Ngân Hà** - Arcade bắn tàu vũ trụ theo đợt).
   - `games/sample-games/puzzle-blocks` (**Xếp Khối Tinh Thể** - Puzzle ghép khối lưới 8x8).

---

## 🚀 Hướng Dẫn Cài Đặt Trên Windows 10/11 + Laragon

```powershell
# 1. Di chuyển vào thư mục dự án
cd H:\laragon\www\webgame

# 2. Cài đặt gói phụ thuộc PHP (nếu chưa có thư mục vendor)
composer install

# 3. Sao chép tệp môi trường và tạo khóa ứng dụng
copy .env.example .env
php artisan key:generate

# 4. Chạy Migration và Seeder (Tự động đóng gói & nạp 3 game HTML5 mẫu)
php artisan migrate:fresh --seed

# 5. Tạo liên kết thư mục lưu trữ công khai
php artisan storage:link

# 6. Khởi chạy máy chủ phát triển
php artisan serve
```

Truy cập ngay: **http://localhost:8000** (hoặc `http://webgame.test` trên Laragon).

---

## 🔑 Tài Khoản Mặc Định (Môi Trường Local Dev)

| Vai trò | Email | Mật khẩu | Quyền hạn |
|---|---|---|---|
| **Super Admin** | `admin@gamehub.local` | `GameHubLocal2026!` | Toàn quyền hệ thống & Upload Center (`/admin`) |
| **Admin** | `moderator@gamehub.local` | `GameHubAdmin2026!` | Quản trị game, danh mục, đánh giá, báo cáo (`/admin`) |
| **Người chơi** | `player@gamehub.local` | `GameHubPlayer2026!` | Chơi game, lưu điểm cao, yêu thích, đánh giá |

---

## 🧪 Chạy Kiểm Thử Tự Động (PHPUnit)

```powershell
php artisan test
```

## 📚 Danh Mục Tài Liệu Hệ Thống
- [PRD.md](PRD.md) - Tài liệu đặc tả yêu cầu sản phẩm
- [DATABASE.md](DATABASE.md) - Kiến trúc cơ sở dữ liệu & Sơ đồ ERD
- [ARCHITECTURE.md](ARCHITECTURE.md) - Kiến trúc phần mềm & Luồng xử lý
- [API.md](API.md) - Tài liệu các điểm cuối HTTP & PostMessage
- [SECURITY.md](SECURITY.md) - Kiến trúc bảo mật ZIP & Iframe Sandbox
- [DEPLOYMENT.md](DEPLOYMENT.md) - Hướng dẫn triển khai Production & Laragon
- [USER_GUIDE.md](USER_GUIDE.md) - Cẩm nang hướng dẫn người chơi
- [ADMIN_GUIDE.md](ADMIN_GUIDE.md) - Cẩm nang hướng dẫn Quản trị viên
