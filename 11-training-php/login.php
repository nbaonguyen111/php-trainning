<?php
require 'vendor/autoload.php'; // Predis

// Kết nối Redis cho queue
$redis = new Predis\Client([
    'scheme' => 'tcp',
    'host'   => 'myredis',
    'port'   => 6379,
]);

// Dùng session Redis
ini_set("session.save_handler", "redis");
ini_set("session.save_path", "tcp://myredis:6379");
ini_set("session.gc_maxlifetime", 3600);
session_start();

require_once 'models/UserModel.php';
$userModel = new UserModel();

if (!empty($_POST['submit'])) {
    $users = [
        'username' => $_POST['username'],
        'password' => $_POST['password']
    ];
    $user = NULL;

    if ($user = $userModel->auth($users['username'], $users['password'])) {
        // Login thành công
        $_SESSION['id'] = $user[0]['id'];
        $_SESSION['message'] = 'Login successful';

        // Thêm thông tin user vào Redis queue để dễ debug
        $job = [
            'user_id' => $user[0]['id'],
            'username' => $user[0]['name'],
            'email' => $user[0]['email'],
            'login_time' => date('Y-m-d H:i:s')
        ];
        $redis->lpush('user_login_queue', json_encode($job));

        header('location: list_users.php');
        exit;
    } else {
        $_SESSION['message'] = 'Login failed';
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
                <form id="login-form" method="post" class="form-horizontal" role="form">

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
