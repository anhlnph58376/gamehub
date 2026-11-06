# TÀI LIỆU BẢO MẬT (SECURITY ARCHITECTURE) — GAMEHUB

Tài liệu này mô tả chi tiết mô hình bảo mật đa lớp (Defense-in-Depth) của hệ thống **GameHub**, tập trung vào 3 bề mặt tấn công trọng yếu của một cổng game HTML5 trực tuyến: **Upload gói ZIP từ bên ngoài**, **Thực thi mã JavaScript của game trong trình duyệt (Iframe Sandbox)**, và **Giao tiếp sự kiện `postMessage` giữa Game và Hệ thống**.

---

## 1. Bảo mật Quy trình Upload & Giải nén Game ZIP (7 Lớp Kiểm tra)

Được cài đặt tại [`ZipValidatorService`](file:///h:/laragon/www/webgame/app/Services/GameUpload/ZipValidatorService.php) và [`ZipExtractorService`](file:///h:/laragon/www/webgame/app/Services/GameUpload/ZipExtractorService.php):

1. **Kiểm tra Magic Bytes (File Signature)**:
   - Không tin tưởng hoàn toàn vào đuôi file `.zip` hoặc `Content-Type` từ client.
   - Đọc 4 byte đầu tiên của file nhị phân để đối chiếu với chữ ký chuẩn của định dạng PKZIP (`50 4B 03 04`, `50 4B 05 06`, hoặc `50 4B 07 08`).

2. **Chống tấn công Zip Slip (Path Traversal)**:
   - Duyệt qua từng entry trong gói ZIP trước khi giải nén.
   - Từ chối ngay lập tức nếu tên đường dẫn chứa chuỗi `../`, `..\`, bắt đầu bằng `/` hoặc `\`, hoặc chứa ký tự đường dẫn ổ đĩa Windows (`:`).
   - Khi giải nén thực tế trong `ZipExtractorService`, hệ thống tiếp tục chuẩn hóa đường dẫn và loại bỏ mọi phân đoạn `..`.

3. **Chống tấn công ZIP Bomb (Decompression Bomb)**:
   - **Giới hạn dung lượng file ZIP tải lên**: Tối đa `150 MB` (`MAX_ZIP_SIZE_BYTES`).
   - **Giới hạn tổng dung lượng sau giải nén**: Tối đa `350 MB` (`MAX_UNCOMPRESSED_BYTES`).
   - **Giới hạn số lượng file trong 1 gói ZIP**: Tối đa `2.500 files` (`MAX_FILE_COUNT`).
   - **Giới hạn tỷ lệ nén (Compression Ratio)**: Nếu tổng dung lượng giải nén vượt quá `80 lần` (`MAX_COMPRESSION_RATIO`) dung lượng nén ban đầu, hệ thống lập tức hủy bỏ và ghi nhận cảnh báo ZIP Bomb.

4. **Danh sách đen (Blacklist) các phần mở rộng thực thi máy chủ**:
   - Mọi file trong gói ZIP có phần mở rộng thuộc danh sách nguy hiểm đều bị chặn đứng:
     `.php`, `.phtml`, `.php3`, `.php4`, `.php5`, `.php7`, `.php8`, `.phar`, `.htaccess`, `.htpasswd`, `.ini`, `.sh`, `.bash`, `.exe`, `.bat`, `.cmd`, `.ps1`, `.vbs`, `.dll`, `.so`, `.py`, `.rb`, `.pl`, `.cgi`, `.jsp`, `.asp`, `.aspx`, `.env`, `.sql`.
   - Kiểm tra cả **Double Extension** (ví dụ: `avatar.php.png` hoặc `shell.phar.js`) bằng cách phân tách tất cả các dấu chấm trong tên file.

5. **Kiểm tra điểm vào bắt buộc (`index.html`)**:
   - Đảm bảo gói ZIP có ít nhất một file `index.html` ở thư mục gốc (hoặc thư mục con cấp 1 duy nhất, hệ thống sẽ tự động chuẩn hóa phẳng khi giải nén).

6. **Tự động chuẩn hóa và làm sạch `game.json`**:
   - Nếu gói ZIP có kèm file manifest `game.json`, hệ thống dùng `strip_tags()` và giới hạn độ dài để làm sạch dữ liệu trước khi lưu vào bảng `game_versions`.

7. **Kiểm tra tính tương thích & Frame-Buster ([`CompatibilityCheckerService`](file:///h:/laragon/www/webgame/app/Services/GameUpload/CompatibilityCheckerService.php))**:
   - Phân tích nội dung `index.html` để phát hiện các đoạn mã cố tình phá vỡ khung Iframe (`top.location = ...`, `parent.location = ...`), kiểm tra đường dẫn tương đối của tài nguyên CSS/JS/hình ảnh và thẻ `<meta name="viewport">`.

---

## 2. Cơ chế Cô lập Iframe Sandbox & Subdomain Isolation

Khi người chơi mở trang chơi game ([`resources/views/games/play.blade.php`](file:///h:/laragon/www/webgame/resources/views/games/play.blade.php)):

1. **Thuộc tính `sandbox` nghiêm ngặt**:
   ```html
   <iframe
       id="game-iframe"
       src="{{ $launchUrl }}"
       sandbox="allow-scripts allow-same-origin"
       allow="fullscreen; autoplay; gamepad"
   ></iframe>
   ```
   - **Không cấp** `allow-top-navigation`: Ngăn chặn game tự ý chuyển hướng cửa sổ trình duyệt chính của người dùng sang trang lừa đảo (phishing).
   - **Không cấp** `allow-popups` / `allow-modals`: Ngăn chặn game mở cửa sổ quảng cáo độc hại hoặc hộp thoại `alert()`/`prompt()` làm treo trình duyệt.
   - **Không cấp** `allow-forms`: Ngăn chặn việc gửi form giả mạo từ bên trong khung game.

2. **Tách biệt tên miền phụ (`GAME_SANDBOX_DOMAIN`)**:
   - Trong môi trường Production, cấu hình biến môi trường `GAME_SANDBOX_DOMAIN=https://games-sandbox.yourdomain.com`.
   - Khi đó, mã JavaScript của game chạy trên một **Origin hoàn toàn khác** với cổng chính (`https://yourdomain.com`). Do chính sách Same-Origin Policy của trình duyệt, mã JS trong game **không thể đọc được Cookie phiên đăng nhập (`laravel_session`, `XSRF-TOKEN`) hay `localStorage` của cổng chính**.

---

## 3. Bảo mật Giao tiếp `window.postMessage`

Cổng giao tiếp giữa Iframe game và trang chủ được bảo vệ ở cả phía Client (`play.blade.php`) và phía Server (`InteractionController::recordEvent`):

1. **Xác thực Origin của thông điệp**:
   - Chỉ chấp nhận sự kiện từ `window.location.origin` hoặc từ `GAME_SANDBOX_DOMAIN` đã cấu hình.
2. **Danh sách trắng (Whitelist) loại sự kiện**:
   - Chỉ cho phép các sự kiện chuẩn: `GAME_READY`, `GAME_START`, `SCORE_UPDATE`, `GAME_OVER`, `UNLOCK_ACHIEVEMENT`, `SAVE_PROGRESS`.
3. **Chống giả mạo điểm số & Giới hạn tần suất (Rate Limiting)**:
   - Endpoint `POST /games/{slug}/event` được bảo vệ bởi CSRF Token của trang chủ và giới hạn tần suất `throttle:60,1` (tối đa 60 sự kiện/phút/IP).
   - Điểm số được ép kiểu số nguyên không âm (`min:0`, `max:999999999`) và chỉ cập nhật vào `leaderboards` nếu điểm mới cao hơn kỷ lục hiện tại của người chơi.

---

## 4. Phân quyền RBAC, Bảo vệ Tài khoản & HTTP Security Headers

1. **Role-Based Access Control (RBAC)**:
   - Middleware [`RoleMiddleware`](file:///h:/laragon/www/webgame/app/Http/Middleware/RoleMiddleware.php) kiểm tra vai trò `super-admin`, `admin`, `user`.
   - Middleware [`EnsureUserIsActive`](file:///h:/laragon/www/webgame/app/Http/Middleware/EnsureUserIsActive.php) kiểm tra trạng thái tài khoản (`active`, `locked`, `banned`) ở mỗi request; nếu tài khoản bị khóa trong khi đang đăng nhập, hệ thống lập tức hủy phiên và đăng xuất.
2. **HTTP Security Headers ([`SecurityHeadersMiddleware`](file:///h:/laragon/www/webgame/app/Http/Middleware/SecurityHeadersMiddleware.php))**:
   - Ẩn hoàn toàn `X-Powered-By` để chống dò phiên bản máy chủ (Server Fingerprinting).
   - `X-Content-Type-Options: nosniff`
   - `X-Frame-Options: SAMEORIGIN` & `Content-Security-Policy: frame-ancestors 'self'; base-uri 'self'; form-action 'self'; object-src 'none'`
   - `Referrer-Policy: strict-origin-when-cross-origin`
   - `X-Permitted-Cross-Domain-Policies: none`
   - `Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=()`
   - `Strict-Transport-Security: max-age=31536000; includeSubDomains` (tự động kích hoạt trên HTTPS / Production).
3. **Giới hạn Tần suất Chống Brute-Force & Spam (`Throttle` trong [`routes/web.php`](file:///h:/laragon/www/webgame/routes/web.php))**:
   - Đăng nhập (`POST /dang-nhap`): `throttle:6,1` (tối đa 6 lần/phút/IP).
   - Đăng ký & Quên mật khẩu (`POST /dang-ky`, `POST /quen-mat-khau`): `throttle:5,1`.
   - Gửi báo lỗi & Liên hệ (`POST /games/{game}/report`, `POST /lien-he`): `throttle:10,1`.
   - Đánh giá & Cập nhật hồ sơ (`POST /games/{game}/reviews`, `PUT /tai-khoan`): `throttle:15,1`.
4. **Chặn Thực Thi Mã Độc Trong Thư Mục Upload ([`storage/app/public/.htaccess`](file:///h:/laragon/www/webgame/storage/app/public/.htaccess))**:
   - Vô hiệu hóa `ExecCGI`, tắt `php_flag engine off`, và chặn truy cập mọi tệp có đuôi thực thi (`.php`, `.phtml`, `.phar`, `.sh`, `.exe`, `.bat`, `.cmd`, `.env`, `.htaccess`) bên trong `storage/app/public/`.
   - Tệp [`.htaccess`](file:///h:/laragon/www/webgame/.htaccess) gốc chặn truy cập trực tiếp vào `.env`, `.git`, `composer.json`, `phpunit.xml`, `artisan` và các thư mục nội bộ (`app/`, `config/`, `database/`, `storage/logs/`, `vendor/`).
5. **Quản lý Bí mật khi Đưa lên Git ([`.gitignore`](file:///h:/laragon/www/webgame/.gitignore) & [`.env.example`](file:///h:/laragon/www/webgame/.env.example))**:
   - Không lưu cứng bất kỳ mật khẩu mặc định nào trong mã nguồn ([`config/gamehub.php`](file:///h:/laragon/www/webgame/config/gamehub.php), [`DatabaseSeeder.php`](file:///h:/laragon/www/webgame/database/seeders/DatabaseSeeder.php)) hay tài liệu Markdown.
   - Tệp `.env`, khóa bảo mật (`*.key`, `*.pem`), bản sao lưu CSDL (`*.sql`, `*.sqlite`) và tài nguyên sinh tự động đều được loại trừ trong [`.gitignore`](file:///h:/laragon/www/webgame/.gitignore).
6. **Nhật ký Kiểm toán (`audit_logs`)**:
   - Mọi thao tác quản trị nhạy cảm (upload phiên bản mới, phát hành game, rollback phiên bản, tải lên/xóa âm thanh, khóa tài khoản, xóa đánh giá) đều được ghi nhận đầy đủ vào bảng `audit_logs` kèm theo địa chỉ IP, User-Agent, dữ liệu trước và sau thay đổi.

