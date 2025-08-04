<?php
// view_pdf.php
if (!isset($_GET['file'])) {
    http_response_code(400);
    echo "No file specified.";
    exit;
}

$file = $_GET['file'];

// Security: prevent directory traversal
$file = basename($file);

$path = __DIR__ . "/uploads/" . $file;

if (!file_exists($path)) {
    http_response_code(404);
    echo "File not found.";
    exit;
}

header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=\"" . basename($path) . "\"");
header("Content-Length: " . filesize($path));

readfile($path);
exit;
