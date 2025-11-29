<?php
// admin/use_session.php

require_once 'session_config.php'; // Security check

define('SESSION_FILE', 'active_session.json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['layout'])) {
    $layoutToRemove = $_POST['layout'];

    if (file_exists(SESSION_FILE)) {
        // 1. Kunin ang current data
        $jsonContent = file_get_contents(SESSION_FILE);
        $data = json_decode($jsonContent, true);

        if (is_array($data) && isset($data['layouts'])) {
            // 2. Hanapin ang index ng layout na tatanggalin
            $index = array_search($layoutToRemove, $data['layouts']);

            // 3. Kung nahanap, tanggalin ang ISA lang
            if ($index !== false) {
                array_splice($data['layouts'], $index, 1);

                // 4. I-save ulit sa JSON file
                if (file_put_contents(SESSION_FILE, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX)) {
                    echo "success";
                } else {
                    http_response_code(500);
                    echo "Error writing file";
                }
            } else {
                echo "Layout not found in queue";
            }
        }
    } else {
        echo "Session file not found";
    }
}
?>