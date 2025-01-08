<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');
require_once __DIR__ . '/../core/Database.php';

// 获取商品ID
$id = $_GET['id'] ?? 0;
$db = Database::getInstance();

// 获取商品详情
$product = $db->fetch(
    'SELECT p.*, c.name as category_name 
     FROM products p 
     LEFT JOIN categories c ON p.category_id = c.id 
     WHERE p.id = ?', 
    [$id]
);

// 如果商品不存在，重定向到首页
if (!$product) {
    header('Location: /');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - 设备仪器商城</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">首页</a></li>
                <li class="breadcrumb-item"><a href="/categories.php">商品分类</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($product['name']) ?></li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-6">
                <img src="<?= htmlspecialchars($product['image']) ?>" class="img-fluid" alt="<?= htmlspecialchars($product['name']) ?>">
            </div>
            <div class="col-md-6">
                <h1 class="mb-4"><?= htmlspecialchars($product['name']) ?></h1>
                <p class="text-muted">分类：<?= htmlspecialchars($product['category_name']) ?></p>
                <h2 class="text-danger mb-4">￥<?= number_format($product['price'], 2) ?></h2>
                <p class="mb-4"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                <div class="mb-4">
                    <label for="quantity" class="form-label">数量</label>
                    <input type="number" class="form-control w-25" id="quantity" value="1" min="1" max="<?= $product['stock'] ?>">
                </div>
                <button class="btn btn-primary btn-lg" onclick="addToCart(<?= $product['id'] ?>)">加入购物车</button>
                <p class="text-muted mt-2">库存：<?= $product['stock'] ?> 件</p>
            </div>
        </div>

        <div class="mt-5">
            <h3>商品详情</h3>
            <hr>
            <div class="product-description">
                <?= nl2br(htmlspecialchars($product['description'])) ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- 添加购物车功能的 JavaScript -->
    <script>
    function addToCart(productId) {
        const quantity = parseInt(document.getElementById('quantity').value);
        
        if (isNaN(quantity) || quantity < 1) {
            alert('请输入有效的数量');
            return;
        }
        
        fetch('/api/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: quantity
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert(data.message);
                // 更新购物车数量显示
                const cartBadge = document.querySelector('.cart-badge');
                if (cartBadge) {
                    cartBadge.textContent = data.cart_count;
                    cartBadge.style.display = 'inline';
                }
            } else {
                alert(data.message || '添加到购物车失败');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('添加到购物车失败，请重试');
        });
    }
    </script>
</body>
</html>
