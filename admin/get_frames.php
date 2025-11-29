<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

define('FRAMES_DIR', '../public/frames/');
define('FRAMES_URL_PREFIX', 'public/frames/');

// --- BAGONG LOGIC: Kunin ang layout mula sa query string ---
$selected_layout = isset($_GET['layout']) ? basename($_GET['layout']) : '';

$web_paths = [];

// Palaging idagdag ang "Plain White" bilang default option para sa lahat
$web_paths[] = [
    'name' => 'Plain White',
    'url' => 'none' // Special keyword
];

// Maghanap lang ng files na tumutugma sa layout
if (!empty($selected_layout)) {
    // Ang pattern ay: [layout_key]__*.png
    $files = glob(FRAMES_DIR . $selected_layout . '__*.png');

    if (is_array($files)) {
        foreach ($files as $file_path) {
            $filename = basename($file_path);
            
            // --- BAGONG PARSING LOGIC ---
            // Alisin ang prefix (e.g., "strip-3__")
            $display_name_parts = explode('__', $filename, 2);
            $display_name_with_timestamp = isset($display_name_parts[1]) ? $display_name_parts[1] : $filename;

            // Alisin ang timestamp (e.g., "1678886400-")
            $display_name = substr($display_name_with_timestamp, 11); 
            $display_name = preg_replace('/\.png$/i', '', $display_name); // Tanggalin ang .png
            $display_name = str_replace(['-', '_'], ' ', $display_name); // Palitan ng space
            $display_name = ucwords($display_name); // Capitalize
            
            $web_paths[] = [
                'name' => $display_name,
                'url' => FRAMES_URL_PREFIX . $filename
            ];
        }
    }
}

echo json_encode($web_paths);
?>