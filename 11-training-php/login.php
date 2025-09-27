<?php
// Thiết lập cookie params trước khi session_start()
// Lưu ý: 'secure' => true chỉ có tác dụng khi site chạy HTTPS
session_set_cookie_params([
    'lifetime' => 0,          // hết khi đóng trình duyệt
    'path' => '/',
    'domain' => '',           // để mặc định
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', // true nếu HTTPS
    'httponly' => true,       // JS không thể đọc cookie
    'samesite' => 'Strict'    // giảm rủi ro CSRF
]);

session_start();

require_once 'models/UserModel.php';
$userModel = new UserModel();

// --- Throttle cơ bản: giới hạn 5 lần thử trong 15 phút ---
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['first_attempt_time'] = time();
} else {
    // reset nếu đã qua 15 phút kể từ lần thử đầu
    if (time() - $_SESSION['first_attempt_time'] > 15 * 60) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['first_attempt_time'] = time();
    }
}

$message = '';
if (!empty($_POST['submit'])) {
    // Kiểm tra throttle
    if ($_SESSION['login_attempts'] >= 5) {
        $message = 'Bạn đã thử quá nhiều lần. Vui lòng thử lại sau 15 phút.';
    } else {
        // Lấy dữ liệu an toàn hơn
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        // Validate cơ bản
        if ($username === '' || $password === '') {
            $message = 'Vui lòng nhập username và password.';
            $_SESSION['login_attempts']++;
        } else {
            // Gọi hàm auth của model (giả sử model làm chuẩn: prepared statements + password_verify)
            $user = $userModel->auth($username, $password);
            if ($user) {
                // Login thành công -> regenerate session id
                session_regenerate_id(true);

                // Lưu session an toàn
                $_SESSION['id'] = $user[0]['id'];
                $_SESSION['users'] = $user[0]['name'];
                $_SESSION['message'] = 'Login successful';

                // Ràng buộc session với user agent & IP (IP có thể thay đổi ở NAT/mobile -> cân nhắc)
                $_SESSION['user_agent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
                $_SESSION['ip'] = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
                $_SESSION['last_activity'] = time();

                // Reset lại login attempts
                $_SESSION['login_attempts'] = 0;
                $_SESSION['first_attempt_time'] = time();

                header('Location: list_users.php');
                exit;
            } else {
                $message = 'Login failed';
                $_SESSION['login_attempts']++;
            }
        }
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
                <?php if ($message): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
                <form id="login-form" method="post" class="form-horizontal" role="form" autocomplete="off">

                    <div class="margin-bottom-25 input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                        <input id="login-username" type="text" class="form-control" name="username" value="" placeholder="username or email" required>
                    </div>

                    <div class="margin-bottom-25 input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                        <input id="login-password" type="password" class="form-control" name="password" placeholder="password" required>
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
            </div>
        </div>
    </div>
</div>

<script>
// Lưu username vào localStorage trước khi submit
document.getElementById("login-form").addEventListener("submit", function(e) {
    let username = document.getElementById("login-username").value;
    if (document.getElementById("remember").checked) {
        localStorage.setItem("username", username);
    } else {
        localStorage.removeItem("username");
    }
});

// Tự động điền lại nếu có lưu
window.onload = function() {
    let savedUser = localStorage.getItem("username");
    if (savedUser) {
        document.getElementById("login-username").value = savedUser;
        document.getElementById("remember").checked = true;
    }
    console.log("LocalStorage:", localStorage); // để test trong F12 console
}
</script>

</body>
</html>
