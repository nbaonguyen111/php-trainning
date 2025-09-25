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
}
header('Location: list_users.php');
exit;

?>