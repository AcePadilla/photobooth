<?php
// admin/get_session.php

// 1. I-off ang errors para hindi mahaluan ng HTML ang JSON response
error_reporting(0);
ini_set('display_errors', 0);

// 2. Headers para sa JSON at No-Cache
header('Content-Type: application/json');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// 3. Isama ang session config (para sa whitelist check)
// Siguraduhin na walang echo o space sa loob ng session_config.php
require_once 'session_config.php';

// 4. Define ang JSON file (Dapat pareho sa dashboard.php)
define('SESSION_FILE', 'active_session.json');

// 5. Basahin ang file at ibalik sa Booth
if (file_exists(SESSION_FILE)) {
    $content = file_get_contents(SESSION_FILE);
    
    if (empty(trim($content)) || $content === false) {
        echo json_encode(['layouts' => []]);
    } else {
        echo $content;
    }
} else {
    echo json_encode(['layouts' => []]);
}
?>