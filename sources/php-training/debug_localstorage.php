<?php
// File debug để kiểm tra localStorage
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug LocalStorage</title>
    <?php include 'views/meta.php' ?>
</head>
<body>
    <?php include 'views/header.php'?>
    <div class="container">
        <h3>🔍 Debug LocalStorage</h3>
        
        <div class="panel panel-info">
            <div class="panel-heading">
                <h4>Thông tin LocalStorage hiện tại</h4>
            </div>
            <div class="panel-body">
                <div id="localStorageInfo">
                    <!-- Nội dung sẽ được JavaScript điền vào -->
                </div>
            </div>
        </div>
        
        <div class="panel panel-warning">
            <div class="panel-heading">
                <h4>Thao tác</h4>
            </div>
            <div class="panel-body">
                <button class="btn btn-primary" onclick="refreshInfo()">Refresh Info</button>
                <button class="btn btn-warning" onclick="clearAll()">Clear All LocalStorage</button>
                <button class="btn btn-info" onclick="showRawData()">Show Raw Data</button>
            </div>
        </div>
        
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4>Raw LocalStorage Data</h4>
            </div>
            <div class="panel-body">
                <pre id="rawData"></pre>
            </div>
        </div>
    </div>

<script>
function refreshInfo() {
    const userDataStr = localStorage.getItem('user');
    
    let info = '';
    
    if (userDataStr) {
        try {
            const userData = JSON.parse(userDataStr);
            
            if (userData && userData.is_logged_in) {
                info += '<div class="alert alert-success">';
                info += '<h5><strong>✅ User đã đăng nhập</strong></h5>';
                info += '<p><strong>User ID:</strong> ' + userData.id + '</p>';
                info += '<p><strong>User Name:</strong> ' + userData.name + '</p>';
                info += '<p><strong>User Fullname:</strong> ' + userData.fullname + '</p>';
                info += '<p><strong>Login Time:</strong> ' + userData.login_time + '</p>';
                
                if (userData.login_time) {
                    const loginDate = new Date(userData.login_time);
                    info += '<p><strong>Login Date (Formatted):</strong> ' + loginDate.toLocaleString() + '</p>';
                }
                info += '</div>';
            } else {
                info += '<div class="alert alert-warning">';
                info += '<p><strong>⚠️ User data không hợp lệ</strong></p>';
                info += '</div>';
            }
        } catch (e) {
            info += '<div class="alert alert-danger">';
            info += '<p><strong>❌ Lỗi parse JSON:</strong> ' + e.message + '</p>';
            info += '</div>';
        }
    } else {
        info += '<div class="alert alert-info">';
        info += '<p><strong>ℹ️ Không có thông tin user trong localStorage</strong></p>';
        info += '</div>';
    }
    
    // Hiển thị tất cả keys trong localStorage
    info += '<h5>Tất cả keys trong localStorage:</h5>';
    info += '<ul>';
    for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        const value = localStorage.getItem(key);
        info += '<li><strong>' + key + ':</strong> ' + value + '</li>';
    }
    info += '</ul>';
    
    document.getElementById('localStorageInfo').innerHTML = info;
}

function clearAll() {
    if (confirm('Bạn có chắc muốn xóa tất cả dữ liệu localStorage?')) {
        localStorage.clear();
        alert('Đã xóa tất cả dữ liệu localStorage!');
        refreshInfo();
        showRawData();
    }
}

function showRawData() {
    let rawData = '';
    for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        const value = localStorage.getItem(key);
        rawData += key + ' = ' + value + '\n';
    }
    
    if (rawData === '') {
        rawData = 'LocalStorage trống';
    }
    
    document.getElementById('rawData').textContent = rawData;
}

// Tự động load thông tin khi trang load
document.addEventListener('DOMContentLoaded', function() {
    refreshInfo();
    showRawData();
});
</script>

</body>
</html>
