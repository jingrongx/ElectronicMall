<?php
require_once __DIR__ . '/../core/Database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    
    if ($email) {
        $db = Database::getInstance();
        $user = $db->fetch(
            "SELECT * FROM users WHERE email = ?",
            [$email]
        );
        
        if ($user) {
            // 生成重置令牌
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // 保存重置令牌
            $db->execute(
                "INSERT INTO password_resets (user_id, token, expires_at) 
                 VALUES (?, ?, ?)",
                [$user['id'], $token, $expiry]
            );
            
            // 发送重置邮件
            $resetLink = "http://localhost/reset-password.php?token=" . $token;
            $to = $user['email'];
            $subject = "=?UTF-8?B?" . base64_encode("重置密码 - 设备仪器商城") . "?=";
            $message = "
<html>
<head>
<meta charset='UTF-8'>
</head>
<body>
    <h2>重置密码</h2>
    <p>您好，</p>
    <p>我们收到了重置您账户密码的请求。如果这不是您本人的操作，请忽略此邮件。</p>
    <p>点击以下链接重置密码（链接有效期为1小时）：</p>
    <p><a href='{$resetLink}'>{$resetLink}</a></p>
    <p>如果您无法点击链接，请复制链接到浏览器地址栏访问。</p>
    <p>祝您使用愉快！</p>
    <p>设备仪器商城团队</p>
</body>
</html>
";

            $headers = array(
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=UTF-8',
                'From: 设备仪器商城 <noreply@localhost.com>',
                'Reply-To: noreply@localhost.com',
                'X-Mailer: PHP/' . phpversion()
            );

            if (mail($to, $subject, $message, implode("\r\n", $headers))) {
                $success = '重置密码链接已发送到您的邮箱，请查收。';
            } else {
                $error = '发送邮件失败，请稍后重试。';
                error_log("Failed to send password reset email to: " . $to);
            }
        } else {
            $error = '该邮箱未注册。';
        }
    } else {
        $error = '请输入邮箱地址。';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>忘记密码 - 设备仪器商城</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-header text-center">
                        <h4 class="mb-0">忘记密码</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php else: ?>
                            <form method="post">
                                <div class="mb-3">
                                    <label for="email" class="form-label">邮箱地址</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                    <div class="form-text">
                                        我们将向您的邮箱发送重置密码的链接。
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">发送重置链接</button>
                            </form>
                        <?php endif; ?>
                        
                        <div class="mt-3 text-center">
                            <a href="/login.php" class="text-decoration-none">返回登录</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>