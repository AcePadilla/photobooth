<?php
// Siguraduhin na may laman ang db.php mo
require_once 'db.php';

// --- Ilagay ang gusto mong default admin dito ---
$admin_email = "wency@gmail.com";
$admin_password = "Wency#23";
// ----------------------------------------------

try {
    // I-hash ang password
    $hashed_password = password_hash($admin_password, PASSWORD_BCRYPT);

    // I-check kung may user na
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE email = ?");
    $stmt->execute([$admin_email]);
    if ($stmt->fetchColumn() > 0) {
        echo "<h1>Error: May admin user na!</h1>";
        echo "<p>May user na na may email na: " . htmlspecialchars($admin_email) . "</p>";
        echo "<p>Pumunta ka na sa <a href='login.php'>login.php</a> para mag-login.</p>";
        echo "<p>Kung nakalimutan mo ang password, kailangan mong i-delete manual sa database.</p>";
    } else {
        // Ipasok ang bagong admin sa database
        $stmt = $pdo->prepare("INSERT INTO admins (email, password_hash) VALUES (?, ?)");
        $stmt->execute([$admin_email, $hashed_password]);

        echo "<h1>SUCCESS!</h1>";
        echo "<p>Nagawa na ang admin user:</p>";
        echo "<p><strong>Email:</strong> " . htmlspecialchars($admin_email) . "</p>";
        echo "<p><strong>Password:</strong> " . htmlspecialchars($admin_password) . "</p>";
        echo "<hr>";
        echo "<p>Pumunta ka na sa <a href='login.php'>login.php</a> para mag-login.</p>";
        echo "<h2 style='color:red;'>IMPORTANTE: BURAHIN (DELETE) MO NA ANG 'create_first_admin.php' FILE NA ITO NGAYON!</h2>";
    }

} catch (PDOException $e) {
    echo "<h1>Database Error!</h1>";
    echo "<p>Siguraduhin na ang 'admins' table ay may columns na 'email' at 'password_hash'.</p>";
    echo "<p>Error details: " . $e->getMessage() . "</p>";
}
?>