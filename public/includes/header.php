<?php
// 确保 session 已经启动
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="/">设备仪器商城</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/">首页</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/categories.php">商品分类</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/about.php">关于我们</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <?= htmlspecialchars($_SESSION['username']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/user/profile.php">个人中心</a></li>
                            <li><a class="dropdown-item" href="/user/orders.php">我的订单</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/logout.php">退出登录</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/login.php">登录</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/register.php">注册</a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="/cart.php">
                        <i class="bi bi-cart"></i> 购物车
                        <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
                            <span class="badge bg-danger cart-badge"><?= array_sum($_SESSION['cart']) ?></span>
                        <?php else: ?>
                            <span class="badge bg-danger cart-badge" style="display: none;">0</span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
