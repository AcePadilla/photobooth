<?php
require_once 'session_config.php';

// --- IDAGDAG ANG MGA HEADERS NA ITO ---
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
// --- END NG DAGDAG ---

$session_data = ['layouts' => []];

if (file_exists(SESSION_FILE)) {
    $json_content = file_get_contents(SESSION_FILE);
    $data = json_decode($json_content, true);
    if (is_array($data) && isset($data['layouts'])) {
        $session_data = $data;
    }
}

echo json_encode($session_data);
?>