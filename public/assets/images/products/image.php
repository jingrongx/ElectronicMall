<?php
// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 获取请求的文件名
$requested_file = basename($_SERVER['REQUEST_URI']);
$requested_file = urldecode($requested_file);
$requested_file = preg_replace('/\.[^.]+$/', '', $requested_file);

// 设置图片头
header('Content-Type: image/jpeg');

// 创建图片
$width = 400;
$height = 300;
$image = imagecreatetruecolor($width, $height);

// 设置颜色
$bg = imagecolorallocate($image, 240, 240, 240);  // 浅灰色背景
$text_color = imagecolorallocate($image, 100, 100, 100);  // 深灰色文字
$border_color = imagecolorallocate($image, 200, 200, 200);  // 边框颜色

// 填充背景
imagefill($image, 0, 0, $bg);

// 绘制边框
imagerectangle($image, 0, 0, $width-1, $height-1, $border_color);

// 添加文字
$text = $requested_file;
$font_size = 5;

// 自动换行文本
$words = explode(' ', $text);
$lines = [];
$current_line = '';

foreach ($words as $word) {
    $test_line = $current_line . ' ' . $word;
    if (strlen($test_line) > 25) {
        $lines[] = trim($current_line);
        $current_line = $word;
    } else {
        $current_line = trim($test_line);
    }
}
$lines[] = $current_line;

// 计算文本位置并绘制
$line_height = 20;
$total_height = count($lines) * $line_height;
$start_y = ($height - $total_height) / 2;

foreach ($lines as $index => $line) {
    $text_width = strlen($line) * imagefontwidth($font_size);
    $x = ($width - $text_width) / 2;
    $y = $start_y + ($index * $line_height);
    imagestring($image, $font_size, $x, $y, $line, $text_color);
}

// 输出图片
imagejpeg($image);
imagedestroy($image);
