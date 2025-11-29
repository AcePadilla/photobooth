<?php
ob_start(); // 1. I-buffer ang output para gumana ang header redirect kahit may spaces

// 2. Gamitin ang session_config.php para match sa settings ng dashboard
// Kung wala kang session_config.php, ibalik mo sa session_start();
require_once 'session_config.php'; 

require_once 'db.php'; 

// --- SECURITY HEADERS ---
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");

// Generate CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// --- CONFIGURATION ---
const MAX_LOGIN_ATTEMPTS = 5;
const LOCKOUT_TIME = 300; 

if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;
if (!isset($_SESSION['last_attempt_time'])) $_SESSION['last_attempt_time'] = 0;

$error_message = '';
$submitted_email = '';

// --- RATE LIMIT CHECK ---
$time_since_last_attempt = time() - $_SESSION['last_attempt_time'];

if ($_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
    if ($time_since_last_attempt < LOCKOUT_TIME) {
        $remaining_time = ceil((LOCKOUT_TIME - $time_since_last_attempt) / 60);
        $error_message = "Too many failed attempts. Please try again in " . $remaining_time . " minute(s).";
    } else {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt_time'] = 0;
    }
}

// --- LOGIN PROCESS ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($error_message)) {
    if (!isset($_POST['csrf_token']) || !hash_equals($csrf_token, $_POST['csrf_token'])) {
        $error_message = 'Security Token Expired. Please refresh the page.';
    } else {
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $submitted_email = $email;

        try {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                // SUCCESS
                session_regenerate_id(true);
                $_SESSION['login_attempts'] = 0;
                $_SESSION['last_attempt_time'] = 0;
                
                // Set Session Variables
                $_SESSION['user_id'] = $user['id']; // Important: Ito ang chinecheck sa session_config.php
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_email'] = $user['email'];
                
                // 3. I-save at isara ang session bago mag-redirect para sigurado
                session_write_close(); 
                
                // 4. Redirect
                header('Location: dashboard.php');
                ob_end_flush(); // I-flush ang buffer
                exit;

            } else {
                // FAIL
                $_SESSION['login_attempts']++;
                $_SESSION['last_attempt_time'] = time();
                $error_message = 'Incorrect email or password.';
            }
        } catch (PDOException $e) {
            error_log("Login Database Error: " . $e->getMessage());
            $error_message = 'System error. Please try again later.';
        }
    }
}
?>  

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Marahuyo</title>
    <link rel="icon" type="image/jpeg" href="../public/images/marahuyologo.jpg">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: { 
                            red: '#DC2626', 
                            dark: '#111827', 
                            surface: '#1F2937' 
                        }
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-out',
                        'shake': 'shake 0.5s cubic-bezier(.36,.07,.19,.97) both'
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        shake: {
                            '10%, 90%': { transform: 'translate3d(-1px, 0, 0)' },
                            '20%, 80%': { transform: 'translate3d(2px, 0, 0)' },
                            '30%, 50%, 70%': { transform: 'translate3d(-4px, 0, 0)' },
                            '40%, 60%': { transform: 'translate3d(4px, 0, 0)' }
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-900 text-gray-100 flex items-center justify-center min-h-screen relative overflow-hidden selection:bg-brand-red selection:text-white">

    <div class="absolute inset-0 z-0">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-brand-red/20 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[30%] h-[30%] bg-blue-600/10 rounded-full blur-[120px]"></div>
    </div>

    <main class="relative z-10 w-full max-w-md p-6 mx-4 animate-fade-in">
        <div class="bg-brand-surface/80 backdrop-blur-xl border border-gray-700 rounded-2xl shadow-2xl overflow-hidden">
            
            <div class="p-8">
                <div class="text-center mb-8">
                    <div class="inline-block p-1 rounded-full bg-gradient-to-tr from-brand-red to-orange-500 mb-4 shadow-lg shadow-brand-red/20">
                         <img src="../public/images/marahuyologo.jpg" alt="Logo" class="w-16 h-16 rounded-full border-2 border-gray-800 object-cover">
                    </div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Welcome Back</h1>
                    <p class="text-gray-400 text-sm mt-1">Sign in to manage the dashboard</p>
                </div>

                <?php if (!empty($error_message)): ?>
                    <div class="animate-shake mb-6 flex items-center p-4 text-sm text-red-200 border border-red-800 rounded-lg bg-red-900/30" role="alert">
                        <svg class="flex-shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                        </svg>
                        <span class="font-medium"><?php echo htmlspecialchars($error_message); ?></span>
                    </div>
                <?php endif; ?>

                <form id="loginForm" action="" method="POST" class="space-y-5">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                    <div>
                        <label for="email" class="block mb-2 text-sm font-medium text-gray-300">Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 16">
                                    <path d="m10.036 8.278 9.258-7.79A1.979 1.979 0 0 0 18 0H2A1.987 1.987 0 0 0 .641.541l9.395 7.737Z"/>
                                    <path d="M11.241 9.817c-.36.275-.801.425-1.255.427-.428 0-.845-.138-1.187-.395L0 2.6V14a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V2.5l-8.759 7.317Z"/>
                                </svg>
                            </div>
                            <input type="email" name="email" id="email" required
                                class="bg-gray-800/50 border border-gray-600 text-white text-sm rounded-lg focus:ring-brand-red focus:border-brand-red block w-full ps-10 p-2.5 placeholder-gray-500 transition-all duration-200 focus:bg-gray-800" 
                                placeholder="admin@marahuyo.com"
                                value="<?php echo htmlspecialchars($submitted_email); ?>">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block mb-2 text-sm font-medium text-gray-300">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 20">
                                    <path d="M14 7h-1.5V4.5a4.5 4.5 0 1 0-9 0V7H2a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2Zm-5 8a1 1 0 1 1-2 0v-3a1 1 0 1 1 2 0v3Z"/>
                                </svg>
                            </div>
                            <input type="password" name="password" id="password" required
                                class="bg-gray-800/50 border border-gray-600 text-white text-sm rounded-lg focus:ring-brand-red focus:border-brand-red block w-full ps-10 p-2.5 pr-10 placeholder-gray-500 transition-all duration-200 focus:bg-gray-800" 
                                placeholder="••••••••">
                            
                            <button type="button" id="togglePass" class="absolute inset-y-0 end-0 flex items-center pe-3 text-gray-400 hover:text-white focus:outline-none">
                                <svg id="icon-eye" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                <svg id="icon-eye-off" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7 .987-3.14 3.635-5.515 6.84-6.318M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.125 7.125A9.953 9.953 0 003.542 12c1.274 4.057 5.064 7 9.542 7 1.48 0 2.89-.32 4.192-.88M21.542 12c-1.274-4.057-5.064-7-9.542-7a9.953 9.953 0 00-2.333.318m-3.09 3.09A9.953 9.953 0 0012 5c4.478 0 8.268 2.943 9.542 7a9.953 9.953 0 01-1.07 2.067M1 1l22 22"></path></svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" id="btnSubmit"
                        class="w-full text-white bg-brand-red hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-900 font-semibold rounded-lg text-sm px-5 py-3 text-center transition-all duration-200 shadow-lg shadow-brand-red/20 hover:shadow-brand-red/40 hover:-translate-y-0.5 disabled:opacity-70 disabled:cursor-not-allowed"
                        <?php if (!empty($error_message) && strpos($error_message, 'Too many') !== false) echo 'disabled'; ?>>
                        
                        <span id="btnText">Sign In</span>
                        <div id="btnLoader" class="hidden flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Verifying...
                        </div>
                    </button>
                </form>
            </div>
            
            <div class="bg-gray-800/50 border-t border-gray-700 p-4 text-center">
                <p class="text-xs text-gray-500">&copy; <?php echo date('Y'); ?> Marahuyo System. Secure Access.</p>
            </div>
        </div>
    </main>

    <script>
        // Password Toggle Logic
        const toggleBtn = document.getElementById('togglePass');
        const passInput = document.getElementById('password');
        const iconEye = document.getElementById('icon-eye');
        const iconEyeOff = document.getElementById('icon-eye-off');

        toggleBtn.addEventListener('click', () => {
            const type = passInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passInput.setAttribute('type', type);
            iconEye.classList.toggle('hidden');
            iconEyeOff.classList.toggle('hidden');
        });

        // Form Submission Animation
        const form = document.getElementById('loginForm');
        const btnSubmit = document.getElementById('btnSubmit');
        const btnText = document.getElementById('btnText');
        const btnLoader = document.getElementById('btnLoader');

        form.addEventListener('submit', function(e) {
            // Don't prevent default unless invalid, we need the PHP post
            if (form.checkValidity()) {
                btnSubmit.disabled = true;
                btnText.classList.add('hidden');
                btnLoader.classList.remove('hidden');
                // Just visual feedback, allow form to submit normally
            }
        });
    </script>
</body>
</html>