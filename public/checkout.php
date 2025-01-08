<?php
// 在文件最开始添加错误显示
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/../core/Database.php';

// 添加调试信息
var_dump($_SESSION);
var_dump('Checkpoint 1');

// 检查用户是否登录
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = '/checkout.php';
    header('Location: /login.php');
    exit;
}

var_dump('Checkpoint 2');

// 检查购物车是否为空
if (empty($_SESSION['cart'])) {
    header('Location: /cart.php');
    exit;
}

var_dump('Checkpoint 3');

try {
    $db = Database::getInstance();

    // 获取购物车商品信息
    $cart_items = [];
    $total = 0;

    if (!empty($_SESSION['cart'])) {
        $product_ids = array_keys($_SESSION['cart']);
        $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
        
        $products = $db->fetchAll(
            "SELECT * FROM products WHERE id IN ($placeholders)",
            $product_ids
        );
        
        foreach ($products as $product) {
            $quantity = $_SESSION['cart'][$product['id']];
            $subtotal = $product['price'] * $quantity;
            $cart_items[] = [
                'product' => $product,
                'quantity' => $quantity,
                'subtotal' => $subtotal
            ];
            $total += $subtotal;
        }
    }

    var_dump('Checkpoint 4');

    // 处理表单提交
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // 验证必填字段
            if (empty($_POST['address']) || empty($_POST['phone'])) {
                throw new Exception('请填写完整的收货信息');
            }

            $db->beginTransaction();

            // 创建订单
            $userId = $_SESSION['user_id'];
            $address = $_POST['address'];
            $phone = $_POST['phone'];
            
            // 插入订单
            $db->execute(
                "INSERT INTO orders (user_id, total_amount, shipping_address, phone, status) 
                 VALUES (?, ?, ?, ?, 'pending_payment')",
                [$userId, $total, $address, $phone]
            );
            
            $orderId = $db->lastInsertId();

            // 插入订单项
            foreach ($cart_items as $item) {
                $db->execute(
                    "INSERT INTO order_items (order_id, product_id, quantity, price) 
                     VALUES (?, ?, ?, ?)",
                    [$orderId, $item['product']['id'], $item['quantity'], $item['product']['price']]
                );
                
                // 更新库存
                $db->execute(
                    "UPDATE products SET stock = stock - ? WHERE id = ?",
                    [$item['quantity'], $item['product']['id']]
                );
            }

            $db->commit();
            
            // 清空购物车
            $_SESSION['cart'] = [];
            
            // 跳转到订单确认页面
            header("Location: /order_confirmation.php?id=" . $orderId);
            exit;
            
        } catch (Exception $e) {
            $db->rollBack();
            $error = $e->getMessage();
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    var_dump($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>结算 - 设备仪器商城</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <h2 class="mb-4">订单结算</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">收货信息</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" id="checkout-form">
                            <div class="mb-3">
                                <label for="address" class="form-label">收货地址</label>
                                <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">联系电话</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">支付方式</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment" id="payment1" value="alipay" checked>
                            <label class="form-check-label" for="payment1">
                                支付宝
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment" id="payment2" value="wechat">
                            <label class="form-check-label" for="payment2">
                                微信支付
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">订单摘要</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <div>
                                    <?= htmlspecialchars($item['product']['name']) ?> × <?= $item['quantity'] ?>
                                </div>
                                <div>
                                    ￥<?= number_format($item['subtotal'], 2) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>总计</strong>
                            <strong>￥<?= number_format($total, 2) ?></strong>
                        </div>
                    </div>
                </div>

                <button type="submit" form="checkout-form" class="btn btn-primary w-100 mt-3">
                    提交订单
                </button>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
