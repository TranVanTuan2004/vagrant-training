<?php
// Test login flow
echo "<h2>Test Login Flow</h2>";

// Kiểm tra session data trong localStorage
echo "<h3>1. Kiểm tra Session Data</h3>";
echo "<p>Mở Developer Tools (F12) và chạy lệnh sau trong Console:</p>";
echo "<pre>console.log('Session data:', localStorage.getItem('session'));</pre>";

// Test session manager
echo "<h3>2. Test Session Manager</h3>";
try {
    require_once 'models/SessionManager.php';
    $sessionManager = new SessionManager();
    
    if ($sessionManager->isConnected()) {
        echo "<p style='color: green;'>✅ SessionManager hoạt động bình thường</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ SessionManager sử dụng file fallback</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi SessionManager: " . $e->getMessage() . "</p>";
}

// Test auth helper
echo "<h3>3. Test Auth Helper</h3>";
try {
    require_once 'auth_helper.php';
    echo "<p style='color: green;'>✅ AuthHelper tải thành công</p>";
    echo "<p>Đăng nhập: " . ($auth->isLoggedIn() ? 'Có' : 'Không') . "</p>";
    
    if ($auth->isLoggedIn()) {
        $user = $auth->getCurrentUser();
        echo "<p>User: " . print_r($user, true) . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi AuthHelper: " . $e->getMessage() . "</p>";
}

// Test URL parameters
echo "<h3>4. Test URL Parameters</h3>";
echo "<p>Session ID từ URL: " . ($_GET['session_id'] ?? 'Không có') . "</p>";
echo "<p>Session ID từ POST: " . ($_POST['session_id'] ?? 'Không có') . "</p>";

// Hướng dẫn test
echo "<h3>5. Hướng dẫn Test</h3>";
echo "<ol>";
echo "<li>Truy cập <a href='login.php'>login.php</a> và đăng nhập</li>";
echo "<li>Kiểm tra localStorage có session data không</li>";
echo "<li>Truy cập <a href='list_users.php'>list_users.php</a></li>";
echo "<li>Kiểm tra URL có session_id parameter không</li>";
echo "<li>Nếu bị redirect về login, kiểm tra console log</li>";
echo "</ol>";

// Debug info
echo "<h3>6. Debug Info</h3>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Current URL: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>Request Method: " . $_SERVER['REQUEST_METHOD'] . "</p>";
echo "<p>Headers:</p><pre>" . print_r(getallheaders(), true) . "</pre>";
?>
