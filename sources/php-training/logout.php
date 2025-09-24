<?php
require_once 'models/SessionManager.php';
require_once 'csrf_helper.php';

$sessionManager = new SessionManager();

// Kiểm tra CSRF token cho POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$csrf->validatePostToken()) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'CSRF token không hợp lệ'
        ]);
        exit;
    }
}

// Lấy session_id từ request
$sessionId = $_POST['session_id'] ?? $_GET['session_id'] ?? null;

$success = false;
if ($sessionId) {
    // Xóa session khỏi Redis/File
    $success = $sessionManager->destroySession($sessionId);
    
    // Xóa tất cả CSRF tokens của session
    $csrf->destroyAllSessionTokens();
}

// Trả về JSON response
header('Content-Type: application/json');
echo json_encode([
    'success' => $success,
    'message' => $success ? 'Đăng xuất thành công' : 'Không tìm thấy session để xóa'
]);
?>