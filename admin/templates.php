<?php
session_start();
date_default_timezone_set('Asia/Manila');

// --- PANGUNAHING CONFIGURATIONS ---
$current_page_name = basename($_SERVER['PHP_SELF']);
define('FRAMES_DIR', '../public/frames/');
define('FRAMES_URL', '../public/frames/');

$available_layouts = [
    'strip-3' => 'Photo Strip (3 Shots)',
    'strip-4' => 'Long Strip (4 Shots)',
    'grid-4' => 'Grid (4 Shots)',
    'spotlight-3' => 'Spotlight (3 Shots)',
    'grid-6-portrait' => 'Grid Portrait (6 Shots)',
    'grid-6-landscape' => 'Grid Landscape (6 Shots)',
];

// --- DATA PARA SA JAVASCRIPT (Layout Guides) ---
$layout_configs_json = json_encode([
    'strip-3' => ['w' => 2, 'h' => 6, 'guides' => [['x' => 0.04, 'y' => 0.025, 'w' => 0.92, 'h' => 0.275], ['x' => 0.04, 'y' => 0.325, 'w' => 0.92, 'h' => 0.275], ['x' => 0.04, 'y' => 0.625, 'w' => 0.92, 'h' => 0.275]]],
    'strip-4' => ['w' => 2, 'h' => 8, 'guides' => [['x' => 0.04, 'y' => 0.02, 'w' => 0.92, 'h' => 0.205], ['x' => 0.04, 'y' => 0.245, 'w' => 0.92, 'h' => 0.205], ['x' => 0.04, 'y' => 0.47, 'w' => 0.92, 'h' => 0.205], ['x' => 0.04, 'y' => 0.695, 'w' => 0.92, 'h' => 0.205]]],
    'grid-4' => ['w' => 4, 'h' => 6, 'guides' => [['x' => 0.03, 'y' => 0.03, 'w' => 0.455, 'h' => 0.42], ['x' => 0.515, 'y' => 0.03, 'w' => 0.455, 'h' => 0.42], ['x' => 0.03, 'y' => 0.48, 'w' => 0.455, 'h' => 0.42], ['x' => 0.515, 'y' => 0.48, 'w' => 0.455, 'h' => 0.42]]],
    'spotlight-3' => ['w' => 6, 'h' => 4, 'guides' => [['x' => 0.033, 'y' => 0.04, 'w' => 0.6, 'h' => 0.75], ['x' => 0.666, 'y' => 0.04, 'w' => 0.3, 'h' => 0.36], ['x' => 0.666, 'y' => 0.43, 'w' => 0.3, 'h' => 0.36]]],
    'grid-6-portrait' => ['w' => 4, 'h' => 6, 'guides' => [['x' => 0.03, 'y' => 0.02, 'w' => 0.455, 'h' => 0.28], ['x' => 0.515, 'y' => 0.02, 'w' => 0.455, 'h' => 0.28], ['x' => 0.03, 'y' => 0.32, 'w' => 0.455, 'h' => 0.28], ['x' => 0.515, 'y' => 0.32, 'w' => 0.455, 'h' => 0.28], ['x' => 0.03, 'y' => 0.62, 'w' => 0.455, 'h' => 0.28], ['x' => 0.515, 'y' => 0.62, 'w' => 0.455, 'h' => 0.28]]],
    'grid-6-landscape' => ['w' => 6, 'h' => 4, 'guides' => [['x' => 0.02, 'y' => 0.04, 'w' => 0.3, 'h' => 0.38], ['x' => 0.34, 'y' => 0.04, 'w' => 0.3, 'h' => 0.38], ['x' => 0.66, 'y' => 0.04, 'w' => 0.3, 'h' => 0.38], ['x' => 0.02, 'y' => 0.46, 'w' => 0.3, 'h' => 0.38], ['x' => 0.34, 'y' => 0.46, 'w' => 0.3, 'h' => 0.38], ['x' => 0.66, 'y' => 0.46, 'w' => 0.3, 'h' => 0.38]]],
]);

if (!is_dir(FRAMES_DIR)) {
    mkdir(FRAMES_DIR, 0755, true);
}

// --- FORM HANDLING ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // --- SAVE LOGIC ---
    if (isset($_POST['action']) && $_POST['action'] == 'save' && isset($_POST['layout_key']) && isset($_POST['imageData'])) {
        $layout_key = basename($_POST['layout_key']);
        $imageData = $_POST['imageData'];
        if (!array_key_exists($layout_key, $available_layouts)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid layout.']);
            exit;
        }
        $imageData = str_replace('data:image/png;base64,', '', $imageData);
        $imageData = str_replace(' ', '+', $imageData);
        $data = base64_decode($imageData);
        if ($data === false) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to decode image data.']);
            exit;
        }
        $target_filename = $layout_key . '__' . time() . '-custom-frame.png';
        $target_path = FRAMES_DIR . $target_filename;
        if (file_put_contents($target_path, $data)) {
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'New custom frame saved!'];
            echo json_encode(['status' => 'success', 'message' => 'Frame saved!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save file to server.']);
        }
        exit;
    }
    
    // --- DELETE LOGIC ---
    if (isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['frame_to_delete'])) {
        $file_to_delete = basename($_POST['frame_to_delete']); 
        $target_path = FRAMES_DIR . $file_to_delete;
        if (file_exists($target_path) && is_file($target_path)) {
            if (unlink($target_path)) {
                $_SESSION['alert'] = ['type' => 'info', 'message' => 'Frame deleted.'];
            } else {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'Could not delete file.'];
            }
        } else {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'File not found.'];
        }
        header('Location: templates.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frame Editor - Marahuyo Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js"></script>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Roboto:wght@400;500;700&family=Orbitron:wght@400;700&family=Playfair+Display:wght@400;700&family=Pacifico&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Poppins', sans-serif; }
        
        /* === MAIN ADMIN HEADER (Gallery View) === */
        .nav-link-header { display: inline-block; padding: 0.5rem 1rem; border-radius: 0.375rem; font-weight: 500; transition: all 0.2s ease; color: #D1D5DB; }
        .nav-link-header:hover { background-color: #374151; color: #FFFFFF; }
        .nav-link-header.active { background-color: #F59E0B; color: #111827; font-weight: 600; }
        .logout-link-header { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 0.375rem; font-weight: 500; color: #D1D5DB; transition: all 0.2s ease; }
        .logout-link-header:hover { background-color: #4B5563; color: #FFFFFF; }
        
        /* === GALLERY VIEW === */
        .frame-card { background-color: #1F2937; border-radius: 0.5rem; overflow: hidden; position: relative; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); transition: all 0.2s ease; }
        .frame-card:hover { transform: translateY(-4px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .frame-preview { height: 200px; background-color: #374151; background-image: linear-gradient(45deg, #4B5563 25%, transparent 25%), linear-gradient(-45deg, #4B5563 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #4B5563 75%), linear-gradient(-45deg, transparent 75%, #4B5563 75%); background-size: 20px 20px; background-position: 0 0, 0 10px, 10px -10px, -10px 0px; }
        .frame-preview img { width: 100%; height: 100%; object-fit: contain; }

        /* === EDITOR VIEW (Full Screen) === */
        #editor-view {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            display: grid;
            grid-template-rows: auto 1fr; /* Header, Main Content */
            z-index: 50;
            background-color: #1F2937;
        }
        #editor-header {
            background-color: #111827;
            border-bottom: 1px solid #374151;
            display: flex; justify-content: space-between; align-items: center;
            padding: 0.75rem 1.5rem;
            flex-shrink: 0;
        }
        #editor-body {
            display: grid;
            grid-template-columns: 320px 1fr; /* Mas malaking sidebar, 1fr ang main */
            height: 100%; overflow: hidden;
        }
        #editor-sidebar {
            background-color: #111827;
            border-right: 1px solid #374151;
            display: grid;
            grid-template-columns: 80px 1fr; /* Icon Tabs, Tab Content */
        }
        #sidebar-tabs { background-color: #0D1117; padding-top: 1rem; }
        #sidebar-content { background-color: #111827; padding: 1.5rem; overflow-y: auto; }
        
        .sidebar-tab-btn {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 1rem 0.5rem; width: 100%;
            color: #9CA3AF; border-left: 4px solid transparent;
            transition: all 0.2s ease;
        }
        .sidebar-tab-btn:hover { background-color: #1F2937; color: white; }
        .sidebar-tab-btn.active {
            background-color: #111827;
            color: #F59E0B;
            border-left-color: #F59E0B;
        }
        .sidebar-tab-btn .material-icons { font-size: 28px; }
        .sidebar-tab-btn span { font-size: 0.75rem; margin-top: 0.25rem; font-weight: 500; }
        
        .sidebar-panel { display: none; }
        .sidebar-panel.active { display: block; }
        .sidebar-heading { font-size: 1.25rem; font-weight: 600; color: #F3F4F6; margin-bottom: 1.5rem; }

        .element-btn {
            background-color: #374151; color: white; padding: 1rem; border-radius: 0.5rem;
            transition: all 0.2s ease; display: flex; align-items: center; gap: 1rem;
            font-weight: 500; text-align: left; width: 100%;
        }
        .element-btn:hover { background-color: #4B5563; }
        .element-preview {
            width: 40px; height: 40px; background-color: #4B5563;
            border-radius: 0.25rem; display: flex; align-items: center;
            justify-content: center; flex-shrink: 0;
        }
        
        #editor-main {
            display: grid;
            grid-template-rows: auto 1fr; /* Properties, Canvas */
            overflow: hidden;
        }
        #properties-toolbar {
            background-color: #111827;
            border-bottom: 1px solid #374151;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            min-height: 60px;
            z-index: 10;
        }
        #canvas-stage {
            background-color: #374151; /* Dark gray background */
            overflow: auto;
            padding: 2.5rem; /* Mas malaking padding */
            display: flex;
            align-items: center;
            justify-content: center;
        }
        #canvas-wrapper {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
            background-color: white; /* Para makita ang canvas */
        }
        .canvas-container { margin: 0 auto !important; }

        /* Properties Panel Styles */
        .prop-group { display: flex; align-items: center; gap: 0.5rem; }
        .prop-label { font-size: 0.875rem; color: #9CA3AF; }
        .prop-input {
            background-color: #374151; border: 1px solid #4B5563; color: white;
            border-radius: 0.375rem; padding: 0.25rem 0.5rem;
        }
        .prop-btn {
            background-color: #374151; border: 1px solid #4B5563; color: white;
            border-radius: 0.375rem; padding: 0.25rem;
            width: 36px; height: 36px;
            display: inline-flex; align-items: center; justify-content: center;
        }
        .prop-btn:hover { background-color: #4B5563; }
        .prop-btn.active {
            background-color: #F59E0B; color: #111827; border-color: #F59E0B;
        }
        .color-well {
            width: 36px; height: 36px; border: 2px solid #4B5563; border-radius: 0.375rem;
            padding: 3px; cursor: pointer; background-clip: content-box !important;
        }
        .color-well:hover { border-color: #9CA3AF; }
        .prop-input[type="color"] { opacity: 0; width: 0; height: 0; padding: 0; position: absolute; }
        .divider { width: 1px; height: 24px; background-color: #374151; }
    </style>
</head>
<body class="bg-gray-800 text-white flex flex-col h-screen overflow-hidden">
    
    <div id="gallery-view" class="flex flex-col h-full">
        <header class="bg-gray-900 text-white p-4 shadow-lg w-full flex-shrink-0 z-20">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <div class="flex items-center space-x-8">
                    <img src="../public/images/nobglogo.png" alt="Marahuyo Logo" class="h-10 w-auto">
                    <nav>
                        <ul class="flex space-x-2">
                            <li><a href="dashboard.php" class="nav-link-header <?php echo ($current_page_name == 'dashboard.php') ? 'active' : ''; ?>">Dashboard</a></li>
                            <li><a href="templates.php" class="nav-link-header <?php echo ($current_page_name == 'templates.php') ? 'active' : ''; ?>">Templates</a></li>
                        </ul>
                    </nav>
                </div>
                <a href="logout.php" class="logout-link-header"><span class="material-icons">logout</span> Log Out</a>
            </div>
        </header>

        <main class="flex-1 w-full overflow-y-auto p-4 md:p-8">
            <div class="max-w-7xl mx-auto">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-4xl font-bold">Frame Templates</h1>
                    <button id="btn-show-creator" class="bg-amber-500 hover:bg-amber-400 text-gray-900 font-bold py-3 px-6 rounded-lg transition-colors inline-flex items-center gap-2">
                        <span class="material-icons">add_circle</span> Create New Frame
                    </button>
                </div>

                <div class="bg-gray-900 p-6 rounded-lg shadow-lg space-y-8">
                    <h2 class="text-2xl font-semibold mb-2">Current Saved Frames</h2>
                    <?php foreach ($available_layouts as $layout_key => $layout_name): ?>
                        <?php $current_frames = glob(FRAMES_DIR . $layout_key . '__*.png'); ?>
                        <div>
                            <h3 class="text-xl font-semibold text-amber-400 border-b border-gray-700 pb-2 mb-4"><?php echo htmlspecialchars($layout_name); ?></h3>
                            <?php if (empty($current_frames)): ?>
                                <p class="text-gray-400 italic">No frames saved for this layout yet.</p>
                            <?php else: ?>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
                                    <?php foreach ($current_frames as $frame_path): ?>
                                        <?php 
                                            $frame_filename = basename($frame_path); 
                                            $frame_url = FRAMES_URL . $frame_filename;
                                            $display_name_parts = explode('__', $frame_filename, 2);
                                            $display_name = isset($display_name_parts[1]) ? $display_name_parts[1] : $frame_filename;
                                            $display_name = preg_replace('/^\d+-/', '', $display_name); // Alisin ang timestamp
                                        ?>
                                        <div class="frame-card">
                                            <div class="frame-preview"><img src="<?php echo $frame_url; ?>?t=<?php echo time(); ?>" alt="<?php echo htmlspecialchars($frame_filename); ?>"></div>
                                            <div class="p-4">
                                                <h3 class="font-semibold text-white truncate" title="<?php echo htmlspecialchars($display_name); ?>"><?php echo htmlspecialchars($display_name); ?></h3>
                                                <form action="templates.php" method="POST" class="mt-3" onsubmit="return confirmDelete(this);">
                                                    <input type="hidden" name="action" value="delete"><input type="hidden" name="frame_to_delete" value="<?php echo htmlspecialchars($frame_filename); ?>">
                                                    <button type="submit" class="w-full text-sm bg-red-600 hover:bg-red-500 text-white font-bold py-2 px-4 rounded-lg transition-colors inline-flex items-center justify-center gap-2">
                                                        <span class="material-icons text-base">delete</span> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>

    <div id="editor-view" class="hidden">
        <header id="editor-header">
            <div class="flex items-center gap-4">
                <button id="btn-back-to-gallery" class="text-gray-400 hover:text-white" title="Back to Gallery">
                    <span class="material-icons text-3xl">arrow_back</span>
                </button>
                <div>
                    <h2 class="text-xl font-bold text-white">Frame Editor</h2>
                    <span id="editor-layout-name" class="text-sm text-amber-400">No Layout Selected</span>
                </div>
            </div>
            <button id="btn-save-frame" class="bg-green-600 hover:bg-green-500 text-white font-bold py-2 px-5 rounded-lg transition-colors inline-flex items-center gap-2">
                <span class="material-icons">save</span> Save Frame
            </button>
        </header>

        <div id="editor-body">
            <div id="editor-sidebar">
                <div id="sidebar-tabs">
                    <button class="sidebar-tab-btn active" data-panel="panel-elements" title="Elements">
                        <span class="material-icons">category</span>
                        <span class="text-xs">Elements</span>
                    </button>
                    <button class="sidebar-tab-btn" data-panel="panel-uploads" title="Uploads">
                        <span class="material-icons">upload_file</span>
                        <span class="text-xs">Uploads</span>
                    </button>
                    <button class="sidebar-tab-btn" data-panel="panel-background" title="Background">
                        <span class="material-icons">layers</span>
                        <span class="text-xs">Background</span>
                    </button>
                </div>
                <div id="sidebar-content">
                    <div id="panel-elements" class="sidebar-panel active">
                        <h3 class="sidebar-heading">Elements</h3>
                        <div class="space-y-3">
                            <button id="btn-add-text" class="element-btn"><div class="element-preview"><span class="material-icons">title</span></div> Add Text</button>
                            <button id="btn-add-rect" class="element-btn"><div class="element-preview"><span class="material-icons">check_box_outline_blank</span></div> Add Rectangle</button>
                            <button id="btn-add-circle" class="element-btn"><div class="element-preview"><span class="material-icons">radio_button_unchecked</span></div> Add Circle</button>
                            <button id="btn-add-triangle" class="element-btn"><div class="element-preview"><span class="material-icons">change_history</span></div> Add Triangle</button>
                        </div>
                    </div>
                    <div id="panel-uploads" class="sidebar-panel">
                        <h3 class="sidebar-heading">Uploads</h3>
                        <p class="text-sm text-gray-400 mb-4">Upload your own logos, stickers, or icons to add to the frame.</p>
                        <label class="element-btn cursor-pointer bg-amber-600 hover:bg-amber-500">
                            <span class="material-icons">upload</span> Upload Image
                            <input type="file" id="image-uploader" accept="image/png, image/jpeg" class="hidden">
                        </label>
                    </div>
                    <div id="panel-background" class="sidebar-panel">
                        <h3 class="sidebar-heading">Background Color</h3>
                        <p class="text-sm text-gray-400 mb-4">Choose a solid color for the frame background.</p>
                        <div class="flex justify-center">
                            <input type="color" id="bg-color-picker" value="#FFFFFF" class="w-full h-24 p-0 m-0 border-0 cursor-pointer">
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="editor-main">
                <div id="properties-toolbar" class="hidden">
                    </div>
                <div id="canvas-stage">
                    <div id="canvas-wrapper">
                        <canvas id="frame-canvas"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div id="properties-templates" class="hidden">
        <div id="text-properties" class="w-full flex items-center justify-between px-6 h-full">
            <div class="flex items-center gap-4">
                <div class="prop-group">
                    <select id="font-family" class="prop-input w-36">
                        <option>Poppins</option><option>Roboto</option><option>Orbitron</option><option>Playfair Display</option><option>Pacifico</option><option>Arial</option><option>Times New Roman</option>
                    </select>
                </div>
                <div class="prop-group">
                    <input type="number" id="font-size" class="prop-input w-20" value="40" min="8" max="500">
                </div>
                <div class="prop-group">
                    <div class="color-well" style="background-color: #000000;"><input type="color" id="text-color" value="#000000" class="prop-input"></div>
                </div>
                <div class="prop-group">
                    <button id="font-bold" class="prop-btn" title="Bold"><span class="material-icons text-lg">format_bold</span></button>
                    <button id="font-italic" class="prop-btn" title="Italic"><span class="material-icons text-lg">format_italic</span></button>
                    <button id="font-underline" class="prop-btn" title="Underline"><span class="material-icons text-lg">format_underline</span></button>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="prop-group">
                    <span class="material-icons text-lg text-gray-400" title="Opacity">opacity</span>
                    <input type="range" id="obj-opacity" min="0" max="100" value="100" class="w-24">
                    <span id="obj-opacity-value" class="text-white w-10 text-sm">100%</span>
                </div>
                <div class="divider"></div>
                <div class="prop-group">
                    <button id="btn-bring-forward" class="prop-btn" title="Bring Forward"><span class="material-icons text-lg">flip_to_front</span></button>
                    <button id="btn-send-backward" class="prop-btn" title="Send Backward"><span class="material-icons text-lg">flip_to_back</span></button>
                </div>
                <div class="divider"></div>
                <div class="prop-group">
                    <button id="btn-delete-object" class="prop-btn hover:bg-red-600 hover:border-red-500" title="Delete"><span class="material-icons text-lg">delete</span></button>
                </div>
            </div>
        </div>

        <div id="image-properties" class="w-full flex items-center justify-end px-6 h-full">
            <div class="flex items-center gap-4">
                <div class="prop-group">
                    <span class="material-icons text-lg text-gray-400" title="Opacity">opacity</span>
                    <input type="range" id="obj-opacity" min="0" max="100" value="100" class="w-24">
                    <span id="obj-opacity-value" class="text-white w-10 text-sm">100%</span>
                </div>
                <div class="divider"></div>
                <div class="prop-group">
                    <button id="btn-bring-forward" class="prop-btn" title="Bring Forward"><span class="material-icons text-lg">flip_to_front</span></button>
                    <button id="btn-send-backward" class="prop-btn" title="Send Backward"><span class="material-icons text-lg">flip_to_back</span></button>
                </div>
                <div class="divider"></div>
                <div class="prop-group">
                    <button id="btn-delete-object" class="prop-btn hover:bg-red-600 hover:border-red-500" title="Delete"><span class="material-icons text-lg">delete</span></button>
                </div>
            </div>
        </div>
        
        <div id="shape-properties" class="w-full flex items-center justify-between px-6 h-full">
            <div class="flex items-center gap-4">
                <div class="prop-group">
                    <label class="prop-label">Fill</label>
                    <div class="color-well" style="background-color: #000000;"><input type="color" id="fill-color" value="#000000" class="prop-input"></div>
                </div>
                <div class="prop-group">
                    <label class="prop-label">Stroke</label>
                    <div class="color-well" style="background-color: #000000;"><input type="color" id="stroke-color" value="#000000" class="prop-input"></div>
                </div>
                 <div class="prop-group">
                    <label for="stroke-width" class="prop-label">Width</label>
                    <input type="number" id="stroke-width" class="prop-input w-20" value="0" min="0" max="100">
                </div>
            </div>
            <div class="flex items-center gap-4">
                 <div class="prop-group">
                    <span class="material-icons text-lg text-gray-400" title="Opacity">opacity</span>
                    <input type="range" id="obj-opacity" min="0" max="100" value="100" class="w-24">
                    <span id="obj-opacity-value" class="text-white w-10 text-sm">100%</span>
                </div>
                <div class="divider"></div>
                <div class="prop-group">
                    <button id="btn-bring-forward" class="prop-btn" title="Bring Forward"><span class="material-icons text-lg">flip_to_front</span></button>
                    <button id="btn-send-backward" class="prop-btn" title="Send Backward"><span class="material-icons text-lg">flip_to_back</span></button>
                </div>
                <div class="divider"></div>
                <div class="prop-group">
                    <button id="btn-delete-object" class="prop-btn hover:bg-red-600 hover:border-red-500" title="Delete"><span class="material-icons text-lg">delete</span></button>
                </div>
            </div>
        </div>
    </div>


    <script>
        const layoutConfig = <?php echo $layout_configs_json; ?>;
        const availableLayouts = <?php echo json_encode($available_layouts); ?>;
        const PRINT_DPI = 300; 

        let fabricCanvas = null;
        let currentLayoutKey = '';

        // --- DOM Elements ---
        const galleryView = document.getElementById('gallery-view');
        const editorView = document.getElementById('editor-view');
        const btnShowCreator = document.getElementById('btn-show-creator');
        const btnBackToGallery = document.getElementById('btn-back-to-gallery');
        const btnSaveFrame = document.getElementById('btn-save-frame');
        
        const propertiesToolbar = document.getElementById('properties-toolbar');
        const canvasWrapper = document.getElementById('canvas-wrapper');
        const canvasEl = document.getElementById('frame-canvas');
        const imageUploader = document.getElementById('image-uploader');
        const editorLayoutName = document.getElementById('editor-layout-name');
        
        // Templates
        const propertiesTemplates = document.getElementById('properties-templates');
        const textPropsTemplate = document.getElementById('text-properties').cloneNode(true);
        const imagePropsTemplate = document.getElementById('image-properties').cloneNode(true);
        const shapePropsTemplate = document.getElementById('shape-properties').cloneNode(true);

        // --- View Switching ---
        btnShowCreator.addEventListener('click', async () => {
            const inputOptions = new Promise((resolve) => {
                const options = {};
                for (const [key, value] of Object.entries(availableLayouts)) {
                    options[key] = value;
                }
                resolve(options);
            });

            const { value: layout } = await Swal.fire({
                title: 'Select a Layout',
                text: 'Choose a layout to start creating your frame:',
                input: 'select',
                inputOptions: inputOptions,
                inputPlaceholder: 'Select a layout',
                showCancelButton: true,
                confirmButtonText: 'Start Editing &rarr;',
                confirmButtonColor: '#F59E0B',
                cancelButtonColor: '#374151',
            });

            if (layout) {
                currentLayoutKey = layout;
                editorLayoutName.textContent = availableLayouts[layout];
                galleryView.classList.add('hidden');
                editorView.classList.remove('hidden');
                setTimeout(() => initEditor(currentLayoutKey), 50); 
            }
        });

        btnBackToGallery.addEventListener('click', () => {
            Swal.fire({
                title: 'Are you sure?',
                text: "Any unsaved changes will be lost!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DC2626',
                cancelButtonColor: '#374151',
                confirmButtonText: 'Yes, go back to gallery'
            }).then((result) => {
                if (result.isConfirmed) {
                    galleryView.classList.remove('hidden');
                    editorView.classList.add('hidden');
                    if (fabricCanvas) {
                        fabricCanvas.dispose();
                        fabricCanvas = null;
                    }
                }
            });
        });

        // --- Editor Initialization ---
        function initEditor(layoutKey) {
            if (!layoutKey) return;
            const config = layoutConfig[layoutKey];
            
            propertiesToolbar.classList.add('hidden');
            propertiesToolbar.innerHTML = ''; 

            const stage = document.getElementById('canvas-stage');
            const stageW = stage.clientWidth - 128; // p-8 horizontal (8rem = 128px)
            const stageH = stage.clientHeight - 128; // p-8 vertical
            const aspect = config.w / config.h;

            let editorW = stageW;
            let editorH = editorW / aspect;

            if (editorH > stageH) {
                editorH = stageH;
                editorW = editorH * aspect;
            }

            if (fabricCanvas) { fabricCanvas.dispose(); }
            
            canvasEl.width = editorW;
            canvasEl.height = editorH;
            canvasWrapper.style.width = `${editorW}px`;
            canvasWrapper.style.height = `${editorH}px`;

            fabricCanvas = new fabric.Canvas('frame-canvas', {
                width: editorW,
                height: editorH,
                backgroundColor: '#FFFFFF',
                preserveObjectStacking: true
            });

            fabricCanvas.printWidth = config.w * PRINT_DPI;
            fabricCanvas.printHeight = config.h * PRINT_DPI;

            addPhotoGuides(config);
            fabricCanvas.renderAll();
            attachCanvasListeners();
            document.getElementById('bg-color-picker').value = '#FFFFFF';
        }

        function addPhotoGuides(config) {
            config.guides.forEach(g => {
                const rect = new fabric.Rect({
                    left: g.x * fabricCanvas.width,
                    top: g.y * fabricCanvas.height,
                    width: g.w * fabricCanvas.width,
                    height: g.h * fabricCanvas.height,
                    fill: 'rgba(0,0,0,0.3)',
                    stroke: '#FFF',
                    strokeDashArray: [5, 5],
                    selectable: false, evented: false, data: { isGuide: true }
                });
                fabricCanvas.add(rect);
                rect.sendToBack();
            });
        }

        // --- Sidebar Tab Logic ---
        const tabButtons = document.querySelectorAll('.sidebar-tab-btn');
        const tabPanels = document.querySelectorAll('.sidebar-panel');
        tabButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                tabButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                const panelId = btn.dataset.panel;
                tabPanels.forEach(p => p.classList.toggle('active', p.id === panelId));
            });
        });

        // --- Properties Panel Logic ---
        function updatePropertiesPanel(e) {
            const activeObject = e.target;
            if (!activeObject) return;
            
            // --- General Handlers (para sa lahat ng object types) ---
            const addGeneralHandlers = (props) => {
                // Opacity
                const opacityControl = props.querySelector('#obj-opacity');
                const opacityValue = props.querySelector('#obj-opacity-value');
                if(opacityControl) {
                    const currentOpacity = (activeObject.get('opacity') * 100).toFixed(0);
                    opacityControl.value = currentOpacity;
                    opacityValue.textContent = `${currentOpacity}%`;
                    opacityControl.addEventListener('input', (e) => {
                        const newOpacity = parseFloat(e.target.value);
                        activeObject.set('opacity', newOpacity / 100).setCoords();
                        fabricCanvas.renderAll();
                        opacityValue.textContent = `${newOpacity}%`;
                    });
                }
                // Align
                props.querySelector('#btn-bring-forward')?.addEventListener('click', () => {
                    activeObject.bringForward();
                    fabricCanvas.renderAll();
                });
                props.querySelector('#btn-send-backward')?.addEventListener('click', () => {
                    activeObject.sendBackwards();
                    fabricCanvas.renderAll();
                });
                // Delete
                props.querySelector('#btn-delete-object')?.addEventListener('click', deleteSelectedObject);
            };

            propertiesToolbar.innerHTML = '';
            propertiesToolbar.classList.remove('hidden');

            // === TEXT PROPERTIES ===
            if (activeObject.type === 'i-text' || activeObject.type === 'textbox') {
                const props = textPropsTemplate.cloneNode(true);
                propertiesToolbar.appendChild(props);
                addGeneralHandlers(props);

                const fontControl = props.querySelector('#font-family');
                const sizeControl = props.querySelector('#font-size');
                const colorControl = props.querySelector('#text-color');
                const colorWell = props.querySelector('.color-well');
                const boldControl = props.querySelector('#font-bold');
                const italicControl = props.querySelector('#font-italic');
                const underlineControl = props.querySelector('#font-underline');

                fontControl.value = activeObject.get('fontFamily') || 'Poppins';
                sizeControl.value = activeObject.get('fontSize') || 40;
                colorControl.value = activeObject.get('fill') || '#000000';
                colorWell.style.backgroundColor = activeObject.get('fill') || '#000000';
                if (activeObject.get('fontWeight') === 'bold') boldControl.classList.add('active');
                if (activeObject.get('fontStyle') === 'italic') italicControl.classList.add('active');
                if (activeObject.get('underline')) underlineControl.classList.add('active');

                fontControl.addEventListener('change', (e) => activeObject.set('fontFamily', e.target.value).setCoords() & fabricCanvas.renderAll());
                sizeControl.addEventListener('change', (e) => activeObject.set('fontSize', parseInt(e.target.value) || 40).setCoords() & fabricCanvas.renderAll());
                colorControl.addEventListener('input', (e) => {
                    activeObject.set('fill', e.target.value).setCoords();
                    fabricCanvas.renderAll();
                    colorWell.style.backgroundColor = e.target.value;
                });
                boldControl.addEventListener('click', () => {
                    const isBold = activeObject.get('fontWeight') === 'bold';
                    activeObject.set('fontWeight', isBold ? 'normal' : 'bold').setCoords();
                    fabricCanvas.renderAll();
                    boldControl.classList.toggle('active', !isBold);
                });
                italicControl.addEventListener('click', () => {
                    const isItalic = activeObject.get('fontStyle') === 'italic';
                    activeObject.set('fontStyle', isItalic ? 'normal' : 'italic').setCoords();
                    fabricCanvas.renderAll();
                    italicControl.classList.toggle('active', !isItalic);
                });
                underlineControl.addEventListener('click', () => {
                    const isUnderline = activeObject.get('underline');
                    activeObject.set('underline', !isUnderline).setCoords();
                    fabricCanvas.renderAll();
                    underlineControl.classList.toggle('active', !isUnderline);
                });

            // === IMAGE PROPERTIES ===
            } else if (activeObject.type === 'image') {
                const props = imagePropsTemplate.cloneNode(true);
                propertiesToolbar.appendChild(props);
                addGeneralHandlers(props);
                
            // === SHAPE PROPERTIES ===
            } else if (['rect', 'circle', 'triangle'].includes(activeObject.type)) {
                const props = shapePropsTemplate.cloneNode(true);
                propertiesToolbar.appendChild(props);
                addGeneralHandlers(props);

                const fillControl = props.querySelector('#fill-color');
                const fillWell = props.querySelector('label[class="prop-label"] + .color-well'); // Mas specific selector
                const strokeControl = props.querySelector('#stroke-color');
                const strokeWell = props.querySelector('label[for="Stroke"] + .color-well');
                const strokeWidthControl = props.querySelector('#stroke-width');

                fillControl.value = activeObject.get('fill') || '#000000';
                fillWell.style.backgroundColor = activeObject.get('fill') || '#000000';
                strokeControl.value = activeObject.get('stroke') || '#000000';
                strokeWell.style.backgroundColor = activeObject.get('stroke') || '#000000';
                strokeWidthControl.value = activeObject.get('strokeWidth') || 0;
                
                fillControl.addEventListener('input', (e) => {
                    activeObject.set('fill', e.target.value).setCoords();
                    fabricCanvas.renderAll();
                    fillWell.style.backgroundColor = e.target.value;
                });
                strokeControl.addEventListener('input', (e) => {
                    activeObject.set('stroke', e.target.value).setCoords();
                    fabricCanvas.renderAll();
                    strokeWell.style.backgroundColor = e.target.value;
                });
                strokeWidthControl.addEventListener('change', (e) => activeObject.set('strokeWidth', parseInt(e.target.value) || 0).setCoords() & fabricCanvas.renderAll());
            }
        }

        function deleteSelectedObject() {
             if (fabricCanvas) {
                const activeObjects = fabricCanvas.getActiveObjects();
                if (activeObjects.length > 0) {
                    activeObjects.forEach(obj => fabricCanvas.remove(obj));
                    fabricCanvas.discardActiveObject();
                }
            }
        }

        function attachCanvasListeners() {
            if (!fabricCanvas) return;
            fabricCanvas.on('selection:created', (e) => updatePropertiesPanel(e));
            fabricCanvas.on('selection:updated', (e) => updatePropertiesPanel(e));
            fabricCanvas.on('selection:cleared', () => {
                propertiesToolbar.classList.add('hidden');
                propertiesToolbar.innerHTML = '';
            });
            fabricCanvas.on('object:modified', (e) => updatePropertiesPanel(e));
        }

        // --- Sidebar Element Button Listeners ---
        document.getElementById('btn-add-text').addEventListener('click', () => {
            if (!fabricCanvas) return;
            const text = new fabric.IText('Your Text Here', {
                left: 20, top: 20, fill: '#000000', fontFamily: 'Poppins', fontSize: 40,
                data: { isGuide: false }
            });
            fabricCanvas.add(text);
            fabricCanvas.setActiveObject(text);
        });
        document.getElementById('btn-add-rect').addEventListener('click', () => {
            if (!fabricCanvas) return;
            const rect = new fabric.Rect({
                left: 20, top: 20, fill: '#4B5563', width: 200, height: 100,
                data: { isGuide: false }
            });
            fabricCanvas.add(rect);
            fabricCanvas.setActiveObject(rect);
        });
        document.getElementById('btn-add-circle').addEventListener('click', () => {
            if (!fabricCanvas) return;
            const circle = new fabric.Circle({
                left: 20, top: 20, fill: '#4B5563', radius: 50,
                data: { isGuide: false }
            });
            fabricCanvas.add(circle);
            fabricCanvas.setActiveObject(circle);
        });
        document.getElementById('btn-add-triangle').addEventListener('click', () => {
            if (!fabricCanvas) return;
            const tri = new fabric.Triangle({
                left: 20, top: 20, fill: '#4B5563', width: 100, height: 100,
                data: { isGuide: false }
            });
            fabricCanvas.add(tri);
            fabricCanvas.setActiveObject(tri);
        });
        document.getElementById('bg-color-picker').addEventListener('input', (e) => {
            if (!fabricCanvas) return;
            fabricCanvas.setBackgroundColor(e.target.value, fabricCanvas.renderAll.bind(fabricCanvas));
        });
        imageUploader.addEventListener('change', (e) => {
            if (!e.target.files[0] || !fabricCanvas) return;
            const reader = new FileReader();
            reader.onload = (f) => {
                fabric.Image.fromURL(f.target.result, (img) => {
                    img.scaleToWidth(200);
                    img.set({ data: { isGuide: false } });
                    fabricCanvas.add(img);
                    fabricCanvas.setActiveObject(img);
                    img.bringToFront();
                });
            };
            reader.readAsDataURL(e.target.files[0]);
            e.target.value = '';
        });
        
        // Keyboard Delete
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Delete' || e.key === 'Backspace') {
                if (document.activeElement.tagName === 'INPUT' || document.activeElement.tagName === 'SELECT' || (fabricCanvas && fabricCanvas.getActiveObject() && fabricCanvas.getActiveObject().isEditing)) {
                    return;
                }
                deleteSelectedObject();
            }
        });

        // --- SAVE FUNCTION ---
        btnSaveFrame.addEventListener('click', async () => {
            if (!fabricCanvas || !currentLayoutKey) return;
            Swal.fire({ title: 'Saving...', text: 'Generating high-resolution frame...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
            const guides = fabricCanvas.getObjects().filter(obj => obj.data && obj.data.isGuide);
            guides.forEach(g => g.set('visible', false));
            const dataURL = fabricCanvas.toDataURL({ format: 'png', multiplier: fabricCanvas.printWidth / fabricCanvas.width });
            guides.forEach(g => g.set('visible', true));
            fabricCanvas.renderAll();
            const formData = new FormData();
            formData.append('action', 'save');
            formData.append('layout_key', currentLayoutKey);
            formData.append('imageData', dataURL);
            try {
                const response = await fetch('templates.php', { method: 'POST', body: formData });
                if (!response.ok) throw new Error('Server error');
                const result = await response.json();
                if (result.status === 'success') {
                    await Swal.fire('Saved!', 'Your new frame has been saved.', 'success');
                    window.location.reload(); 
                } else {
                    throw new Error(result.message || 'Unknown error');
                }
            } catch (error) {
                Swal.fire('Error', 'Could not save the frame: ' + error.message, 'error');
            }
        });

        // --- SweetAlert para sa PHP messages ---
        <?php
        if (isset($_SESSION['alert'])) {
            $alert_type = $_SESSION['alert']['type']; 
            $alert_message = $_SESSION['alert']['message'];
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

        // --- SweetAlert para sa Delete ---
        function confirmDelete(form) {
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: "This frame will be permanently deleted!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DC2626',
                cancelButtonColor: '#374151',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
            return false;
        }
    </script>
</body>
</html>