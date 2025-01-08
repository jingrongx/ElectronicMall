<?php
require_once __DIR__ . '/../core/Database.php';

$db = Database::getInstance();
$categories = $db->fetchAll('SELECT * FROM categories');
$products = $db->fetchAll('SELECT p.*, c.name as category_name 
                          FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品分类 - 设备仪器商城</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <h2 class="mb-4">商品分类</h2>
        <div class="row">
            <div class="col-md-3">
                <div class="list-group">
                    <?php foreach ($categories as $category): ?>
                        <a href="?category=<?= $category['id'] ?>" class="list-group-item list-group-item-action">
                            <?= $category['name'] ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-md-9">
                <div class="row">
                    <?php foreach ($products as $product): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <img src="<?= $product['image'] ?>" class="card-img-top" alt="<?= $product['name'] ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?= $product['name'] ?></h5>
                                    <p class="card-text"><?= mb_substr($product['description'], 0, 100) ?>...</p>
                                    <p class="text-danger">￥<?= number_format($product['price'], 2) ?></p>
                                    <a href="/product.php?id=<?= $product['id'] ?>" class="btn btn-primary">查看详情</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
