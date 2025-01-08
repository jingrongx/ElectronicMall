<?php
// 确保在任何输出之前启动会话
session_start();

// 设置响应头
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../core/Database.php';

// 初始化购物车
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $product_id = $data['product_id'] ?? null;
        $quantity = intval($data['quantity'] ?? 1);
        $action = $data['action'] ?? 'add';
        
        if (!$product_id || $quantity < 0) {
            throw new Exception('无效的商品或数量');
        }
        
        if ($action === 'update') {
            $_SESSION['cart'][$product_id] = $quantity;
        } else {
            // 添加到购物车
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id] += $quantity;
            } else {
                $_SESSION['cart'][$product_id] = $quantity;
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => '购物车已更新',
            'cart_count' => array_sum($_SESSION['cart'])
        ]);
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);
        $product_id = $data['product_id'] ?? null;
        
        if ($product_id && isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
            echo json_encode([
                'success' => true,
                'message' => '商品已从购物车中移除',
                'cart_count' => array_sum($_SESSION['cart'])
            ]);
            exit;
        }
        
        throw new Exception('商品不存在');
    }
    
    throw new Exception('不支持的请求方法');
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
