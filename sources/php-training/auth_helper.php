<?php
require_once 'models/SessionManager.php';

class AuthHelper {
    private $sessionManager;
    private $currentUser = null;
    private $isLoggedIn = false;

    public function __construct() {
        $this->sessionManager = new SessionManager();
        $this->checkSession();
    }

    /**
     * Kiểm tra session hiện tại
     */
    private function checkSession() {
        // Lấy session_id từ localStorage (cần JavaScript để gửi qua AJAX)
        $sessionId = $this->getSessionIdFromRequest();
        
        if ($sessionId) {
            $session = $this->sessionManager->getSession($sessionId);
            if ($session) {
                $this->currentUser = $session['user_data'];
                $this->isLoggedIn = true;
            }
        }
    }

    /**
     * Lấy session_id từ request
     */
    private function getSessionIdFromRequest() {
        // Thử lấy từ GET trước (URL parameter)
        if (!empty($_GET['session_id'])) {
            return $_GET['session_id'];
        }
        
        // Thử lấy từ POST
        if (!empty($_POST['session_id'])) {
            return $_POST['session_id'];
        }
        
        // Thử lấy từ header
        $headers = getallheaders();
        if (isset($headers['X-Session-ID'])) {
            return $headers['X-Session-ID'];
        }
        
        return null;
    }

    /**
     * Kiểm tra user đã đăng nhập chưa
     */
    public function isLoggedIn() {
        return $this->isLoggedIn;
    }

    /**
     * Lấy thông tin user hiện tại
     */
    public function getCurrentUser() {
        return $this->currentUser;
    }

    /**
     * Yêu cầu đăng nhập (redirect nếu chưa đăng nhập)
     */
    public function requireLogin() {
        if (!$this->isLoggedIn) {
            // Trả về JSON response cho AJAX requests
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Vui lòng đăng nhập',
                    'redirect' => 'login.php'
                ]);
                exit;
            } else {
                // Redirect cho normal requests
                header('Location: login.php');
                exit;
            }
        }
    }

    /**
     * Kiểm tra có phải AJAX request không
     */
    private function isAjaxRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    /**
     * Đăng xuất user hiện tại
     */
    public function logout() {
        $sessionId = $this->getSessionIdFromRequest();
        if ($sessionId) {
            $this->sessionManager->destroySession($sessionId);
        }
        
        $this->currentUser = null;
        $this->isLoggedIn = false;
    }

    /**
     * Lấy session ID hiện tại
     */
    public function getCurrentSessionId() {
        return $this->getSessionIdFromRequest();
    }
}

// Tạo instance global
$auth = new AuthHelper();
?>
