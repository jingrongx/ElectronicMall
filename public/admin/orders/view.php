<?php
$pageTitle = '订单详情';
require_once __DIR__ . '/../../../core/Database.php';

// 获取订单ID
$orderId = $_GET['id'] ?? 0;
if (!$orderId) {
    header('Location: index.php');
    exit;
}

$db = Database::getInstance();

// 获取订单信息
$order = $db->fetch(
    "SELECT o.*, 
            u.username, u.phone, u.email,
            COALESCE(a.province, '') as province,
            COALESCE(a.city, '') as city,
            COALESCE(a.district, '') as district,
            COALESCE(a.address, '') as address,
            COALESCE(a.receiver, '') as receiver,
            COALESCE(a.receiver_phone, '') as receiver_phone,
            o.payment_time,
            o.shipping_time
     FROM orders o
     LEFT JOIN users u ON o.user_id = u.id
     LEFT JOIN addresses a ON o.address_id = a.id
     WHERE o.id = ?",
    [$orderId]
);

if (!$order) {
    header('Location: index.php');
    exit;
}

// 获取订单商品
$items = $db->fetchAll(
    "SELECT oi.*, p.name, p.image
     FROM order_items oi
     LEFT JOIN products p ON oi.product_id = p.id
     WHERE oi.order_id = ?",
    [$orderId]
);

// 获取订单日志
$logs = $db->fetchAll(
    "SELECT ol.*, a.name as admin_name
     FROM order_logs ol
     LEFT JOIN admins a ON ol.admin_id = a.id
     WHERE ol.order_id = ?
     ORDER BY ol.created_at DESC",
    [$orderId]
);

// 订单状态映射
$statusMap = [
    'pending_payment' => ['待支付', 'warning'],
    'pending_shipment' => ['待发货', 'info'],
    'shipped' => ['已发货', 'primary'],
    'completed' => ['已完成', 'success'],
    'cancelled' => ['已取消', 'danger']
];

// 开始输出
ob_start();
?>

<div class="container-fluid">
    <!-- 订单基本信息 -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">订单信息</h5>
            <div>
                <?php if ($order['status'] === 'pending_shipment'): ?>
                    <button type="button" class="btn btn-success" onclick="showShipModal()">
                        发货
                    </button>
                <?php endif; ?>
                <a href="index.php" class="btn btn-secondary">返回列表</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th class="w-25">订单编号：</th>
                            <td><?= $order['id'] ?></td>
                        </tr>
                        <tr>
                            <th>订单状态：</th>
                            <td>
                                <span class="badge bg-<?= $statusMap[$order['status']][1] ?>">
                                    <?= $statusMap[$order['status']][0] ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>下单时间：</th>
                            <td><?= date('Y-m-d H:i:s', strtotime($order['created_at'])) ?></td>
                        </tr>
                        <tr>
                            <th>支付时间：</th>
                            <td><?= $order['payment_time'] ? date('Y-m-d H:i:s', strtotime($order['payment_time'])) : '-' ?></td>
                        </tr>
                        <tr>
                            <th>发货时间：</th>
                            <td><?= $order['shipping_time'] ? date('Y-m-d H:i:s', strtotime($order['shipping_time'])) : '-' ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th class="w-25">用户名：</th>
                            <td><?= htmlspecialchars($order['username']) ?></td>
                        </tr>
                        <tr>
                            <th>联系电话：</th>
                            <td><?= htmlspecialchars($order['phone']) ?></td>
                        </tr>
                        <tr>
                            <th>电子邮箱：</th>
                            <td><?= htmlspecialchars($order['email']) ?></td>
                        </tr>
                        <tr>
                            <th>收货人：</th>
                            <td><?= htmlspecialchars($order['receiver']) ?></td>
                        </tr>
                        <tr>
                            <th>收货电话：</th>
                            <td><?= htmlspecialchars($order['receiver_phone']) ?></td>
                        </tr>
                        <tr>
                            <th>收货地址：</th>
                            <td>
                                <?= htmlspecialchars($order['province'] . $order['city'] . $order['district'] . $order['address']) ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 商品信息 -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">商品信息</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>商品图片</th>
                            <th>商品名称</th>
                            <th>单价</th>
                            <th>数量</th>
                            <th>小计</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <img src="<?= htmlspecialchars($item['image']) ?>" 
                                         alt="<?= htmlspecialchars($item['name']) ?>"
                                         class="img-thumbnail"
                                         style="max-width: 50px;">
                                </td>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td>￥<?= number_format($item['price'], 2) ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td>￥<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="4" class="text-end"><strong>总计：</strong></td>
                            <td><strong>￥<?= number_format($order['total_amount'], 2) ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 物流信息 -->
    <?php if ($order['shipping_company']): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">物流信息</h5>
        </div>
        <div class="card-body">
            <table class="table table-borderless">
                <tr>
                    <th class="w-25">物流公司：</th>
                    <td>
                        <?php
                        $companies = [
                            'sf' => '顺丰快递',
                            'zt' => '中通快递',
                            'yd' => '韵达快递',
                            'ems' => 'EMS'
                        ];
                        echo $companies[$order['shipping_company']] ?? $order['shipping_company'];
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>物流单号：</th>
                    <td><?= htmlspecialchars($order['tracking_number']) ?></td>
                </tr>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- 订单日志 -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">订单日志</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>操作时间</th>
                            <th>操作人</th>
                            <th>操作内容</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= date('Y-m-d H:i:s', strtotime($log['created_at'])) ?></td>
                                <td><?= htmlspecialchars($log['admin_name'] ?: '系统') ?></td>
                                <td><?= htmlspecialchars($log['action']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- 发货模态框 -->
<div class="modal fade" id="shipModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">订单发货</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="shipForm">
                    <input type="hidden" name="order_id" value="<?= $orderId ?>">
                    <div class="mb-3">
                        <label class="form-label">物流公司</label>
                        <select name="shipping_company" class="form-select" required>
                            <option value="sf">顺丰快递</option>
                            <option value="zt">中通快递</option>
                            <option value="yd">韵达快递</option>
                            <option value="ems">EMS</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">物流单号</label>
                        <input type="text" name="tracking_number" class="form-control" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" onclick="submitShipment()">确认发货</button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// JavaScript 脚本
ob_start();
?>
<script>
let shipModal;

document.addEventListener('DOMContentLoaded', function() {
    shipModal = new bootstrap.Modal(document.getElementById('shipModal'));
});

function showShipModal() {
    shipModal.show();
}

function submitShipment() {
    const form = document.getElementById('shipForm');
    const formData = new FormData(form);
    
    fetch('/admin/api/ship_order.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('发货成功！');
            location.reload();
        } else {
            alert(data.message || '发货失败');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('发货失败，请重试');
    });
}
</script>
<?php
$scripts = ob_get_clean();

require_once __DIR__ . '/../includes/layout.php';
?>