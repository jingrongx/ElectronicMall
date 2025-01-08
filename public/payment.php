<?php
session_start();
require_once __DIR__ . '/../core/Database.php';

// 检查用户是否登录
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

// 获取订单ID和支付方式
$orderId = $_GET['id'] ?? 0;
$paymentMethod = $_GET['method'] ?? 'alipay'; // 设置默认支付方式为支付宝

$db = Database::getInstance();

// 获取订单信息
$order = $db->fetch(
    "SELECT * FROM orders WHERE id = ? AND user_id = ?",
    [$orderId, $_SESSION['user_id']]
);

// 如果订单不存在或不属于当前用户
if (!$order) {
    header('Location: /');
    exit;
}

// 如果订单已支付
if ($order['status'] !== 'pending_payment') {
    header("Location: /order_detail.php?id=$orderId");
    exit;
}

// 生成支付交易号
$transactionNo = date('YmdHis') . rand(1000, 9999);

// 创建支付交易记录
$db->execute(
    "INSERT INTO financial_transactions (order_id, user_id, amount, type, payment_method, status, transaction_no) 
     VALUES (?, ?, ?, 'payment', ?, 'pending', ?)",
    [$orderId, $_SESSION['user_id'], $order['total_amount'], $paymentMethod, $transactionNo]
);

$transactionId = $db->lastInsertId();

// 获取支付方式显示文本
$paymentMethodText = $paymentMethod === 'wechat' ? '微信' : '支付宝';
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>订单支付 - 设备仪器商城</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">订单支付</h4>
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title mb-4">支付金额：￥<?= number_format($order['total_amount'], 2) ?></h5>
                        <p class="text-muted mb-4">订单号：<?= $orderId ?></p>
                        
                        <!-- 支付方式选择 -->
                        <div class="payment-method mb-4">
                            <div class="btn-group" role="group">
                                <a href="?id=<?= $orderId ?>&method=alipay" 
                                   class="btn btn<?= $paymentMethod === 'alipay' ? '' : '-outline' ?>-primary">
                                    <i class="bi bi-alipay"></i> 支付宝支付
                                </a>
                                <a href="?id=<?= $orderId ?>&method=wechat" 
                                   class="btn btn<?= $paymentMethod === 'wechat' ? '' : '-outline' ?>-success">
                                    <i class="bi bi-wechat"></i> 微信支付
                                </a>
                            </div>
                        </div>
                        
                        <!-- 支付二维码 -->
                        <div class="qr-code-container mb-4">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= urlencode($transactionNo) ?>" 
                                 alt="支付二维码" 
                                 class="img-fluid border p-2">
                        </div>
                        
                        <div class="payment-status mb-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">请使用<?= $paymentMethodText ?>扫码支付</p>
                        </div>

                        <div class="alert alert-info" role="alert">
                            <i class="bi bi-info-circle"></i>
                            请在30分钟内完成支付，超时订单将自动取消
                        </div>

                        <div class="mt-4">
                            <button type="button" class="btn btn-secondary me-2" onclick="checkPaymentStatus()">
                                <i class="bi bi-arrow-clockwise"></i> 刷新支付状态
                            </button>
                            <a href="/order_detail.php?id=<?= $orderId ?>" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left"></i> 返回订单详情
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    let checkCount = 0;
    const maxChecks = 60; // 最多检查60次
    
    function checkPaymentStatus() {
        if (checkCount >= maxChecks) {
            alert('支付超时，请重新发起支付');
            window.location.href = '/order_detail.php?id=<?= $orderId ?>';
            return;
        }

        fetch('/api/check_payment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                order_id: <?= $orderId ?>,
                transaction_id: <?= $transactionId ?>
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // 支付成功
                alert('支付成功！');
                window.location.href = '/order_detail.php?id=<?= $orderId ?>';
            } else if (data.status === 'pending') {
                // 继续检查
                checkCount++;
                setTimeout(checkPaymentStatus, 3000);
            } else {
                // 支付失败
                alert(data.message || '支付失败，请重试');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            checkCount++;
            setTimeout(checkPaymentStatus, 3000);
        });
    }

    // 开始自动检查支付状态
    checkPaymentStatus();
    </script>
</body>
</html>