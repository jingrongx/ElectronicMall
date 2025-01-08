<?php
session_start();
require_once __DIR__ . '/../core/Database.php';

// 添加错误显示
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 检查用户是否登录
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

// 获取订单ID
$orderId = $_GET['id'] ?? 0;
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

// 获取订单项
$orderItems = $db->fetchAll(
    "SELECT oi.*, p.name, p.image 
     FROM order_items oi 
     JOIN products p ON oi.product_id = p.id 
     WHERE oi.order_id = ?",
    [$orderId]
);

// 获取订单状态中文描述
$statusMap = [
    'pending_payment' => '待支付',
    'pending_shipment' => '待发货',
    'shipped' => '已发货',
    'completed' => '已完成',
    'cancelled' => '已取消'
];

// 获取支付方式中文描述
$paymentMethodMap = [
    'alipay' => '支付宝',
    'wechat' => '微信支付'
];
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>订单详情 - 设备仪器商城</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">订单详情</h4>
                <span class="badge bg-<?= $order['status'] === 'completed' ? 'success' : 'primary' ?>">
                    <?= $statusMap[$order['status']] ?? '未知状态' ?>
                </span>
            </div>
            <div class="card-body">
                <!-- 订单状态和操作 -->
                <div class="row mb-4">
                    <div class="col">
                        <?php if ($order['status'] === 'pending_payment'): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                                订单待支付，请尽快完成支付
                            </div>
                            <a href="/payment.php?id=<?= $orderId ?>" class="btn btn-primary">
                                <i class="bi bi-credit-card"></i> 立即支付
                            </a>
                        <?php elseif ($order['status'] === 'shipped'): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-truck"></i>
                                商品已发货，请注意查收
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 订单基本信息 -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="mb-3">订单信息</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>订单编号：</strong></td>
                                <td><?= $orderId ?></td>
                            </tr>
                            <tr>
                                <td><strong>下单时间：</strong></td>
                                <td><?= date('Y-m-d H:i:s', strtotime($order['created_at'])) ?></td>
                            </tr>
                            <tr>
                                <td><strong>支付方式：</strong></td>
                                <td><?= $paymentMethodMap[$order['payment_method']] ?? '未支付' ?></td>
                            </tr>
                            <?php if ($order['payment_time']): ?>
                            <tr>
                                <td><strong>支付时间：</strong></td>
                                <td><?= date('Y-m-d H:i:s', strtotime($order['payment_time'])) ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5 class="mb-3">收货信息</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>收货地址：</strong></td>
                                <td><?= htmlspecialchars($order['shipping_address']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>联系电话：</strong></td>
                                <td><?= htmlspecialchars($order['phone']) ?></td>
                            </tr>
                            <?php if ($order['tracking_number']): ?>
                            <tr>
                                <td><strong>物流公司：</strong></td>
                                <td><?= htmlspecialchars($order['shipping_company']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>物流单号：</strong></td>
                                <td><?= htmlspecialchars($order['tracking_number']) ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <!-- 商品列表 -->
                <h5 class="mb-3">商品信息</h5>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>商品</th>
                                <th>单价</th>
                                <th>数量</th>
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
                                    <td>￥<?= number_format($item['price'], 2) ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td>￥<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>总计：</strong></td>
                                <td><strong>￥<?= number_format($order['total_amount'], 2) ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- 底部按钮 -->
                <div class="text-center mt-4">
                    <a href="/user/orders.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> 返回订单列表
                    </a>
                    <?php if ($order['status'] === 'shipped'): ?>
                        <button class="btn btn-success" onclick="confirmReceipt(<?= $orderId ?>)">
                            <i class="bi bi-check-circle"></i> 确认收货
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php if ($order['status'] === 'shipped'): ?>
    <script>
    function confirmReceipt(orderId) {
        if (confirm('确认已收到商品？')) {
            fetch('/api/confirm_receipt.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    order_id: orderId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('确认收货成功！');
                    location.reload();
                } else {
                    alert(data.message || '操作失败');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('操作失败，请重试');
            });
        }
    }
    </script>
    <?php endif; ?>
</body>
</html>