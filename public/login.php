<?php
session_start();
require_once __DIR__ . '/../core/Database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $db = Database::getInstance();
    $user = $db->fetch(
        "SELECT * FROM users WHERE username = ?",
        [$username]
    );
    
    if ($user && password_verify($password, $user['password'])) {
        // 设置登录状态
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        
        // 检查是否有重定向
        if (isset($_SESSION['redirect_after_login'])) {
            $redirect = $_SESSION['redirect_after_login'];
            unset($_SESSION['redirect_after_login']);
            header("Location: $redirect");
            exit;
        }
        
        header('Location: /');
        exit;
    } else {
        $error = '用户名或密码错误';
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录 - 设备仪器商城</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">用户登录</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <form method="post" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="username" class="form-label">用户名/邮箱</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">密码</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="mt-2">
                                    <a href="/forgot-password.php" class="text-decoration-none">忘记密码？</a>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">登录</button>
                            <a href="/register.php" class="btn btn-link">没有账号？立即注册</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>