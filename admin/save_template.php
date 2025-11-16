<?php
date_default_timezone_set('Asia/Manila');
header('Content-Type: application/json');
$baseDir = 'templates/';

function send_response($status, $message, $data = []) {
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_response('error', 'Invalid request method.');
}

if (!isset($_POST['filename']) || !isset($_POST['json_data']) || !isset($_POST['image_data'])) {
    send_response('error', 'Missing required data.');
}

$filename = $_POST['filename'];
$jsonData = $_POST['json_data'];
$imageData = $_POST['image_data'];

if (empty(trim($filename))) {
    send_response('error', 'Filename cannot be empty.');
}

$safeFilename = preg_replace('/[^a-zA-Z0-9\-_]/', '_', $filename);

if (strpos($safeFilename, '..') !== false) {
    send_response('error', 'Invalid filename.');
}

if (empty($safeFilename)) {
    send_response('error', 'Invalid filename provided.');
}

if (!is_dir($baseDir) && !mkdir($baseDir, 0775, true)) {
    send_response('error', "Failed to create directory: {$baseDir}");
}

$jsonFilePath = $baseDir . $safeFilename . '.json';
$pngFilePath = $baseDir . $safeFilename . '.png';

if (file_put_contents($jsonFilePath, $jsonData) === false) {
    send_response('error', "Failed to save template data to {$jsonFilePath}. Check permissions.");
}

$imageData = str_replace('data:image/png;base64,', '', $imageData);
$imageData = str_replace(' ', '+', $imageData);
$decodedImage = base64_decode($imageData);

if ($decodedImage === false) {
    send_response('error', 'Failed to decode image data.');
}

if (file_put_contents($pngFilePath, $decodedImage) === false) {
    unlink($jsonFilePath); 
    send_response('error', "Failed to save template preview to {$pngFilePath}. Check permissions.");
}

send_response('success', "Template '{$safeFilename}' saved successfully!");
?>
