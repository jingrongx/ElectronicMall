<?php
$pageTitle = '订单管理';
require_once __DIR__ . '/../../../core/Database.php';

// 获取筛选参数
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;

// 构建查询
$db = Database::getInstance();
$where = [];
$params = [];

if ($status) {
    $where[] = "o.status = ?";
    $params[] = $status;
}

if ($search) {
    $where[] = "(o.id LIKE ? OR u.username LIKE ? OR u.phone LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// 获取总记录数
$total = $db->fetch(
    "SELECT COUNT(*) as count 
     FROM orders o 
     LEFT JOIN users u ON o.user_id = u.id 
     $whereClause",
    $params
)['count'];

// 获取订单列表
$orders = $db->fetchAll(
    "SELECT o.*, 
            u.username, u.phone,
            COUNT(oi.id) as item_count,
            GROUP_CONCAT(p.name SEPARATOR '、') as product_names
     FROM orders o 
     LEFT JOIN users u ON o.user_id = u.id
     LEFT JOIN order_items oi ON o.id = oi.order_id
     LEFT JOIN products p ON oi.product_id = p.id
     $whereClause
     GROUP BY o.id
     ORDER BY o.created_at DESC
     LIMIT ? OFFSET ?",
    array_merge($params, [$perPage, ($page - 1) * $perPage])
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
    <!-- 筛选表单 -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">订单状态</label>
                    <select name="status" class="form-select">
                        <option value="">全部状态</option>
                        <?php foreach ($statusMap as $key => $value): ?>
                            <option value="<?= $key ?>" <?= $status === $key ? 'selected' : '' ?>>
                                <?= $value[0] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">搜索</label>
                    <input type="text" name="search" class="form-control" 
                           value="<?= htmlspecialchars($search) ?>" 
                           placeholder="订单号/用户名/手机号">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">搜索</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 订单列表 -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>订单号</th>
                            <th>用户信息</th>
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
                                <td><?= $order['id'] ?></td>
                                <td>
                                    <?= htmlspecialchars($order['username']) ?><br>
                                    <small class="text-muted"><?= htmlspecialchars($order['phone']) ?></small>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;">
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
                                        <a href="view.php?id=<?= $order['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            查看
                                        </a>
                                        <?php if ($order['status'] === 'pending_shipment'): ?>
                                            <button type="button" 
                                                    class="btn btn-sm btn-success"
                                                    onclick="showShipModal(<?= $order['id'] ?>)">
                                                发货
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($order['status'] === 'pending_payment'): ?>
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger"
                                                    onclick="cancelOrder(<?= $order['id'] ?>)">
                                                取消
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- 分页 -->
            <?php if ($totalPages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
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
                    <input type="hidden" id="orderId" name="order_id">
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

function showShipModal(orderId) {
    document.getElementById('orderId').value = orderId;
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

function cancelOrder(orderId) {
    if (confirm('确定要取消这个订单吗？')) {
        fetch('/admin/api/cancel_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ order_id: orderId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('订单已取消');
                location.reload();
            } else {
                alert(data.message || '取消失败');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('操作失败，请重试');
        });
    }
}
</script>
<?php
$scripts = ob_get_clean();

require_once __DIR__ . '/../includes/layout.php';
?>