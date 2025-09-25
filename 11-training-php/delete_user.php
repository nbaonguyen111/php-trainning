<?php
require_once 'auth.php';
require_once 'models/UserModel.php';
$userModel = new UserModel();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); exit('Method Not Allowed');
}

if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403); exit('Invalid CSRF token');
}


$user = NULL; //Add new user

$id = $_POST['id'] ?? null;

if ($id !== null) {
    $userModel->deleteUserById($id); // Xóa user

    // Cập nhật session nếu user hiện tại bị xóa
    if (isset($_SESSION['user']) && $_SESSION['user']['id'] == $id) {
        unset($_SESSION['user']);  // Xóa thông tin user khỏi session
        session_destroy();         // Hủy session hoàn toàn nếu muốn
        header('Location: login.php'); // Chuyển về trang login
        exit;
    }
}

header('Location: list_users.php');
exit;

?>