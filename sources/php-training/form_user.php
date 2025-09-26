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
    
    if (empty($name) || empty($password)) {
        $error = "Tên và mật khẩu không được để trống";
    } else {
        if (!empty($_id)) {
            $userModel->updateUser($_POST);
        } else {
            $userModel->insertUser($_POST);
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