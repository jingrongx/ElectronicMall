<?php
session_start();
require_once __DIR__ . '/../../core/Database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '未登录']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $orderId = $data['order_id'] ?? 0;
    $transactionId = $data['transaction_id'] ?? 0;

    $db = Database::getInstance();

    // 获取交易信息
    $transaction = $db->fetch(
        "SELECT * FROM financial_transactions 
         WHERE id = ? AND order_id = ? AND user_id = ?",
        [$transactionId, $orderId, $_SESSION['user_id']]
    );

    if (!$transaction) {
        echo json_encode(['success' => false, 'message' => '交易不存在']);
        exit;
    }

    // 模拟支付过程
    // 在实际应用中，这里应该调用支付网关的API检查支付状态
    $randomSuccess = (rand(1, 10) === 1); // 10%的概率支付成功

    if ($randomSuccess && $transaction['status'] === 'pending') {
        $db->beginTransaction();

        try {
            // 更新交易状态
            $db->execute(
                "UPDATE financial_transactions SET status = 'completed' WHERE id = ?",
                [$transactionId]
            );

            // 更新订单状态
            $db->execute(
                "UPDATE orders SET 
                    status = 'pending_shipment',
                    payment_method = ?,
                    payment_time = NOW()
                 WHERE id = ?",
                [$transaction['payment_method'], $orderId]
            );

            $db->commit();

            echo json_encode([
                'success' => true,
                'message' => '支付成功'
            ]);
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    // 返回待支付状态
    echo json_encode([
        'success' => false,
        'status' => 'pending',
        'message' => '等待支付'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}