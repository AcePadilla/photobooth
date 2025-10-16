<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$response = [];
$upload_dir = "uploads_temp/";

if (isset($_FILES['image'])) {
    // Basic validation
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
    if (!in_array($_FILES['image']['type'], $allowed_types)) {
        $response = ['status' => 'error', 'message' => 'Invalid file type.'];
    } else {
        $filename = time() . '_' . basename($_FILES["image"]["name"]);
        $target_file = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Return the web-accessible path
            $response = ['status' => 'success', 'url' => $target_file];
        } else {
            $response = ['status' => 'error', 'message' => 'Failed to upload file.'];
        }
    }
} else {
    $response = ['status' => 'error', 'message' => 'No file received.'];
}

echo json_encode($response);
?>