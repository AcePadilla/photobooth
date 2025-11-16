<?php
// admin/use_session.php
require_once 'session_config.php'; // Para makuha ang SESSION_FILE path

// 1. Suriin kung ang request ay POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo 'Error: Invalid request method.';
    exit;
}

// 2. Suriin kung may ipinadalang 'layout'
$layout_to_use = isset($_POST['layout']) ? $_POST['layout'] : '';
if (empty($layout_to_use)) {
    http_response_code(400); // Bad Request
    echo 'Error: No layout specified.';
    exit;
}

// 3. Basahin ang kasalukuyang session file (na may 'file lock')
$json_content = file_get_contents(SESSION_FILE);
$session_data = json_decode($json_content, true);

if (!is_array($session_data) || !isset($session_data['layouts'])) {
    http_response_code(500);
    echo 'Error: Invalid session file structure.';
    exit;
}

// 4. Hanapin at alisin ang *isang* kopya ng layout
$layouts = $session_data['layouts'];
$found_key = array_search($layout_to_use, $layouts);

if ($found_key !== false) {
    // Nahanap! Alisin ito sa array
    array_splice($layouts, $found_key, 1);
    
    // I-update ang session data
    $session_data['layouts'] = $layouts;
    
    // 5. I-save pabalik sa session.json file
    if (file_put_contents(SESSION_FILE, json_encode($session_data, JSON_PRETTY_PRINT), LOCK_EX)) {
        // Success!
        http_response_code(200);
        echo 'Success: Layout used and session updated.';
    } else {
        http_response_code(500);
        echo 'Error: Could not write to session file.';
    }
} else {
    // Hindi nahanap (posibleng naubos na pero nag-reload ang user)
    http_response_code(404);
    echo 'Warning: Layout not found in active session (already used?).';
}

?>