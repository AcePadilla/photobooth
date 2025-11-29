<?php
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

require_once 'session_config.php'; 

date_default_timezone_set('Asia/Manila');

define('SALES_FILE', 'sales_log.json');
define('SESSION_FILE', 'active_session.json'); 

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$layout_prices = [
    'strip-3' => 199,
    'strip-4' => 249,
    'grid-4' => 249,
    'spotlight-3' => 249,
    'grid-6-portrait' => 299,
    'grid-6-landscape' => 299,
];

$available_layouts = [
    'strip-3' => 'Photo Strip (3 Shots) - ₱199',
    'strip-4' => 'Long Strip (4 Shots) - ₱249',
    'grid-4' => 'Grid (4 Shots) - ₱249',
    'spotlight-3' => 'Spotlight (3 Shots) - ₱249',
    'grid-6-portrait' => 'Grid Portrait (6 Shots) - ₱299',
    'grid-6-landscape' => 'Grid Landscape (6 Shots) - ₱299',
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Security Warning: CSRF Token validation failed. Please refresh the page.");
    }

    if (isset($_POST['action'])) {
        
        if ($_POST['action'] == 'activate' && isset($_POST['layouts'])) {
            $active_layouts = [];
            $total_sale_amount = 0;

            foreach ($_POST['layouts'] as $layout_key => $count) {
                $count = intval($count); // Sanitize input
                if ($count > 0 && array_key_exists($layout_key, $available_layouts)) {
                    $total_sale_amount += $layout_prices[$layout_key] * $count;

                    // --- FIXED: 1 is to 1 LOGIC ---
                    // Tinanggal ko ang "* 2". Kung ano ang count, yun lang ang idadagdag.
                    for ($i = 0; $i < $count; $i++) {
                        $active_layouts[] = $layout_key;
                    }
                }
            }

            if ($total_sale_amount > 0) {
                $current_sales = [];
                if (file_exists(SALES_FILE)) {
                    $content = file_get_contents(SALES_FILE);
                    $current_sales = json_decode($content, true) ?? [];
                }
                $current_sales[] = [
                    'timestamp' => time(),
                    'amount' => $total_sale_amount,
                    'details' => $_POST['layouts'] 
                ];
                // Use LOCK_EX to prevent race conditions when writing to file
                file_put_contents(SALES_FILE, json_encode($current_sales, JSON_PRETTY_PRINT), LOCK_EX);
            }

            // Append to existing session layouts instead of overwriting, or create new if empty
            $existing_layouts = [];
            if (file_exists(SESSION_FILE)) {
                $existing_data = json_decode(file_get_contents(SESSION_FILE), true);
                if (is_array($existing_data) && isset($existing_data['layouts'])) {
                    $existing_layouts = $existing_data['layouts'];
                }
            }
            
            // Merge new layouts with existing ones
            $final_layouts = array_merge($existing_layouts, $active_layouts);

            $session_data = [
                'layouts' => $final_layouts,
                'startTime' => time()
            ];

            if (file_put_contents(SESSION_FILE, json_encode($session_data, JSON_PRETTY_PRINT), LOCK_EX)) {
                $_SESSION['alert'] = ['type' => 'success', 'message' => 'Session activated! Added ₱' . number_format($total_sale_amount) . ' to sales. (' . count($active_layouts) . ' prints added to queue)'];
            } else {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'ERROR: Could not save session file. Check permissions.'];
            }
        }
        
        if ($_POST['action'] == 'clear') {
            $session_data = ['layouts' => [], 'startTime' => 0];
            if (file_put_contents(SESSION_FILE, json_encode($session_data, JSON_PRETTY_PRINT), LOCK_EX)) {
                $_SESSION['alert'] = ['type' => 'info', 'message' => 'Active session cleared.'];
            } else {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'ERROR: Could not clear session file.'];
            }
        }
    }
    header('Location: dashboard.php');
    exit;
}

// --- Load Sales Data for JS ---
$raw_sales_data = [];
if (file_exists(SALES_FILE)) {
    $raw_sales_data = json_decode(file_get_contents(SALES_FILE), true) ?? [];
}

// --- Load Session Data ---
$current_session = ['layouts' => []];
if (file_exists(SESSION_FILE)) {
    $json_content = file_get_contents(SESSION_FILE);
    $data = json_decode($json_content, true);
    if (is_array($data) && isset($data['layouts'])) {
        $current_session = $data;
    }
}
$current_layout_counts = array_count_values($current_session['layouts']);
$total_remaining_layouts = count($current_session['layouts']);

// --- Image Handling ---
$upload_dir = '../uploads/';
// Ensure directory exists
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$all_images = glob($upload_dir . '*.{jpg,jpeg,png}', GLOB_BRACE); 
if ($all_images) {
    usort($all_images, function($a, $b) {
        return filemtime($b) - filemtime($a); 
    });
} else {
    $all_images = [];
}
$photo_count = count($all_images);

// Pagination
$photos_per_page = 12;
$total_pages = $photo_count > 0 ? ceil($photo_count / $photos_per_page) : 1;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, min($total_pages, $current_page)); 
$offset = ($current_page - 1) * $photos_per_page;
$paginated_images = array_slice($all_images, $offset, $photos_per_page);

$current_page_name = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Marahuyo Admin</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <link rel="stylesheet" href="style.css"> 
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #0f172a; /* Slate 900 */
            background-image: radial-gradient(circle at top right, #1e293b 0%, transparent 40%), radial-gradient(circle at bottom left, #1e293b 0%, transparent 40%);
        }

        /* --- Glassmorphism & Cards --- */
        .glass-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        /* --- Custom Scrollbar --- */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(255,255,255,0.02); border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #64748b; }

        /* Navigation */
        .nav-link-header {
            position: relative; padding: 0.5rem 1rem; font-weight: 500; color: #94a3b8; transition: all 0.3s ease;
        }
        .nav-link-header:hover { color: #f8fafc; }
        .nav-link-header.active { color: #f59e0b; }
        .nav-link-header.active::after {
            content: ''; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%);
            width: 20px; height: 2px; background-color: #f59e0b; border-radius: 2px;
        }

        /* Form Controls */
        .form-row { transition: background-color 0.2s ease; }
        .form-row:hover { background-color: rgba(255, 255, 255, 0.03); }

        .counter-btn {
            width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;
            background-color: #334155; color: white; border-radius: 50%; transition: all 0.2s;
        }
        .counter-btn:hover { background-color: #f59e0b; color: #0f172a; transform: scale(1.1); }
        .counter-btn:active { transform: scale(0.95); }

        .form-input-display {
            background: transparent; color: white; text-align: center;
            border: none; outline: none; font-weight: 700; font-size: 1.1rem; width: 40px;
        }

        /* Gallery Grid Improvements */
        .photo-item { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s ease; }
        .photo-item:hover { transform: translateY(-5px); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3); }
        
        /* List View Specifics */
        #photo-gallery.view-list .photo-item {
            display: flex; align-items: center; padding: 0.75rem;
            background-color: #1e293b; margin-bottom: 0.75rem; border: 1px solid rgba(255,255,255,0.05);
        }
        #photo-gallery.view-list .photo-item-image-container { width: 80px; height: 60px; margin-right: 1rem; }
        #photo-gallery.view-list .photo-item-info { flex-grow: 1; text-align: left; background: transparent; border:none; }
        #photo-gallery.view-list .photo-item-actions { display: flex; gap: 0.5rem; }
        
        /* Grid View Specifics */
        #photo-gallery.view-grid .photo-item-actions { display: none; }
        #photo-gallery.view-grid .photo-item-info { padding: 0.75rem; background-color: #1e293b; border-top: 1px solid rgba(255,255,255,0.05); }
        #photo-gallery.view-grid .photo-item-image-container { width: 100%; height: 224px; position: relative; }

        /* Filter Tabs */
        .filter-tab {
            padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; color: #94a3b8; transition: all 0.2s;
        }
        .filter-tab.active {
            background-color: #f59e0b; color: #0f172a; font-weight: 600; box-shadow: 0 0 10px rgba(245, 158, 11, 0.3);
        }
    </style>
</head>
<body class="text-slate-200 flex flex-col min-h-screen">
    
    <header class="sticky top-0 z-50 glass-card border-b-0 border-b-white/5 shadow-lg w-full">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-8">
                    <img src="../public/images/nobglogo.png" alt="Marahuyo Logo" class="h-10 w-auto drop-shadow-md">
                    <nav class="hidden md:block">
                        <ul class="flex space-x-1">
                            <li><a href="dashboard.php" class="nav-link-header <?php echo ($current_page_name == 'dashboard.php') ? 'active' : ''; ?>">Dashboard</a></li>
                            <li><a href="templates.php" class="nav-link-header <?php echo ($current_page_name == 'templates.php') ? 'active' : ''; ?>">Templates</a></li>
                        </ul>
                    </nav>
                </div>
                <div class="flex items-center gap-4">
                    <span class="hidden sm:inline text-xs font-semibold bg-slate-800 border border-slate-700 px-2 py-1 rounded text-slate-400">ADMIN</span>
                    <a href="logout.php" class="flex items-center gap-2 text-slate-400 hover:text-white transition-colors text-sm font-medium">
                        <span class="material-icons text-[20px]">logout</span>
                        <span class="hidden sm:inline">Log Out</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-1 w-full p-4 md:p-8">
        <div class="max-w-7xl mx-auto space-y-6"> 
            
            <div class="flex flex-col md:flex-row justify-between items-end mb-2">
                <div>
                    <h1 class="text-3xl font-bold text-white tracking-tight">Overview</h1>
                    <p class="text-slate-400 text-sm mt-1">Monitor sales and print queue activity.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="lg:col-span-2 glass-card rounded-2xl p-6 shadow-xl h-full">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                        <div>
                            <h2 class="text-slate-400 text-xs font-bold uppercase tracking-widest">Total Revenue</h2>
                            <div class="flex items-baseline gap-2 mt-1">
                                <p id="display-total-sales" class="text-4xl font-bold text-white">₱0</p>
                            </div>
                        </div>
                        <div class="bg-slate-800/80 p-1 rounded-full flex border border-slate-700">
                            <button onclick="setFilter('week')" class="filter-tab active" id="btn-week">Week</button>
                            <button onclick="setFilter('month')" class="filter-tab" id="btn-month">Month</button>
                            <button onclick="setFilter('year')" class="filter-tab" id="btn-year">Year</button>
                        </div>
                    </div>
                    <div class="w-full h-[450px] relative">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>

                <div class="glass-card rounded-2xl p-6 shadow-xl flex flex-col h-full">
                    <h2 class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-4">Popular Layouts</h2>
                    <div class="flex-grow flex items-center justify-center relative h-[320px]">
                        <canvas id="layoutChart"></canvas>
                        <div id="no-data-msg" class="absolute inset-0 flex items-center justify-center text-slate-500 text-sm hidden">
                            No sales data
                        </div>
                    </div>
                    <div id="top-layout-legend" class="mt-4 space-y-3 text-sm text-slate-300 max-h-[150px] overflow-y-auto custom-scrollbar pr-2"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="glass-card rounded-2xl p-6 border-l-4 border-emerald-500 flex items-center justify-between">
                    <div>
                        <p class="text-slate-400 text-sm font-medium">Prints Queue</p>
                        <p id="total-layouts-remaining" class="text-4xl font-bold text-white mt-1"><?php echo $total_remaining_layouts; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-emerald-500/10 rounded-full flex items-center justify-center text-emerald-500">
                        <span class="material-icons text-3xl">layers</span>
                    </div>
                </div>
                
                <div class="glass-card rounded-2xl p-6 border-l-4 border-amber-500 flex items-center justify-between">
                    <div>
                        <p class="text-slate-400 text-sm font-medium">Gallery Storage</p>
                        <p id="total-photos-count" class="text-4xl font-bold text-white mt-1"><?php echo $photo_count; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-amber-500/10 rounded-full flex items-center justify-center text-amber-500">
                        <span class="material-icons text-3xl">photo_library</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
                
                <div class="glass-card rounded-2xl p-6 shadow-xl h-full flex flex-col">
                    <div class="flex items-center gap-2 mb-4">
                         <span class="material-icons text-amber-500">point_of_sale</span>
                         <h2 class="text-xl font-semibold text-white">New Transaction</h2>
                    </div>
                    <p class="text-slate-400 text-sm mb-6">Select packages to add to the queue.</p>
                    
                    <form method="POST" action="dashboard.php" class="flex-grow flex flex-col">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" value="activate">
                        
                        <div class="space-y-3 max-h-[400px] overflow-y-auto custom-scrollbar pr-2 flex-grow">
                            <?php foreach ($available_layouts as $key => $name): ?>
                            <div class="form-row bg-slate-800/50 border border-slate-700/50 rounded-lg p-3 flex items-center justify-between">
                                <label for="layout_<?php echo $key; ?>" class="text-slate-300 text-sm font-medium cursor-pointer select-none flex-1">
                                    <?php echo htmlspecialchars($name); ?>
                                </label>
                                
                                <div class="flex items-center bg-slate-900 rounded-full p-1 border border-slate-700">
                                    <button type="button" class="counter-btn" onclick="adjustLayoutCount('<?php echo $key; ?>', -1)">
                                        <span class="material-icons text-[16px]">remove</span>
                                    </button>
                                    
                                    <input type="number" name="layouts[<?php echo $key; ?>]" id="layout_<?php echo $key; ?>" min="0" value="0" 
                                           class="form-input-display" readonly>
                                    
                                    <button type="button" class="counter-btn" onclick="adjustLayoutCount('<?php echo $key; ?>', 1)">
                                        <span class="material-icons text-[16px]">add</span>
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <button type="submit" class="w-full mt-6 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-400 hover:to-orange-500 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-amber-900/20 transition-all transform active:scale-[0.98] flex items-center justify-center gap-2">
                            <span>Process Payment & Activate</span>
                            <span class="material-icons text-sm">arrow_forward</span>
                        </button>
                    </form>
                </div>

                <div class="glass-card rounded-2xl p-6 shadow-xl h-full flex flex-col">
                    <div class="flex items-center gap-2 mb-4">
                         <span class="material-icons text-emerald-500">playlist_play</span>
                         <h2 class="text-xl font-semibold text-white">Active Queue</h2>
                    </div>
                    
                    <div id="session-card-content" class="flex-1 flex flex-col">
                        <?php if ($total_remaining_layouts == 0): ?>
                            <div class="flex-1 flex flex-col items-center justify-center text-slate-500 space-y-2 opacity-60 h-[300px]">
                                <span class="material-icons text-5xl">print_disabled</span>
                                <p>Queue is empty</p>
                            </div>
                        <?php else: ?>
                            <p class="text-slate-400 text-sm mb-4">Remaining prints available:</p>
                            <ul id="session-layout-list" class="space-y-2 mb-6 max-h-[400px] overflow-y-auto custom-scrollbar pr-2 flex-1">
                                <?php foreach ($current_layout_counts as $layout_key => $count): ?>
                                    <li class="flex justify-between items-center bg-slate-800/50 p-3 rounded-lg border border-slate-700/50">
                                        <span class="text-slate-300 text-sm font-medium"><?php echo htmlspecialchars($available_layouts[$layout_key] ?? $layout_key); ?></span>
                                        <span class="bg-emerald-500/20 text-emerald-400 px-3 py-1 rounded-full text-xs font-bold border border-emerald-500/20"><?php echo $count; ?>x</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            
                            <form id="clear-session-form" method="POST" action="dashboard.php" class="mt-auto">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <input type="hidden" name="action" value="clear">
                                <button type="button" id="clear-session-btn" class="w-full bg-rose-900/50 hover:bg-rose-900/80 border border-rose-800 text-rose-200 font-semibold py-3 px-4 rounded-xl transition-all flex items-center justify-center gap-2">
                                    <span class="material-icons text-sm">delete_forever</span>
                                    Clear Queue
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="glass-card rounded-2xl p-6 shadow-xl">
                <div class="flex flex-col md:flex-row justify-between md:items-center gap-4 mb-6 border-b border-slate-700/50 pb-4">
                    <div>
                        <h2 class="text-2xl font-semibold text-white">Recent Captures</h2>
                        <p class="text-slate-400 text-sm">Manage and print captured photos.</p>
                    </div>
                    <div class="flex gap-2">
                        <div class="bg-slate-800 p-1 rounded-lg border border-slate-700 flex">
                            <button id="view-grid-btn" class="p-2 rounded text-amber-500 hover:bg-slate-700 transition-colors" title="Grid View"><span class="material-icons">grid_view</span></button>
                            <button id="view-list-btn" class="p-2 rounded text-slate-400 hover:bg-slate-700 transition-colors" title="List View"><span class="material-icons">view_list</span></button>
                        </div>
                        <button id="delete-all-btn" class="py-2 px-4 bg-rose-600 hover:bg-rose-500 text-white font-medium rounded-lg flex items-center gap-2 ml-2 shadow-lg shadow-rose-900/20 transition-all">
                            <span class="material-icons text-sm">delete_sweep</span> <span class="hidden sm:inline">Delete All</span>
                        </button>
                    </div>
                </div>

                <?php if (empty($all_images)): ?>
                    <div class="py-12 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-800 mb-4">
                            <span class="material-icons text-3xl text-slate-500">hide_image</span>
                        </div>
                        <p class="text-slate-400">No photos have been captured yet.</p>
                    </div>
                <?php else: ?>
                    <div id="photo-gallery" class="view-grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        <?php foreach ($paginated_images as $image): 
                            $image_name = basename($image);
                            $file_size = filesize($image);
                            $date_modified = date("M d, g:ia", filemtime($image)); 
                        ?>
                            <div class="photo-item group rounded-xl overflow-hidden relative bg-slate-800 shadow-lg border border-slate-700/50">
                                <div class="photo-item-image-container bg-slate-900"> 
                                    <img src="<?php echo $image; ?>" loading="lazy" alt="Captured photo" class="w-full h-full object-cover">
                                    
                                    <div class="photo-item-overlay absolute inset-0 bg-slate-900/80 backdrop-blur-sm flex items-center justify-center gap-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                        <a href="<?php echo $image; ?>" download title="Download" class="p-3 bg-slate-700 rounded-full text-white hover:bg-amber-500 hover:text-white transition-colors transform hover:scale-110 shadow-lg"><span class="material-icons">download</span></a>
                                        <a href="print_image.php?file=<?php echo urlencode($image); ?>" target="_blank" title="Print" class="p-3 bg-slate-700 rounded-full text-white hover:bg-emerald-500 hover:text-white transition-colors transform hover:scale-110 shadow-lg"><span class="material-icons">print</span></a>
                                        <a href="delete_photo.php?file=<?php echo urlencode($image_name); ?>" title="Delete" class="delete-photo-btn p-3 bg-slate-700 rounded-full text-white hover:bg-rose-500 hover:text-white transition-colors transform hover:scale-110 shadow-lg"><span class="material-icons">delete</span></a>
                                    </div>
                                </div>
                                <div class="photo-item-info">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="text-sm font-semibold text-slate-200 flex items-center gap-1">
                                                <?php echo $date_modified; ?>
                                            </h3>
                                            <p class="text-xs text-slate-500 mt-1"><?php echo round($file_size / 1024); ?> KB</p> 
                                        </div>
                                    </div>
                                </div>
                                <div class="photo-item-actions">
                                    <a href="<?php echo $image; ?>" download class="p-2 bg-slate-700 rounded-full text-slate-300 hover:bg-amber-500 hover:text-white"><span class="material-icons text-lg">download</span></a>
                                    <a href="print_image.php?file=<?php echo urlencode($image); ?>" target="_blank" class="p-2 bg-slate-700 rounded-full text-slate-300 hover:bg-emerald-500 hover:text-white"><span class="material-icons text-lg">print</span></a>
                                    <a href="delete_photo.php?file=<?php echo urlencode($image_name); ?>" class="delete-photo-btn p-2 bg-slate-700 rounded-full text-slate-300 hover:bg-rose-500 hover:text-white"><span class="material-icons text-lg">delete</span></a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="pagination flex justify-center gap-2 mt-8">
                        <?php if ($current_page > 1): ?>
                            <a href="?page=<?php echo $current_page - 1; ?>" class="w-10 h-10 flex items-center justify-center rounded-full bg-slate-800 text-slate-400 hover:bg-amber-500 hover:text-white transition-colors"><span class="material-icons">chevron_left</span></a>
                        <?php else: ?>
                            <span class="w-10 h-10 flex items-center justify-center rounded-full bg-slate-800/50 text-slate-600 cursor-not-allowed"><span class="material-icons">chevron_left</span></span>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>" class="w-10 h-10 flex items-center justify-center rounded-full transition-colors font-medium <?php echo ($i == $current_page) ? 'bg-amber-500 text-white shadow-lg shadow-amber-500/25' : 'bg-slate-800 text-slate-400 hover:bg-slate-700'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($current_page < $total_pages): ?>
                            <a href="?page=<?php echo $current_page + 1; ?>" class="w-10 h-10 flex items-center justify-center rounded-full bg-slate-800 text-slate-400 hover:bg-amber-500 hover:text-white transition-colors"><span class="material-icons">chevron_right</span></a>
                        <?php else: ?>
                            <span class="w-10 h-10 flex items-center justify-center rounded-full bg-slate-800/50 text-slate-600 cursor-not-allowed"><span class="material-icons">chevron_right</span></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <script>
        // --- Chart & Filter Logic ---
        const rawSalesData = <?php echo json_encode($raw_sales_data); ?>;
        const availableLayouts = <?php echo json_encode($available_layouts); ?>;
        let salesChart = null;
        let layoutChart = null;

        function getStartOfWeek(date) {
            const d = new Date(date);
            const day = d.getDay();
            const diff = d.getDate() - day + (day === 0 ? -6 : 1); 
            d.setDate(diff);
            d.setHours(0,0,0,0);
            return d;
        }

        function filterData(filterType) {
            const now = new Date();
            const filtered = rawSalesData.filter(item => {
                const itemDate = new Date(item.timestamp * 1000);
                if (filterType === 'week') {
                    const startOfWeek = getStartOfWeek(now);
                    const endOfWeek = new Date(startOfWeek);
                    endOfWeek.setDate(endOfWeek.getDate() + 6);
                    endOfWeek.setHours(23,59,59,999);
                    return itemDate >= startOfWeek && itemDate <= endOfWeek;
                } else if (filterType === 'month') {
                    return itemDate.getMonth() === now.getMonth() && itemDate.getFullYear() === now.getFullYear();
                } else if (filterType === 'year') {
                    return itemDate.getFullYear() === now.getFullYear();
                }
                return false;
            });
            return filtered;
        }

        function processChartData(filteredData, filterType) {
            const totalSales = filteredData.reduce((sum, item) => sum + item.amount, 0);
            document.getElementById('display-total-sales').innerText = '₱' + totalSales.toLocaleString();

            const trendMap = {};
            if (filterType === 'week') {
                const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                days.forEach(d => trendMap[d] = 0);
                filteredData.forEach(item => {
                    const d = new Date(item.timestamp * 1000);
                    trendMap[days[d.getDay()]] += item.amount;
                });
            } else if (filterType === 'month') {
                filteredData.forEach(item => {
                    const d = new Date(item.timestamp * 1000);
                    trendMap[d.getDate()] = (trendMap[d.getDate()] || 0) + item.amount;
                });
            } else {
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                months.forEach(m => trendMap[m] = 0);
                filteredData.forEach(item => {
                    const d = new Date(item.timestamp * 1000);
                    trendMap[months[d.getMonth()]] += item.amount;
                });
            }

            const layoutCounts = {};
            filteredData.forEach(item => {
                if (item.details) {
                    for (const [key, count] of Object.entries(item.details)) {
                        layoutCounts[key] = (layoutCounts[key] || 0) + parseInt(count);
                    }
                }
            });

            return { 
                total: totalSales, 
                trendLabels: Object.keys(trendMap), 
                trendValues: Object.values(trendMap),
                layoutLabels: Object.keys(layoutCounts).map(k => availableLayouts[k] ? availableLayouts[k].split(' - ')[0] : k),
                layoutValues: Object.values(layoutCounts)
            };
        }

        function setFilter(type) {
            document.querySelectorAll('.filter-tab').forEach(btn => btn.classList.remove('active'));
            document.getElementById('btn-' + type).classList.add('active');
            const filtered = filterData(type);
            const chartData = processChartData(filtered, type);
            updateCharts(chartData);
        }

        function updateCharts(data) {
            const ctxSales = document.getElementById('salesChart').getContext('2d');
            if (salesChart) salesChart.destroy();

            // Create Gradient for Line Chart
            let gradient = ctxSales.createLinearGradient(0, 0, 0, 400); // Adjusted for taller chart
            gradient.addColorStop(0, 'rgba(245, 158, 11, 0.3)');
            gradient.addColorStop(1, 'rgba(245, 158, 11, 0.0)');

            salesChart = new Chart(ctxSales, {
                type: 'line',
                data: {
                    labels: data.trendLabels,
                    datasets: [{
                        label: 'Sales (₱)',
                        data: data.trendValues,
                        borderColor: '#F59E0B',
                        backgroundColor: gradient,
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#0F172A',
                        pointBorderColor: '#F59E0B',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.9)',
                            titleColor: '#fff',
                            bodyColor: '#cbd5e1',
                            borderColor: 'rgba(255,255,255,0.1)',
                            borderWidth: 1,
                            padding: 10,
                            displayColors: false,
                            callbacks: { label: (c) => '₱ ' + c.raw.toLocaleString() }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: '#64748b', font: { family: "'Plus Jakarta Sans', sans-serif" } }
                        },
                        y: {
                            grid: { color: 'rgba(255,255,255,0.05)', borderDash: [4, 4] },
                            ticks: { color: '#64748b', callback: (val) => '₱' + val, font: { family: "'Plus Jakarta Sans', sans-serif" } },
                            beginAtZero: true
                        }
                    }
                }
            });

            const ctxLayout = document.getElementById('layoutChart').getContext('2d');
            const noDataMsg = document.getElementById('no-data-msg');
            if (layoutChart) layoutChart.destroy();

            if (data.layoutValues.length === 0) {
                noDataMsg.classList.remove('hidden');
            } else {
                noDataMsg.classList.add('hidden');
                const bgColors = ['#3B82F6', '#10B981', '#8B5CF6', '#F59E0B', '#EC4899', '#6366F1'];
                layoutChart = new Chart(ctxLayout, {
                    type: 'doughnut',
                    data: {
                        labels: data.layoutLabels,
                        datasets: [{
                            data: data.layoutValues,
                            backgroundColor: bgColors,
                            borderWidth: 0,
                            hoverOffset: 10
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip: { enabled: true } },
                        cutout: '75%'
                    }
                });

                const legendContainer = document.getElementById('top-layout-legend');
                legendContainer.innerHTML = '';
                data.layoutLabels.forEach((label, index) => {
                    const val = data.layoutValues[index];
                    const color = bgColors[index % bgColors.length];
                    legendContainer.innerHTML += `
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full shadow-[0_0_8px_rgba(0,0,0,0.5)]" style="background-color: ${color}; box-shadow: 0 0 5px ${color}"></span>
                                <span class="text-slate-300 text-xs tracking-wide">${label}</span>
                            </div>
                            <span class="font-bold text-white text-xs">${val}</span>
                        </div>
                    `;
                });
            }
        }

        function adjustLayoutCount(layoutKey, adjustment) {
            const input = document.getElementById('layout_' + layoutKey);
            let currentValue = parseInt(input.value) || 0;
            currentValue += adjustment;
            if (currentValue < 0) currentValue = 0;
            input.value = currentValue;
        }

        let currentLayoutCount = <?php echo $total_remaining_layouts; ?>;
        let currentPhotoCount = <?php echo $photo_count; ?>;
        const availableLayoutNames = <?php echo json_encode($available_layouts); ?>;

        function updateSessionUI(layoutsArray) {
            const cardContentEl = document.getElementById('session-card-content');
            const newLayoutCount = layoutsArray.length;
            document.getElementById('total-layouts-remaining').textContent = newLayoutCount;

            if (newLayoutCount === 0) {
                cardContentEl.innerHTML = `
                    <div class="flex-1 flex flex-col items-center justify-center text-slate-500 space-y-2 opacity-60 h-[300px]">
                        <span class="material-icons text-5xl">print_disabled</span>
                        <p>Queue is empty</p>
                    </div>`;
            } else {
                const counts = {};
                layoutsArray.forEach(layout => { counts[layout] = (counts[layout] || 0) + 1; });
                let listHtml = '';
                for (const layoutKey in counts) {
                    const count = counts[layoutKey];
                    const layoutName = availableLayoutNames[layoutKey] || layoutKey;
                    listHtml += `
                    <li class="flex justify-between items-center bg-slate-800/50 p-3 rounded-lg border border-slate-700/50">
                        <span class="text-slate-300 text-sm font-medium">${layoutName}</span>
                        <span class="bg-emerald-500/20 text-emerald-400 px-3 py-1 rounded-full text-xs font-bold border border-emerald-500/20">${count}x</span>
                    </li>`;
                }
                cardContentEl.innerHTML = `
                    <p class="text-slate-400 text-sm mb-4">Remaining prints available:</p>
                    <ul id="session-layout-list" class="space-y-2 mb-6 max-h-[400px] overflow-y-auto custom-scrollbar pr-2 flex-1">
                        ${listHtml}
                    </ul>
                    <form id="clear-session-form" method="POST" action="dashboard.php" class="mt-auto">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" value="clear">
                        <button type="button" id="clear-session-btn" class="w-full bg-rose-900/50 hover:bg-rose-900/80 border border-rose-800 text-rose-200 font-semibold py-3 px-4 rounded-xl transition-all flex items-center justify-center gap-2">
                            <span class="material-icons text-sm">delete_forever</span> Clear Queue
                        </button>
                    </form>
                `;
            }
            attachClearButtonListener();
        }

        async function pollData() {
            try {
                const response = await fetch('get_live_data.php?t=' + new Date().getTime());
                if (!response.ok) return; 
                const data = await response.json();
                const newLayoutCount = data.layouts.length;
                const newPhotoCount = data.photo_count;

                if (newPhotoCount !== currentPhotoCount) {
                    document.getElementById('total-photos-count').textContent = newPhotoCount;
                    currentPhotoCount = newPhotoCount;
                }

                if (newLayoutCount !== currentLayoutCount) {
                    updateSessionUI(data.layouts);
                    if (newLayoutCount === 0 && currentLayoutCount > 0) {
                        Swal.fire({
                            title: 'Session Finished!',
                            text: 'All queued prints have been completed.',
                            icon: 'success',
                            confirmButtonColor: '#F59E0B',
                            background: '#1e293b',
                            color: '#fff'
                        });
                    }
                    currentLayoutCount = newLayoutCount;
                }
            } catch (error) { console.error('Error polling:', error); }
        }

        setInterval(pollData, 3000);

        <?php if (isset($_SESSION['alert'])): ?>
            Swal.fire({
                title: '<?php echo ucfirst($_SESSION['alert']['type']); ?>!',
                text: '<?php echo addslashes($_SESSION['alert']['message']); ?>',
                icon: '<?php echo $_SESSION['alert']['type']; ?>',
                confirmButtonColor: '#F59E0B',
                background: '#1e293b',
                color: '#fff'
            });
            <?php unset($_SESSION['alert']); ?>
        <?php endif; ?>
        
        function attachClearButtonListener() {
            const clearBtn = document.getElementById('clear-session-btn');
            if (clearBtn) {
                clearBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Clear Queue?',
                        text: "All remaining prints will be removed!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e11d48',
                        cancelButtonColor: '#334155',
                        confirmButtonText: 'Yes, clear it!',
                        background: '#1e293b',
                        color: '#fff'
                    }).then((result) => {
                        if (result.isConfirmed) document.getElementById('clear-session-form').submit();
                    });
                });
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            setFilter('week');
            const gallery = document.getElementById('photo-gallery');
            const gridBtn = document.getElementById('view-grid-btn');
            const listBtn = document.getElementById('view-list-btn');
            const deleteAllBtn = document.getElementById('delete-all-btn');
            
            attachClearButtonListener(); 

            document.querySelectorAll('.delete-photo-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault(); 
                    const url = e.currentTarget.href; 
                    Swal.fire({
                        title: 'Delete Photo?',
                        text: "This cannot be undone!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e11d48',
                        cancelButtonColor: '#334155',
                        confirmButtonText: 'Delete',
                        background: '#1e293b',
                        color: '#fff'
                    }).then((result) => { if (result.isConfirmed) window.location.href = url; });
                });
            });

            if (gallery) {
                gridBtn.addEventListener('click', () => {
                    gallery.classList.add('view-grid'); gallery.classList.remove('view-list');
                    gridBtn.classList.add('text-amber-500'); gridBtn.classList.remove('text-slate-400');
                    listBtn.classList.add('text-slate-400'); listBtn.classList.remove('text-amber-500');
                });
                listBtn.addEventListener('click', () => {
                    gallery.classList.add('view-list'); gallery.classList.remove('view-grid');
                    listBtn.classList.add('text-amber-500'); listBtn.classList.remove('text-slate-400');
                    gridBtn.classList.add('text-slate-400'); gridBtn.classList.remove('text-amber-500');
                });
            }

            if (deleteAllBtn) {
                deleteAllBtn.addEventListener('click', (e) => {
                    e.preventDefault(); 
                    Swal.fire({
                        title: 'Delete All Photos?',
                        text: "Permanently delete ALL files? This cannot be undone!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e11d48',
                        cancelButtonColor: '#334155',
                        confirmButtonText: 'Yes, delete all!',
                        background: '#1e293b',
                        color: '#fff'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const form = document.createElement('form');
                            form.method = 'POST'; form.action = 'delete_all.php'; 
                            const input = document.createElement('input');
                            input.type = 'hidden'; input.name = 'confirm_delete'; input.value = 'true';
                            form.appendChild(input);
                            const csrf = document.createElement('input');
                            csrf.type = 'hidden'; csrf.name = 'csrf_token'; csrf.value = '<?php echo $_SESSION['csrf_token']; ?>';
                            form.appendChild(csrf);
                            document.body.appendChild(form);
                            form.submit();
                        }
                    })
                });
            }
        });
    </script>
</body>
</html>