<?php
require_once 'configs/redis.php';
require_once 'FileSessionManager.php';

class SessionManager {
    private $redis;
    private $fileManager;
    private $connected = false;
    private $useFileFallback = false;

    public function __construct() {
        $this->connect();
        
        // Nếu Redis không kết nối được, sử dụng file fallback
        if (!$this->connected) {
            $this->fileManager = new FileSessionManager();
            $this->useFileFallback = true;
            $this->connected = $this->fileManager->isConnected();
        }
    }

    private function connect() {
        try {
            // Kiểm tra Redis extension có tồn tại không
            if (!extension_loaded('redis')) {
                error_log("Redis extension chưa được cài đặt. Vui lòng chạy: docker compose down && docker compose up --build -d");
                $this->connected = false;
                return;
            }
            
            $this->redis = new Redis();
            $this->connected = $this->redis->connect(REDIS_HOST, REDIS_PORT);
            
            if (!empty(REDIS_PASSWORD)) {
                $this->redis->auth(REDIS_PASSWORD);
            }
            
            $this->redis->select(REDIS_DATABASE);
            
            if (!$this->connected) {
                error_log("Không thể kết nối đến Redis server tại " . REDIS_HOST . ":" . REDIS_PORT);
            }
        } catch (Exception $e) {
            error_log("Lỗi kết nối Redis: " . $e->getMessage());
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

        if ($this->useFileFallback) {
            return $this->fileManager->createUserSession($userId, $userData);
        }

        try {
            $sessionId = $this->generateSessionId();
            $sessionKey = REDIS_USER_SESSION_PREFIX . $sessionId;
            
            $sessionData = [
                'user_id' => $userId,
                'user_data' => $userData,
                'created_at' => time(),
                'last_activity' => time(),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ];

            $this->redis->setex($sessionKey, REDIS_USER_SESSION_TTL, json_encode($sessionData));
            
            // Lưu mapping user_id -> session_id để có thể tìm session theo user
            $userSessionsKey = 'user_sessions:' . $userId;
            $this->redis->sadd($userSessionsKey, $sessionId);
            $this->redis->expire($userSessionsKey, REDIS_USER_SESSION_TTL);
            
            return $sessionId;
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

        if ($this->useFileFallback) {
            return $this->fileManager->getSession($sessionId);
        }

        try {
            $sessionKey = REDIS_USER_SESSION_PREFIX . $sessionId;
            $sessionData = $this->redis->get($sessionKey);
            
            if ($sessionData) {
                $data = json_decode($sessionData, true);
                // Cập nhật last_activity
                $data['last_activity'] = time();
                $this->redis->setex($sessionKey, REDIS_USER_SESSION_TTL, json_encode($data));
                return $data;
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

        if ($this->useFileFallback) {
            return $this->fileManager->destroySession($sessionId);
        }

        try {
            $sessionKey = REDIS_USER_SESSION_PREFIX . $sessionId;
            $sessionData = $this->redis->get($sessionKey);
            
            if ($sessionData) {
                $data = json_decode($sessionData, true);
                $userId = $data['user_id'];
                
                // Xóa session
                $this->redis->del($sessionKey);
                
                // Xóa session khỏi danh sách sessions của user
                $userSessionsKey = 'user_sessions:' . $userId;
                $this->redis->srem($userSessionsKey, $sessionId);
                
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

        if ($this->useFileFallback) {
            return $this->fileManager->destroyAllUserSessions($userId);
        }

        try {
            $userSessionsKey = 'user_sessions:' . $userId;
            $sessions = $this->redis->smembers($userSessionsKey);
            
            foreach ($sessions as $sessionId) {
                $sessionKey = REDIS_USER_SESSION_PREFIX . $sessionId;
                $this->redis->del($sessionKey);
            }
            
            $this->redis->del($userSessionsKey);
            return true;
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

        if ($this->useFileFallback) {
            return $this->fileManager->getUserSessions($userId);
        }

        try {
            $userSessionsKey = 'user_sessions:' . $userId;
            return $this->redis->smembers($userSessionsKey);
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

        if ($this->useFileFallback) {
            return $this->fileManager->cleanupExpiredSessions();
        }

        try {
            $pattern = REDIS_USER_SESSION_PREFIX . '*';
            $keys = $this->redis->keys($pattern);
            
            foreach ($keys as $key) {
                $ttl = $this->redis->ttl($key);
                if ($ttl <= 0) {
                    $this->redis->del($key);
                }
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Lỗi cleanup sessions: " . $e->getMessage());
            return false;
        }
    }

    public function __destruct() {
        if ($this->connected && $this->redis) {
            $this->redis->close();
        }
    }
}
?>
