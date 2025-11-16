<?php
// admin/get_live_data.php
require_once 'session_config.php'; // Kunin ang path ng session file

// --- 1. Kunin ang Session Data ---
$layouts = [];
if (file_exists(SESSION_FILE)) {
    $json_content = file_get_contents(SESSION_FILE);
    $data = json_decode($json_content, true);
    if (is_array($data) && isset($data['layouts'])) {
        $layouts = $data['layouts'];
    }
}

// --- 2. Kunin ang Bilang ng Photos ---
$upload_dir = '../uploads/';
// Bilangin ang .png AT .gif files
$images = glob($upload_dir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
$photo_count = count($images);

// --- 3. Ibalik ang data bilang JSON ---
header('Content-Type: application/json');
echo json_encode([
    'layouts' => $layouts,
    'photo_count' => $photo_count
]);
exit;
?>