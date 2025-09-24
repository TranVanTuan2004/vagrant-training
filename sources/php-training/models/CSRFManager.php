<?php
class CSRFManager {
    private $connected = true;

    public function __construct() {
        // Khởi động session nếu chưa có
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }


    public function isConnected() {
        return $this->connected;
    }

    /**
     * Tạo CSRF token mới
     */
    public function generateToken($sessionId = null) {
        try {
            $token = bin2hex(random_bytes(32));
            
            // Lưu token vào session
            if (!isset($_SESSION['csrf_tokens'])) {
                $_SESSION['csrf_tokens'] = [];
            }
            
            $_SESSION['csrf_tokens'][$token] = [
                'token' => $token,
                'created_at' => time(),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ];
            
            // Giới hạn số lượng tokens trong session (tối đa 10)
            if (count($_SESSION['csrf_tokens']) > 10) {
                // Xóa token cũ nhất
                $oldestToken = null;
                $oldestTime = time();
                foreach ($_SESSION['csrf_tokens'] as $t => $data) {
                    if ($data['created_at'] < $oldestTime) {
                        $oldestTime = $data['created_at'];
                        $oldestToken = $t;
                    }
                }
                if ($oldestToken) {
                    unset($_SESSION['csrf_tokens'][$oldestToken]);
                }
            }
            
            return $token;
        } catch (Exception $e) {
            error_log("Lỗi tạo CSRF token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Xác thực CSRF token
     */
    public function validateToken($token, $sessionId = null) {
        if (!$token || !isset($_SESSION['csrf_tokens'])) {
            return false;
        }

        try {
            // Kiểm tra token có tồn tại trong session không
            if (!isset($_SESSION['csrf_tokens'][$token])) {
                return false;
            }
            
            $tokenData = $_SESSION['csrf_tokens'][$token];
            
            // Kiểm tra TTL (1 giờ)
            $age = time() - $tokenData['created_at'];
            if ($age > 3600) {
                unset($_SESSION['csrf_tokens'][$token]);
                return false;
            }
            
            // Kiểm tra IP address (optional)
            $currentIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            if ($tokenData['ip_address'] !== $currentIP) {
                // Có thể bỏ qua kiểm tra IP nếu cần
                // return false;
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Lỗi xác thực CSRF token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Xóa CSRF token
     */
    public function destroyToken($token) {
        if (!$token || !isset($_SESSION['csrf_tokens'])) {
            return false;
        }

        try {
            if (isset($_SESSION['csrf_tokens'][$token])) {
                unset($_SESSION['csrf_tokens'][$token]);
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Lỗi xóa CSRF token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Xóa tất cả tokens của session
     */
    public function destroyAllSessionTokens($sessionId = null) {
        try {
            if (isset($_SESSION['csrf_tokens'])) {
                $_SESSION['csrf_tokens'] = [];
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Lỗi xóa tất cả CSRF tokens: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Làm sạch tokens hết hạn
     */
    public function cleanupExpiredTokens() {
        try {
            if (!isset($_SESSION['csrf_tokens'])) {
                return true;
            }
            
            $currentTime = time();
            foreach ($_SESSION['csrf_tokens'] as $token => $data) {
                $age = $currentTime - $data['created_at'];
                if ($age > 3600) { // 1 giờ
                    unset($_SESSION['csrf_tokens'][$token]);
                }
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Lỗi cleanup CSRF tokens: " . $e->getMessage());
            return false;
        }
    }
}
?>
