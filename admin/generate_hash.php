<?php
// generate_hash.php
$password = 'Wency#23'; // Palitan mo ito ng gusto mong password
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Ang password na: <strong>" . $password . "</strong><br>";
echo "Ang hash na ilalagay sa database: <br><br>";
echo "<textarea cols='60' rows='3'>" . $hash . "</textarea>";
echo "<br><br>Copyahin ang hash na nasa taas at i-update ang 'password_hash' column sa iyong 'admins' table sa database.";
?>