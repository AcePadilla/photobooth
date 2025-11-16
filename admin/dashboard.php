<?php
session_start();
date_default_timezone_set('Asia/Manila');

require_once 'session_config.php'; // (Para makuha ang SESSION_FILE path)

// Listahan ng lahat ng available layouts
$available_layouts = [
    'strip-3' => 'Photo Strip (3 Shots)',
    'strip-4' => 'Long Strip (4 Shots)',
    'grid-4' => 'Grid (4 Shots)',
    'spotlight-3' => 'Spotlight (3 Shots)',
    'grid-6-portrait' => 'Grid Portrait (6 Shots)',
    'grid-6-landscape' => 'Grid Landscape (6 Shots)',
];

// --- FORM HANDLING ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        
        if ($_POST['action'] == 'activate' && isset($_POST['layouts'])) {
            $active_layouts = [];
            foreach ($_POST['layouts'] as $layout_key => $count) {
                $count = intval($count);
                if ($count > 0 && array_key_exists($layout_key, $available_layouts)) {
                    for ($i = 0; $i < $count; $i++) {
                        $active_layouts[] = $layout_key;
                    }
                }
            }
            $session_data = [
                'layouts' => $active_layouts,
                'startTime' => time()
            ];
            if (file_put_contents(SESSION_FILE, json_encode($session_data, JSON_PRETTY_PRINT))) {
                $_SESSION['alert'] = ['type' => 'success', 'message' => 'New session activated! (' . count($active_layouts) . ' total)'];
            } else {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'ERROR: Could not save session file.'];
            }
        }
        
        if ($_POST['action'] == 'clear') {
            $session_data = ['layouts' => [], 'startTime' => 0];
            if (file_put_contents(SESSION_FILE, json_encode($session_data, JSON_PRETTY_PRINT))) {
                $_SESSION['alert'] = ['type' => 'info', 'message' => 'Active session cleared.'];
            } else {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'ERROR: Could not clear session file.'];
            }
        }
    }
    header('Location: dashboard.php');
    exit;
}
// --- END NG FORM HANDLING ---

// --- Read current data (para sa initial page load) ---
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

// --- Kunin ang photo count (PNG/JPG lang) ---
$upload_dir = '../uploads/';
$all_images = glob($upload_dir . '*.{jpg,jpeg,png}', GLOB_BRACE); 
usort($all_images, function($a, $b) {
    return filemtime($b) - filemtime($a); 
});
$photo_count = count($all_images);

// --- PAGINATION LOGIC ---
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .stat-card {
            background-color: #1F2937; 
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 80px;
            gap: 1rem;
            align-items: center;
            padding: 0.75rem;
            border-radius: 0.5rem;
            transition: background-color 0.2s ease;
        }
        .form-row:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        .form-row label {
            cursor: pointer;
            text-align: left;
        }
        .form-input {
            background-color: #374151; 
            color: white;
            border-radius: 0.375rem;
            padding: 0.5rem;
            width: 100%;
            text-align: center;
            border: 2px solid #4B5563; 
            outline: none;
            transition: all 0.2s ease;
        }
        .form-input:focus {
            border-color: #F59E0B; 
            box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.3); 
        }

        /* List View Styles */
        #photo-gallery.view-list { display: block; }
        #photo-gallery.view-list .photo-item {
            display: flex; align-items: center; padding: 0.75rem;
            background-color: #1F2937; margin-bottom: 0.75rem; 
            border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        #photo-gallery.view-list .photo-item-image-container {
            width: 80px; height: 60px; flex-shrink: 0;
            border-radius: 0.375rem; margin-right: 1rem; overflow: hidden; 
        }
        #photo-gallery.view-list .photo-item-image-container img {
            width: 100%; height: 100%; object-fit: cover;
        }
        #photo-gallery.view-list .photo-item-info {
            flex-grow: 1; text-align: left; padding: 0; 
        }
        #photo-gallery.view-list .photo-item-info h3 {
            font-size: 1rem; font-weight: 600; color: #F3F4F6;
        }
        #photo-gallery.view-list .photo-item-info p {
            display: block; font-size: 0.875rem; color: #D1D5DB;
        }
        #photo-gallery.view-list .photo-item-actions { display: flex; gap: 1rem; }
        #photo-gallery.view-list .photo-item-actions a {
            opacity: 1; background-color: #374151; padding: 0.5rem; border-radius: 50%;
        }
        #photo-gallery.view-list .photo-item-actions a:hover {
            background-color: #4B5563; transform: none; 
        }
        #photo-gallery.view-list .photo-item-actions .material-icons { font-size: 20px; }
        
        /* Grid View Styles */
        #photo-gallery.view-list .photo-item-overlay { display: none; }
        #photo-gallery.view-grid .photo-item-actions { display: none; }
        #photo-gallery.view-grid .photo-item-info {
            padding: 0.75rem; background-color: #111827; 
            border-top: 1px solid #374151; 
        }
        #photo-gallery.view-grid .photo-item-info p { display: none; }
        #photo-gallery.view-grid .photo-item-image-container {
            width: 100%; height: 224px; position: relative; 
        }
        #photo-gallery.view-grid .photo-item-image-container img {
            width: 100%; height: 100%; object-fit: cover;
        }

        /* Pagination Styles */
        .pagination {
            display: flex; justify-content: center; gap: 0.5rem; margin-top: 2rem;
        }
        .pagination a, .pagination span {
            display: inline-flex; align-items: center; justify-content: center;
            width: 40px; height: 40px; border-radius: 50%;
            background-color: #374151; color: #D1D5DB; 
            font-weight: 500; text-decoration: none; transition: all 0.2s ease;
        }
        .pagination a:hover {
            background-color: #F59E0B; color: #111827;
        }
        .pagination .active {
            background-color: #F59E0B; color: #111827; font-weight: 700;
        }
        .pagination .disabled {
            background-color: #1F2937; color: #4B5563; cursor: not-allowed;
        }
    </style>
</head>
<body class="bg-gray-800 text-white">
    <div class="flex h-screen">
        <aside class="w-64 bg-gray-900 p-6 flex flex-col justify-between">
            <div>
                <img src="../public/images/nobglogo.png" alt="Marahuyo Logo" class="w-40 mx-auto mb-8">
                
                <nav>
                    <ul>
                        <li class="mb-4">
                            <a href="dashboard.php" class="nav-link hover:bg-gray-700 <?php echo ($current_page_name == 'dashboard.php') ? 'active' : ''; ?>">
                                <span class="material-icons">dashboard</span> Dashboard
                            </a>
                        </li>
                        <li class="mb-4">
                            <a href="templates.php" class="nav-link hover:bg-gray-700 <?php echo ($current_page_name == 'templates.php') ? 'active' : ''; ?>">
                                <span class="material-icons">layers</span> Templates
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            <a href="logout.php" class="nav-link logout-link hover:bg-gray-700">
                <span class="material-icons">logout</span> Log Out
            </a>
        </aside>

        <main class="flex-1 p-8 overflow-y-auto">
            <h1 class="text-4xl font-bold mb-8">Dashboard</h1>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="stat-card">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg text-gray-400">Layouts Remaining</h2>
                        <span class="material-icons text-3xl text-green-400">layers</span>
                    </div>
                    <p id="total-layouts-remaining" class="text-5xl font-bold text-white"><?php echo $total_remaining_layouts; ?></p>
                </div>
                
                <div class="stat-card">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg text-gray-400">Total Photos Captured</h2>
                        <span class="material-icons text-3xl text-amber-400">photo_library</span>
                    </div>
                    <p id="total-photos-count" class="text-5xl font-bold text-white"><?php echo $photo_count; ?></p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                
                <div class="bg-gray-900 p-6 rounded-lg shadow-lg">
                    <h2 class="text-2xl font-semibold mb-4 text-amber-400">Activate New Session</h2>
                    <p class="text-gray-400 mb-4">Set the count for each layout. This will replace any active session.</p>
                    
                    <form method="POST" action="dashboard.php">
                        <input type="hidden" name="action" value="activate">
                        <div class="space-y-2">
                            
                            <?php foreach ($available_layouts as $key => $name): ?>
                            <div class="form-row" onclick="document.getElementById('layout_<?php echo $key; ?>').focus()">
                                <label for="layout_<?php echo $key; ?>" class="text-gray-300"><?php echo htmlspecialchars($name); ?></label>
                                <input type="number" name="layouts[<?php echo $key; ?>]" id="layout_<?php echo $key; ?>" min="0" value="0" 
                                       class="form-input">
                            </div>
                            <?php endforeach; ?>

                        </div>
                        <button type="submit" class="w-full mt-6 bg-amber-500 hover:bg-amber-400 text-gray-900 font-bold py-3 px-4 rounded-lg transition-colors">
                            <span class="material-icons align-middle mr-2">play_circle_filled</span>
                            Activate New Session
                        </button>
                    </form>
                </div>

                <div class="bg-gray-900 p-6 rounded-lg shadow-lg">
                    <h2 class="text-2xl font-semibold mb-4 text-green-400">Current Active Session</h2>
                    
                    <div id="session-card-content">
                        <?php if ($total_remaining_layouts == 0): ?>
                            <p class="text-gray-400">No active session. Use the form on the left to start one.</p>
                        <?php else: ?>
                            <p class="text-gray-300 mb-4">These are the remaining layouts in the session:</p>
                            <ul id="session-layout-list" class="list-disc list-inside space-y-2 mb-6">
                                <?php foreach ($current_layout_counts as $layout_key => $count): ?>
                                    <li>
                                        <strong class="text-white"><?php echo $count; ?>x</strong> 
                                        <span class="text-gray-400"><?php echo htmlspecialchars($available_layouts[$layout_key] ?? $layout_key); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            
                            <form id="clear-session-form" method="POST" action="dashboard.php">
                                <input type="hidden" name="action" value="clear">
                                <button type="button" id="clear-session-btn" class="w-full bg-red-600 hover:bg-red-500 text-white font-bold py-3 px-4 rounded-lg transition-colors">
                                    <span class="material-icons align-middle mr-2">stop_circle</span>
                                    Clear Current Session
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-900 p-6 rounded-lg shadow-lg mt-8">
                <div class="flex flex-col md:flex-row justify-between md:items-center gap-4 mb-6">
                    <h2 class="text-3xl font-semibold">Recently Captured Photos</h2>
                    <div class="flex gap-2">
                        <button id="view-grid-btn" class="p-2 bg-gray-700 rounded-lg text-amber-400" title="Grid View">
                            <span class="material-icons">grid_view</span>
                        </button>
                        <button id="view-list-btn" class="p-2 bg-gray-700 rounded-lg text-white" title="List View">
                            <span class="material-icons">view_list</span>
                        </button>
                        
                        <button id="delete-all-btn" class="py-2 px-4 bg-red-600 hover:bg-red-500 text-white font-semibold rounded-lg flex items-center gap-2 ml-4">
                            <span class="material-icons">delete_sweep</span> Delete All
                        </button>
                    </div>
                </div>

                <?php if (empty($all_images)): ?>
                    <p class="text-gray-400">No photos have been captured yet.</p>
                <?php else: ?>
                    <div id="photo-gallery" class="view-grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        <?php foreach ($paginated_images as $image): 
                            $image_name = basename($image);
                            $file_size = filesize($image);
                            $date_modified = date("F d, Y g:ia", filemtime($image)); // Tama na ang oras nito
                            $file_extension = pathinfo($image_name, PATHINFO_EXTENSION);
                        ?>
                            <div class="photo-item group bg-gray-800 rounded-lg overflow-hidden shadow-lg relative">
                                
                                <div class="photo-item-image-container"> 
                                    <img src="<?php echo $image; ?>" alt="Captured photo">
                                    
                                    <div class="photo-item-overlay absolute inset-0 bg-black bg-opacity-60 flex items-center justify-center space-x-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                        <a href="<?php echo $image; ?>" download title="Download" class="text-white hover:text-amber-400 transform hover:scale-125 transition-transform">
                                            <span class="material-icons text-3xl">download</span>
                                        </a>
                                        <a href="print_image.php?file=<?php echo urlencode($image); ?>" target="_blank" title="Print" class="text-white hover:text-amber-400 transform hover:scale-125 transition-transform">
                                            <span class="material-icons text-3xl">print</span>
                                        </a>
                                        <a href="delete_photo.php?file=<?php echo urlencode($image_name); ?>" title="Delete" 
                                           class="delete-photo-btn text-red-500 hover:text-red-400 transform hover:scale-125 transition-transform">
                                            <span class="material-icons text-3xl">delete</span>
                                        </a>
                                    </div>
                                </div>

                                <div class="photo-item-info">
                                    <h3 class="font-semibold text-white truncate flex items-center">
                                        <span class="material-icons text-lg mr-2 text-amber-400">image</span>
                                        <?php echo $date_modified; ?>
                                    </h3>
                                    <p><?php echo round($file_size / 1024); ?> KB</p> 
                                </div>
                                
                                <div class="photo-item-actions">
                                    <a href="<?php echo $image; ?>" download title="Download" class="text-white hover:text-amber-400">
                                        <span class="material-icons">download</span>
                                    </a>
                                    <a href="print_image.php?file=<?php echo urlencode($image); ?>" target="_blank" title="Print" class="text-white hover:text-amber-400">
                                        <span class="material-icons">print</span>
                                    </a>
                                    <a href="delete_photo.php?file=<?php echo urlencode($image_name); ?>" title="Delete" 
                                       class="delete-photo-btn text-red-500 hover:text-red-400">
                                        <span class="material-icons">delete</span>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="pagination">
                        <?php if ($current_page > 1): ?>
                            <a href="?page=<?php echo $current_page - 1; ?>" title="Previous Page"><span class="material-icons">chevron_left</span></a>
                        <?php else: ?>
                            <span class="disabled"><span class="material-icons">chevron_left</span></span>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>" class="<?php echo ($i == $current_page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>
                        
                        <?php if ($current_page < $total_pages): ?>
                            <a href="?page=<?php echo $current_page + 1; ?>" title="Next Page"><span class="material-icons">chevron_right</span></a>
                        <?php else: ?>
                            <span class="disabled"><span class="material-icons">chevron_right</span></span>
                        <?php endif; ?>
                    </div>

                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        // --- (Live Data Polling) ---
        let currentLayoutCount = <?php echo $total_remaining_layouts; ?>;
        let currentPhotoCount = <?php echo $photo_count; ?>;
        const availableLayoutNames = <?php echo json_encode($available_layouts); ?>;

        function updateSessionUI(layoutsArray) {
            const cardContentEl = document.getElementById('session-card-content');
            const newLayoutCount = layoutsArray.length;
            
            document.getElementById('total-layouts-remaining').textContent = newLayoutCount;

            if (newLayoutCount === 0) {
                cardContentEl.innerHTML = '<p class="text-gray-400">No active session. Use the form on the left to start one.</p>';
            } else {
                const counts = {};
                layoutsArray.forEach(layout => { counts[layout] = (counts[layout] || 0) + 1; });

                let listHtml = '';
                for (const layoutKey in counts) {
                    const count = counts[layoutKey];
                    const layoutName = availableLayoutNames[layoutKey] || layoutKey;
                    listHtml += `<li><strong class="text-white">${count}x</strong> <span class="text-gray-400">${layoutName}</span></li>`;
                }

                cardContentEl.innerHTML = `
                    <p class="text-gray-300 mb-4">These are the remaining layouts in the session:</p>
                    <ul id="session-layout-list" class="list-disc list-inside space-y-2 mb-6">
                        ${listHtml}
                    </ul>
                    <form id="clear-session-form" method="POST" action="dashboard.php">
                        <input type="hidden" name="action" value="clear">
                        <button type="button" id="clear-session-btn" class="w-full bg-red-600 hover:bg-red-500 text-white font-bold py-3 px-4 rounded-lg transition-colors">
                            <span class="material-icons align-middle mr-2">stop_circle</span>
                            Clear Current Session
                        </button>
                    </form>
                `;
            }
            
            // Re-attach ang event listener para sa bagong "Clear" button (kung ginawa ito)
            attachClearButtonListener();
        }

        async function pollData() {
            try {
                const response = await fetch('admin/get_live_data.php?t=' + new Date().getTime());
                if (!response.ok) return; 

                const data = await response.json();
                const newLayoutCount = data.layouts.length;
                const newPhotoCount = data.photo_count;

                if (newPhotoCount !== currentPhotoCount) {
                    if (currentPhotoCount > 0 && newPhotoCount > 0) { 
                        console.log('New photo detected. Reloading...');
                        window.location.reload(); 
                    } else {
                        window.location.reload();
                    }
                    currentPhotoCount = newPhotoCount;
                }

                if (newLayoutCount !== currentLayoutCount) {
                    updateSessionUI(data.layouts);
                    
                    if (newLayoutCount === 0 && currentLayoutCount > 0) {
                        Swal.fire({
                            title: 'Session Finished!',
                            text: 'All activated layouts have been used.',
                            icon: 'success',
                            confirmButtonColor: '#F59E0B' 
                        });
                    }
                    currentLayoutCount = newLayoutCount;
                }

            } catch (error) {
                console.error('Error polling data:', error);
            }
        }

        setInterval(pollData, 3000);

        // --- (SweetAlert para sa PHP messages) ---
        <?php
        if (isset($_SESSION['alert'])) {
            $alert_type = $_SESSION['alert']['type']; 
            $alert_message = $_SESSION['alert']['message'];
            
            // *** BINAGO: Inalis ang 'toast: true' para maging modal ***
            echo "
                Swal.fire({
                    title: '" . ucfirst($alert_type) . "!',
                    text: '" . addslashes($alert_message) . "',
                    icon: '" . $alert_type . "',
                    confirmButtonColor: '#F59E0B'
                });
            ";
            
            unset($_SESSION['alert']); 
        }
        ?>
        
        // --- Function para sa Clear Session Button (para magamit ulit) ---
        function attachClearButtonListener() {
            const clearSessionBtn = document.getElementById('clear-session-btn');
            const clearSessionForm = document.getElementById('clear-session-form');

            if (clearSessionBtn && clearSessionForm) {
                clearSessionBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "All remaining layouts will be lost!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#DC2626',
                        cancelButtonColor: '#374151',
                        confirmButtonText: 'Yes, clear it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            clearSessionForm.submit(); // Manually submit the form
                        }
                    });
                });
            }
        }

        // --- (JS para sa View Toggle at Delete All) ---
        document.addEventListener('DOMContentLoaded', () => {
            const gallery = document.getElementById('photo-gallery');
            const gridBtn = document.getElementById('view-grid-btn');
            const listBtn = document.getElementById('view-list-btn');
            const deleteAllBtn = document.getElementById('delete-all-btn');
            
            // Para sa "Clear Session" button
            attachClearButtonListener(); 

            // Para sa "Delete Photo" buttons
            const deletePhotoButtons = document.querySelectorAll('.delete-photo-btn');
            deletePhotoButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault(); // Pigilan ang link
                    const deleteUrl = e.currentTarget.href; // Kunin ang URL
                    
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This will delete the photo and its matching GIF. This cannot be undone!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#DC2626',
                        cancelButtonColor: '#374151',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = deleteUrl; // Ituloy ang pag-navigate
                        }
                    });
                });
            });

            if (gallery) {
                gridBtn.addEventListener('click', () => {
                    gallery.classList.add('view-grid');
                    gallery.classList.remove('view-list');
                    gridBtn.classList.add('text-amber-400');
                    gridBtn.classList.remove('text-white');
                    listBtn.classList.add('text-white');
                    listBtn.classList.remove('text-amber-400');
                });

                listBtn.addEventListener('click', () => {
                    gallery.classList.add('view-list');
                    gallery.classList.remove('view-grid');
                    listBtn.classList.add('text-amber-400');
                    listBtn.classList.remove('text-white');
                    gridBtn.classList.add('text-white');
                    gridBtn.classList.remove('text-amber-400');
                });
            }

            if (deleteAllBtn) {
                deleteAllBtn.addEventListener('click', (e) => {
                    e.preventDefault(); 
                    
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This will permanently delete ALL files (PNGs and GIFs). This action cannot be undone!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#DC2626',
                        cancelButtonColor: '#374151',
                        confirmButtonText: 'Yes, delete all!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = 'admin/delete_all.php';
                            
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'confirm_delete';
                            input.value = 'true';
                            form.appendChild(input);
                            
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