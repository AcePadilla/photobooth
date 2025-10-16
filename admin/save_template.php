<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$response = [];
$frame_dir = "../frames/";

if (isset($_POST['imageData']) && isset($_POST['filename']) && isset($_POST['layout_type'])) {
    $img_data = $_POST['imageData'];
    $layout_type = basename($_POST['layout_type']); // e.g., 'strip-3' or 'custom'
    
    $base_filename = preg_replace('/[^A-Za-z0-9_\-]/', '', $_POST['filename']);
    // Add the correct prefix based on the layout type
    $filename = $layout_type . '_' . $base_filename . '.png';

    if (empty($base_filename)) {
         $response = ['status' => 'error', 'message' => 'Invalid filename.'];
    } else {
        $img_data = str_replace('data:image/png;base64,', '', $img_data);
        $img_data = str_replace(' ', '+', $img_data);
        $decoded_data = base64_decode($img_data);
        $file_path = $frame_dir . $filename;
        
        if (file_exists($file_path)) {
            $response = ['status' => 'error', 'message' => 'A template with this name already exists.'];
        } elseif (file_put_contents($file_path, $decoded_data)) {
            $response = ['status' => 'success', 'message' => 'Template saved successfully.'];
        } else {
            $response = ['status' => 'error', 'message' => 'Failed to save file.'];
        }
    }
} else {
    $response = ['status' => 'error', 'message' => 'Missing data.'];
}

echo json_encode($response);
?>