<?php
// Start the session
session_start();

// Nếu chưa login thì đá về login
if (empty($_SESSION['id']) || empty($_SESSION['users'])) {
    header("Location: login.php");
    exit;
}

// Check thêm tính hợp lệ của session
if (
    $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']
    || $_SESSION['ip'] !== $_SERVER['REMOTE_ADDR']
) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

// Timeout session 30 phút
if (!isset($_SESSION['last_activity'])) {
    $_SESSION['last_activity'] = time();
} elseif (time() - $_SESSION['last_activity'] > 1800) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}
$_SESSION['last_activity'] = time();

require_once 'models/UserModel.php';
$userModel = new UserModel();

$params = [];
if (!empty($_GET['keyword'])) {
    // Escape input để chống XSS, còn chống SQL injection thì sửa trong UserModel
    $params['keyword'] = htmlspecialchars($_GET['keyword'], ENT_QUOTES, 'UTF-8');
}

$users = $userModel->getUsers($params);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Home</title>
    <?php include 'views/meta.php' ?>
</head>

<body>
    <?php include 'views/header.php' ?>
    <div class="container">
        <div class="alert alert-success" role="alert">
            Xin chào <b><?php echo htmlspecialchars($_SESSION['users'], ENT_QUOTES, 'UTF-8'); ?></b>
        </div>

        <?php if (!empty($users)) { ?>
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
                    <?php foreach ($users as $user) { ?>
                        <tr>
                            <th scope="row"><?php echo $user['id'] ?></th>
                            <td><?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?php echo htmlspecialchars($user['fullname'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?php echo htmlspecialchars($user['type'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>

                            <td>
                                <a href="form_user.php?id=<?php echo $user['id'] ?>">
                                    <i class="fa fa-pencil-square-o" aria-hidden="true" title="Update"></i>
                                </a>
                                <a href="view_user.php?id=<?php echo $user['id'] ?>">
                                    <i class="fa fa-eye" aria-hidden="true" title="View"></i>
                                </a>
                                <a href="delete_user.php?id=<?php echo $user['id'] ?>">
                                    <i class="fa fa-eraser" aria-hidden="true" title="Delete"></i>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="alert alert-dark" role="alert">
                No users found.
            </div>
        <?php } ?>
    </div>
</body>

</html>