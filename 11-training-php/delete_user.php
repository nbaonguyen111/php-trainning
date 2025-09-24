<?php
require_once 'models/UserModel.php';
$userModel = new UserModel();

// Nhận danh sách id, phân tách bằng dấu phẩy
if (!empty($_GET['id'])) {
    $ids = explode(',', $_GET['id']); 
    foreach ($ids as $id) {
        $userModel->deleteUserById($id);
    }
}

header('location: list_users.php');
exit;
?>
