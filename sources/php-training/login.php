<?php
require_once 'models/UserModel.php';
require_once 'models/SessionManager.php';

$userModel = new UserModel();
$sessionManager = new SessionManager();

if (!empty($_POST['submit'])) {
    $users = [
        'username' => $_POST['username'],
        'password' => $_POST['password']
    ];
    $user = NULL;
    if ($user = $userModel->auth($users['username'], $users['password'])) {
        // Tạo session trong Redis
        $userData = [
            'id' => $user[0]['id'],
            'name' => $user[0]['name'],
            'fullname' => $user[0]['fullname']
        ];
        
        $sessionId = $sessionManager->createUserSession($user[0]['id'], $userData);
        
        if ($sessionId) {
            //Login successful - trả JSON với session ID
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'session_id' => $sessionId,
                'user' => $userData
            ]);
        } else {
            // Lỗi tạo session
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi tạo phiên đăng nhập'
            ]);
        }
        exit;
    } else {
        //Login failed - trả JSON
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Login failed'
        ]);
        exit;
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>User form</title>
    <?php include 'views/meta.php' ?>
</head>
<body>
<?php include 'views/header.php'?>

    <div class="container">
        <div id="loginbox" style="margin-top:50px;" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
            <div class="panel panel-info" >
                <div class="panel-heading">
                    <div class="panel-title">Login</div>
                    <div style="float:right; font-size: 80%; position: relative; top:-10px"><a href="#">Forgot password?</a></div>
                </div>

                <div style="padding-top:30px" class="panel-body" >
                    <form id="loginForm" class="form-horizontal" role="form">

                        <div class="margin-bottom-25 input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                            <input id="login-username" type="text" class="form-control" name="username" value="" placeholder="username or email">
                        </div>

                        <div class="margin-bottom-25 input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                            <input id="login-password" type="password" class="form-control" name="password" placeholder="password">
                        </div>

                        <div class="margin-bottom-25">
                            <input type="checkbox" tabindex="3" class="" name="remember" id="remember">
                            <label for="remember"> Remember Me</label>
                        </div>

                        <div class="margin-bottom-25 input-group">
                            <!-- Button -->
                            <div class="col-sm-12 controls">
                                <button type="submit" name="submit" value="submit" class="btn btn-primary">Submit</button>
                                <a id="btn-fblogin" href="#" class="btn btn-primary">Login with Facebook</a>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-12 control">
                                    Don't have an account!
                                    <a href="form_user.php">
                                        Sign Up Here
                                    </a>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Hiển thị thông báo -->
                    <div id="message" class="alert" style="display:none; margin-top: 15px;"></div>
                </div>
            </div>
        </div>
    </div>

<script>
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('submit', 'submit');
    
    fetch('login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const messageDiv = document.getElementById('message');
        
        if (data.success) {
            // Lưu session_id vào localStorage
            const sessionData = {
                session_id: data.session_id,
                user: data.user,
                login_time: new Date().toISOString(),
                is_logged_in: true
            };
            
            localStorage.setItem('session', JSON.stringify(sessionData));
            
            messageDiv.className = 'alert alert-success';
            messageDiv.textContent = data.message;
            messageDiv.style.display = 'block';
            
            setTimeout(() => {
                window.location.href = 'list_users.php?session_id=' + data.session_id;
            }, 1000);
        } else {
            messageDiv.className = 'alert alert-danger';
            messageDiv.textContent = data.message;
            messageDiv.style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const messageDiv = document.getElementById('message');
        messageDiv.textContent = 'Có lỗi xảy ra khi đăng nhập!';
        messageDiv.className = 'alert alert-danger';
        messageDiv.style.display = 'block';
    });
});
</script>

</body>
</html>