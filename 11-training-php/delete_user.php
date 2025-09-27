<?php
// delete_users.php
session_start();

require_once 'models/UserModel.php';

// --- BASIC AUTH CHECK (tùy app bạn) ---
// Nếu app có login, kiểm tra session user/role
if (empty($_SESSION['id'])) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo 'Method Not Allowed';
    exit;
}

if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(400);
    echo 'Invalid CSRF token';
    exit;
}

// Validate id
if (!isset($_POST['id'])) {
    http_response_code(400);
    echo 'Missing id';
    exit;
}

$id = (int) $_POST['id'];
if ($id <= 0) {
    http_response_code(400);
    echo 'Invalid id';
    exit;
}

// Perform delete
$userModel = new UserModel();
$affected = $userModel->deleteUserById($id);

if ($affected) {
    $_SESSION['message'] = 'User deleted';
} else {
    $_SESSION['message'] = 'No user deleted';
}

header('Location: list_users.php');
exit;
