<?php
session_start();
require_once __DIR__ . '/../core/Database.php';

// 初始化购物车
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// 获取购物车中的商品信息
$cart_items = [];
if (!empty($_SESSION['cart'])) {
    $db = Database::getInstance();
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
    
    $products = $db->fetchAll(
        "SELECT * FROM products WHERE id IN ($placeholders)",
        $product_ids
    );
    
    foreach ($products as $product) {
        $cart_items[] = [
            'product' => $product,
            'quantity' => $_SESSION['cart'][$product['id']],
            'subtotal' => $product['price'] * $_SESSION['cart'][$product['id']]
        ];
    }
}

// 计算总金额
$total = array_sum(array_column($cart_items, 'subtotal'));
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>购物车 - 设备仪器商城</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <h2 class="mb-4">购物车</h2>
        
        <?php if (empty($cart_items)): ?>
            <div class="alert alert-info">
                购物车是空的，<a href="/categories.php">去购物</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>商品</th>
                            <th>单价</th>
                            <th>数量</th>
                            <th>小计</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?= htmlspecialchars($item['product']['image']) ?>" 
                                             alt="<?= htmlspecialchars($item['product']['name']) ?>" 
                                             style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px;">
                                        <div>
                                            <a href="/product.php?id=<?= $item['product']['id'] ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($item['product']['name']) ?>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td>￥<?= number_format($item['product']['price'], 2) ?></td>
                                <td>
                                    <input type="number" 
                                           class="form-control form-control-sm quantity-input" 
                                           style="width: 80px"
                                           value="<?= $item['quantity'] ?>" 
                                           min="1" 
                                           max="<?= $item['product']['stock'] ?>"
                                           data-product-id="<?= $item['product']['id'] ?>">
                                </td>
                                <td>￥<?= number_format($item['subtotal'], 2) ?></td>
                                <td>
                                    <button class="btn btn-danger btn-sm remove-item" 
                                            data-product-id="<?= $item['product']['id'] ?>">
                                        删除
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>总计：</strong></td>
                            <td colspan="2"><strong>￥<?= number_format($total, 2) ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="d-flex justify-content-between mt-4">
                <a href="/categories.php" class="btn btn-secondary">继续购物</a>
                <a href="/checkout.php" class="btn btn-primary">结算</a>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 更新商品数量
        const quantityInputs = document.querySelectorAll('.quantity-input');
        quantityInputs.forEach(input => {
            input.addEventListener('change', function() {
                const productId = this.dataset.productId;
                const quantity = parseInt(this.value);
                
                if (quantity < 1) {
                    this.value = 1;
                    return;
                }
                
                updateCartItem(productId, quantity);
            });
        });
        
        // 删除商品
        const removeButtons = document.querySelectorAll('.remove-item');
        removeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.productId;
                if (confirm('确定要删除这个商品吗？')) {
                    removeCartItem(productId);
                }
            });
        });
    });
    
    function updateCartItem(productId, quantity) {
        fetch('/api/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: quantity,
                action: 'update'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || '更新失败');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('更新失败，请重试');
        });
    }
    
    function removeCartItem(productId) {
        fetch('/api/cart.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || '删除失败');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('删除失败，请重试');
        });
    }
    </script>
</body>
</html>