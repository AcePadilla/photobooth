<?php
// Laging simulan ang session sa pinakataas
session_start();

// Hardcoded admin credentials (for simplicity)
$admin_email = 'wency@gmail.com';
$admin_password = 'wency123'; // Sa totoong project, gamit ka ng password_hash()

$error_message = '';

// I-check kung may na-submit na form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // I-verify ang credentials
    if ($email === $admin_email && $password === $admin_password) {
        // Tama ang credentials, mag-set ng session variable
        $_SESSION['admin_logged_in'] = true;
        // I-redirect sa gallery
        header('Location: dashboard.php');
        exit;
    } else {
        // Mali ang credentials
        $error_message = 'Invalid email or password.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-800 text-white flex items-center justify-center min-h-screen">

    <div class="bg-gray-900 p-8 rounded-lg shadow-xl w-full max-w-sm">
        <h1 class="text-3xl font-bold text-center mb-6">Admin Login</h1>

        <?php if (!empty($error_message)): ?>
            <div class="bg-red-500 text-white p-3 rounded mb-4 text-center">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="mb-4">
                <label for="email" class="block mb-2 text-sm font-medium text-gray-300">Email</label>
                <input type="email" name="email" id="email" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
            </div>
            <div class="mb-6">
                <label for="password" class="block mb-2 text-sm font-medium text-gray-300">Password</label>
                <input type="password" name="password" id="password" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
            </div>
            <button type="submit" class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                Log In
            </button>
        </form>
    </div>

</body>
</html>