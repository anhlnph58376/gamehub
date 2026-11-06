# 🔌 API.md - Tài Liệu Điểm Cuối HTTP & Giao Tiếp PostMessage

## 1. Các Điểm Cuối Tương Tác Trò Chơi (JSON / Web Endpoints)

### 1.1. Ghi Nhận Phiên Chơi & Điểm Số Cao
- **URL:** `POST /games/{game}/session`
- **Headers:** `X-CSRF-TOKEN`, `Accept: application/json`
- **Payload:**
```json
{
  "session_token": "550e8400-e29b-41d4-a716-446655440000",
  "duration_seconds": 120,
  "score": 450
}
```
- **Phản hồi (`200 OK`):**
```json
{
  "status": "success",
  "session_id": 15
}
```

### 1.2. Thêm / Xóa Game Yêu Thích
- **URL:** `POST /games/{game}/favorite`
- **Yêu cầu:** Đã đăng nhập (`auth`).
- **Phản hồi JSON (`Accept: application/json`):**
```json
{
  "status": "success",
  "favorited": true,
  "favorite_count": 42,
  "message": "Đã thêm game vào danh sách Yêu thích!"
}
```

### 1.3. Gửi Báo Cáo Lỗi Game / Bình Luận Vi Phạm
- **URL:** `POST /games/{game}/report`
- **Payload:**
```json
{
  "type": "bug",
  "description": "Mô tả chi tiết sự cố kỹ thuật...",
  "game_review_id": null
}
```

---

## 2. Giao Thức `window.postMessage` Giữa Game HTML5 & Cổng GameHub

### 2.1. Game Gửi Điểm Số Lên GameHub (Từ Iframe -> Trang Chính)
Khi kết thúc màn chơi (`Game Over`), trò chơi HTML5 gửi thông điệp:
```javascript
window.parent.postMessage({
    type: 'GAMEHUB_SCORE',
    score: 850
}, '*');
```
Trang chơi game của GameHub kiểm tra chặt `event.origin` và kiểu dữ liệu `typeof event.data.score === 'number'` trước khi đồng bộ điểm số về máy chủ.

### 2.2. GameHub Điều Khiển Bật/Tắt Âm Thanh (Từ Trang Chính -> Iframe)
Khi người chơi nhấn nút **Âm thanh** trên thanh công cụ, GameHub gửi thông điệp xuống Iframe:
```javascript
iframe.contentWindow.postMessage({
    type: 'GAMEHUB_MUTE_TOGGLE',
    muted: true
}, '*');
```

