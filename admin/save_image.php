<?php
// Folder kung saan ise-save ang mga images
$upload_dir = "uploads/";

// Kunin ang image data na pinadala via POST
$img_data = $_POST['imageData'];

// Linisin ang data
// Ang data ay may format na "data:image/png;base64,STUFF"
// Kailangan nating tanggalin ang "data:image/png;base64," para makuha lang ang actual na data
$img_data = str_replace('data:image/png;base64,', '', $img_data);
$img_data = str_replace(' ', '+', $img_data);

// I-decode ang Base64 data para maging binary image data
$decoded_data = base64_decode($img_data);

// Gumawa ng unique na filename para hindi mag-overwrite
$filename = uniqid() . '.png';
$file_path = $upload_dir . $filename;

// Isulat ang image data sa file
$success = file_put_contents($file_path, $decoded_data);

// Ibalik (echo) ang path ng na-save na file pabalik sa JavaScript
if ($success) {
    // Kailangan nating ayusin ang path para ma-access ito ng HTML
    // Ang ../save_image.php ay nasa root, kaya ang path pabalik sa image ay 'uploads/filename.png'
    echo 'uploads/' . $filename;
} else {
    // Magpadala ng error message kung hindi nag-succeed
    // It will be caught by the .catch() in JavaScript
    http_response_code(500);
    echo 'Unable to save the file.';
}
?>