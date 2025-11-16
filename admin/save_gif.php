<?php
// admin/save_gif.php

// Kunin ang base filename (e.g., photo-123.png)
$base_filename = isset($_POST['filename']) ? basename($_POST['filename']) : '';

if (empty($base_filename) || !isset($_FILES['gifData'])) {
    http_response_code(400);
    echo 'Error: No filename or file data provided.';
    exit;
}

// Palitan ang extension from .png to .gif
$gif_filename = str_replace('.png', '.gif', $base_filename);

// Ang path kung saan ise-save (../ ay para lumabas sa 'admin' folder at pumasok sa 'uploads')
$upload_dir = '../uploads/';
$file_path = $upload_dir . $gif_filename;

// I-move ang temporary file (ang GIF) sa tamang pwesto
if (move_uploaded_file($_FILES['gifData']['tmp_name'], $file_path)) {
    http_response_code(200);
    // Magpadala ng success message pabalik sa JavaScript
    echo 'Success: GIF saved as ' . $gif_filename;
} else {
    http_response_code(500);
    echo 'Error: Failed to move uploaded file.';
}
?>