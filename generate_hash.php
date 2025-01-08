<?php
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Password Hash: " . $hash . "\n";

// 验证
$verify = password_verify('admin123', $hash);
echo "Verification result: " . ($verify ? 'true' : 'false') . "\n";