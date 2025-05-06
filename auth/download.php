<?php
if (!isset($_GET['file'])) {
    die("اسم الملف غير محدد.");
}

$filename = $_GET['file'];
$filepath = __DIR__ . '/uploads/' . $filename;

if (!file_exists($filepath)) {
    die("الملف غير موجود: " . $filepath);
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filepath));
readfile($filepath);
exit;
