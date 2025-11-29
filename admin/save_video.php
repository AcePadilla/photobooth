<?php
// admin/save_video.php

require_once 'session_config.php'; // Security check

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_FILES['videoData']) && isset($_POST['filename'])) {
        
        $uploadDir = '../uploads/'; // Folder sa labas ng admin
        
        // Siguraduhing may uploads folder
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = basename($_POST['filename']);
        $targetFile = $uploadDir . $filename;

        // I-move ang uploaded file
        if (move_uploaded_file($_FILES['videoData']['tmp_name'], $targetFile)) {
            // Ibalik ang path na maiintindihan ng index.html (walang "../")
            echo "uploads/" . $filename;
        } else {
            http_response_code(500);
            echo "Failed to save video file.";
        }
    } else {
        http_response_code(400);
        echo "No video data received.";
    }
}
?>