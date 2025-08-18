<?php
$scriptDir = __DIR__;
$selectedDir = '';
$images = [];#VER指定目录
if (isset($_GET['ver'])) {
    $ver = $_GET['ver'];
    $selectedDir = $scriptDir . '/' . $ver;
    if (!is_dir($selectedDir) || !is_readable($selectedDir)) {
        http_response_code(404);
        exit;
    }
} else {
    $dirs = array_filter(glob($scriptDir . '/*'), 'is_dir');
    if (empty($dirs)) {
        http_response_code(404);
        exit;
    }
    $randomDir = $dirs[array_rand($dirs)];
    $selectedDir = $randomDir;
}
$images = glob($selectedDir . '/*.webp'); #可自行修改适配格式
if (empty($images)) {
    http_response_code(404);
    exit;
}
#将此文件放到网站第一目录，图片目录与此文件同级
$randomImage = $images[array_rand($images)];
header('Content-Type: image/webp');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
readfile($randomImage);
?>    