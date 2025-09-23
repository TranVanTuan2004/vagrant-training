# Hướng dẫn khắc phục lỗi Redis

## Lỗi hiện tại
```
Fatal error: Uncaught Error: Class "Redis" not found
```

## Nguyên nhân
Redis extension chưa được cài đặt trong PHP container.

## Giải pháp

### 1. Kiểm tra Redis extension
Truy cập: http://localhost:8080/test_redis.php

### 2. Rebuild container với Redis extension
```bash
# Dừng containers
docker compose down

# Rebuild và khởi động lại
docker compose up --build -d

# Kiểm tra logs
docker logs web-backend
```

### 3. Kiểm tra Redis extension trong container
```bash
# Vào container PHP
docker exec -it web-backend bash

# Kiểm tra Redis extension
php -m | grep redis

# Hoặc kiểm tra bằng PHP
php -r "echo extension_loaded('redis') ? 'Redis OK' : 'Redis NOT FOUND';"
```

### 4. Nếu vẫn lỗi, sử dụng File Fallback
Hệ thống đã được thiết kế để tự động fallback sang file-based session khi Redis không khả dụng.

**Kiểm tra:**
- Sessions sẽ được lưu trong thư mục `sources/php-training/sessions/`
- Tạo thư mục nếu chưa có: `mkdir -p sources/php-training/sessions`

### 5. Cài đặt Redis extension thủ công (nếu cần)
```bash
# Vào container
docker exec -it web-backend bash

# Cài đặt Redis extension
pecl install redis
echo "extension=redis.so" >> /usr/local/etc/php/conf.d/redis.ini

# Restart Apache
apache2ctl restart
```

## Kiểm tra hoạt động

### 1. Test Redis connection
```bash
# Vào Redis container
docker exec -it web-redis redis-cli

# Test commands
> ping
> set test "Hello Redis"
> get test
> keys *
```

### 2. Test PHP Redis
```bash
# Vào PHP container
docker exec -it web-backend php -r "
\$redis = new Redis();
\$redis->connect('web-redis', 6379);
echo 'Redis connection: ' . (\$redis->ping() ? 'OK' : 'FAILED');
"
```

### 3. Test session
1. Truy cập: http://localhost:8080/login.php
2. Đăng nhập với tài khoản bất kỳ
3. Kiểm tra session được tạo:
   - Redis: `docker exec -it web-redis redis-cli keys user_session:*`
   - File: `ls -la sources/php-training/sessions/`

## Fallback Mode

Khi Redis không khả dụng, hệ thống sẽ tự động chuyển sang file-based session:

### Ưu điểm:
- ✅ Hoạt động ngay lập tức
- ✅ Không cần Redis
- ✅ Dữ liệu session được lưu trữ an toàn

### Nhược điểm:
- ❌ Chậm hơn Redis
- ❌ Không scale được với nhiều server
- ❌ Cần cleanup thủ công

### Cấu trúc file session:
```
sources/php-training/sessions/
├── user_session_abc123.json    # Session data
├── user_session_def456.json    # Session data
├── user_sessions_1.json        # User 1 sessions list
└── user_sessions_2.json        # User 2 sessions list
```

## Monitoring

### 1. Kiểm tra logs
```bash
# PHP logs
docker logs web-backend

# Redis logs
docker logs web-redis

# Tất cả logs
docker compose logs
```

### 2. Kiểm tra session storage
```bash
# Redis sessions
docker exec -it web-redis redis-cli keys user_session:*

# File sessions
ls -la sources/php-training/sessions/
```

### 3. Cleanup sessions
```bash
# Redis cleanup (tự động với TTL)
docker exec -it web-redis redis-cli flushdb

# File cleanup
rm -rf sources/php-training/sessions/*
```

## Kết luận

1. **Nhanh nhất**: Rebuild container với `docker compose up --build -d`
2. **Tạm thời**: Hệ thống sẽ tự động dùng file fallback
3. **Lâu dài**: Cài đặt Redis extension đúng cách

Sau khi khắc phục, hệ thống sẽ hoạt động bình thường với Redis session management! 🚀
