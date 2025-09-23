# Hướng dẫn sử dụng Redis Session

## Tổng quan
Dự án đã được tích hợp Redis để quản lý phiên đăng nhập của user, kết hợp với localStorage để tối ưu hiệu suất.

## Kiến trúc

### 1. Redis (Server-side)
- **Mục đích**: Lưu trữ session thực sự, bảo mật cao
- **Dữ liệu**: Session ID, thông tin user, thời gian tạo, IP address, User Agent
- **TTL**: 2 giờ (7200 giây)
- **Prefix**: `user_session:` cho session, `user_sessions:` cho danh sách sessions của user

### 2. localStorage (Client-side)
- **Mục đích**: Lưu trữ tạm thời để hiển thị UI nhanh chóng
- **Dữ liệu**: Session ID, thông tin user cơ bản, trạng thái đăng nhập
- **Cấu trúc**:
```json
{
  "session_id": "abc123...",
  "user": {
    "id": 1,
    "name": "admin",
    "fullname": "Administrator"
  },
  "login_time": "2024-01-01T00:00:00.000Z",
  "is_logged_in": true
}
```

## Các file đã được tạo/cập nhật

### 1. Cấu hình
- `configs/redis.php`: Cấu hình kết nối Redis
- `docker-compose.yml`: Thêm Redis service
- `config/backend/Dockerfile`: Cài đặt Redis extension cho PHP

### 2. Models
- `models/SessionManager.php`: Class quản lý session với Redis

### 3. Helpers
- `auth_helper.php`: Helper kiểm tra authentication

### 4. Controllers
- `login.php`: Đăng nhập và tạo session
- `logout.php`: Đăng xuất và xóa session
- `list_users.php`: Kiểm tra session trước khi hiển thị

### 5. Views
- `views/header.php`: Hiển thị thông tin user và nút logout

## Cách hoạt động

### 1. Đăng nhập
1. User nhập username/password
2. Server xác thực với database
3. Nếu thành công:
   - Tạo session trong Redis với TTL 2 giờ
   - Trả về session_id và thông tin user
   - Client lưu vào localStorage

### 2. Truy cập trang
1. Client gửi session_id qua AJAX
2. Server kiểm tra session trong Redis
3. Nếu hợp lệ: cho phép truy cập
4. Nếu không hợp lệ: redirect về login

### 3. Đăng xuất
1. Client gửi session_id đến server
2. Server xóa session khỏi Redis
3. Client xóa dữ liệu khỏi localStorage
4. Reload trang

## Lợi ích

### 1. Bảo mật
- Session được lưu trên server (Redis)
- Client chỉ lưu session_id, không lưu thông tin nhạy cảm
- Có thể kiểm soát session từ server

### 2. Hiệu suất
- localStorage giúp hiển thị UI nhanh chóng
- Redis giúp xử lý session nhanh hơn database
- TTL tự động xóa session hết hạn

### 3. Scalability
- Redis có thể scale horizontal
- Session có thể được chia sẻ giữa nhiều server
- Dễ dàng quản lý session tập trung

## Cách sử dụng

### 1. Khởi động dự án
```bash
cd sources
docker-compose up -d
```

### 2. Kiểm tra Redis
```bash
docker exec -it web-redis redis-cli
> keys user_session:*
> keys user_sessions:*
```

### 3. Xem session của user
```bash
docker exec -it web-redis redis-cli
> get user_session:abc123...
> smembers user_sessions:1
```

## Troubleshooting

### 1. Redis không kết nối được
- Kiểm tra Redis container đã chạy: `docker ps`
- Kiểm tra logs: `docker logs web-redis`
- Kiểm tra cấu hình trong `configs/redis.php`

### 2. Session không được tạo
- Kiểm tra Redis extension đã cài: `php -m | grep redis`
- Kiểm tra logs PHP: `docker logs web-backend`
- Kiểm tra kết nối database

### 3. Session bị mất
- Kiểm tra TTL của session: `ttl user_session:abc123...`
- Kiểm tra Redis có đủ memory không
- Kiểm tra Redis có restart không

## Mở rộng

### 1. Thêm thông tin session
Sửa trong `SessionManager::createUserSession()`:
```php
$sessionData = [
    'user_id' => $userId,
    'user_data' => $userData,
    'created_at' => time(),
    'last_activity' => time(),
    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
    'device_info' => 'mobile', // Thêm thông tin mới
    'location' => 'VN' // Thêm thông tin mới
];
```

### 2. Thay đổi TTL
Sửa trong `configs/redis.php`:
```php
define('REDIS_USER_SESSION_TTL', 14400); // 4 giờ
```

### 3. Thêm cleanup job
Tạo cron job để dọn dẹp session hết hạn:
```php
// cleanup_sessions.php
$sessionManager = new SessionManager();
$sessionManager->cleanupExpiredSessions();
```

## Kết luận
Hệ thống Redis session đã được tích hợp thành công, kết hợp với localStorage để tối ưu hiệu suất và bảo mật. User có thể đăng nhập/đăng xuất bình thường, và session được quản lý an toàn trên server.
