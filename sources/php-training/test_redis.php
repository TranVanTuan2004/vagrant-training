<?php
// File test để kiểm tra Redis extension
echo "<h2>Kiểm tra Redis Extension</h2>";

// Kiểm tra Redis extension có được cài đặt không
if (extension_loaded('redis')) {
    echo "<p style='color: green;'>✅ Redis extension đã được cài đặt</p>";
    
    // Thử tạo Redis connection
    try {
        $redis = new Redis();
        echo "<p style='color: green;'>✅ Class Redis() có thể sử dụng được</p>";
        
        // Thử kết nối
        $connected = $redis->connect('web-redis', 6379);
        if ($connected) {
            echo "<p style='color: green;'>✅ Kết nối Redis thành công</p>";
            
            // Test set/get
            $redis->set('test_key', 'Hello Redis!');
            $value = $redis->get('test_key');
            echo "<p style='color: green;'>✅ Test set/get thành công: " . $value . "</p>";
            
            $redis->close();
        } else {
            echo "<p style='color: red;'>❌ Không thể kết nối đến Redis server</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Lỗi khi sử dụng Redis: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Redis extension chưa được cài đặt</p>";
    echo "<p>Vui lòng chạy lệnh sau để rebuild container:</p>";
    echo "<pre>docker compose down && docker compose up --build -d</pre>";
}

// Hiển thị thông tin PHP
echo "<h3>Thông tin PHP:</h3>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Loaded Extensions:</p>";
echo "<ul>";
$extensions = get_loaded_extensions();
foreach ($extensions as $ext) {
    if (strpos($ext, 'redis') !== false || strpos($ext, 'Redis') !== false) {
        echo "<li style='color: green;'><strong>$ext</strong></li>";
    } else {
        echo "<li>$ext</li>";
    }
}
echo "</ul>";

// Kiểm tra cấu hình Redis
echo "<h3>Cấu hình Redis:</h3>";
if (file_exists('configs/redis.php')) {
    echo "<p style='color: green;'>✅ File configs/redis.php tồn tại</p>";
    include 'configs/redis.php';
    echo "<p>Redis Host: " . REDIS_HOST . "</p>";
    echo "<p>Redis Port: " . REDIS_PORT . "</p>";
} else {
    echo "<p style='color: red;'>❌ File configs/redis.php không tồn tại</p>";
}
?>
