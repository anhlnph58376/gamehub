<div align="center">

# 🎮 GameHub — Cổng Game HTML5 Trực Tuyến Thế Hệ Mới

**Nền tảng cổng game HTML5 trực tuyến chuẩn quốc tế (tương tự CrazyGames / Poki) xây dựng trên Laravel 12, MySQL 8, Tailwind CSS, Alpine.js, Howler.js & Phaser 3.**

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-Dark_Gaming_UI-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![Phaser 3](https://img.shields.io/badge/Audio_&_Game-Howler.js_%7C_Phaser_3-7C5CFF?style=for-the-badge)](https://phaser.io)
[![Tests](https://img.shields.io/badge/Tests-20_Passed_(100%25)-10B981?style=for-the-badge)](#%EF%B8%8F-kiểm-thử-tự-động-automated-testing)

</div>

---

## 📖 Giới Thiệu Dự Án

**GameHub** là hệ thống cổng trò chơi trực tuyến (Web Gaming Portal) cho phép người chơi trải nghiệm tức thì hàng trăm tựa game HTML5 2D ngay trên trình duyệt máy tính và điện thoại mà không cần cài đặt. Đi kèm là **Game Upload Center** dành cho Quản trị viên để đăng tải, kiểm duyệt bảo mật tự động, quản lý phiên bản (Version Control) và khôi phục (1-Click Rollback) các gói trò chơi `.zip`.

### ✨ Điểm Nhấn Công Nghệ & Tính Năng Cốt Lõi

- 🎨 **Giao Diện Chuẩn CrazyGames / Poki (`#10121B` Dark Mode):**
  - Thiết kế phân cấp thị giác rõ ràng, hiệu ứng hover mượt mà, skeleton loading, danh mục trượt ngang (Quick Categories), tìm kiếm tức thì **Instant Search (`Ctrl + K`)**, nút **Chơi Ngẫu Nhiên (`🎲`)**, và thanh điều hướng đáy (Bottom Navigation Bar) tối ưu cho thiết bị di động.
- 📦 **Game Upload Center & Quản Lý Phiên Bản (`.zip` Pipeline):**
  - Tự động kiểm tra chữ ký nhị phân **Magic Bytes**, chống **Zip Slip (Path Traversal)**, chống **ZIP Bomb**, và chặn tuyệt đối các tệp thực thi máy chủ (`.php`, `.phar`, `.sh`, `.exe`, `.htaccess`...).
  - Tự động chuẩn hóa thư mục gốc chứa `index.html`, đọc metadata từ `game.json`, chấm điểm tương thích (`CompatibilityCheckerService`), hỗ trợ **Preview (Chơi thử)** và **Rollback phiên bản chỉ với 1 cú nhấp chuột**.
- 🛡️ **Cô Lập Bảo Mật Iframe Sandbox & Giao Tiếp `postMessage`:**
  - Nhúng game trong `<iframe sandbox="allow-scripts allow-same-origin">`, ngăn chặn truy cập trái phép hoặc tự ý chuyển hướng trang chính (`allow-top-navigation` bị khóa).
  - Giao tiếp `window.postMessage` xác thực `origin` nghiêm ngặt để ghi nhận điểm số kỷ lục, thời lượng phiên chơi và tự động mở khóa **Huy hiệu Thành tích (Achievements)**.
- 🔊 **Hệ Thống Âm Thanh Đa Tầng (Howler.js & Phaser 3 Audio SDK):**
  - Âm thanh giao diện Website (Click, Hover, Thông báo, Modal, Nhạc nền chuyển đổi mượt theo khu vực có Fade-in/Fade-out) quản lý bởi **Howler.js**.
  - Trình quản lý **Global Audio Settings** (Master, BGM, UI, Game Volume, Mute All) đồng bộ hai chiều giữa `localStorage` và tài khoản người dùng, truyền trực tiếp vào Iframe game.
  - Tích hợp **Phaser 3 Sound Manager SDK** (`gamehub-phaser-audio.js`) và trang **Quản trị Âm thanh (`/admin/audio`)** kèm kiểm soát bản quyền/giấy phép.
- 🔐 **Xác Thực Đa Phương Thức & Bảo Mật Tài Khoản (OAuth 2.0 + Email OTP + RBAC):**
  - Hỗ trợ **Đăng nhập 1 chạm bằng tài khoản Google (Gmail) & Facebook** theo chuẩn OAuth 2.0 (có kiểm tra `state` chống CSRF và tự động đồng bộ hồ sơ/avatar).
  - Hỗ trợ cả **Đăng nhập bằng Mật khẩu** và **Đăng nhập không mật khẩu bằng Mã xác thực 6 chữ số gửi về Email (Email OTP)**.
  - Luồng **Quên mật khẩu gửi mã OTP 6 chữ số về Email** (băm SHA-256, hiệu lực 10 phút, giới hạn tối đa 5 lần nhập sai chống Brute-Force).
  - Phân quyền **RBAC** (`Super Admin`, `Admin`, `User`), bảo vệ tuyến đường bằng `throttle` Rate Limiting, OWASP Security Headers và Nhật ký kiểm toán (`Audit Logs`).
- 🕹️ **Tích Hợp Sẵn 3 Tựa Game HTML5 Mẫu Chơi Được Ngay (PC & Mobile Touch):**
  1. **Pixel Runner Siêu Tốc** (`games/sample-games/pixel-runner`) — Endless Runner nhảy đôi vượt chướng ngại vật Neon.
  2. **Chiến Cơ Ngân Hà** (`games/sample-games/space-shooter`) — Arcade bắn phi thuyền không gian theo đợt.
  3. **Xếp Khối Tinh Thể** (`games/sample-games/puzzle-blocks`) — Puzzle kéo thả khối tinh thể vào lưới 8×8 tính điểm Combo.

---

## 🛠️ Yêu Cầu Hệ Thống

- **PHP:** `>= 8.2` (khuyến nghị PHP 8.2 / 8.3 / 8.4 / 8.5 với các extension: `pdo_mysql`, `zip`, `mbstring`, `fileinfo`, `gd`, `openssl`)
- **Cơ sở dữ liệu:** MySQL `>= 8.0` hoặc MariaDB `>= 10.6`
- **Quản lý gói:** Composer `>= 2.x`

---

## 🚀 Hướng Dẫn Cài Đặt Nhanh (Từ GitHub)

### 1. Clone kho mã nguồn & cài đặt thư viện
```bash
git clone https://github.com/anhlnph58376/gamehub.git
cd gamehub
composer install
```

### 2. Khởi tạo tệp môi trường `.env`
```bash
cp .env.example .env
php artisan key:generate
```
*(Trên Windows PowerShell / CMD có thể dùng `copy .env.example .env`)*.

### 3. Cấu hình Cơ sở dữ liệu & Email trong `.env`
Mở tệp `.env` và cập nhật thông tin kết nối MySQL (cùng SMTP nếu muốn gửi mã OTP qua email thực tế):
```env
APP_NAME="GameHub"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gamehub
DB_USERNAME=root
DB_PASSWORD=

# Cấu hình SMTP gửi mã OTP Đăng nhập & Quên mật khẩu (để 'log' nếu chạy thử cục bộ)
MAIL_MAILER=log
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="support@gamehub.local"
MAIL_FROM_NAME="${APP_NAME}"
```

### 4. Chạy Migration, Seeder & Liên kết Storage
Lệnh `--seed` sẽ tự động khởi tạo danh mục, quyền hạn RBAC, tổng hợp kho âm thanh chuẩn (WAV PCM 16-bit), đóng gói `.zip` và xuất bản 3 tựa game HTML5 mẫu:
```bash
php artisan migrate:fresh --seed
php artisan storage:link
```

### 5. Khởi chạy ứng dụng
```bash
php artisan serve
```
Truy cập ngay trên trình duyệt tại: **`http://127.0.0.1:8000`**

---

## 🔑 Quản Lý Tài Khoản Khởi Tạo (Seeder)

Để đảm bảo an toàn bảo mật trên Git, dự án **không lưu mật khẩu mặc định trong mã nguồn**. Khi chạy `php artisan migrate:fresh --seed`:
- Bạn có thể chủ động đặt mật khẩu mong muốn trong tệp `.env` cục bộ thông qua các biến:
  ```env
  SEED_SUPER_ADMIN_EMAIL="admin@gamehub.local"
  SEED_SUPER_ADMIN_PASSWORD="YourStrongPasswordHere"
  SEED_ADMIN_EMAIL="moderator@gamehub.local"
  SEED_ADMIN_PASSWORD="YourAdminPasswordHere"
  SEED_USER_EMAIL="player@gamehub.local"
  SEED_USER_PASSWORD="YourPlayerPasswordHere"
  ```
- Nếu các biến mật khẩu trên để trống, `DatabaseSeeder` sẽ tự động sinh **mật khẩu ngẫu nhiên 24 ký tự** cho từng tài khoản và in trực tiếp ra màn hình Terminal.

| Vai trò | Biến Email (`.env`) | Quyền hạn chính |
|---|---|---|
| **Super Admin** | `SEED_SUPER_ADMIN_EMAIL` | Toàn quyền hệ thống, Quản lý người dùng, Upload Center & Âm thanh (`/admin`) |
| **Admin (Biên tập viên)** | `SEED_ADMIN_EMAIL` | Quản lý kho game, danh mục, kiểm duyệt đánh giá & xử lý báo lỗi (`/admin`) |
| **Người chơi (Player)** | `SEED_USER_EMAIL` | Chơi game, lưu kỷ lục điểm số, mở khóa thành tích, yêu thích & đánh giá sao |

---

## 📂 Cấu Trúc Thư Mục Chính

```text
gamehub/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          # Quản trị Game, Audio, Danh mục, User, CMS, Báo lỗi
│   │   │   ├── Auth/           # Đăng nhập (Mật khẩu & Email OTP), Đăng ký, Quên mật khẩu
│   │   │   ├── Game/           # Trang chủ, Khám phá, Chơi game, Phiên chơi, Cài đặt Audio
│   │   │   └── User/           # Hồ sơ cá nhân, Yêu thích, Lịch sử chơi
│   │   └── Middleware/         # RBAC (RoleMiddleware), Khóa tài khoản, OWASP Security Headers
│   ├── Mail/                   # AuthOtpMail gửi mã xác thực 6 chữ số
│   ├── Models/                 # 28 Eloquent Models (Game, GameVersion, AudioAsset, User...)
│   └── Services/
│       ├── Audio/              # AudioSynthesizerService & AudioUploadService
│       ├── Auth/               # EmailOtpService (SHA-256 OTP, chống Brute-Force)
│       ├── GameManagement/     # Quản lý vòng đời Game & Thống kê Dashboard
│       └── GameUpload/         # ZipValidator, ZipExtractor, CompatibilityChecker, VersionService
├── config/
│   └── gamehub.php             # Cấu hình giới hạn ZIP, danh sách đuôi file cấm & Sandbox
├── database/
│   ├── migrations/             # 29 tệp Migrations chuẩn 3NF
│   └── seeders/                # DatabaseSeeder tự động đóng gói 3 game mẫu & kho âm thanh
├── games/
│   └── sample-games/           # Mã nguồn 3 game HTML5 mẫu (pixel-runner, space-shooter, puzzle-blocks)
├── public/
│   └── js/                     # SDK Âm thanh (gamehub-audio.js & gamehub-phaser-audio.js)
├── resources/
│   └── views/                  # Giao diện Blade (Dark Gaming Portal, Admin Portal, Email OTP)
├── routes/
│   └── web.php                 # Định tuyến Public, Auth, API & Admin (kèm Rate Limiting)
└── tests/
    └── Feature/                # Bộ kiểm thử tự động PHPUnit (Auth, OTP, Audio, ZIP Security, E2E)
```

---

## 🧪 Kiểm Thử Tự Động (Automated Testing)

Hệ thống đi kèm bộ kiểm thử tự động kiểm tra toàn diện các luồng: Xác thực & Phân quyền RBAC, Đăng nhập & Khôi phục mật khẩu bằng mã Email OTP, Bảo mật tải lên gói ZIP (chống Zip Slip, chặn file `.php` giả mạo, kiểm tra Magic Bytes), Hệ thống Âm thanh và Luồng E2E từ lúc Admin đăng game đến khi Người chơi ghi điểm:

```bash
php artisan test
```

---

## 📚 Tài Liệu Kỹ Thuật Chi Tiết

- 📄 [PRD.md](PRD.md) — Đặc tả yêu cầu sản phẩm & Nghiệp vụ
- 🏗️ [ARCHITECTURE.md](ARCHITECTURE.md) — Kiến trúc hệ thống & Luồng xử lý kỹ thuật
- 🗄️ [DATABASE.md](DATABASE.md) — Thiết kế cơ sở dữ liệu & Sơ đồ quan hệ bảng
- 🔌 [API.md](API.md) — Tài liệu các điểm cuối HTTP JSON & Giao thức `postMessage`
- 🛡️ [SECURITY.md](SECURITY.md) — Kiến trúc bảo mật 7 lớp ZIP, Iframe Sandbox & OWASP Headers
- 🚀 [DEPLOYMENT.md](DEPLOYMENT.md) — Hướng dẫn triển khai Production (Nginx/Apache, SSL, Subdomain Sandbox)
- 🎮 [USER_GUIDE.md](USER_GUIDE.md) — Hướng dẫn sử dụng dành cho Người chơi
- ⚙️ [ADMIN_GUIDE.md](ADMIN_GUIDE.md) — Cẩm nang vận hành dành cho Quản trị viên

---

## 📄 Giấy Phép (License)

Dự án được phát hành theo giấy phép [MIT License](https://opensource.org/licenses/MIT). Toàn bộ tài nguyên âm thanh mặc định được tổng hợp trực tiếp bởi `AudioSynthesizerService` thuộc phạm vi **CC0 1.0 Public Domain**.
