<?php
require_once 'models/SessionManager.php';

$sessionManager = new SessionManager();

// Lấy session_id từ request
$sessionId = $_POST['session_id'] ?? $_GET['session_id'] ?? null;

$success = false;
if ($sessionId) {
    // Xóa session khỏi Redis
    $success = $sessionManager->destroySession($sessionId);
}

// Trả về JSON response
header('Content-Type: application/json');
echo json_encode([
    'success' => $success,
    'message' => $success ? 'Đăng xuất thành công' : 'Không tìm thấy session để xóa'
]);
?>