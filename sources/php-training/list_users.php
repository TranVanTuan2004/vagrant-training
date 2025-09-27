<?php
require_once 'auth_helper.php';
require_once 'models/UserModel.php';
require_once 'csrf_helper.php';

// Chỉ kiểm tra login nếu có session_id trong URL
if (!empty($_GET['session_id'])) {
    $auth->requireLogin();
} else {
    // Nếu không có session_id, hiển thị trang với JavaScript để xử lý
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Home</title>
        <?php include 'views/meta.php' ?>
        <?php echo $csrf->getMetaTag(); ?>
    </head>
    <body>
        <?php include 'views/header.php'?>
        
        <div class="container">
            <div class="alert alert-info">
                <i class="fa fa-spinner fa-spin"></i> Đang kiểm tra phiên đăng nhập...
            </div>
        </div>

        <script>
        // Kiểm tra session và chuyển hướng
        document.addEventListener('DOMContentLoaded', function() {
            const sessionData = localStorage.getItem('session');
            
            if (!sessionData) {
                console.log('Không có session data');
                window.location.href = 'login.php';
                return;
            }
            
            try {
                const session = JSON.parse(sessionData);
                console.log('Session data:', session);
                
                if (!session.session_id || !session.is_logged_in) {
                    console.log('Session không hợp lệ');
                    localStorage.removeItem('session');
                    window.location.href = 'login.php';
                    return;
                }
                
                // Chuyển hướng với session_id
                const url = new URL(window.location);
                url.searchParams.set('session_id', session.session_id);
                window.location.href = url.toString();
                
            } catch (e) {
                console.error('Lỗi parse session data:', e);
                localStorage.removeItem('session');
                window.location.href = 'login.php';
            }
        });
        </script>
    </body>
    </html>
    <?php
    exit;
}

// Nếu có session_id, tiếp tục xử lý bình thường
$userModel = new UserModel();

$params = [];
if (!empty($_GET['keyword'])) {
    $params['keyword'] = trim($_GET['keyword']);
    // Sanitize keyword input
    $params['keyword'] = htmlspecialchars($params['keyword'], ENT_QUOTES, 'UTF-8');
}

$users = $userModel->getUsers($params);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <?php include 'views/meta.php' ?>
    <?php echo $csrf->getMetaTag(); ?>
</head>
<body>
    <?php include 'views/header.php'?>
    <div class="container">
        <?php if (!empty($users)) {?>
            <div class="alert alert-warning" role="alert">
                List of users! <br>
                Hacker: http://php.local/list_users.php?keyword=ASDF%25%22%3BTRUNCATE+banks%3B%23%23
            </div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Username</th>
                        <th scope="col">Fullname</th>
                        <th scope="col">Type</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user) {?>
                        <tr>
                            <th scope="row"><?php echo htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8')?></th>
                            <td>
                                <?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8')?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($user['fullname'], ENT_QUOTES, 'UTF-8')?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($user['type'], ENT_QUOTES, 'UTF-8')?>
                            </td>
                            <td>
                                <a href="form_user.php?id=<?php echo htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8') ?>">
                                    <i class="fa fa-pencil-square-o" aria-hidden="true" title="Update"></i>
                                </a>
                                <a href="view_user.php?id=<?php echo htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8') ?>">
                                    <i class="fa fa-eye" aria-hidden="true" title="View"></i>
                                </a>
                                <a href="delete_user.php?id=<?php echo htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8') ?>">
                                    <i class="fa fa-eraser" aria-hidden="true" title="Delete"></i>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php }else { ?>
            <div class="alert alert-dark" role="alert">
                This is a dark alert—check it out!
            </div>
        <?php } ?>
    </div>

<?php echo $csrf->getAjaxScript(); ?>

</body>
</html>