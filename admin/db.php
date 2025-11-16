<?php
$db_host = 'localhost';
$db_name = 'photobooth_db';  
$db_user = 'root';          
$db_pass = '';           
$db_char = 'utf8mb4';

$dsn = "mysql:host=$db_host;dbname=$db_name;charset=$db_char";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (\PDOException $e) {
    http_response_code(503);
    die("Can't connect to the database contact Ace Padilla.");
}
?>