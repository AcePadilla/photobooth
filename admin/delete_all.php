<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['confirm_delete'])) {
    $_SESSION['alert'] = ['type' => 'error', 'message' => 'Invalid delete request.'];
    header('Location: dashboard.php');
    exit;
}

$upload_dir = '../uploads/';
// *** BINAGO: Buburahin na ang LAHAT ng file types, pati GIF ***
$files = glob($upload_dir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
$deleted_count = 0;
$error_count = 0;

foreach ($files as $file) {
    if (is_file($file)) {
        if (unlink($file)) {
            $deleted_count++;
        } else {
            $error_count++;
        }
    }
}

if ($error_count > 0) {
    $_SESSION['alert'] = ['type' => 'warning', 'message' => "Successfully deleted $deleted_count files, but failed to delete $error_count files."];
} else {
    $_SESSION['alert'] = ['type' => 'success', 'message' => "All $deleted_count files have been successfully deleted."];
}

header('Location: dashboard.php');
exit;
?>