<?php
$pageTitle = '控制台';
require_once __DIR__ . '/../../core/Database.php';

$db = Database::getInstance();

// 获取统计数据
$stats = [
    'orders' => $db->fetch("SELECT COUNT(*) as count FROM orders")['count'],
    'pending_orders' => $db->fetch("SELECT COUNT(*) as count FROM orders WHERE status = 'pending_shipment'")['count'],
    'users' => $db->fetch("SELECT COUNT(*) as count FROM users")['count'],
    'products' => $db->fetch("SELECT COUNT(*) as count FROM products")['count'],
    'today_orders' => $db->fetch("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()")['count'],
    'today_amount' => $db->fetch("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE DATE(created_at) = CURDATE()")['total']
];

// 获取最近订单
$recent_orders = $db->fetchAll(
    "SELECT o.*, u.username 
     FROM orders o 
     LEFT JOIN users u ON o.user_id = u.id 
     ORDER BY o.created_at DESC 
     LIMIT 5"
);

// 开始输出
ob_start();
?>

<div class="container-fluid">
    <!-- 统计卡片 -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">总订单数</h5>
                    <p class="card-text h2"><?= $stats['orders'] ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">待发货订单</h5>
                    <p class="card-text h2"><?= $stats['pending_orders'] ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">今日订单数</h5>
                    <p class="card-text h2"><?= $stats['today_orders'] ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">今日交易额</h5>
                    <p class="card-text h2">￥<?= number_format($stats['today_amount'], 2) ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- 最近订单 -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">最近订单</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>订单号</th>
                                    <th>用户</th>
                                    <th>金额</th>
                                    <th>状态</th>
                                    <th>时间</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                    <tr>
                                        <td>
                                            <a href="/admin/orders/view.php?id=<?= $order['id'] ?>">
                                                <?= $order['id'] ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($order['username']) ?></td>
                                        <td>￥<?= number_format($order['total_amount'], 2) ?></td>
                                        <td>
                                            <?php
                                            $statusMap = [
                                                'pending_payment' => '<span class="badge bg-warning">待支付</span>',
                                                'pending_shipment' => '<span class="badge bg-info">待发货</span>',
                                                'shipped' => '<span class="badge bg-primary">已发货</span>',
                                                'completed' => '<span class="badge bg-success">已完成</span>',
                                                'cancelled' => '<span class="badge bg-danger">已取消</span>'
                                            ];
                                            echo $statusMap[$order['status']] ?? $order['status'];
                                            ?>
                                        </td>
                                        <td><?= date('Y-m-d H:i', strtotime($order['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 系统信息 -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">系统信息</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            注册用户数
                            <span class="badge bg-primary rounded-pill"><?= $stats['users'] ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            商品数量
                            <span class="badge bg-primary rounded-pill"><?= $stats['products'] ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            PHP 版本
                            <span class="badge bg-secondary rounded-pill"><?= PHP_VERSION ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            MySQL 版本
                            <span class="badge bg-secondary rounded-pill">
                                <?= $db->fetch("SELECT VERSION() as version")['version'] ?>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/includes/layout.php';
?>
