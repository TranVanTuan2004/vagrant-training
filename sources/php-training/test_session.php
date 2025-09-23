<?php
// Test session management
echo "<h2>Test Session Management</h2>";

try {
    require_once 'models/SessionManager.php';
    $sessionManager = new SessionManager();
    
    echo "<h3>1. Kiểm tra kết nối</h3>";
    if ($sessionManager->isConnected()) {
        echo "<p style='color: green;'>✅ SessionManager kết nối thành công</p>";
        
        // Test tạo session
        echo "<h3>2. Test tạo session</h3>";
        $userData = [
            'id' => 1,
            'name' => 'test_user',
            'fullname' => 'Test User'
        ];
        
        $sessionId = $sessionManager->createUserSession(1, $userData);
        if ($sessionId) {
            echo "<p style='color: green;'>✅ Tạo session thành công: $sessionId</p>";
            
            // Test lấy session
            echo "<h3>3. Test lấy session</h3>";
            $session = $sessionManager->getSession($sessionId);
            if ($session) {
                echo "<p style='color: green;'>✅ Lấy session thành công</p>";
                echo "<pre>" . print_r($session, true) . "</pre>";
                
                // Test xóa session
                echo "<h3>4. Test xóa session</h3>";
                $deleted = $sessionManager->destroySession($sessionId);
                if ($deleted) {
                    echo "<p style='color: green;'>✅ Xóa session thành công</p>";
                } else {
                    echo "<p style='color: red;'>❌ Lỗi xóa session</p>";
                }
            } else {
                echo "<p style='color: red;'>❌ Lỗi lấy session</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Lỗi tạo session</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ SessionManager không kết nối được</p>";
        echo "<p>Hệ thống sẽ sử dụng file fallback hoặc cần cài đặt Redis extension</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi: " . $e->getMessage() . "</p>";
    echo "<p>Vui lòng kiểm tra Redis extension hoặc file fallback</p>";
}

echo "<h3>5. Thông tin hệ thống</h3>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Redis Extension: " . (extension_loaded('redis') ? 'Có' : 'Không') . "</p>";
echo "<p>Thư mục sessions: " . (is_dir('sessions') ? 'Tồn tại' : 'Không tồn tại') . "</p>";
echo "<p>Quyền ghi sessions: " . (is_writable('sessions') ? 'Có' : 'Không') . "</p>";

if (is_dir('sessions')) {
    $files = glob('sessions/*.json');
    echo "<p>Số file session: " . count($files) . "</p>";
    if (count($files) > 0) {
        echo "<p>Files:</p><ul>";
        foreach ($files as $file) {
            echo "<li>" . basename($file) . "</li>";
        }
        echo "</ul>";
    }
}
?>
