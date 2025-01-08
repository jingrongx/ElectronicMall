<?php
session_start();
require_once __DIR__ . '/../../../core/Database.php';

// 检查管理员登录状态
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => '未登录']);
    exit;
}

// 获取POST数据
$data = json_decode(file_get_contents('php://input'), true);
$orderId = $data['order_id'] ?? '';

if (!$orderId) {
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
    
    if ($order['status'] !== 'pending_payment') {
        throw new Exception('只能取消待支付的订单');
    }
    
    // 更新订单状态
    $db->execute(
        "UPDATE orders 
         SET status = 'cancelled',
             updated_at = CURRENT_TIMESTAMP
         WHERE id = ?",
        [$orderId]
    );
    
    // 提交事务
    $db->commit();
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    // 回滚事务
    $db->rollBack();
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}