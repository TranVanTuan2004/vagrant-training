<?php
require_once 'models/UserModel.php';
$userModel = new UserModel();

$user = NULL; //Add new user
$id = NULL;

if (!empty($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($id !== false) {
        $userModel->deleteUserById($id);//Delete existing user
    }
}
header('location: list_users.php');
?>