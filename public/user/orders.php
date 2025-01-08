<?php
session_start();
require_once __DIR__ . '/../../core/Database.php';

// 检查用户是否登录
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$db = Database::getInstance();

// 获取用户的所有订单
$orders = $db->fetchAll(
    "SELECT o.*, 
            COUNT(oi.id) as item_count,
            GROUP_CONCAT(p.name SEPARATOR '、') as product_names
     FROM orders o 
     LEFT JOIN order_items oi ON o.id = oi.order_id
     LEFT JOIN products p ON oi.product_id = p.id
     WHERE o.user_id = ?
     GROUP BY o.id
     ORDER BY o.created_at DESC",
    [$_SESSION['user_id']]
);

// 订单状态映射
$statusMap = [
    'pending_payment' => ['待支付', 'warning'],
    'pending_shipment' => ['待发货', 'info'],
    'shipped' => ['已发货', 'primary'],
    'completed' => ['已完成', 'success'],
    'cancelled' => ['已取消', 'danger']
];
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>我的订单 - 设备仪器商城</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container mt-4">
        <h2 class="mb-4">我的订单</h2>

        <?php if (empty($orders)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> 您还没有任何订单
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>订单号</th>
                            <th>商品</th>
                            <th>金额</th>
                            <th>状态</th>
                            <th>下单时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>
                                    <a href="/order_detail.php?id=<?= $order['id'] ?>" 
                                       class="text-decoration-none">
                                        <?= $order['id'] ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 300px;">
                                        <?= htmlspecialchars($order['product_names']) ?>
                                    </div>
                                    <small class="text-muted">
                                        共<?= $order['item_count'] ?>件商品
                                    </small>
                                </td>
                                <td>￥<?= number_format($order['total_amount'], 2) ?></td>
                                <td>
                                    <span class="badge bg-<?= $statusMap[$order['status']][1] ?>">
                                        <?= $statusMap[$order['status']][0] ?>
                                    </span>
                                </td>
                                <td><?= date('Y-m-d H:i', strtotime($order['created_at'])) ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="/order_detail.php?id=<?= $order['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            查看详情
                                        </a>
                                        <?php if ($order['status'] === 'pending_payment'): ?>
                                            <a href="/payment.php?id=<?= $order['id'] ?>" 
                                               class="btn btn-sm btn-primary">
                                                立即支付
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($order['status'] === 'shipped'): ?>
                                            <button type="button" 
                                                    class="btn btn-sm btn-success"
                                                    onclick="confirmReceipt(<?= $order['id'] ?>)">
                                                确认收货
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
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
</body>
</html>