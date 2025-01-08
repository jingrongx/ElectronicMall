<?php
session_start();
require_once __DIR__ . '/../../../core/Database.php';

// 检查管理员登录状态
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => '未登录']);
    exit;
}

// 检查请求方法
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => '方法不允许']);
    exit;
}

// 获取参数
$orderId = $_POST['order_id'] ?? '';
$shippingCompany = $_POST['shipping_company'] ?? '';
$trackingNumber = $_POST['tracking_number'] ?? '';

// 验证参数
if (!$orderId || !$shippingCompany || !$trackingNumber) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '参数不完整']);
    exit;
}

try {
    $db = Database::getInstance();
    
    // 开始事务
    $db->beginTransaction();
    
    // 检查订单状态
    $order = $db->fetch(
        "SELECT status FROM orders WHERE id = ?",
        [$orderId]
    );
    
    if (!$order) {
        throw new Exception('订单不存在');
    }
    
    if ($order['status'] !== 'pending_shipment') {
        throw new Exception('订单状态不正确');
    }
    
    // 更新订单状态和物流信息
    $db->execute(
        "UPDATE orders 
         SET status = 'shipped',
             shipping_company = ?,
             tracking_number = ?,
             shipping_time = CURRENT_TIMESTAMP
         WHERE id = ?",
        [$shippingCompany, $trackingNumber, $orderId]
    );
    
    // 添加订单日志
    $companies = [
        'sf' => '顺丰快递',
        'zt' => '中通快递',
        'yd' => '韵达快递',
        'ems' => 'EMS'
    ];
    
    try {
        $db->execute(
            "INSERT INTO order_logs (order_id, admin_id, action) VALUES (?, ?, ?)",
            [
                $orderId, 
                $_SESSION['admin_id'],
                "订单发货 - 物流公司：{$companies[$shippingCompany]}，物流单号：{$trackingNumber}"
            ]
        );
    } catch (Exception $e) {
        // 如果日志记录失败，不影响发货操作
        error_log("Failed to log order shipment: " . $e->getMessage());
    }
    
    // 提交事务
    $db->commit();
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    // 回滚事务
    if (isset($db)) {
        $db->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}