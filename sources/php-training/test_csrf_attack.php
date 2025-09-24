<?php
require_once 'csrf_helper.php';

echo "<h2>🚨 CSRF Attack Simulation</h2>";

// Test 1: Normal request (should work)
echo "<h3>1. Normal Request (Should Work)</h3>";
if (isset($_POST['normal_action'])) {
    if ($csrf->validatePostToken()) {
        echo "<p style='color: green;'>✅ Normal request: SUCCESS</p>";
        echo "<p>Action completed successfully!</p>";
    } else {
        echo "<p style='color: red;'>❌ Normal request: BLOCKED</p>";
    }
}

// Test 2: Request without CSRF token (should fail)
echo "<h3>2. Request Without CSRF Token (Should Fail)</h3>";
if (isset($_POST['no_csrf_action'])) {
    if ($csrf->validatePostToken()) {
        echo "<p style='color: red;'>❌ Request without CSRF: SUCCESS (VULNERABLE!)</p>";
    } else {
        echo "<p style='color: green;'>✅ Request without CSRF: BLOCKED (SECURE!)</p>";
    }
}

// Test 3: Request with invalid CSRF token (should fail)
echo "<h3>3. Request With Invalid CSRF Token (Should Fail)</h3>";
if (isset($_POST['invalid_csrf_action'])) {
    if ($csrf->validatePostToken()) {
        echo "<p style='color: red;'>❌ Request with invalid CSRF: SUCCESS (VULNERABLE!)</p>";
    } else {
        echo "<p style='color: green;'>✅ Request with invalid CSRF: BLOCKED (SECURE!)</p>";
    }
}

// Test 4: AJAX without CSRF header (should fail)
echo "<h3>4. AJAX Without CSRF Header (Should Fail)</h3>";
if (isset($_POST['ajax_no_csrf'])) {
    $headers = getallheaders();
    $csrfHeader = $headers['X-CSRF-Token'] ?? $headers['x-csrf-token'] ?? null;
    
    if ($csrfHeader && $csrf->validateToken($csrfHeader)) {
        echo "<p style='color: green;'>✅ AJAX without CSRF header: BLOCKED (SECURE!)</p>";
    } else {
        echo "<p style='color: red;'>❌ AJAX without CSRF header: SUCCESS (VULNERABLE!)</p>";
    }
}

// Test forms
echo "<h3>5. Test Forms</h3>";

// Normal form with CSRF
echo "<h4>Normal Form (With CSRF Token)</h4>";
echo "<form method='post' style='margin: 10px 0; border: 1px solid #ccc; padding: 10px;'>";
echo $csrf->getHiddenInput();
echo "<button type='submit' name='normal_action' class='btn btn-success'>Normal Action (Should Work)</button>";
echo "</form>";

// Form without CSRF
echo "<h4>Form Without CSRF Token</h4>";
echo "<form method='post' style='margin: 10px 0; border: 1px solid #ccc; padding: 10px;'>";
echo "<button type='submit' name='no_csrf_action' class='btn btn-warning'>No CSRF Token (Should Fail)</button>";
echo "</form>";

// Form with invalid CSRF
echo "<h4>Form With Invalid CSRF Token</h4>";
echo "<form method='post' style='margin: 10px 0; border: 1px solid #ccc; padding: 10px;'>";
echo '<input type="hidden" name="csrf_token" value="invalid_token_123">';
echo "<button type='submit' name='invalid_csrf_action' class='btn btn-danger'>Invalid CSRF Token (Should Fail)</button>";
echo "</form>";

// AJAX test buttons
echo "<h3>6. AJAX Tests</h3>";
echo "<button onclick='testAjaxWithCSRF()' class='btn btn-success'>AJAX With CSRF (Should Work)</button> ";
echo "<button onclick='testAjaxWithoutCSRF()' class='btn btn-warning'>AJAX Without CSRF (Should Fail)</button>";

// CSRF Meta tag
echo $csrf->getMetaTag();

echo "<hr>";
echo "<h3>📊 Test Results</h3>";
echo "<p><strong>Purpose:</strong> Verify CSRF protection is working</p>";
echo "<p><strong>Expected:</strong> Only requests with valid CSRF tokens should succeed</p>";
echo "<p><strong>Security Level:</strong> " . ($csrf->csrfManager->isConnected() ? 'HIGH' : 'LOW') . "</p>";

echo "<script>
function testAjaxWithCSRF() {
    const csrfToken = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content');
    
    if (!csrfToken) {
        alert('No CSRF token found');
        return;
    }
    
    fetch('test_csrf_attack.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': csrfToken
        },
        body: 'ajax_test=1'
    })
    .then(response => response.text())
    .then(data => {
        window.location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function testAjaxWithoutCSRF() {
    fetch('test_csrf_attack.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'ajax_no_csrf=1'
    })
    .then(response => response.text())
    .then(data => {
        window.location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
</script>";

echo "<p><strong>Instructions:</strong></p>";
echo "<ol>";
echo "<li>Click 'Normal Action' - should work</li>";
echo "<li>Click 'No CSRF Token' - should fail</li>";
echo "<li>Click 'Invalid CSRF Token' - should fail</li>";
echo "<li>Test AJAX buttons</li>";
echo "</ol>";
?>
