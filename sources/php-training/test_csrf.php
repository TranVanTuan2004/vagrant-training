<?php
require_once 'csrf_helper.php';

echo "<h2>🔒 CSRF Protection Test</h2>";

// Test 1: Kiểm tra CSRF Manager
echo "<h3>1. CSRF Manager Status</h3>";
// Test bằng cách tạo token
$testToken = $csrf->generateToken();
if ($testToken) {
    echo "<p style='color: green;'>✅ CSRF Manager: CONNECTED (PHP Session)</p>";
} else {
    echo "<p style='color: red;'>❌ CSRF Manager: NOT CONNECTED</p>";
}

// Test 2: Tạo CSRF token
echo "<h3>2. Generate CSRF Token</h3>";
$token = $csrf->generateToken();
if ($token) {
    echo "<p style='color: green;'>✅ Token generated: " . substr($token, 0, 20) . "...</p>";
    echo "<p>Full token: <code>" . $token . "</code></p>";
} else {
    echo "<p style='color: red;'>❌ Failed to generate token</p>";
}

// Test 3: Validate token
echo "<h3>3. Token Validation</h3>";
if ($token) {
    $isValid = $csrf->validateToken($token);
    if ($isValid) {
        echo "<p style='color: green;'>✅ Token validation: SUCCESS</p>";
    } else {
        echo "<p style='color: red;'>❌ Token validation: FAILED</p>";
    }
} else {
    echo "<p style='color: red;'>❌ No token to validate</p>";
}

// Test 4: Form submission test
echo "<h3>4. Form Submission Test</h3>";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_form'])) {
    if ($csrf->validatePostToken()) {
        echo "<p style='color: green;'>✅ Form CSRF validation: SUCCESS</p>";
    } else {
        echo "<p style='color: red;'>❌ Form CSRF validation: FAILED</p>";
    }
}

// Test 5: AJAX test
echo "<h3>5. AJAX Request Test</h3>";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_test'])) {
    $headers = getallheaders();
    $csrfHeader = $headers['X-CSRF-Token'] ?? $headers['x-csrf-token'] ?? null;
    
    if ($csrfHeader && $csrf->validateToken($csrfHeader)) {
        echo "<p style='color: green;'>✅ AJAX CSRF validation: SUCCESS</p>";
    } else {
        echo "<p style='color: red;'>❌ AJAX CSRF validation: FAILED</p>";
    }
}

// Test 6: Session info
echo "<h3>6. Session Information</h3>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? 'ACTIVE' : 'INACTIVE') . "</p>";

if (isset($_SESSION['csrf_tokens'])) {
    echo "<p>CSRF Tokens in session: " . count($_SESSION['csrf_tokens']) . "</p>";
    echo "<p>Tokens:</p><ul>";
    foreach ($_SESSION['csrf_tokens'] as $t => $data) {
        $age = time() - $data['created_at'];
        echo "<li>" . substr($t, 0, 10) . "... (age: " . $age . "s)</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No CSRF tokens in session</p>";
}

// Test form
echo "<h3>7. Test Forms</h3>";
echo "<form method='post' style='margin: 20px 0;'>";
echo $csrf->getHiddenInput();
echo "<button type='submit' name='test_form' class='btn btn-primary'>Test Form Submission</button>";
echo "</form>";

// AJAX test button
echo "<button onclick='testAjax()' class='btn btn-success'>Test AJAX Request</button>";

echo "<script>
function testAjax() {
    const csrfToken = document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content');
    fetch('test_csrf.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': csrfToken
        },
        body: 'ajax_test=1'
    })
    .then(response => response.text())
    .then(data => {
        document.body.innerHTML = data;
    });
}
</script>";

echo "<hr>";
echo "<h3>📊 Summary</h3>";
echo "<p><strong>CSRF Manager:</strong> " . ($testToken ? 'CONNECTED' : 'NOT CONNECTED') . "</p>";
echo "<p><strong>Token Generated:</strong> " . ($token ? 'YES' : 'NO') . "</p>";
echo "<p><strong>Token Valid:</strong> " . ($token && $csrf->validateToken($token) ? 'YES' : 'NO') . "</p>";
echo "<p><strong>Session Active:</strong> " . (session_status() === PHP_SESSION_ACTIVE ? 'YES' : 'NO') . "</p>";
echo "<p><strong>CSRF Tokens in Session:</strong> " . (isset($_SESSION['csrf_tokens']) ? count($_SESSION['csrf_tokens']) : 0) . "</p>";

echo "<p><strong>Status:</strong> ";
if ($testToken && $token && $csrf->validateToken($token)) {
    echo "<span style='color: green;'>CSRF Protection is working!</span>";
} else {
    echo "<span style='color: red;'>CSRF Protection has issues!</span>";
}
echo "</p>";
?>