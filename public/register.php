<?php
session_start();
require_once __DIR__ . '/../core/Database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    
    $db = Database::getInstance();
    
    // 检查用户名是否已存在
    $existingUser = $db->fetch(
        "SELECT id FROM users WHERE username = ?",
        [$username]
    );
    
    if ($existingUser) {
        $error = '用户名已存在';
    } else {
        // 创建新用户
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $db->execute(
                "INSERT INTO users (username, password, email, phone) VALUES (?, ?, ?, ?)",
                [$username, $hashedPassword, $email, $phone]
            );
            
            // 自动登录
            $userId = $db->lastInsertId();
            $_SESSION['user_id'] = $userId;
            $_SESSION['username'] = $username;
            
            // 检查是否有重定向
            if (isset($_SESSION['redirect_after_login'])) {
                $redirect = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
                header("Location: $redirect");
                exit;
            }
            
            header('Location: /');
            exit;
            
        } catch (Exception $e) {
            $error = '注册失败，请重试';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注册 - 设备仪器商城</title>
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
                        <h4 class="mb-0">用户注册</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <form method="post">
                            <div class="mb-3">
                                <label for="username" class="form-label">用户名</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">密码</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">电子邮箱</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">手机号码</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                            <button type="submit" class="btn btn-primary">注册</button>
                            <a href="/login.php" class="btn btn-link">已有账号？立即登录</a>
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