<?php
require_once 'configs/redis.php';

class FileSessionManager {
    private $sessionDir;
    private $connected = false;

    public function __construct() {
        $this->sessionDir = __DIR__ . '/../sessions/';
        $this->connect();
    }

    private function connect() {
        try {
            // Tạo thư mục sessions nếu chưa có
            if (!is_dir($this->sessionDir)) {
                mkdir($this->sessionDir, 0755, true);
            }
            
            // Kiểm tra có thể ghi được không
            if (is_writable($this->sessionDir)) {
                $this->connected = true;
            } else {
                error_log("Không thể ghi vào thư mục sessions: " . $this->sessionDir);
                $this->connected = false;
            }
        } catch (Exception $e) {
            error_log("Lỗi khởi tạo FileSessionManager: " . $e->getMessage());
            $this->connected = false;
        }
    }

    public function isConnected() {
        return $this->connected;
    }

    /**
     * Tạo session mới cho user
     */
    public function createUserSession($userId, $userData) {
        if (!$this->connected) {
            return false;
        }

        try {
            $sessionId = $this->generateSessionId();
            $sessionFile = $this->sessionDir . 'user_session_' . $sessionId . '.json';
            
            $sessionData = [
                'user_id' => $userId,
                'user_data' => $userData,
                'created_at' => time(),
                'last_activity' => time(),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ];

            $result = file_put_contents($sessionFile, json_encode($sessionData));
            
            if ($result !== false) {
                // Lưu mapping user_id -> session_id
                $userSessionsFile = $this->sessionDir . 'user_sessions_' . $userId . '.json';
                $userSessions = [];
                
                if (file_exists($userSessionsFile)) {
                    $userSessions = json_decode(file_get_contents($userSessionsFile), true) ?: [];
                }
                
                $userSessions[] = $sessionId;
                file_put_contents($userSessionsFile, json_encode($userSessions));
                
                return $sessionId;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Lỗi tạo session: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy thông tin session
     */
    public function getSession($sessionId) {
        if (!$this->connected) {
            return false;
        }

        try {
            $sessionFile = $this->sessionDir . 'user_session_' . $sessionId . '.json';
            
            if (file_exists($sessionFile)) {
                $sessionData = json_decode(file_get_contents($sessionFile), true);
                
                if ($sessionData) {
                    // Kiểm tra TTL
                    $age = time() - $sessionData['created_at'];
                    if ($age > REDIS_USER_SESSION_TTL) {
                        $this->destroySession($sessionId);
                        return false;
                    }
                    
                    // Cập nhật last_activity
                    $sessionData['last_activity'] = time();
                    file_put_contents($sessionFile, json_encode($sessionData));
                    
                    return $sessionData;
                }
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Lỗi lấy session: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kiểm tra session có hợp lệ không
     */
    public function isValidSession($sessionId) {
        $session = $this->getSession($sessionId);
        return $session !== false;
    }

    /**
     * Xóa session
     */
    public function destroySession($sessionId) {
        if (!$this->connected) {
            return false;
        }

        try {
            $sessionFile = $this->sessionDir . 'user_session_' . $sessionId . '.json';
            
            if (file_exists($sessionFile)) {
                $sessionData = json_decode(file_get_contents($sessionFile), true);
                $userId = $sessionData['user_id'] ?? null;
                
                // Xóa file session
                unlink($sessionFile);
                
                // Xóa session khỏi danh sách sessions của user
                if ($userId) {
                    $userSessionsFile = $this->sessionDir . 'user_sessions_' . $userId . '.json';
                    if (file_exists($userSessionsFile)) {
                        $userSessions = json_decode(file_get_contents($userSessionsFile), true) ?: [];
                        $userSessions = array_filter($userSessions, function($id) use ($sessionId) {
                            return $id !== $sessionId;
                        });
                        file_put_contents($userSessionsFile, json_encode(array_values($userSessions)));
                    }
                }
                
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Lỗi xóa session: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Xóa tất cả sessions của user
     */
    public function destroyAllUserSessions($userId) {
        if (!$this->connected) {
            return false;
        }

        try {
            $userSessionsFile = $this->sessionDir . 'user_sessions_' . $userId . '.json';
            
            if (file_exists($userSessionsFile)) {
                $sessions = json_decode(file_get_contents($userSessionsFile), true) ?: [];
                
                foreach ($sessions as $sessionId) {
                    $sessionFile = $this->sessionDir . 'user_session_' . $sessionId . '.json';
                    if (file_exists($sessionFile)) {
                        unlink($sessionFile);
                    }
                }
                
                unlink($userSessionsFile);
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Lỗi xóa tất cả sessions: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy danh sách sessions của user
     */
    public function getUserSessions($userId) {
        if (!$this->connected) {
            return [];
        }

        try {
            $userSessionsFile = $this->sessionDir . 'user_sessions_' . $userId . '.json';
            
            if (file_exists($userSessionsFile)) {
                return json_decode(file_get_contents($userSessionsFile), true) ?: [];
            }
            
            return [];
        } catch (Exception $e) {
            error_log("Lỗi lấy danh sách sessions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Tạo session ID ngẫu nhiên
     */
    private function generateSessionId() {
        return bin2hex(random_bytes(32));
    }

    /**
     * Làm sạch sessions hết hạn
     */
    public function cleanupExpiredSessions() {
        if (!$this->connected) {
            return false;
        }

        try {
            $files = glob($this->sessionDir . 'user_session_*.json');
            
            foreach ($files as $file) {
                $sessionData = json_decode(file_get_contents($file), true);
                if ($sessionData) {
                    $age = time() - $sessionData['created_at'];
                    if ($age > REDIS_USER_SESSION_TTL) {
                        unlink($file);
                    }
                }
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Lỗi cleanup sessions: " . $e->getMessage());
            return false;
        }
    }
}
?>
