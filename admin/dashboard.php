<?php
session_start();
// Security Check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
// Kunin ang pangalan ng current page para i-highlight ang active link
$current_page = basename($_SERVER['PHP_SELF']);

$upload_dir = '../uploads/';
$images = glob($upload_dir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
// I-sort ang images from newest to oldest
usort($images, function($a, $b) {
    return filemtime($b) - filemtime($a);
});
$photo_count = count($images);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Marahuyo Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-gray-800 text-white font-sans">
    <div class="flex h-screen">
        <aside class="w-64 bg-gray-900 p-6 flex flex-col justify-between">
            <div>
                <h1 class="font-brand text-3xl text-amber-400 mb-8 text-center">Marahuyo</h1>
                <nav>
                    <ul>
                        <li class="mb-4">
                            <a href="dashboard.php" class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                                <span class="material-icons">dashboard</span> Dashboard
                            </a>
                        </li>
                        <li class="mb-4">
                            <a href="templates.php" class="nav-link <?php echo ($current_page == 'templates.php') ? 'active' : ''; ?>">
                                <span class="material-icons">layers</span> Templates
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            <a href="logout.php" class="nav-link logout-link">
                <span class="material-icons">logout</span> Log Out
            </a>
        </aside>

        <main class="flex-1 p-8 overflow-y-auto">
            <h1 class="text-4xl font-bold mb-2">Dashboard</h1>
            <p class="text-gray-400 mb-8">Welcome, Admin! Here's an overview of your photo booth.</p>

            <div class="bg-gray-900 p-6 rounded-lg mb-8 inline-block">
                <h2 class="text-lg text-gray-400">Total Photos Captured</h2>
                <p class="text-5xl font-bold text-amber-400"><?php echo $photo_count; ?></p>
            </div>

            <h2 class="text-3xl font-semibold mb-6">Recently Captured Photos</h2>

            <?php
            // Mag-display ng message galing sa delete_photo.php (kung meron)
            if (isset($_SESSION['message'])) {
                // Determine color based on message content
                $message_class = strpos(strtolower($_SESSION['message']), 'error') === false ? 'bg-green-600' : 'bg-red-600';
                echo '<div class="' . $message_class . ' text-white p-3 rounded-lg mb-6">' . htmlspecialchars($_SESSION['message']) . '</div>';
                unset($_SESSION['message']); // Alisin ang message para hindi na lumabas ulit
            }
            ?>

            <?php if (empty($images)): ?>
                <p class="text-gray-400">No photos have been captured yet.</p>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <?php foreach ($images as $image): ?>
                        <div class="group bg-gray-900 rounded-lg overflow-hidden shadow-lg relative">
                            <img src="<?php echo $image; ?>" alt="Captured photo" class="w-full h-56 object-cover">
                            
                            <div class="absolute inset-0 bg-black bg-opacity-60 flex items-center justify-center space-x-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                
                                <a href="<?php echo $image; ?>" download title="Download" class="text-white hover:text-amber-400 transform hover:scale-125 transition-transform">
                                    <span class="material-icons text-3xl">download</span>
                                </a>

                                <a href="print_image.php?file=<?php echo urlencode($image); ?>" target="_blank" title="Print" class="text-white hover:text-amber-400 transform hover:scale-125 transition-transform">
                                    <span class="material-icons text-3xl">print</span>
                                </a>
                                
                                <a href="delete_photo.php?file=<?php echo urlencode(basename($image)); ?>" title="Delete" 
                                   onclick="return confirm('Sigurado ka bang gusto mong burahin ang litratong ito? Hindi na ito maibabalik.');" 
                                   class="text-red-500 hover:text-red-400 transform hover:scale-125 transition-transform">
                                    <span class="material-icons text-3xl">delete</span>
                                </a>

                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>