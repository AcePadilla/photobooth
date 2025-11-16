<?php
session_start();

if (isset($_GET['file'])) {
    $filename_png = basename($_GET['file']); // e.g., "photo-123.png"
    $upload_dir = '../uploads/';
    $file_path_png = $upload_dir . $filename_png;

    // Buuin ang path para sa associated GIF
    $filename_gif = str_replace(['.png', '.jpg', '.jpeg'], '.gif', $filename_png);
    $file_path_gif = $upload_dir . $filename_gif;

    $png_deleted = false;
    $gif_deleted = false; 
    $error_msg = '';

    // Subukang i-delete ang PNG (Print)
    if (file_exists($file_path_png)) {
        if (unlink($file_path_png)) {
            $png_deleted = true;
        } else {
            $error_msg .= 'Could not delete main photo. ';
        }
    } else {
        $error_msg .= 'Main photo not found. ';
    }

    // Subukang i-delete ang GIF (Boomerang)
    if (file_exists($file_path_gif)) {
        if (unlink($file_path_gif)) {
            $gif_deleted = true;
        } else {
            $error_msg .= 'Could not delete associated GIF. ';
        }
    }

    if ($png_deleted) {
        $message = $gif_deleted ? 'Photo & associated GIF deleted.' : 'Photo deleted.';
        $_SESSION['alert'] = ['type' => 'success', 'message' => $message];
    } else {
        $_SESSION['alert'] = ['type' => 'error', 'message' => $error_msg];
    }

} else {
    $_SESSION['alert'] = ['type' => 'error', 'message' => 'No file specified.'];
}

header('Location: dashboard.php');
exit;
?>