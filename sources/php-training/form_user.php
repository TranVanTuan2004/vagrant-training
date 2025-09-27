<?php
// Start the session
session_start();
require_once 'models/UserModel.php';
$userModel = new UserModel();

$user = NULL; //Add new user
$_id = NULL;

if (!empty($_GET['id'])) {
    $_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($_id === false) {
        $_id = NULL;
    } else {
        $user = $userModel->findUserById($_id);//Update existing user
    }
}


if (!empty($_POST['submit'])) {
    // Validate input
    $name = trim($_POST['name'] ?? '');
    $password = $_POST['password'] ?? '';
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $type = trim($_POST['type'] ?? 'user');
    
    // Sanitize inputs
    $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $password = htmlspecialchars($password, ENT_QUOTES, 'UTF-8');
    $fullname = htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $type = htmlspecialchars($type, ENT_QUOTES, 'UTF-8');
    
    if (empty($name) || empty($password)) {
        $error = "Tên và mật khẩu không được để trống";
    } elseif (strlen($name) < 3 || strlen($name) > 50) {
        $error = "Tên phải có từ 3-50 ký tự";
    } elseif (strlen($password) < 6) {
        $error = "Mật khẩu phải có ít nhất 6 ký tự";
    } else {
        $inputData = [
            'name' => $name,
            'password' => $password,
            'fullname' => $fullname,
            'email' => $email,
            'type' => $type,
            'id' => $_id
        ];
        
        if (!empty($_id)) {
            $userModel->updateUser($inputData);
        } else {
            $userModel->insertUser($inputData);
        }
        header('location: list_users.php');
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

            <?php if ($user || !isset($_id)) { ?>
                <div class="alert alert-warning" role="alert">
                    User form
                </div>
                <?php if (isset($error)) { ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php } ?>
                <form method="POST">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($_id, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input class="form-control" name="name" placeholder="Name" value='<?php if (!empty($user[0]['name'])) echo htmlspecialchars($user[0]['name'], ENT_QUOTES, 'UTF-8') ?>'>
                    </div>
                    <div class="form-group">
                        <label for="fullname">Full Name</label>
                        <input class="form-control" name="fullname" placeholder="Full Name" value='<?php if (!empty($user[0]['fullname'])) echo htmlspecialchars($user[0]['fullname'], ENT_QUOTES, 'UTF-8') ?>'>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" name="email" placeholder="Email" value='<?php if (!empty($user[0]['email'])) echo htmlspecialchars($user[0]['email'], ENT_QUOTES, 'UTF-8') ?>'>
                    </div>
                    <div class="form-group">
                        <label for="type">Type</label>
                        <select class="form-control" name="type">
                            <option value="user" <?php if (!empty($user[0]['type']) && $user[0]['type'] == 'user') echo 'selected'; ?>>User</option>
                            <option value="admin" <?php if (!empty($user[0]['type']) && $user[0]['type'] == 'admin') echo 'selected'; ?>>Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Password">
                    </div>

                    <button type="submit" name="submit" value="submit" class="btn btn-primary">Submit</button>
                </form>
            <?php } else { ?>
                <div class="alert alert-success" role="alert">
                    User not found!
                </div>
            <?php } ?>
    </div>
</body>
</html>