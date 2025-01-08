<?php
session_start();
require_once __DIR__ . '/../core/Database.php';

// 检查用户是否登录
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

// 获取订单ID
$orderId = $_GET['id'] ?? 0;

// 获取数据库实例
$db = Database::getInstance();

// 获取订单信息（确保只能查看自己的订单）
$order = $db->fetch(
    "SELECT * FROM orders WHERE id = ? AND user_id = ?",
    [$orderId, $_SESSION['user_id']]
);

// 如果订单不存在或不属于当前用户，重定向到首页
if (!$order) {
    header('Location: /');
    exit;
}

// 获取订单项详情
$orderItems = $db->fetchAll(
    "SELECT oi.*, p.name, p.image 
     FROM order_items oi 
     JOIN products p ON oi.product_id = p.id 
     WHERE oi.order_id = ?",
    [$orderId]
);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>订单确认 - 设备仪器商城</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <div class="card">
            <div class="card-body">
                <h1 class="card-title text-success text-center mb-4">
                    <i class="bi bi-check-circle"></i> 订单提交成功
                </h1>
                <div class="text-center mb-4">
                    <p class="lead">订单号：<?= htmlspecialchars($orderId) ?></p>
                    <p>订单金额：￥<?= number_format($order['total_amount'], 2) ?></p>
                </div>
                <hr>
                <h5>订单详情</h5>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>商品</th>
                                <th>数量</th>
                                <th>单价</th>
                                <th>小计</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderItems as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?= htmlspecialchars($item['image']) ?>" 
                                                 alt="<?= htmlspecialchars($item['name']) ?>" 
                                                 style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px;">
                                            <?= htmlspecialchars($item['name']) ?>
                                        </div>
                                    </td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td>￥<?= number_format($item['price'], 2) ?></td>
                                    <td>￥<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>收货地址：</strong><?= htmlspecialchars($order['shipping_address']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>联系电话：</strong><?= htmlspecialchars($order['phone']) ?></p>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <a href="/payment.php?id=<?= $orderId ?>" class="btn btn-primary btn-lg">立即支付</a>
                    <a href="/" class="btn btn-secondary">返回首页</a>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>