<?php
require_once 'csrf_helper.php';
require_once 'models/SessionManager.php';
require_once 'auth_helper.php';

echo "<h2>🛡️ Security System Test</h2>";

// Test 1: Session Management
echo "<h3>1. Session Management</h3>";
try {
    $sessionManager = new SessionManager();
    if ($sessionManager->isConnected()) {
        echo "<p style='color: green;'>✅ Session Manager: CONNECTED</p>";
        echo "<p>Storage: " . ($sessionManager->useFileFallback ? 'File System' : 'Redis') . "</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Session Manager: Using fallback</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Session Manager Error: " . $e->getMessage() . "</p>";
}

// Test 2: CSRF Protection
echo "<h3>2. CSRF Protection</h3>";
if ($csrf->csrfManager->isConnected()) {
    echo "<p style='color: green;'>✅ CSRF Manager: CONNECTED</p>";
    echo "<p>Storage: PHP Session</p>";
    
    $token = $csrf->generateToken();
    if ($token) {
        echo "<p>Token generated: " . substr($token, 0, 10) . "...</p>";
        echo "<p>Token valid: " . ($csrf->validateToken($token) ? 'YES' : 'NO') . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ CSRF Manager: NOT CONNECTED</p>";
}

// Test 3: Authentication
echo "<h3>3. Authentication System</h3>";
try {
    require_once 'auth_helper.php';
    echo "<p style='color: green;'>✅ Auth Helper: LOADED</p>";
    echo "<p>Logged in: " . ($auth->isLoggedIn() ? 'YES' : 'NO') . "</p>";
    
    if ($auth->isLoggedIn()) {
        $user = $auth->getCurrentUser();
        echo "<p>Current user: " . $user['name'] . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Auth Helper Error: " . $e->getMessage() . "</p>";
}

// Test 4: Login Flow
echo "<h3>4. Login Flow Test</h3>";
if (isset($_POST['test_login'])) {
    try {
        require_once 'models/UserModel.php';
        $userModel = new UserModel();
        
        $user = $userModel->auth('admin', 'admin');
        if ($user) {
            echo "<p style='color: green;'>✅ User authentication: SUCCESS</p>";
            
            $userData = [
                'id' => $user[0]['id'],
                'name' => $user[0]['name'],
                'fullname' => $user[0]['fullname']
            ];
            
            $sessionId = $sessionManager->createUserSession($user[0]['id'], $userData);
            if ($sessionId) {
                echo "<p style='color: green;'>✅ Session creation: SUCCESS</p>";
                echo "<p>Session ID: " . substr($sessionId, 0, 20) . "...</p>";
            } else {
                echo "<p style='color: red;'>❌ Session creation: FAILED</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ User authentication: FAILED</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Login test error: " . $e->getMessage() . "</p>";
    }
}

// Test 5: Security Headers
echo "<h3>5. Security Headers</h3>";
$headers = [
    'X-Content-Type-Options' => 'nosniff',
    'X-Frame-Options' => 'DENY',
    'X-XSS-Protection' => '1; mode=block',
    'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains'
];

echo "<p>Recommended security headers:</p><ul>";
foreach ($headers as $header => $value) {
    echo "<li><strong>$header:</strong> $value</li>";
}
echo "</ul>";

// Test 6: Session Security
echo "<h3>6. Session Security</h3>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? 'ACTIVE' : 'INACTIVE') . "</p>";
echo "<p>Session Name: " . session_name() . "</p>";

// Test forms
echo "<h3>7. Security Tests</h3>";
echo "<form method='post' style='margin: 20px 0;'>";
echo $csrf->getHiddenInput();
echo "<button type='submit' name='test_login' class='btn btn-primary'>Test Login Flow</button>";
echo "</form>";

// Test links
echo "<h3>8. Test Links</h3>";
echo "<p><a href='test_csrf.php' target='_blank'>🔗 CSRF Test</a></p>";
echo "<p><a href='test_csrf_attack.php' target='_blank'>🔗 CSRF Attack Simulation</a></p>";
echo "<p><a href='login.php' target='_blank'>🔗 Login Page</a></p>";
echo "<p><a href='list_users.php' target='_blank'>🔗 List Users</a></p>";

echo "<hr>";
echo "<h3>📊 Security Summary</h3>";

$securityScore = 0;
$maxScore = 5;

// Session Management
if (isset($sessionManager) && $sessionManager->isConnected()) {
    $securityScore++;
    echo "<p>✅ Session Management: SECURE</p>";
} else {
    echo "<p>❌ Session Management: NEEDS ATTENTION</p>";
}

// CSRF Protection
if ($csrf->csrfManager->isConnected()) {
    $securityScore++;
    echo "<p>✅ CSRF Protection: ACTIVE</p>";
} else {
    echo "<p>❌ CSRF Protection: INACTIVE</p>";
}

// Authentication
if (isset($auth)) {
    $securityScore++;
    echo "<p>✅ Authentication System: ACTIVE</p>";
} else {
    echo "<p>❌ Authentication System: INACTIVE</p>";
}

// Session Security
if (session_status() === PHP_SESSION_ACTIVE) {
    $securityScore++;
    echo "<p>✅ Session Security: ACTIVE</p>";
} else {
    echo "<p>❌ Session Security: INACTIVE</p>";
}

// CSRF Token
$token = $csrf->generateToken();
if ($token && $csrf->validateToken($token)) {
    $securityScore++;
    echo "<p>✅ CSRF Token: WORKING</p>";
} else {
    echo "<p>❌ CSRF Token: NOT WORKING</p>";
}

echo "<hr>";
echo "<h3>🎯 Security Score: $securityScore/$maxScore</h3>";

if ($securityScore >= 4) {
    echo "<p style='color: green; font-weight: bold;'>🛡️ SECURITY LEVEL: HIGH</p>";
} elseif ($securityScore >= 2) {
    echo "<p style='color: orange; font-weight: bold;'>⚠️ SECURITY LEVEL: MEDIUM</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>🚨 SECURITY LEVEL: LOW</p>";
}

echo "<p><strong>Recommendations:</strong></p>";
echo "<ul>";
if ($securityScore < 5) {
    echo "<li>Ensure all security components are active</li>";
    echo "<li>Test CSRF protection thoroughly</li>";
    echo "<li>Verify session management is working</li>";
    echo "<li>Check authentication flow</li>";
}
echo "<li>Add security headers to web server</li>";
echo "<li>Use HTTPS in production</li>";
echo "<li>Regular security audits</li>";
echo "</ul>";
?>
