<?php
// Không dùng session nữa - sẽ đọc từ localStorage bằng JavaScript
$keyword = '';
if(!empty($_GET['keyword'])) {
    $keyword = $_GET['keyword'];
}
?>
<div class="container">
    <nav class="navbar navbar-icon-top navbar-default">

            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="list_users.php">App Web 1</a>
            </div>
            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    <li><a href="form_user.php">Add new user</a></li>

                </ul>
                <form class="navbar-form navbar-left">
                    <div class="form-group">
                        <input type="text" name="keyword" class="form-control" placeholder="Search users"
                               value="<?php echo $keyword ?>"
                        >
                    </div>
                    <button type="submit" class="btn btn-default">Search</button>
                </form>
                <ul class="nav navbar-nav navbar-right">
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-user-circle-o"></i>
                            <span id="userDisplay">Account</span> <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="#" id="profileLink" style="display:none;">Profile</a></li>
                            <li role="separator" class="divider" id="profileDivider" style="display:none;"></li>
                            <li><a href="login.php" id="loginLink">Login</a></li>
                            <li><a href="#" onclick="logout()" id="logoutLink" style="display:none;">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div><!-- /.navbar-collapse -->
    </nav>
    <!-- Hiển thị thông báo từ localStorage -->
    <div id="messageAlert" class="alert" style="display:none;"></div>
</div>

<script>
// Kiểm tra localStorage khi load trang
document.addEventListener('DOMContentLoaded', function() {
    const sessionDataStr = localStorage.getItem('session');
    
    if (sessionDataStr) {
        try {
            const sessionData = JSON.parse(sessionDataStr);
            
            if (sessionData && sessionData.is_logged_in && sessionData.user) {
                // User đã đăng nhập
                document.getElementById('userDisplay').textContent = sessionData.user.name;
                document.getElementById('profileLink').href = 'view_user.php?id=' + sessionData.user.id;
                document.getElementById('profileLink').style.display = 'block';
                document.getElementById('profileDivider').style.display = 'block';
                document.getElementById('logoutLink').style.display = 'block';
                document.getElementById('loginLink').style.display = 'none';
            } else {
                // Session data không hợp lệ
                showGuestMode();
            }
        } catch (e) {
            // Lỗi parse JSON
            console.error('Lỗi parse session data:', e);
            showGuestMode();
        }
    } else {
        // Không có session data
        showGuestMode();
    }
});

function showGuestMode() {
    document.getElementById('userDisplay').textContent = 'Account';
    document.getElementById('profileLink').style.display = 'none';
    document.getElementById('profileDivider').style.display = 'none';
    document.getElementById('logoutLink').style.display = 'none';
    document.getElementById('loginLink').style.display = 'block';
}

function logout() {
    const sessionDataStr = localStorage.getItem('session');
    
    if (sessionDataStr) {
        try {
            const sessionData = JSON.parse(sessionDataStr);
            
            if (sessionData && sessionData.session_id) {
                // Lấy CSRF token từ meta tag
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                
                // Gửi session_id và CSRF token đến server
                const formData = 'session_id=' + encodeURIComponent(sessionData.session_id);
                if (csrfToken) {
                    formData += '&csrf_token=' + encodeURIComponent(csrfToken);
                }
                
                fetch('logout.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-Token': csrfToken || ''
                    },
                    body: formData
                })
                .then(response => {
                    // Xóa session khỏi localStorage sau khi server đã xử lý
                    localStorage.removeItem('session');
                    localStorage.removeItem('user');
                    
                    // Hiển thị thông báo
                    const messageDiv = document.getElementById('messageAlert');
                    messageDiv.className = 'alert alert-info';
                    messageDiv.textContent = 'Đã đăng xuất thành công!';
                    messageDiv.style.display = 'block';
                    
                    // Ẩn thông báo sau 2 giây
                    setTimeout(() => {
                        messageDiv.style.display = 'none';
                    }, 2000);
                    
                    // Reload trang để cập nhật UI
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                })
                .catch(error => {
                    console.error('Lỗi khi đăng xuất:', error);
                    // Vẫn xóa localStorage ngay cả khi server lỗi
                    localStorage.removeItem('session');
                    localStorage.removeItem('user');
                    window.location.reload();
                });
            } else {
                // Không có session_id, chỉ xóa localStorage
                localStorage.removeItem('session');
                localStorage.removeItem('user');
                window.location.reload();
            }
        } catch (e) {
            console.error('Lỗi parse session data:', e);
            localStorage.removeItem('session');
            localStorage.removeItem('user');
            window.location.reload();
        }
    } else {
        // Không có session data
        localStorage.removeItem('session');
        localStorage.removeItem('user');
        window.location.reload();
    }
}
</script>