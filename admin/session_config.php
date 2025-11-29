<?php
// session_config.php

// 1. Secure Session Settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');

// 2. Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Session Hijacking Prevention
if (!isset($_SESSION['last_regeneration'])) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
} else {
    $interval = 60 * 30; // 30 minutes
    if (time() - $_SESSION['last_regeneration'] >= $interval) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

// 4. Authentication Check & Whitelisting
$current_page = basename($_SERVER['PHP_SELF']);

// LISTAHAN NG MGA FILES NA PWEDENG I-ACCESS KAHIT HINDI NAKA-LOGIN
$allowed_pages = [
    'index.php', 
    'login.php', 
    'generate_hash.php',
    'get_session.php',   
    'save_image.php',   
    'save_video.php',   
    'use_session.php',  
    'get_live_data.php', 
    'print_image.php',   
    'delete_photo.php'   
]; 

// Logic: Kung HINDI naka-set ang user_id AT ang current page ay WALA sa allowed list
if (!isset($_SESSION['user_id']) && !in_array($current_page, $allowed_pages)) {
    // Redirect sa login page
    header("Location: index.php"); 
    exit();
}
?>