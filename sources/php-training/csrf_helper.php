<?php
require_once 'models/CSRFManager.php';

class CSRFHelper {
    private $csrfManager;
    private $currentToken = null;

    public function __construct() {
        $this->csrfManager = new CSRFManager();
    }

    /**
     * Tạo CSRF token cho form
     */
    public function generateToken($sessionId = null) {
        $this->currentToken = $this->csrfManager->generateToken();
        return $this->currentToken;
    }

    /**
     * Lấy token hiện tại
     */
    public function getCurrentToken() {
        return $this->currentToken;
    }

    /**
     * Tạo hidden input cho form
     */
    public function getHiddenInput($sessionId = null) {
        $token = $this->generateToken();
        if ($token) {
            return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
        }
        return '';
    }

    /**
     * Tạo meta tag cho AJAX requests
     */
    public function getMetaTag($sessionId = null) {
        $token = $this->generateToken();
        if ($token) {
            return '<meta name="csrf-token" content="' . htmlspecialchars($token) . '">';
        }
        return '';
    }

    /**
     * Xác thực CSRF token từ POST
     */
    public function validatePostToken($sessionId = null) {
        $token = $_POST['csrf_token'] ?? null;
        return $this->validateToken($token, $sessionId);
    }

    /**
     * Xác thực CSRF token từ GET
     */
    public function validateGetToken($sessionId = null) {
        $token = $_GET['csrf_token'] ?? null;
        return $this->validateToken($token, $sessionId);
    }

    /**
     * Xác thực CSRF token từ header
     */
    public function validateHeaderToken($sessionId = null) {
        $headers = getallheaders();
        $token = $headers['X-CSRF-Token'] ?? $headers['x-csrf-token'] ?? null;
        return $this->validateToken($token, $sessionId);
    }

    /**
     * Xác thực CSRF token
     */
    public function validateToken($token, $sessionId = null) {
        if (!$token) {
            return false;
        }
        return $this->csrfManager->validateToken($token, $sessionId);
    }

    /**
     * Xóa token sau khi sử dụng
     */
    public function destroyToken($token) {
        return $this->csrfManager->destroyToken($token);
    }

    /**
     * Xóa tất cả tokens của session
     */
    public function destroyAllSessionTokens($sessionId) {
        return $this->csrfManager->destroyAllSessionTokens($sessionId);
    }

    /**
     * Kiểm tra và xử lý CSRF cho form submission
     */
    public function handleFormSubmission($sessionId = null, $method = 'POST') {
        $valid = false;
        
        switch (strtoupper($method)) {
            case 'POST':
                $valid = $this->validatePostToken($sessionId);
                break;
            case 'GET':
                $valid = $this->validateGetToken($sessionId);
                break;
            default:
                $valid = $this->validateHeaderToken($sessionId);
                break;
        }

        if (!$valid) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'CSRF token không hợp lệ hoặc đã hết hạn',
                'error_code' => 'CSRF_INVALID'
            ]);
            exit;
        }

        return true;
    }

    /**
     * Tạo JavaScript để gửi CSRF token với AJAX
     */
    public function getAjaxScript() {
        return '
        <script>
        // CSRF token cho AJAX requests
        const csrfToken = document.querySelector(\'meta[name="csrf-token"]\')?.getAttribute(\'content\');
        
        // Thêm CSRF token vào tất cả AJAX requests
        if (csrfToken) {
            // Override fetch để tự động thêm CSRF token
            const originalFetch = window.fetch;
            window.fetch = function(url, options = {}) {
                if (options.method && options.method.toUpperCase() !== \'GET\') {
                    options.headers = options.headers || {};
                    options.headers[\'X-CSRF-Token\'] = csrfToken;
                }
                return originalFetch(url, options);
            };
            
            // Override XMLHttpRequest để tự động thêm CSRF token
            const originalOpen = XMLHttpRequest.prototype.open;
            XMLHttpRequest.prototype.open = function(method, url, async, user, password) {
                this._method = method;
                return originalOpen.apply(this, arguments);
            };
            
            const originalSend = XMLHttpRequest.prototype.send;
            XMLHttpRequest.prototype.send = function(data) {
                if (this._method && this._method.toUpperCase() !== \'GET\') {
                    this.setRequestHeader(\'X-CSRF-Token\', csrfToken);
                }
                return originalSend.apply(this, arguments);
            };
        }
        </script>';
    }
}

// Tạo instance global
$csrf = new CSRFHelper();
?>
