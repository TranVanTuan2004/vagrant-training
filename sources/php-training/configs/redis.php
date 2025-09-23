<?php
// Cấu hình Redis
define('REDIS_HOST', 'web-redis');
define('REDIS_PORT', 6379);
define('REDIS_PASSWORD', ''); // Không có password cho Redis local
define('REDIS_DATABASE', 0);

// Cấu hình session Redis
define('REDIS_SESSION_PREFIX', 'php_session:');
define('REDIS_SESSION_TTL', 3600); // 1 giờ (3600 giây)
define('REDIS_USER_SESSION_PREFIX', 'user_session:');
define('REDIS_USER_SESSION_TTL', 7200); // 2 giờ (7200 giây)
?>
