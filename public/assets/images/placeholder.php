<?php
// 设置图片头
header('Content-Type: image/jpeg');

// 创建图片
$width = 400;
$height = 300;
$image = imagecreatetruecolor($width, $height);

// 设置颜色
$bg = imagecolorallocate($image, 240, 240, 240);  // 浅灰色背景
$text_color = imagecolorallocate($image, 120, 120, 120);  // 深灰色文字

// 填充背景
imagefill($image, 0, 0, $bg);

// 添加文字
$text = "Product Image";
$font_size = 5;
$text_box = imagettfbbox($font_size, 0, 'arial', $text);
$text_width = abs($text_box[4] - $text_box[0]);
$text_height = abs($text_box[5] - $text_box[1]);
$x = ($width - $text_width) / 2;
$y = ($height - $text_height) / 2;

imagestring($image, $font_size, $x, $y, $text, $text_color);

// 输出图片
imagejpeg($image);
imagedestroy($image);
