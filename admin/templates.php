<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creative Studio - Marahuyo Admin</title>
    <!-- External libraries and fonts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amatic+SC&family=Anton&family=Arimo&family=Bebas+Neue&family=Bitter&family=Cabin&family=Caveat&family=Comfortaa&family=Dancing+Script&family=Fjalla+One&family=Great+Vibes&family=Indie+Flower&family=Josefin+Sans&family=Lato&family=Lobster&family=Merriweather&family=Montserrat&family=Nunito&family=Open+Sans&family=Oswald&family=PT+Sans&family=Pacifico&family=Playfair+Display:wght@700&family=Poppins:wght@400;500;600;700&family=Raleway&family=Roboto&family=Satisfy&family=Shadows+Into+Light&family=Source+Sans+Pro&family=Ubuntu&display=swap" rel="stylesheet">
    
    <!-- Custom CSS for the application -->
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #0d1117; }
        .control-panel { background-color: #1f2937; }
        .property-group, .mode-specific { display: none; }
        .property-group.active, .mode-specific.active { display: block; }
        .layer-item { display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background-color: #374151; border-radius: 8px; cursor: pointer; transition: background-color 0.2s; }
        .layer-item:hover { background-color: #4b5563; }
        .layer-item.active { background-color: #4338ca; }
        .layer-item .layer-up, .layer-item .layer-down { background: none; border: none; color: white; cursor: pointer; }
        .toggle-btn { background-color: #374151; }
        .toggle-btn.active { background-color: #4338ca; }
        .chooser-btn { background-color: #374151; border: 2px solid #4b5563; transition: all 0.2s ease-out; }
        .chooser-btn:hover { border-color: #f59e0b; transform: translateY(-5px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05); }
        
        #canvas-container {
            background-color: #1f2937;
            background-image: 
                linear-gradient(45deg, rgba(255,255,255,0.05) 25%, transparent 25%), 
                linear-gradient(-45deg, rgba(255,255,255,0.05) 25%, transparent 25%),
                linear-gradient(45deg, transparent 75%, rgba(255,255,255,0.05) 75%),
                linear-gradient(-45deg, transparent 75%, rgba(255,255,255,0.05) 75%);
            background-size: 20px 20px;
        }

        .asset-panel {
            position: absolute;
            top: 0;
            left: 5rem; /* Width of the toolbar */
            width: 288px;
            height: 100%;
            background-color: #1f2937;
            z-index: 10;
            transform: translateX(-105%);
            opacity: 0;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.2s ease;
            border-right: 1px solid #4b5563;
            pointer-events: none;
        }
        .asset-panel.open {
            transform: translateX(0);
            opacity: 1;
            pointer-events: auto;
        }

        .tool-btn { display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%; height: 72px; border-radius: 8px; background-color: transparent; border: 2px solid transparent; transition: all 0.2s; }
        .tool-btn.active, .tool-btn:hover { background-color: #374151; }
        .nav-link { display: flex; align-items: center; gap: 12px; padding: 10px; border-radius: 8px; transition: background-color 0.2s; }
        .nav-link:hover, .nav-link.active { background-color: #374151; color: #f59e0b; }
        .logout-link { margin-top: auto; }
        #notification-container { position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; }
        .notification { padding: 12px 20px; border-radius: 8px; color: white; font-weight: 500; box-shadow: 0 4px 15px rgba(0,0,0,0.2); animation: fadeIn 0.3s ease-out; }
        .notification.success { background-color: #16a34a; }
        .notification.error { background-color: #dc2626; }

        .control-panel input[type="number"],
        .control-panel textarea,
        .control-panel select {
            background-color: #374151;
            border: 1px solid #4b5563;
            border-radius: 0.5rem;
            padding: 0.6rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .control-panel input:focus,
        .control-panel textarea:focus,
        .control-panel select:focus {
            outline: none;
            border-color: #4338ca;
            box-shadow: 0 0 0 2px rgba(67, 56, 202, 0.5);
        }
        .prop-section-title {
            font-size: 0.75rem;
            font-weight: 600;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: 1.25rem;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #374151;
        }
        .prop-section-title:first-child { margin-top: 0; }
        @keyframes fadeIn { from{opacity:0; transform: translateY(5px);} to{opacity:1; transform: translateY(0);} }
    </style>
</head>
<body class="text-white font-sans">
    <div id="notification-container"></div>
    <div class="flex h-screen">
        <!-- Sidebar Navigation -->
        <aside class="w-64 bg-gray-900 p-6 flex-col justify-between hidden lg:flex">
              <div>
                <h1 class="text-3xl text-amber-400 mb-8 text-center" style="font-family: 'Playfair Display', serif;">Marahuyo</h1>
                <nav>
                    <ul>
                        <li class="mb-4"><a href="#" class="nav-link"><span class="material-icons">dashboard</span> Dashboard</a></li>
                        <li class="mb-4"><a href="#" class="nav-link active"><span class="material-icons">layers</span> Templates</a></li>
                    </ul>
                </nav>
            </div>
            <a href="#" class="nav-link logout-link"><span class="material-icons">logout</span> Log Out</a>
        </aside>

        <!-- Main Content Area -->
        <main id="main-content" class="flex-1 p-4 lg:p-6 flex flex-col h-full bg-black/20">
            <!-- Mode Chooser Screen -->
            <div id="chooser-screen" class="flex-grow flex flex-col items-center justify-center">
                <h1 class="text-4xl font-bold mb-4">Choose Your Editing Mode</h1>
                <p class="text-gray-400 mb-8 max-w-2xl text-center">Create a frame for a standard photo booth layout, or build a completely custom design from scratch.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 w-full max-w-4xl">
                    <button id="fixed-mode-btn" class="chooser-btn p-8 rounded-2xl text-left">
                        <span class="material-icons text-5xl text-amber-400 mb-4">view_carousel</span>
                        <h2 class="text-2xl font-bold">Start with a Layout</h2>
                        <p class="text-gray-400">Design a frame for a pre-defined layout like a 2x6 Strip or a 4x6 Grid. Fast and fully editable.</p>
                    </button>
                    <button id="custom-mode-btn" class="chooser-btn p-8 rounded-2xl text-left">
                        <span class="material-icons text-5xl text-indigo-400 mb-4">brush</span>
                        <h2 class="text-2xl font-bold">Start with a Blank Canvas</h2>
                        <p class="text-gray-400">Total creative freedom. Set your own canvas size and add photo slots anywhere you like.</p>
                    </button>
                </div>
            </div>

            <!-- Fixed Layout Chooser Screen -->
            <div id="fixed-layout-chooser-screen" class="hidden flex-grow flex flex-col items-center justify-center">
                <h1 class="text-4xl font-bold mb-8">Select a Base Layout</h1>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 w-full max-w-5xl">
                    <button data-layout="strip-3" class="fixed-layout-btn chooser-btn p-6 text-center"><h3 class="text-xl font-semibold">Photo Strip (3)</h3></button>
                    <button data-layout="strip-4" class="fixed-layout-btn chooser-btn p-6 text-center"><h3 class="text-xl font-semibold">Long Strip (4)</h3></button>
                    <button data-layout="grid-4" class="fixed-layout-btn chooser-btn p-6 text-center"><h3 class="text-xl font-semibold">Grid</h3></button>
                    <button data-layout="spotlight-3" class="fixed-layout-btn chooser-btn p-6 text-center"><h3 class="text-xl font-semibold">Spotlight</h3></button>
                </div>
                <button id="back-to-chooser-from-fixed" class="mt-8 bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded-full text-sm inline-flex items-center gap-1"><span class="material-icons text-base">arrow_back</span> Back to Modes</button>
            </div>

            <!-- Editor Screen -->
            <div id="editor-screen" class="hidden h-full flex flex-col">
                <div class="grid grid-cols-12 gap-6 flex-grow min-h-0">
                    <!-- Left Toolbar & Asset Panels -->
                    <div class="col-span-12 lg:col-span-2 relative">
                        <div class="w-20 control-panel rounded-2xl p-2 space-y-2 h-full flex flex-col">
                            <button data-panel="panel-elements" class="tool-btn"><span class="material-icons">category</span><span class="text-xs mt-1">Elements</span></button>
                            <button data-panel="panel-text" class="tool-btn"><span class="material-icons">text_fields</span><span class="text-xs mt-1">Text</span></button>
                            <button data-panel="panel-background" class="tool-btn"><span class="material-icons">wallpaper</span><span class="text-xs mt-1">BG</span></button>
                            <button data-panel="panel-uploads" class="tool-btn"><span class="material-icons">cloud_upload</span><span class="text-xs mt-1">Uploads</span></button>
                        </div>
                        
                        <!-- Backgrounds Panel -->
                        <div id="panel-background" class="asset-panel rounded-r-2xl p-4 overflow-y-auto"><h3 class="font-bold text-lg mb-4">Background</h3><div class="space-y-4"><label class="block text-sm font-medium text-gray-400">Background Color</label><input type="color" id="bg-color-input" value="#FFFFFF" class="w-full h-10 p-1 bg-gray-700 rounded-lg"><button id="clear-bg-btn" class="w-full text-sm bg-gray-600 py-2 rounded-lg">Clear Background</button></div></div>
                        
                        <!-- Elements Panel -->
                        <div id="panel-elements" class="asset-panel rounded-r-2xl p-4 overflow-y-auto"><h3 class="font-bold text-lg mb-4">Elements</h3><div class="space-y-4"><button id="add-placeholder-btn" class="w-full bg-gray-700 p-3 rounded-lg text-left inline-flex items-center gap-2 font-medium"><span class="material-icons">photo_size_select_large</span>Add Photo Slot</button><button id="add-rect-btn" class="w-full bg-gray-700 p-3 rounded-lg text-left inline-flex items-center gap-2 font-medium"><span class="material-icons">check_box_outline_blank</span>Add Rectangle</button><button id="add-circle-btn" class="w-full bg-gray-700 p-3 rounded-lg text-left inline-flex items-center gap-2 font-medium"><span class="material-icons">radio_button_unchecked</span>Add Circle</button><hr class="border-gray-600"><h4 class="font-semibold">Stickers</h4><div class="grid grid-cols-3 gap-2">
                            <div class="bg-gray-800 p-1 rounded"><img src="https://placehold.co/100x100/374151/FFFFFF?text=S1" class="asset-thumb w-full h-16 object-contain" onclick="addSticker(this.src)"></div>
                            <div class="bg-gray-800 p-1 rounded"><img src="https://placehold.co/100x100/374151/FFFFFF?text=S2" class="asset-thumb w-full h-16 object-contain" onclick="addSticker(this.src)"></div>
                            <div class="bg-gray-800 p-1 rounded"><img src="https://placehold.co/100x100/374151/FFFFFF?text=S3" class="asset-thumb w-full h-16 object-contain" onclick="addSticker(this.src)"></div>
                        </div></div></div>
                        
                        <!-- Text Panel -->
                        <div id="panel-text" class="asset-panel rounded-r-2xl p-4 overflow-y-auto"><h3 class="font-bold text-lg mb-4">Text</h3><div class="space-y-4"><button id="add-text-heading" class="w-full bg-gray-700 p-2 rounded-lg text-left font-bold text-2xl">Add Heading</button><button id="add-text-subheading" class="w-full bg-gray-700 p-2 rounded-lg text-left font-semibold text-lg">Add Subheading</button><button id="add-text-body" class="w-full bg-gray-700 p-2 rounded-lg text-left">Add body text</button></div></div>
                        
                        <!-- Uploads Panel -->
                        <div id="panel-uploads" class="asset-panel rounded-r-2xl p-4 overflow-y-auto"><h3 class="font-bold text-lg mb-4">Your Uploads</h3><button onclick="document.getElementById('image-upload-input').click()" class="w-full bg-indigo-600 hover:bg-indigo-700 p-4 rounded-lg font-semibold inline-flex items-center justify-center gap-2"><span class="material-icons">add</span>Upload Image</button><input type="file" id="image-upload-input" accept="image/*" class="hidden"></div>
                    </div>
                    
                    <!-- Center Canvas Area -->
                    <div class="col-span-12 lg:col-span-7 flex flex-col">
                        <div class="flex-shrink-0 bg-gray-900 rounded-2xl p-2 flex items-center justify-between">
                            <button id="back-to-chooser-btn" class="bg-gray-700 hover:bg-gray-600 px-3 py-1 rounded-full text-sm inline-flex items-center gap-1"><span class="material-icons text-base">arrow_back</span> Modes</button>
                            <div class="mode-specific custom"><div class="flex items-center gap-2"><label class="text-sm">W: <input id="canvas-width" type="number" value="1800" class="w-20 bg-gray-700 rounded p-1 text-center"></label><label class="text-sm">H: <input id="canvas-height" type="number" value="1200" class="w-20 bg-gray-700 rounded p-1 text-center"></label><button id="update-canvas-size" class="bg-blue-600 px-3 py-1 rounded text-sm">Apply</button></div></div>
                            <input type="text" id="filename-input" placeholder="Enter filename..." class="bg-gray-700 border-gray-600 rounded-lg w-1/3 p-2 text-center text-base">
                            <button id="save-btn" class="bg-amber-500 hover:bg-amber-600 text-gray-900 font-bold py-2 px-6 rounded-lg inline-flex items-center gap-2"><span class="material-icons">save</span><span id="save-btn-text">Save</span></button>
                        </div>
                        <div id="canvas-container" class="w-full flex-grow flex items-center justify-center p-4 mt-4 rounded-2xl"><canvas id="template-canvas"></canvas></div>
                    </div>
                    
                    <!-- Right Properties Panel -->
                    <div class="col-span-12 lg:col-span-3 control-panel rounded-2xl p-4 flex flex-col">
                        <div id="properties-panel" class="overflow-y-auto">
                            <h3 class="text-lg font-bold text-center border-b border-gray-700 pb-2 mb-3">Properties</h3>
                            <div id="general-props" class="property-group active"><p class="text-gray-400 text-sm text-center">Select an object to edit its properties.</p></div>
                            <div id="text-props" class="property-group">
                                <h4 class="prop-section-title">Content</h4>
                                <textarea id="text-edit-input" class="w-full h-24 text-sm"></textarea>
                                
                                <h4 class="prop-section-title">Typography</h4>
                                <select id="font-select" class="w-full text-sm">
                                    <option>Poppins</option>
                                    <option>Montserrat</option>
                                    <option>Roboto</option>
                                    <option>Open Sans</option>
                                    <option>Lato</option>
                                    <option>Oswald</option>
                                    <option>Raleway</option>
                                    <option>Merriweather</option>
                                    <option>Playfair Display</option>
                                    <option>Ubuntu</option>
                                    <option>Nunito</option>
                                    <option>PT Sans</option>
                                    <option>Source Sans Pro</option>
                                    <option>Anton</option>
                                    <option>Bebas Neue</option>
                                    <option>Lobster</option>
                                    <option>Pacifico</option>
                                    <option>Dancing Script</option>
                                    <option>Caveat</option>
                                    <option>Indie Flower</option>
                                    <option>Amatic SC</option>
                                    <option>Comfortaa</option>
                                    <option>Josefin Sans</option>
                                    <option>Arimo</option>
                                    <option>Bitter</option>
                                    <option>Cabin</option>
                                    <option>Fjalla One</option>
                                    <option>Great Vibes</option>
                                    <option>Satisfy</option>
                                    <option>Shadows Into Light</option>
                                </select>
                                <div class="grid grid-cols-2 gap-4 mt-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-400 mb-1">Color</label>
                                        <input type="color" id="text-color" class="w-full h-10 p-1">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-400 mb-1">Size</label>
                                        <input type="number" id="font-size" class="w-full text-center">
                                    </div>
                                </div>
                    
                                <h4 class="prop-section-title">Style & Alignment</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="grid grid-cols-3 gap-2">
                                        <button id="font-bold" class="p-2 rounded toggle-btn font-bold">B</button>
                                        <button id="font-italic" class="p-2 rounded toggle-btn italic">I</button>
                                        <button id="font-underline" class="p-2 rounded toggle-btn underline">U</button>
                                    </div>
                                    <select id="font-align" class="rounded w-full text-sm"><option value="left">Left</option><option value="center">Center</option><option value="right">Right</option></select>
                                </div>
                            </div>
                            <div id="shape-props" class="property-group">
                                <h4 class="prop-section-title">Appearance</h4>
                                <div class="flex justify-between items-center mb-3">
                                   <label for="shape-fill-color" class="text-sm text-gray-400">Fill Color</label>
                                   <input type="color" id="shape-fill-color" class="h-10 p-1 bg-transparent rounded-lg w-16">
                                </div>
                                <div>
                                    <label class="text-sm text-gray-400">Opacity</label>
                                    <input type="range" id="shape-opacity" min="0" max="1" step="0.05" class="w-full mt-1">
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow flex flex-col min-h-0 mt-4">
                            <h3 class="text-lg font-bold text-center border-b border-gray-700 pb-2 mb-3 flex-shrink-0">Layers</h3>
                            <div id="layers-panel" class="space-y-2 overflow-y-auto flex-grow"></div>
                             <button id="delete-btn-layers" class="w-full bg-red-800/50 hover:bg-red-700/50 font-bold py-2 px-4 rounded-lg inline-flex items-center justify-center gap-2 mt-2 flex-shrink-0"><span class="material-icons text-sm">delete</span>Delete Selected</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

<script>
    let canvas;
    let editorMode = null;
    let selectedLayout = null;
    const canvasContainer = document.getElementById('canvas-container');
    const canvasEl = document.getElementById('template-canvas');
    
    // Configuration for fixed photo booth layouts
    const layoutConfigs = {
        'strip-3': { name: 'Photo Strip (3)', w: 2, h: 6, guides: [{ x: 0.075, y: 0.025, w: 0.85, h: 0.25 }, { x: 0.075, y: 0.3125, w: 0.85, h: 0.25 }, { x: 0.075, y: 0.6, w: 0.85, h: 0.25 }] },
        'strip-4': { name: 'Long Strip (4)', w: 2, h: 8, guides: [{ x: 0.06, y: 0.02, w: 0.88, h: 0.2 }, { x: 0.06, y: 0.247, w: 0.88, h: 0.2 }, { x: 0.06, y: 0.474, w: 0.88, h: 0.2 }, { x: 0.06, y: 0.701, w: 0.88, h: 0.2 }] },
        'grid-4': { name: 'Grid', w: 4, h: 6, guides: [{ x: 0.025, y: 0.05, w: 0.45, h: 0.3 }, { x: 0.525, y: 0.05, w: 0.45, h: 0.3 }, { x: 0.025, y: 0.4, w: 0.45, h: 0.3 }, { x: 0.525, y: 0.4, w: 0.45, h: 0.3 }] },
        'spotlight-3': { name: 'Spotlight', w: 6, h: 4, guides: [{ x: 0.033, y: 0.05, w: 0.6, h: 0.81 }, { x: 0.666, y: 0.05, w: 0.3, h: 0.387 }, { x: 0.666, y: 0.473, w: 0.3, h: 0.387 }] }
    };

    // --- UI Navigation and Initialization ---
    function showScreen(screenId) {
        ['chooser-screen', 'fixed-layout-chooser-screen', 'editor-screen'].forEach(id => {
            const el = document.getElementById(id);
            if (id === screenId) {
                el.style.display = 'flex';
                el.querySelectorAll('.chooser-btn').forEach((btn, index) => {
                    btn.style.animation = `fadeIn 0.5s ease-out ${index * 0.1}s both`;
                });
            } else {
                el.style.display = 'none';
            }
        });
    }

    function initializeCanvas(config, width, height) {
        const DPI = 300;
        const canvasW = config ? config.w * DPI : width;
        const canvasH = config ? config.h * DPI : height;
        const containerW = canvasContainer.clientWidth - 32;
        const containerH = canvasContainer.clientHeight - 32;
        const scale = Math.min(containerW / canvasW, containerH / canvasH);
        canvasEl.width = canvasW;
        canvasEl.height = canvasH;
        if (canvas) canvas.dispose();
        canvas = new fabric.Canvas('template-canvas', { backgroundColor: '#FFFFFF' });
        canvas.originalWidth = canvasW;
        canvas.originalHeight = canvasH;
        canvas.setDimensions({ width: canvasW * scale, height: canvasH * scale });
        canvas.setZoom(scale);
        if (config && config.guides) {
            const rects = config.guides.map(g => new fabric.Rect({ left: canvasW * g.x, top: canvasH * g.y, width: canvasW * g.w, height: canvasH * g.h, fill: '#cccccc', stroke: '#aaaaaa', strokeWidth: 2, isPlaceholder: true, selectable: true, evented: true, rx: 20, ry: 20 }));
            rects.forEach(rect => canvas.add(rect));
        }
        updatePropertiesPanel();
        updateLayersPanel();
        updateEditorHeader();
        setupCanvasListeners();
    }
    
    function updateEditorHeader(){
        const customControls = document.querySelector('.mode-specific.custom');
        if(editorMode === 'custom'){
            customControls.style.display = 'block';
            document.getElementById('add-placeholder-btn').style.display = 'block';
        } else {
            customControls.style.display = 'none';
            document.getElementById('add-placeholder-btn').style.display = 'none';
        }
    }

    // --- Mode Switching Logic ---
    document.getElementById('fixed-mode-btn').addEventListener('click', () => showScreen('fixed-layout-chooser-screen'));
    document.getElementById('custom-mode-btn').addEventListener('click', () => {
        editorMode = 'custom';
        selectedLayout = 'custom';
        showScreen('editor-screen');
        setTimeout(() => initializeCanvas(null, 1800, 1200), 50);
    });
    document.getElementById('back-to-chooser-from-fixed').addEventListener('click', () => showScreen('chooser-screen'));
    document.querySelectorAll('.fixed-layout-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            editorMode = 'fixed';
            selectedLayout = btn.dataset.layout;
            showScreen('editor-screen');
            setTimeout(() => initializeCanvas(layoutConfigs[selectedLayout]), 50);
        });
    });
    document.getElementById('back-to-chooser-btn').addEventListener('click', () => {
        showScreen('chooser-screen');
        if (canvas) { canvas.dispose(); canvas = null; }
    });
    document.getElementById('update-canvas-size').addEventListener('click', () => {
        const w = parseInt(document.getElementById('canvas-width').value, 10);
        const h = parseInt(document.getElementById('canvas-height').value, 10);
        initializeCanvas(null, w, h);
    });

    // --- Core Editor UI & Canvas Logic ---
    const propGroups = { general: document.getElementById('general-props'), text: document.getElementById('text-props'), shape: document.getElementById('shape-props') };
    function updatePropertiesPanel() {
        Object.values(propGroups).forEach(g => g.classList.remove('active'));
        const activeObject = canvas.getActiveObject();
        if (!activeObject) {
            propGroups.general.classList.add('active');
            return;
        }
        const type = activeObject.type;
        if (type.includes('text')) {
            propGroups.text.classList.add('active');
            const textInput = document.getElementById('text-edit-input');
            if (document.activeElement !== textInput) { textInput.value = activeObject.text; }
            document.getElementById('font-select').value = activeObject.fontFamily;
            document.getElementById('text-color').value = activeObject.fill;
            document.getElementById('font-size').value = activeObject.fontSize;
            document.getElementById('font-align').value = activeObject.textAlign;
            document.getElementById('font-bold').classList.toggle('active', activeObject.fontWeight === 'bold');
            document.getElementById('font-italic').classList.toggle('active', activeObject.fontStyle === 'italic');
            document.getElementById('font-underline').classList.toggle('active', activeObject.underline);
        } else if (activeObject.type === 'rect' || activeObject.type === 'circle' || activeObject.type === 'image') {
            propGroups.shape.classList.add('active');
            document.getElementById('shape-fill-color').value = activeObject.fill || '#ffffff';
            document.getElementById('shape-opacity').value = activeObject.opacity;
        } else {
             propGroups.general.classList.add('active');
        }
    }

    function updateLayersPanel() {
        const layersPanel = document.getElementById('layers-panel');
        layersPanel.innerHTML = '';
        const activeObj = canvas.getActiveObject();
        canvas.getObjects().slice().reverse().forEach(obj => {
            const item = document.createElement('div');
            item.className = 'layer-item';
            if(obj === activeObj) item.classList.add('active');
            const isPlaceholder = !!obj.isPlaceholder;
            const typeName = isPlaceholder ? 'Photo Slot' : (obj.type.charAt(0).toUpperCase() + obj.type.slice(1));
            let displayText = typeName;
            if (obj.type.includes('text')) { displayText = obj.text.length > 15 ? obj.text.substring(0, 15) + '...' : obj.text; }
            const moveButtons = isPlaceholder ? '' : `<div class="flex gap-1"><button class="layer-up"><span class="material-icons text-sm">arrow_upward</span></button><button class="layer-down"><span class="material-icons text-sm">arrow_downward</span></button></div>`;
            item.innerHTML = `<span class="text-sm truncate" title="${obj.text || typeName}">${displayText}</span>${moveButtons}`;
            layersPanel.appendChild(item);
            item.addEventListener('click', () => { canvas.setActiveObject(obj).renderAll(); updateLayersPanel(); });
            if(!isPlaceholder){
                item.querySelector('.layer-up')?.addEventListener('click', e => { e.stopPropagation(); canvas.bringForward(obj); canvas.renderAll(); updateLayersPanel(); });
                item.querySelector('.layer-down')?.addEventListener('click', e => { e.stopPropagation(); canvas.sendBackwards(obj); canvas.renderAll(); updateLayersPanel(); });
            }
        });
    }

    function setupCanvasListeners() {
        if (!canvas) return;
        canvas.on({
            'selection:created': (e) => { e.target.set({ borderColor: '#f59e0b', cornerColor: '#f59e0b', cornerSize: 10 }); updatePropertiesPanel(); updateLayersPanel(); },
            'selection:updated': () => { updatePropertiesPanel(); updateLayersPanel(); },
            'selection:cleared': () => { updatePropertiesPanel(); updateLayersPanel(); },
            'object:added': updateLayersPanel,
            'object:removed': updateLayersPanel,
            'object:modified': updatePropertiesPanel
        });
    }

    // --- Asset Panel & Toolbar Logic ---
    const toolBtns = document.querySelectorAll('.tool-btn');
    const assetPanels = document.querySelectorAll('.asset-panel');
    toolBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const targetPanelId = btn.dataset.panel;
            const targetPanel = document.getElementById(targetPanelId);
            const isActive = btn.classList.contains('active');
            assetPanels.forEach(p => p.classList.remove('open'));
            toolBtns.forEach(b => b.classList.remove('active'));
            if (targetPanel && !isActive) { btn.classList.add('active'); targetPanel.classList.add('open'); }
        });
    });
    document.body.addEventListener('click', (e) => {
        if (!e.target.closest('.asset-panel') && !e.target.closest('.tool-btn')) {
            assetPanels.forEach(p => p.classList.remove('open'));
            toolBtns.forEach(b => b.classList.remove('active'));
        }
    });

    // --- Object Addition & Modification ---
    document.getElementById('bg-color-input').addEventListener('input', e => canvas.setBackgroundColor(e.target.value, canvas.renderAll.bind(canvas)));
    document.getElementById('clear-bg-btn').addEventListener('click', () => { canvas.setBackgroundColor('#FFFFFF', canvas.renderAll.bind(canvas)); canvas.backgroundImage = null; });
    function setBackgroundImage(src) { fabric.Image.fromURL(src, img => canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas), { scaleX: canvas.originalWidth / img.width, scaleY: canvas.originalHeight / img.height }), { crossOrigin: 'anonymous' }); }
    function addSticker(src) { fabric.Image.fromURL(src, img => { img.scaleToWidth(250); canvas.add(img).centerObject(img).setActiveObject(img); }, { crossOrigin: 'anonymous' }); }
    document.getElementById('add-placeholder-btn').addEventListener('click', () => { const newRect = new fabric.Rect({ width: 400, height: 300, fill: '#cccccc', stroke: '#aaaaaa', strokeWidth: 2, isPlaceholder: true }); canvas.add(newRect).centerObject(newRect).renderAll(); });
    document.getElementById('add-text-heading').addEventListener('click', () => { const newText = new fabric.Textbox('Heading', { width: 500, fontSize: 120, fontFamily: 'Poppins', fontWeight: 'bold', fill: '#FFFFFF', textAlign: 'center' }); canvas.add(newText).centerObject(newText).setActiveObject(newText); });
    document.getElementById('add-text-subheading').addEventListener('click', () => { const newText = new fabric.Textbox('Subheading', { width: 400, fontSize: 80, fontFamily: 'Poppins', fontWeight: '500', fill: '#dddddd', textAlign: 'center' }); canvas.add(newText).centerObject(newText).setActiveObject(newText); });
    document.getElementById('add-text-body').addEventListener('click', () => { const newText = new fabric.Textbox('Some body text here...', { width: 300, fontSize: 40, fontFamily: 'Poppins', fill: '#cccccc', textAlign: 'center' }); canvas.add(newText).centerObject(newText).setActiveObject(newText); });
    document.getElementById('add-rect-btn').addEventListener('click', () => { const newRect = new fabric.Rect({ width: 200, height: 200, fill: '#555555' }); canvas.add(newRect).centerObject(newRect).setActiveObject(newRect); });
    document.getElementById('add-circle-btn').addEventListener('click', () => { const newCircle = new fabric.Circle({ radius: 100, fill: '#555555' }); canvas.add(newCircle).centerObject(newCircle).setActiveObject(newCircle); });
    const deleteSelected = () => { canvas.getActiveObjects().forEach(obj => canvas.remove(obj)); canvas.discardActiveObject().renderAll(); };
    document.getElementById('delete-btn-layers').addEventListener('click', deleteSelected);
    window.addEventListener('keydown', e => { if (e.key === 'Delete' || e.key === 'Backspace') { deleteSelected(); } });
    const updateActiveObject = (prop, value) => { const obj = canvas.getActiveObject(); if (obj) { obj.set(prop, value); canvas.renderAll(); } };
    document.getElementById('text-edit-input').addEventListener('input', e => updateActiveObject('text', e.target.value));
    document.getElementById('font-select').addEventListener('change', e => updateActiveObject('fontFamily', e.target.value));
    document.getElementById('text-color').addEventListener('input', e => updateActiveObject('fill', e.target.value));
    document.getElementById('font-size').addEventListener('input', e => updateActiveObject('fontSize', parseInt(e.target.value, 10) || 12));
    document.getElementById('shape-fill-color').addEventListener('input', e => updateActiveObject('fill', e.target.value));
    document.getElementById('shape-opacity').addEventListener('input', e => updateActiveObject('opacity', parseFloat(e.target.value)));
    document.getElementById('font-bold').addEventListener('click', () => { const o=canvas.getActiveObject(); if(o) o.set('fontWeight', o.fontWeight === 'bold' ? 'normal' : 'bold'); canvas.renderAll(); updatePropertiesPanel(); });
    document.getElementById('font-italic').addEventListener('click', () => { const o=canvas.getActiveObject(); if(o) o.set('fontStyle', o.fontStyle === 'italic' ? 'normal' : 'italic'); canvas.renderAll(); updatePropertiesPanel(); });
    document.getElementById('font-underline').addEventListener('click', () => { const o=canvas.getActiveObject(); if(o) o.set('underline', !o.underline); canvas.renderAll(); updatePropertiesPanel(); });
    document.getElementById('font-align').addEventListener('change', e => updateActiveObject('textAlign', e.target.value));
    
    // --- Simulation for Server-Side Actions ---
    function showNotification(message, type = 'success') {
        const container = document.getElementById('notification-container');
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        container.appendChild(notification);
        setTimeout(() => { notification.remove(); }, 3000);
    }
    
    document.getElementById('image-upload-input').addEventListener('change', e => {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(event) {
            const imageUrl = event.target.result;
            fabric.Image.fromURL(imageUrl, img => {
                img.scaleToWidth(300);
                canvas.add(img).centerObject(img).setActiveObject(img);
                showNotification('Image added to canvas!');
            }, { crossOrigin: 'anonymous' });
        };
        reader.readAsDataURL(file);
        e.target.value = '';
    });

    document.getElementById('save-btn').addEventListener('click', () => {
        const filename = document.getElementById('filename-input').value;
        if (!filename.trim()) {
            showNotification('Please enter a filename.', 'error');
            return;
        }
        const layoutType = selectedLayout;
        const saveBtnText = document.getElementById('save-btn-text');
        saveBtnText.textContent = 'Saving...';
        const placeholders = canvas.getObjects().filter(o => o.isPlaceholder);
        placeholders.forEach(p => p.set({ visible: false }));
        canvas.renderAll();
        const imageData = canvas.toDataURL({ format: 'png', multiplier: 1 / canvas.getZoom() });
        placeholders.forEach(p => p.set({ visible: true }));
        canvas.renderAll();
        console.log("--- SIMULATING SAVE ---");
        console.log("Filename:", filename);
        console.log("Layout Type:", layoutConfigs[layoutType]?.name || 'Custom');
        console.log("Image Data (first 100 chars):", imageData.substring(0, 100) + "...");
        setTimeout(() => {
            showNotification('Template saved successfully! (Simulated)');
            saveBtnText.textContent = 'Save';
        }, 1000);
    });
    
    showScreen('chooser-screen');

</script>
</body>
</html>


