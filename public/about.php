<?php
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>关于我们 - 设备仪器商城</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">首页</a></li>
                <li class="breadcrumb-item active" aria-current="page">关于我们</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-8">
                <h1 class="mb-4">关于我们</h1>
                
                <div class="card mb-4">
                    <div class="card-body">
                        <h2 class="card-title h4">公司简介</h2>
                        <p class="card-text">
                            设备仪器商城成立于2024年，是一家专注于实验室设备、测量仪器、分析仪器和医疗设备销售的专业电子商务平台。
                            我们致力于为科研机构、实验室、医疗机构等提供高品质的仪器设备和专业的技术支持服务。
                        </p>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <h2 class="card-title h4">我们的优势</h2>
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <h5>专业品质</h5>
                                <p>所有产品均经过严格质量把关，确保每一件商品都符合专业标准。</p>
                            </li>
                            <li class="mb-3">
                                <h5>技术支持</h5>
                                <p>专业的技术团队提供售前、售中、售后全程技术支持服务。</p>
                            </li>
                            <li class="mb-3">
                                <h5>快速配送</h5>
                                <p>与多家物流公司合作，确保商品快速安全送达。</p>
                            </li>
                            <li class="mb-3">
                                <h5>售后保障</h5>
                                <p>提供标准化的售后服务流程，解决您的后顾之忧。</p>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h2 class="card-title h4">联系我们</h2>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <strong>电话：</strong> 400-123-4567
                            </li>
                            <li class="mb-2">
                                <strong>邮箱：</strong> info@example.com
                            </li>
                            <li class="mb-2">
                                <strong>地址：</strong> 北京市海淀区科技园区888号
                            </li>
                            <li class="mb-2">
                                <strong>工作时间：</strong> 周一至周五 9:00-18:00
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title h4">关注我们</h2>
                        <p>扫描下方二维码，关注我们的公众号：</p>
                        <div class="text-center">
                            <!-- 这里可以放置二维码图片 -->
                            <img src="/assets/images/qrcode.jpg" alt="公众号二维码" class="img-fluid" style="max-width: 200px;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
