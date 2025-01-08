<?php
require_once __DIR__ . '/../core/Database.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';

if (!$token) {
    header('Location: /login.php');
    exit;
}

$db = Database::getInstance();

// 验证令牌
$reset = $db->fetch(
    "SELECT pr.*, u.email 
     FROM password_resets pr
     JOIN users u ON pr.user_id = u.id
     WHERE pr.token = ? AND pr.used = 0 AND pr.expires_at > NOW()",
    [$token]
);

if (!$reset) {
    $error = '重置链接无效或已过期。';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    if (strlen($password) < 6) {
        $error = '密码长度至少为6位。';
    } elseif ($password !== $confirm) {
        $error = '两次输入的密码不一致。';
    } else {
        try {
            // 更新密码
            $db->beginTransaction();
            
            $db->execute(
                "UPDATE users SET password = ? WHERE id = ?",
                [password_hash($password, PASSWORD_DEFAULT), $reset['user_id']]
            );
            
            $db->execute(
                "UPDATE password_resets SET used = 1 WHERE id = ?",
                [$reset['id']]
            );
            
            $db->commit();
            $success = '密码重置成功！';
            
        } catch (Exception $e) {
            $db->rollBack();
            $error = '密码重置失败，请重试。';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>重置密码 - 设备仪器商城</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-header text-center">
                        <h4 class="mb-0">重置密码</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <?= $error ?>
                                <div class="mt-2">
                                    <a href="/forgot-password.php" class="text-decoration-none">重新发送重置链接</a>
                                </div>
                            </div>
                        <?php elseif ($success): ?>
                            <div class="alert alert-success">
                                <?= $success ?>
                                <div class="mt-2">
                                    <a href="/login.php" class="text-decoration-none">返回登录</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <form method="post">
                                <div class="mb-3">
                                    <label for="password" class="form-label">新密码</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <div class="form-text">密码长度至少为6位。</div>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">确认密码</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">重置密码</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>