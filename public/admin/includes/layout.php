<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: /admin/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? '管理后台' ?> - 设备仪器商城</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body {
            font-size: 14px;
            background-color: #f5f6fa;
        }
        .container-fluid {
            max-width: 1800px; /* 增加最大宽度 */
            padding: 20px 30px; /* 增加内边距 */
        }
        .table td, .table th {
            padding: 12px 15px; /* 增加表格单元格内边距 */
        }
        .card {
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .card-body {
            padding: 20px; /* 增加卡片内边距 */
        }
        .form-group {
            margin-bottom: 15px;
        }
        /* 调整导航栏样式 */
        .navbar {
            padding: 10px 30px;
            background-color: #fff !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        /* 调整侧边栏样式 */
        .sidebar {
            padding: 20px;
            background-color: #fff;
            border-right: 1px solid #eee;
        }
        .list-group-item {
            padding: 12px 15px;
            border: none;
            margin-bottom: 5px;
            border-radius: 6px !important;
        }
        /* 调整按钮间距 */
        .btn-group .btn {
            margin-right: 5px;
        }
        /* 调整表单控件大小 */
        .form-control, .form-select {
            padding: 8px 12px;
            font-size: 14px;
        }
        /* 增加表格响应式容器的内边距 */
        .table-responsive {
            padding: 5px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="/admin">管理后台</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link">
                            <i class="bi bi-person"></i> <?= htmlspecialchars($_SESSION['admin_name']) ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/logout.php">
                            <i class="bi bi-box-arrow-right"></i> 退出
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar">
                <div class="list-group">
                    <a href="/admin/index.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-speedometer2"></i> 控制台
                    </a>
                    <a href="/admin/orders/index.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-cart3"></i> 订单管理
                    </a>
                    <a href="/admin/products/index.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-box"></i> 商品管理
                    </a>
                    <a href="/admin/categories/index.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-grid"></i> 分类管理
                    </a>
                    <a href="/admin/users/index.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-people"></i> 用户管理
                    </a>
                    <a href="/admin/refunds/index.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-currency-yen"></i> 退款管理
                    </a>
                    <a href="/admin/after-sales/index.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-headset"></i> 售后管理
                    </a>
                </div>
            </div>
            <div class="col-md-10">
                <?= $content ?? '' ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?= $scripts ?? '' ?>
</body>
</html>    <div class="d-flex">
        <div class="sidebar">
            <div class="list-group">
                <a href="/admin/index.php" class="list-group-item list-group-item-action">
                    <i class="bi bi-speedometer2"></i> 控制台
                </a>
                <a href="/admin/orders/index.php" class="list-group-item list-group-item-action">
                    <i class="bi bi-cart3"></i> 订单管理
                </a>
                <a href="/admin/products/index.php" class="list-group-item list-group-item-action">
                    <i class="bi bi-box"></i> 商品管理
                </a>
                <a href="/admin/categories/index.php" class="list-group-item list-group-item-action">
                    <i class="bi bi-grid"></i> 分类管理
                </a>
                <a href="/admin/users/index.php" class="list-group-item list-group-item-action">
                    <i class="bi bi-people"></i> 用户管理
                </a>
                <a href="/admin/refunds/index.php" class="list-group-item list-group-item-action">
                    <i class="bi bi-currency-yen"></i> 退款管理
                </a>
                <a href="/admin/after-sales/index.php" class="list-group-item list-group-item-action">
                    <i class="bi bi-headset"></i> 售后管理
                </a>
                <?php if ($_SESSION['admin_role'] === 'super_admin'): ?>
                <a href="/admin/admins/index.php" class="list-group-item list-group-item-action">
                    <i class="bi bi-shield-lock"></i> 管理员
                </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="main-content">
            <?= $content ?? '' ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?= $scripts ?? '' ?>
</body>
</html>
