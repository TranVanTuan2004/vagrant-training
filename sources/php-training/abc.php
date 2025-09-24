<?php
// test_csrf_real_attack.php
require_once 'csrf_helper.php';

echo "<h2>🚨 Test CSRF Attack Real</h2>";

// Test 1: Tạo file hacker.php có CSRF protection
echo "<h3>1. Tạo hacker.php có CSRF protection</h3>";
$protectedHackerCode = '<?php
require_once "csrf_helper.php";

// Kiểm tra CSRF token trước khi xử lý
if (!$csrf->validateGetToken()) {
    http_response_code(403);
    die("🚨 CSRF Attack Blocked! Token không hợp lệ.");
}

// Chỉ xử lý nếu có CSRF token hợp lệ
if (isset($_GET["cookie"])) {
    file_put_contents("cookie.txt", $_GET["cookie"]);
    echo "✅ Cookie đã được lưu (có CSRF protection)";
} else {
    echo "❌ Thiếu parameter cookie";
}
?>';

echo "<p>Code hacker.php có CSRF protection:</p>";
echo "<pre style='background: #f5f5f5; padding: 10px;'>" . htmlspecialchars($protectedHackerCode) . "</pre>";

// Test 2: Test CSRF attack simulation
echo "<h3>2. Test CSRF Attack Simulation</h3>";

// Tạo CSRF token hợp lệ
$validToken = $csrf->generateToken();
echo "<p>CSRF Token hợp lệ: <code>" . substr($validToken, 0, 20) . "...</code></p>";

// Test 3: Test các trường hợp
echo "<h3>3. Test Cases</h3>";

echo "<h4>Case 1: Truy cập trực tiếp (không có CSRF token)</h4>";
echo "<p>Link: <a href='hacker.php?cookie=direct_attack' target='_blank'>hacker.php?cookie=direct_attack</a></p>";
echo "<p><strong>Kết quả mong đợi:</strong> CSRF Attack Blocked! ❌</p>";

echo "<h4>Case 2: Truy cập với CSRF token hợp lệ</h4>";
echo "<p>Link: <a href='hacker.php?cookie=csrf_attack&csrf_token=" . $validToken . "' target='_blank'>hacker.php?cookie=csrf_attack&csrf_token=...</a></p>";
echo "<p><strong>Kết quả mong đợi:</strong> Cookie đã được lưu ✅</p>";

echo "<h4>Case 3: Truy cập với CSRF token không hợp lệ</h4>";
echo "<p>Link: <a href='hacker.php?cookie=fake_attack&csrf_token=fake_token' target='_blank'>hacker.php?cookie=fake_attack&csrf_token=fake_token</a></p>";
echo "<p><strong>Kết quả mong đợi:</strong> CSRF Attack Blocked! ❌</p>";

// Test 4: Test với form
echo "<h3>4. Test với Form</h3>";
echo "<form method='post' action='hacker.php' style='margin: 20px 0;'>";
echo $csrf->getHiddenInput();
echo "<input type='hidden' name='cookie' value='form_attack'>";
echo "<button type='submit' class='btn btn-primary'>Test Form CSRF Attack</button>";
echo "</form>";

// Test 5: Test với AJAX
echo "<h3>5. Test với AJAX</h3>";
echo "<button onclick='testAjaxAttack()' class='btn btn-success'>Test AJAX CSRF Attack</button>";

echo "<script>
function testAjaxAttack() {
    const csrfToken = document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content');
    
    // Test 1: AJAX với CSRF token hợp lệ
    fetch('hacker.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': csrfToken
        },
        body: 'cookie=ajax_attack&csrf_token=' + csrfToken
    })
    .then(response => response.text())
    .then(data => {
        alert('AJAX với CSRF token: ' + data);
    });
    
    // Test 2: AJAX không có CSRF token
    fetch('hacker.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'cookie=ajax_no_csrf'
    })
    .then(response => response.text())
    .then(data => {
        alert('AJAX không có CSRF token: ' + data);
    });
}
</script>";

// Test 6: Kiểm tra kết quả
echo "<h3>6. Kiểm tra kết quả</h3>";
if (file_exists('cookie.txt')) {
    $cookieContent = file_get_contents('cookie.txt');
    echo "<p>✅ File cookie.txt tồn tại</p>";
    echo "<p>Nội dung: <code>" . htmlspecialchars($cookieContent) . "</code></p>";
    
    // Phân tích kết quả
    if ($cookieContent === 'direct_attack') {
        echo "<p style='color: red;'>❌ CSRF Protection: KHÔNG HOẠT ĐỘNG</p>";
        echo "<p>Hacker có thể truy cập trực tiếp mà không cần CSRF token!</p>";
    } elseif ($cookieContent === 'csrf_attack') {
        echo "<p style='color: green;'>✅ CSRF Protection: HOẠT ĐỘNG</p>";
        echo "<p>Chỉ cho phép truy cập với CSRF token hợp lệ!</p>";
    } elseif ($cookieContent === 'form_attack') {
        echo "<p style='color: green;'>✅ Form CSRF Protection: HOẠT ĐỘNG</p>";
    } elseif ($cookieContent === 'ajax_attack') {
        echo "<p style='color: green;'>✅ AJAX CSRF Protection: HOẠT ĐỘNG</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Kết quả không rõ ràng</p>";
    }
} else {
    echo "<p>❌ File cookie.txt không tồn tại</p>";
    echo "<p>CSRF Protection có thể đang hoạt động!</p>";
}

// Test 7: Test với database query
echo "<h3>7. Test với Database Query</h3>";
echo "<p>Tạo file test database CSRF:</p>";

$dbCsrfTestCode = '<?php
require_once "csrf_helper.php";
require_once "models/UserModel.php";

// Kiểm tra CSRF token trước khi query database
if (!$csrf->validateGetToken()) {
    http_response_code(403);
    die("🚨 CSRF Attack Blocked! Không được phép query database.");
}

// Chỉ query nếu có CSRF token hợp lệ
if (isset($_GET["action"])) {
    try {
        $userModel = new UserModel();
        $users = $userModel->getUsers([]);
        echo "✅ Database query thành công (có CSRF protection)";
        echo "<p>Số users: " . count($users) . "</p>";
    } catch (Exception $e) {
        echo "❌ Database query failed: " . $e->getMessage();
    }
} else {
    echo "❌ Thiếu parameter action";
}
?>';

echo "<pre style='background: #f5f5f5; padding: 10px;'>" . htmlspecialchars($dbCsrfTestCode) . "</pre>";

echo "<hr>";
echo "<h3>📊 Tóm tắt Test</h3>";
echo "<p><strong>CSRF Protection Status:</strong> " . (file_exists('cookie.txt') ? 'Cần kiểm tra' : 'Có thể đang hoạt động') . "</p>";
echo "<p><strong>Recommendation:</strong></p>";
echo "<ul>";
echo "<li>Luôn kiểm tra CSRF token trước khi xử lý request</li>";
echo "<li>Block tất cả request không có CSRF token hợp lệ</li>";
echo "<li>Test với nhiều phương thức: GET, POST, AJAX</li>";
echo "<li>Kiểm tra cả database queries và file operations</li>";
echo "</ul>";
?>