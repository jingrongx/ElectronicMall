<?php
session_start();
require_once __DIR__ . '/../../core/Database.php';

// 检查用户是否登录
if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header('Location: /login.php');
    exit;
}

$db = Database::getInstance();
$user = $db->fetch('SELECT * FROM users WHERE id = ?', [$_SESSION['user_id']]);

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    
    if (!empty($current_password) && !empty($new_password)) {
        // 验证当前密码
        if (password_verify($current_password, $user['password'])) {
            // 更新密码
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $db->execute(
                'UPDATE users SET password = ? WHERE id = ?',
                [$hashed_password, $_SESSION['user_id']]
            );
            $success = '密码修改成功！';
        } else {
            $error = '当前密码不正确！';
        }
    }
    
    // 更新其他信息
    $db->execute(
        'UPDATE users SET email = ?, phone = ? WHERE id = ?',
        [$email, $phone, $_SESSION['user_id']]
    );
    $success = '个人信息更新成功！';
    
    // 重新获取用户信息
    $user = $db->fetch('SELECT * FROM users WHERE id = ?', [$_SESSION['user_id']]);
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>个人中心 - 设备仪器商城</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container mt-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">首页</a></li>
                <li class="breadcrumb-item active">个人中心</li>
            </ol>
        </nav>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-3">
                <div class="list-group">
                    <a href="/user/profile.php" class="list-group-item list-group-item-action active">个人信息</a>
                    <a href="/user/orders.php" class="list-group-item list-group-item-action">我的订单</a>
                </div>
            </div>
            <div class="col-md-9">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">个人信息</h5>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">用户名</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">邮箱</label>
                                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">手机号</label>
                                <input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                            </div>
                            <hr>
                            <h5>修改密码</h5>
                            <div class="mb-3">
                                <label class="form-label">当前密码</label>
                                <input type="password" class="form-control" name="current_password">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">新密码</label>
                                <input type="password" class="form-control" name="new_password">
                            </div>
                            <button type="submit" class="btn btn-primary">保存修改</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>