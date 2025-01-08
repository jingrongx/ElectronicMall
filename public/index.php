<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');
require_once __DIR__ . '/../core/Database.php';

$db = Database::getInstance();
$latest_products = $db->fetchAll('SELECT * FROM products ORDER BY created_at DESC LIMIT 8');
$featured_categories = $db->fetchAll('SELECT * FROM categories LIMIT 4');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>设备仪器商城</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- 轮播图 -->
    <div id="mainCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="/assets/images/banner1.jpg" class="d-block w-100" alt="Banner 1">
            </div>
            <!-- 添加更多轮播项 -->
        </div>
    </div>

    <!-- 商品分类 -->
    <div class="container mt-5">
        <h2 class="mb-4">商品分类</h2>
        <div class="row">
            <?php foreach ($featured_categories as $category): ?>
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?= $category['name'] ?></h5>
                        <p class="card-text"><?= mb_substr($category['description'], 0, 50) ?>...</p>
                        <a href="/categories.php?id=<?= $category['id'] ?>" class="btn btn-outline-primary">查看更多</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 最新商品 -->
    <div class="container mt-5">
        <h2 class="mb-4">最新商品</h2>
        <div class="row">
            <?php foreach ($latest_products as $product): ?>
            <div class="col-md-3 mb-4">
                <div class="card h-100">
                    <?php if ($product['image']): ?>
                        <img src="<?= $product['image'] ?>" class="card-img-top" alt="<?= $product['name'] ?>">
                    <?php else: ?>
                        <img src="/assets/images/no-image.jpg" class="card-img-top" alt="No Image">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?= $product['name'] ?></h5>
                        <p class="card-text"><?= mb_substr($product['description'], 0, 50) ?>...</p>
                        <p class="text-danger">￥<?= number_format($product['price'], 2) ?></p>
                        <a href="/product.php?id=<?= $product['id'] ?>" class="btn btn-primary">查看详情</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
