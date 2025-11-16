<?php
session_start();
require_once 'db.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

const MAX_LOGIN_ATTEMPTS = 5;
const LOCKOUT_TIME = 300;

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}
if (!isset($_SESSION['last_attempt_time'])) {
    $_SESSION['last_attempt_time'] = 0;
}

$error_message = '';
$submitted_email = '';

$time_since_last_attempt = time() - $_SESSION['last_attempt_time'];
if ($_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS && $time_since_last_attempt < LOCKOUT_TIME) {
    $remaining_time = LOCKOUT_TIME - $time_since_last_attempt;
    $error_message = "Masyado nang maraming maling attempt. Subukan ulit pagkatapos ng " . ceil($remaining_time / 60) . " minuto(s).";
} 
else if ($time_since_last_attempt >= LOCKOUT_TIME) {
    $_SESSION['login_attempts'] = 0;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($error_message)) {
    if (!isset($_POST['csrf_token']) || !hash_equals($csrf_token, $_POST['csrf_token'])) {
        $error_message = 'Invalid request. Subukang i-refresh ang page.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $submitted_email = $email;
        
        try {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['login_attempts'] = 0;
                unset($_SESSION['last_attempt_time']);
                
                session_regenerate_id(true);
                
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_email'] = $user['email'];
                $_SESSION['admin_id'] = $user['id'];
                
                header('Location: dashboard.php');
                exit;
            
            } else {
                $_SESSION['login_attempts']++;
                $_SESSION['last_attempt_time'] = time();
                $error_message = 'Invalid email or password.';
            }
        
        } catch (PDOException $e) {
            $error_message = 'Nagkaroon ng error sa system. Pakisubukang muli.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    
    <link rel="icon" type="image/jpeg" href="../public/images/marahuyologo.jpg">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            /* ✅ UI/UX IMPROVEMENT: Subtle gradient background */
            background-image: radial-gradient(circle at center, #111827, #030712);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }
        main {
            animation: fadeIn 0.5s ease-out;
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        'brand-red': {
                            DEFAULT: '#DC2626',
                            light: '#F87171',
                            dark: '#991B1B',
                        },
                        'brand-dark': {
                            light: '#374151',
                            DEFAULT: '#1F2937',
                            dark: '#111827',
                            darkest: '#030712',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="text-gray-300 flex items-center justify-center min-h-screen p-4">

    <main class="w-full max-w-4xl mx-auto bg-brand-dark/70 backdrop-blur-sm border border-gray-700/50 rounded-lg shadow-2xl overflow-hidden md:flex">
        
        <div class="md:w-1/2 p-8 sm:p-12 bg-brand-dark/80 flex flex-col justify-center items-center">
            
            <img src="../public/images/marahuyologo.jpg" alt="Marahuyo Logo" 
                 class="w-32 h-32 rounded-full object-cover shadow-lg border-2 border-brand-dark-light transition-transform duration-300 hover:scale-105">
            
            <h2 class="text-2xl font-bold text-white text-center mt-6">Admin Panel</h2>
            <p class="text-gray-400 text-center mt-2">Secure Management Portal</p>
        </div>

        <div class="w-full md:w-1/2 p-8 sm:p-12">
            <h1 class="text-3xl font-bold text-white text-center mb-6">Admin Login</h1>

            <?php if (!empty($error_message)): ?>
                <div class="bg-red-900/50 border border-red-700 text-red-100 p-3 rounded-lg mb-4 text-center text-sm">
                    <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form id="loginForm" action="login.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                
                <div class="mb-4">
                    <label for="email" class="block mb-2 text-sm font-medium text-gray-300">Email</label>
                    <input type="email" name="email" id="email" 
                           class="bg-brand-dark-light/50 border border-gray-600 text-white text-sm rounded-lg focus:ring-brand-red focus:border-brand-red block w-full p-2.5 transition-colors" 
                           value="<?php echo htmlspecialchars($submitted_email, ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                
                <div class="mb-6">
                    <label for="password" class="block mb-2 text-sm font-medium text-gray-300">Password</label>
                    
                    <div class="relative">
                        <input type="password" name="password" id="password" 
                               class="bg-brand-dark-light/50 border border-gray-600 text-white text-sm rounded-lg focus:ring-brand-red focus:border-brand-red block w-full p-2.5 pr-10 transition-colors" required>
                        
                        <span id="togglePassword" class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer text-gray-400 hover:text-red-300 transition-colors">
                            <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            <svg id="eye-slashed-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7 .987-3.14 3.635-5.515 6.84-6.318M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.125 7.125A9.953 9.953 0 003.542 12c1.274 4.057 5.064 7 9.542 7 1.48 0 2.89-.32 4.192-.88M21.542 12c-1.274-4.057-5.064-7-9.542-7a9.953 9.953 0 00-2.333.318m-3.09 3.09A9.953 9.953 0 0012 5c4.478 0 8.268 2.943 9.542 7a9.953 9.953 0 01-1.07 2.067M1 1l22 22"></path></svg>
                        </span>
                    </div>
                </div>
                
                <button id="loginButton" type="submit" 
                        class="w-full text-white bg-brand-red hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-900 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200 
                               disabled:bg-red-900 disabled:text-gray-400 
                               hover:shadow-lg hover:shadow-brand-red/30 hover:-translate-y-0.5"
                        <?php if (!empty($error_message) && str_contains($error_message, 'Masyado nang maraming')) { echo 'disabled'; } ?>>
                    
                    <span id="buttonText">Log In</span>
                    <svg id="buttonSpinner" aria-hidden="true" role="status" class="hidden inline w-4 h-4 me-3 text-white animate-spin" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="#374151"/><path d="M93.9676 39.0409C96.393 38.0416 97.8624 35.2111 97.0053 32.7758C96.1482 30.3405 93.6565 28.6943 91.0939 29.349C88.5312 30.0037 86.9589 32.551 87.816 34.9863C88.6732 37.4216 91.5422 39.0409 93.9676 39.0409Z" fill="currentColor"/></svg>
                </button>
            </form>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const togglePassword = document.getElementById('togglePassword');
            const eyeIcon = document.getElementById('eye-icon');
            const eyeSlashedIcon = document.getElementById('eye-slashed-icon');

            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    eyeIcon.classList.toggle('hidden');
                    eyeSlashedIcon.classList.toggle('hidden');
                });
            }

            const loginForm = document.getElementById('loginForm');
            const loginButton = document.getElementById('loginButton');
            const buttonText = document.getElementById('buttonText');
            const buttonSpinner = document.getElementById('buttonSpinner');

            if (loginForm && loginButton && buttonText && buttonSpinner) {
                loginForm.addEventListener('submit', function(e) {
                    if (loginButton.disabled) {
                        e.preventDefault();
                        return;
                    }
                    
                    loginButton.disabled = true;
                    buttonText.classList.add('hidden');
                    buttonSpinner.classList.remove('hidden');
                });
            }
        });
    </script>
</body>
</html>