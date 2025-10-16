<?php
session_start();

// Security Check: Siguraduhing admin lang ang pwedeng mag-delete
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Kung hindi admin, huwag ituloy
    http_response_code(403); // Forbidden
    $_SESSION['message'] = "Error: Unauthorized access.";
    header('Location: dashboard.php');
    exit;
}

// Kunin ang filename mula sa URL
if (isset($_GET['file'])) {
    // Gumamit ng basename() para sa security, para maiwasan ang directory traversal attacks
    $filename = basename($_GET['file']); 
    $upload_dir = '../uploads/';
    $filepath = $upload_dir . $filename;

    // I-check kung nage-exist ang file bago burahin
    if (file_exists($filepath)) {
        // Burahin ang file
        if (unlink($filepath)) {
            // Maglagay ng success message
            $_SESSION['message'] = "Photo deleted successfully!";
        } else {
            // Maglagay ng error message kung pumalya ang pag-delete
            $_SESSION['message'] = "Error: Could not delete the photo.";
        }
    } else {
        // Maglagay ng error message kung hindi mahanap ang file
        $_SESSION['message'] = "Error: File not found.";
    }
} else {
    $_SESSION['message'] = "Error: No file specified to delete.";
}

// Pagkatapos ng proseso, ibalik sa dashboard
header('Location: dashboard.php');
exit;
?>