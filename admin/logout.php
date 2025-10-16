<?php
session_start();

// Tanggalin lahat ng session variables
session_unset();

// Sirain ang session
session_destroy();

// I-redirect sa login page
header('Location: login.php');
exit;
?>