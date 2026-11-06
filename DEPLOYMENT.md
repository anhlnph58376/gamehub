# HƯỚNG DẪN TRIỂN KHAI HỆ THỐNG (DEPLOYMENT GUIDE) — GAMEHUB

Tài liệu này hướng dẫn chi tiết cách cài đặt, cấu hình và triển khai cổng game HTML5 **GameHub** trên hai môi trường:
1. **Môi trường Phát triển cục bộ (Windows 10/11 + Laragon)**
2. **Môi trường Production (Linux Ubuntu 22.04/24.04 + Nginx + PHP 8.2+ + MySQL 8.0)**

---

## PHẦN 1: Triển khai trên Windows 10/11 với Laragon (Local Development)

### 1. Yêu cầu hệ thống
- **Laragon Full** (tích hợp sẵn Apache/Nginx, PHP 8.2+, MySQL 8.0, Composer, Node.js, Git).
- PHP Extensions bắt buộc: `zip`, `pdo_mysql`, `mbstring`, `exif`, `pcntl`, `bcmath`, `gd`, `fileinfo`, `openssl`.

### 2. Các bước cài đặt
1. **Đặt mã nguồn vào thư mục gốc của Laragon**:
   ```powershell
   cd H:\laragon\www\webgame
   ```
2. **Cài đặt thư viện PHP qua Composer**:
   ```powershell
   composer install
   ```
3. **Thiết lập file cấu hình `.env`**:
   ```powershell
   copy .env.example .env
   php artisan key:generate
   ```
   Cấu hình thông số kết nối MySQL trong `.env`:
   ```env
   APP_NAME="GameHub"
   APP_ENV=local
   APP_DEBUG=true
   APP_URL=http://webgame.test

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=gamehub
   DB_USERNAME=root
   DB_PASSWORD=

   FILESYSTEM_DISK=public
   GAME_SANDBOX_DOMAIN=http://webgame.test
   ```
4. **Khởi tạo cơ sở dữ liệu và nạp dữ liệu mẫu (kèm 3 game HTML5 mẫu)**:
   ```powershell
   php artisan migrate:fresh --seed
   ```
5. **Tạo liên kết thư mục lưu trữ công khai (`public/storage`)**:
   ```powershell
   php artisan storage:link
   ```
6. **Xóa bộ nhớ đệm và chạy thử nghiệm**:
   ```powershell
   php artisan optimize:clear
   php artisan test
   ```
7. **Truy cập ứng dụng**:
   - Qua Virtual Host của Laragon: `http://webgame.test` hoặc `http://localhost/webgame/public`
   - Hoặc chạy built-in server: `php artisan serve` -> `http://127.0.0.1:8000`

---

## PHẦN 2: Triển khai trên Môi trường Production (Ubuntu + Nginx + MySQL 8)

### 1. Cấu hình PHP (`php.ini`) cho Upload Game ZIP dung lượng lớn
Mở file `/etc/php/8.2/fpm/php.ini` và cập nhật các thông số sau để cho phép upload gói game `.zip` tối đa 150MB:
```ini
upload_max_filesize = 160M
post_max_size = 165M
memory_limit = 512M
max_execution_time = 120
```
Khởi động lại PHP-FPM:
```bash
sudo systemctl restart php8.2-fpm
```

### 2. Cấu hình Tên miền Tách biệt cho Sandbox Game (`GAME_SANDBOX_DOMAIN`)
Để đạt mức độ bảo mật cao nhất (ngăn chặn hoàn toàn mã JS trong game truy cập Cookie của trang chính), bạn nên cấu hình 2 tên miền:
- Tên miền chính: `https://gamehub.vn`
- Tên miền phục vụ file tĩnh của game: `https://sandbox.gamehub.vn`

#### Cấu hình Nginx cho Tên miền chính (`gamehub.vn`):
```nginx
server {
    listen 80;
    server_name gamehub.vn www.gamehub.vn;
    root /var/www/webgame/public;

    index index.php index.html;
    client_max_body_size 165M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

#### Cấu hình Nginx cho Tên miền Sandbox (`sandbox.gamehub.vn`):
Tên miền này chỉ phục vụ các file tĩnh HTML5/JS/CSS/Audio trong `storage/app/public/games` và **tuyệt đối không thực thi PHP**:
```nginx
server {
    listen 80;
    server_name sandbox.gamehub.vn;
    root /var/www/webgame/public;

    # Chặn hoàn toàn việc thực thi PHP trên miền sandbox
    location ~* \.(php|phtml|phar|sh|pl|py|cgi)$ {
        return 403;
    }

    location /storage/games/ {
        add_header Access-Control-Allow-Origin "https://gamehub.vn";
        add_header X-Content-Type-Options "nosniff";
        expires 7d;
        try_files $uri =404;
    }
}
```

### 3. Tối ưu hóa Hiệu năng Laravel trên Production
Chạy các lệnh sau mỗi lần cập nhật phiên bản mới:
```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 4. Phân quyền thư mục chuẩn trên Linux
```bash
sudo chown -R www-data:www-data /var/www/webgame/storage /var/www/webgame/bootstrap/cache
sudo chmod -R 775 /var/www/webgame/storage /var/www/webgame/bootstrap/cache
```

