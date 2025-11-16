<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>point five by marahuyo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator/qrcode.js"></script>
    <style>
        body { 
            font-family: 'Poppins', sans-serif; 
            background: linear-gradient(135deg, #0a0a0a, #2a0101, #5c0000, #2a0101);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
        }
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .card-bg {
            background-color: rgba(17, 24, 39, 0.5);
            backdrop-filter: blur(8px);
            background-image: radial-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 0);
            background-size: 30px 30px;
        }
        .font-brand { font-family: 'Playfair Display', serif; }
        .fade-in { animation: fadeIn 0.5s ease-in-out forwards; }
        .pop-in { animation: popIn 0.3s ease-out forwards; }
        .zoom-in { animation: zoomIn 0.4s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes popIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
        @keyframes zoomIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        .flash { animation: flash 0.3s ease-out; }
        @keyframes flash { 0%, 100% { opacity: 0; } 50% { opacity: 1; } }
        
        .btn {
            transition: all 0.2s ease-in-out;
            border-radius: 9999px;
        }
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        .btn:active {
            transform: translateY(-1px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        .btn-primary {
            background: linear-gradient(45deg, #ef4444, #dc2626);
            color: white;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }
        .btn-primary:hover {
            box-shadow: 0 6px 25px rgba(239, 68, 68, 0.5);
        }
        .btn-secondary {
            background-color: transparent;
            color: #d1d5db;
            border: 2px solid #4b5563;
        }
        .btn-secondary:hover {
             background-color: #4b5563;
             color: white;
        }
        .btn-choice {
             transition: all 0.2s ease-in-out;
             border: 2px solid #4b5563;
             position: relative;
        }
        .btn-choice.selected {
             border-color: #ef4444;
             transform: scale(1.05);
             box-shadow: 0 0 20px rgba(239, 68, 68, 0.4);
        }
        .btn-choice:disabled {
            opacity: 0.3;
            filter: grayscale(80%);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        .btn-choice:disabled .group-hover\:border-red-400 { border-color: #4b5563; }
        .btn-choice:disabled .group-hover\:bg-red-500 { background-color: #4b5563; }

        .layout-counter {
            position: absolute;
            top: -10px;
            right: -10px;
            background-color: #ef4444;
            color: white;
            font-weight: bold;
            font-size: 1rem;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            transform: scale(0);
            transition: transform 0.3s ease-out;
        }
        .btn-choice:not(:disabled) .layout-counter {
            transform: scale(1);
        }

        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .tab-btn {
             transition: all 0.2s ease-in-out;
        }
        .tab-btn.active {
            background-color: #DC2626;
            color: #ffffff;
            transform: scale(1.05);
        }
    </style>
</head>
<body class="text-white flex items-center justify-center min-h-screen font-sans overflow-hidden">

    <div id="app-container" class="w-full h-full ">

        <div id="start-screen" class="flex flex-col items-center justify-center p-4 min-h-screen">
            <div class="card-bg rounded-2xl shadow-2xl p-6 md:p-10 fade-in w-full max-w-6xl">
                <h1 class="font-brand text-5xl sm:text-6xl md:text-8xl text-red-500">point five</h1>
                <p class="text-gray-300 mb-8 text-md sm:text-lg">by <span class="font-brand">marahuyo</span></p>
                <h2 id="start-title" class="text-2xl font-semibold mb-6 text-gray-200">Loading active session...</h2>
                <div id="layout-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <button data-layout="strip-3" class="layout-btn group bg-gray-800 p-4 rounded-lg btn-choice" disabled><div class="layout-counter">0</div><div class="flex flex-col items-center h-full justify-between"><div class="w-16 h-48 border-2 border-gray-600 group-hover:border-red-400 rounded-md flex flex-col justify-around p-1"><div class="h-1/3 bg-gray-600 group-hover:bg-red-500 rounded-sm"></div><div class="h-1/3 bg-gray-600 group-hover:bg-red-500 rounded-sm"></div><div class="h-1/3 bg-gray-600 group-hover:bg-red-500 rounded-sm"></div></div><div class="mt-4"><h3 class="text-lg sm:text-xl font-semibold">Photo Strip</h3><p class="text-xs sm:text-sm text-gray-400">3 Shots (2x6")</p></div></div></button>
                    <button data-layout="strip-4" class="layout-btn group bg-gray-800 p-4 rounded-lg btn-choice" disabled><div class="layout-counter">0</div><div class="flex flex-col items-center h-full justify-between"><div class="w-16 h-48 border-2 border-gray-600 group-hover:border-red-400 rounded-md flex flex-col justify-around p-1"><div class="h-1/4 bg-gray-600 group-hover:bg-red-500 rounded-sm"></div><div class="h-1/4 bg-gray-600 group-hover:bg-red-500 rounded-sm"></div><div class="h-1/4 bg-gray-600 group-hover:bg-red-500 rounded-sm"></div><div class="h-1/4 bg-gray-600 group-hover:bg-red-500 rounded-sm"></div></div><div class="mt-4"><h3 class="text-lg sm:text-xl font-semibold">Long Strip</h3><p class="text-xs sm:text-sm text-gray-400">4 Shots (2x8")</p></div></div></button>
                    <button data-layout="grid-4" class="layout-btn group bg-gray-800 p-4 rounded-lg btn-choice" disabled><div class="layout-counter">0</div><div class="flex flex-col items-center h-full justify-between"><div class="w-32 h-48 border-2 border-gray-600 group-hover:border-red-400 rounded-md grid grid-cols-2 grid-rows-2 gap-1 p-1"><div class="bg-gray-600 group-hover:bg-red-500 rounded-sm"></div><div class="bg-gray-600 group-hover:bg-red-500 rounded-sm"></div><div class="bg-gray-600 group-hover:bg-red-500 rounded-sm"></div><div class="bg-gray-600 group-hover:bg-red-500 rounded-sm"></div></div><div class="mt-4"><h3 class="text-lg sm:text-xl font-semibold">Grid</h3><p class="text-xs sm:text-sm text-gray-400">4R (4x6")</p></div></div></button>
                    <button data-layout="spotlight-3" class="layout-btn group bg-gray-800 p-4 rounded-lg btn-choice" disabled><div class="layout-counter">0</div><div class="flex flex-col items-center h-full justify-between"><div class="w-48 h-32 border-2 border-gray-600 group-hover:border-red-400 rounded-md flex gap-1 p-1"><div class="w-2/3 h-full bg-gray-600 group-hover:bg-red-500 rounded-sm"></div><div class="w-1/3 h-full flex flex-col gap-1"><div class="h-1/2 bg-gray-600 group-hover:bg-red-500 rounded-sm"></div><div class="h-1/2 bg-gray-600 group-hover:bg-red-500 rounded-sm"></div></div></div><div class="mt-4"><h3 class="text-lg sm:text-xl font-semibold">Spotlight</h3><p class="text-xs sm:text-sm text-gray-400">3 Shots (6x4")</p></div></div></button>
                    <button data-layout="grid-6-portrait" class="layout-btn group bg-gray-800 p-4 rounded-lg btn-choice" disabled><div class="layout-counter">0</div><div class="flex flex-col items-center h-full justify-between"><div class="w-32 h-48 border-2 border-gray-600 group-hover:border-red-400 rounded-md grid grid-cols-2 grid-rows-3 gap-1 p-1"><div class="bg-gray-600 group-hover:bg-red-500 rounded-sm"></div><div class="bg-gray-600 group-hover:bg-red-500 rounded-sm"></div><div class="bg-gray-600 group-hover:bg-red-500 rounded-sm"></div><div class="bg-gray-600 group-hover:bg-red-500 rounded-sm"></div><div class="bg-gray-600 group-hover:bg-red-500 rounded-sm"></div><div class="bg-gray-600 group-hover:bg-red-500 rounded-sm"></div></div><div class="mt-4"><h3 class="text-lg sm:text-xl font-semibold">Grid Portrait</h3><p class="text-xs sm:text-sm text-gray-400">6 Shots (4x6")</p></div></div></button>
                    <button data-layout="grid-6-landscape" class="layout-btn group bg-gray-800 p-4 rounded-lg btn-choice" disabled><div class="layout-counter">0</div><div class="flex flex-col items-center h-full justify-between"><div class="w-48 h-32 border-2 border-gray-600 group-hover:border-red-400 rounded-md grid grid-cols-3 grid-rows-2 gap-1 p-1"><div class="bg-gray-600 group-hover:bg-red-500 rounded-sm"></div><div class="bg-gray-600 group-hover:bg-red-500 rounded-sm"></div><div class="bg-gray-600 group-hover:bg-red-500 rounded-sm"></div><div class="bg-gray-600 group-hover:bg-red-500 rounded-sm"></div><div class="bg-gray-600 group-hover:bg-red-500 rounded-sm"></div><div class="bg-gray-600 group-hover:bg-red-500 rounded-sm"></div></div><div class="mt-4"><h3 class="text-lg sm:text-xl font-semibold">Grid Landscape</h3><p class="text-xs sm:text-sm text-gray-400">6 Shots (6x4")</p></div></div></button>
                </div>
            </div>
        </div>

        <div id="booth-screen" class="hidden flex w-full h-screen bg-gray-900">
            <div class="flex-grow h-full flex items-center justify-center">
                <div class="relative w-full h-full">
                    <video id="camera-feed" class="w-full h-full object-cover" autoplay playsinline muted></video>
                    <div id="flash-effect" class="absolute inset-0 bg-white opacity-0 pointer-events-none"></div>
                    <div id="overlay-text" class="absolute inset-0 flex items-center justify-center text-5xl sm:text-7xl md:text-9xl font-bold text-white pointer-events-none" 
                         style="display:none; text-shadow: 0 0 20px rgba(0,0,0,0.7);">
                    </div>
                </div>
            </div>

            <div class="w-full max-w-xs md:max-w-sm card-bg p-4 flex flex-col no-scrollbar">
                <div class="flex-shrink-0 flex justify-between items-center pb-3 border-b border-gray-700">
                    <h2 class="font-brand text-2xl text-red-500">point five</h2>
                    <div id="shot-indicator" class="text-lg font-semibold text-gray-300 h-8"></div>
                </div>
                
                <div class="flex flex-col gap-4 flex-grow items-center overflow-y-auto pt-4">
                    <div id="instructions-text" class="text-amber-300 font-semibold text-lg h-auto text-center"></div>
                    <div id="booth-controls" class="text-center flex-shrink-0 my-4"></div>
                
                    <h3 class_container="text-lg font-semibold text-gray-300 flex-shrink-0">Live Preview</h3>
                    <div id="shot-previews-container" class="w-2/3 mx-auto">
                         <canvas id="live-preview-canvas" class="w-full rounded-lg border-2 border-gray-700"></canvas>
                    </div>
                </div>
            </div>
        </div>


        <div id="preview-screen" class="hidden fixed inset-0 bg-black/80 backdrop-blur-md flex-row items-center justify-center zoom-in">
            <div id="preview-main-area" class="flex-grow h-full flex flex-col items-center justify-center p-4 md:p-8">
                 <div class="flex items-center bg-gray-800 rounded-full p-1 mb-4">
                     <button id="initial-boomerang-tab" class="tab-btn py-2 px-6 font-semibold rounded-full text-sm">Boomerang</button>
                     <button id="initial-print-tab" class="tab-btn py-2 px-6 font-semibold rounded-full text-sm">Print Layout</button>
                 </div>
                 <div class="w-full h-full flex-grow relative">
                    <div id="boomerang-preview-container" class="w-full h-full flex items-center justify-center"></div>
                    <div id="print-preview-container" class="hidden w-full h-full items-center justify-center"></div>
                 </div>
            </div>
            <div id="preview-controls" class="flex-shrink-0 w-full max-w-xs md:max-w-sm card-bg h-full p-6 flex flex-col justify-center items-center gap-6">
                 <button id="retake-btn" class="btn btn-secondary py-3 px-6 text-lg w-full inline-flex items-center justify-center gap-2"><span class="material-icons">refresh</span>Retake (1 left)</button>
                 <button id="confirm-frame-btn" class="btn btn-primary py-3 px-6 text-lg w-full inline-flex items-center justify-center gap-2"><span class="material-icons">check_circle</span>Next: Choose Frame</button>
            </div>
        </div>

        <div id="frame-screen" class="hidden flex-col items-center justify-center p-4 min-h-screen">
             <div class="card-bg rounded-2xl shadow-2xl p-6 md:p-10 fade-in w-full max-w-7xl">
                 <h2 class="text-2xl font-semibold mb-6 text-gray-200">3. Finalize: Choose Your Frame</h2>
                 
                 <div class="flex flex-col md:flex-row gap-8">
                    <div class="w-full md:w-3/4">
                         <p class="text-gray-400 mb-6">Select a frame to see a preview. Click "Save" when you're done.</p>
                         <div id="frame-options-container" class="grid grid-cols-2 sm:grid-cols-4 gap-4 max-h-[50vh] overflow-y-auto no-scrollbar">
                         </div>
                         <button id="back-to-preview-btn" class="mt-8 btn btn-secondary py-2 px-6 inline-flex items-center gap-2"><span class="material-icons">arrow_back</span> Back to Preview</button>
                    </div>
                    
                    <div class="w-full md:w-1/4">
                        <h3 class="text-xl font-semibold mb-4 text-gray-200">Preview</h3>
                        <div id="frame-preview-container" class="bg-gray-900 rounded-lg p-2">
                            <canvas id="frame-preview-canvas" class="w-full h-auto rounded-md border-2 border-gray-700"></canvas>
                        </div>
                        <button id="confirm-save-btn" class="btn btn-primary w-full mt-4 py-3 text-lg hidden">
                            <span class="material-icons">check_circle</span> Save This Photo
                        </button>
                    </div>
                 </div>
             </div>
        </div>
        
        <div id="result-screen" class="hidden fixed inset-0 bg-black/80 backdrop-blur-md flex-row items-center justify-center zoom-in">
             <div id="result-main-area" class="flex-grow h-full flex flex-col items-center justify-center p-4 md:p-8">
                <div class="flex-shrink-0 mt-4 p-1 bg-gray-800 rounded-full">
                    <button id="result-boomerang-tab" class="tab-btn py-2 px-6 font-semibold rounded-full text-sm">Boomerang</button>
                    <button id="result-print-tab" class="tab-btn py-2 px-6 font-semibold rounded-full text-sm">Print Layout</button>
                </div>
                <div class="w-full h-full flex-grow relative">
                    <div id="result-boomerang-container" class="w-full h-full flex items-center justify-center"></div>
                    <div id="result-print-container" class="hidden w-full h-full items-center justify-center"></div>
                </div>
             </div>
             
             <div id="result-controls" class="flex-shrink-0 w-full max-w-xs md:max-w-sm card-bg h-full p-6 flex flex-col justify-center items-center gap-6">
                <div>
                    <h2 class="text-2xl font-bold text-green-400 mb-2">Saved Successfully!</h2>
                    <p class="text-gray-300 mb-4 text-center">Scan the QR code to download your photo.</p>
                    <div id="qr-code-container" class="p-2 bg-white rounded-lg w-48 h-48 mx-auto flex items-center justify-center text-black">
                        ...
                    </div>
                    <p id="saving-status-message" class="mt-3 text-sm h-5 text-center"></p>
                </div>
                <button id="next-session-btn" class="btn btn-primary py-4 px-10 text-xl w-full inline-flex items-center justify-center gap-2">
                    <span class="material-icons">replay</span>Done
                </button>
             </div>
        </div>

    </div>
 <button id="fullscreen-btn" title="Toggle Fullscreen" 
        class="fixed bottom-5 right-5 z-50 p-2 bg-brand-dark-light/70 border border-gray-600 rounded-lg text-gray-400 hover:text-white hover:bg-brand-dark-light focus:outline-none focus:ring-2 focus:ring-brand-red backdrop-blur-sm transition-all">
    <svg id="fullscreen-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m6-5h4m0 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m6 5h4m0 0v-4m0 4l-5-5"></path></svg>
    <svg id="minimize-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l-5 5m0 0v-4m0 4h4m6-10l5-5m0 0v4m0-4h-4m-6 10l5 5m0 0v-4m0 4h-4M10 14l-5 5"></path></svg>
</button>
    <canvas id="capture-canvas" class="hidden"></canvas>
    <canvas id="final-canvas" class="hidden"></canvas>
    
    <audio id="audio-countdown" src="sounds/countdown.mp3" preload="auto"></audio>
    
    <script>
        const screens = {
            start: document.getElementById('start-screen'),
            frame: document.getElementById('frame-screen'),
            booth: document.getElementById('booth-screen'),
            preview: document.getElementById('preview-screen'),
            result: document.getElementById('result-screen'),
        };
        const startTitle = document.getElementById('start-title');
        const layoutButtons = document.querySelectorAll('.layout-btn');
        const backToPreviewBtn = document.getElementById('back-to-preview-btn');
        const frameOptionsContainer = document.getElementById('frame-options-container');
        const boothControls = document.getElementById('booth-controls');
        const retakeBtn = document.getElementById('retake-btn');
        const confirmFrameBtn = document.getElementById('confirm-frame-btn'); 
        const nextSessionBtn = document.getElementById('next-session-btn'); 
        const video = document.getElementById('camera-feed');
        const shotIndicator = document.getElementById('shot-indicator');
        const livePreviewCanvas = document.getElementById('live-preview-canvas');
        const overlayText = document.getElementById('overlay-text');
        const flashEffect = document.getElementById('flash-effect');
        const captureCanvas = document.getElementById('capture-canvas');
        const finalCanvas = document.getElementById('final-canvas');
        const boomerangPreviewContainer = document.getElementById('boomerang-preview-container');
        const printPreviewContainer = document.getElementById('print-preview-container');
        const instructionsText = document.getElementById('instructions-text');
        const savingStatusMessage = document.getElementById('saving-status-message');
        const audioCountdown = document.getElementById('audio-countdown');
        const framePreviewCanvas = document.getElementById('frame-preview-canvas');
        const confirmSaveBtn = document.getElementById('confirm-save-btn');
        
        const initialBoomerangTab = document.getElementById('initial-boomerang-tab');
        const initialPrintTab = document.getElementById('initial-print-tab');
        
        const resultContainer = document.getElementById('result-main-area'); 
        const resultBoomerangTab = document.getElementById('result-boomerang-tab');
        const resultPrintTab = document.getElementById('result-print-tab');
        const resultBoomerangContainer = document.getElementById('result-boomerang-container');
        const resultPrintContainer = document.getElementById('result-print-container');
        const qrCodeContainer = document.getElementById('qr-code-container');

        const layoutConfig = {
            'strip-3': { shots: 3, w: 2, h: 6, guides: [{ x: 0.075, y: 0.02, w: 0.85, h: 0.28 }, { x: 0.075, y: 0.32, w: 0.85, h: 0.28 }, { x: 0.075, y: 0.62, w: 0.85, h: 0.28 }] },
            'strip-4': { shots: 4, w: 2, h: 8, guides: [{ x: 0.06, y: 0.02, w: 0.88, h: 0.19 }, { x: 0.06, y: 0.23, w: 0.88, h: 0.19 }, { x: 0.06, y: 0.44, w: 0.88, h: 0.19 }, { x: 0.06, y: 0.65, w: 0.88, h: 0.19 }] },
            'grid-4': { shots: 4, w: 4, h: 6, guides: [{ x: 0.05, y: 0.03, w: 0.425, h: 0.4 }, { x: 0.525, y: 0.03, w: 0.425, h: 0.4 }, { x: 0.05, y: 0.45, w: 0.425, h: 0.4 }, { x: 0.525, y: 0.45, w: 0.425, h: 0.4 }] },
            'spotlight-3': { shots: 3, w: 6, h: 4, guides: [{ x: 0.033, y: 0.05, w: 0.6, h: 0.8 }, { x: 0.666, y: 0.05, w: 0.3, h: 0.38 }, { x: 0.666, y: 0.47, w: 0.3, h: 0.38 }] },
            'grid-6-portrait': { shots: 6, w: 4, h: 6, guides: [ { x: 0.05, y: 0.02, w: 0.425, h: 0.26 }, { x: 0.525, y: 0.02, w: 0.425, h: 0.26 }, { x: 0.05, y: 0.30, w: 0.425, h: 0.26 }, { x: 0.525, y: 0.30, w: 0.425, h: 0.26 }, { x: 0.05, y: 0.58, w: 0.425, h: 0.26 }, { x: 0.525, y: 0.58, w: 0.425, h: 0.26 } ]},
            'grid-6-landscape': { shots: 6, w: 6, h: 4, guides: [ { x: 0.02, y: 0.05, w: 0.3, h: 0.38 }, { x: 0.34, y: 0.05, w: 0.3, h: 0.38 }, { x: 0.66, y: 0.05, w: 0.3, h: 0.38 }, { x: 0.02, y: 0.47, w: 0.3, h: 0.38 }, { x: 0.34, y: 0.47, w: 0.3, h: 0.38 }, { x: 0.66, y: 0.47, w: 0.3, h: 0.38 } ]},
        };

        let appState = {
            sessionLayouts: [], 
            currentSessionLayout: null, 
            layout: null, 
            frame: null,
            staticShots: [],
            staticShotImages: [],
            boomerangCollections: [],
            finalImage: null,
            fileUrl: null, 
            logoImage: null,
            animationIntervalId: null,
            currentShotIndex: 0,
            frameImage: null,
            retriesLeft: 1, 
            frameColor: null, 
            frameOverlay: null, 
            frameFilter: null, 
        };
        const MAX_RETRIES = 1; 

        function stopAnimation() {
            if (appState.animationIntervalId) {
                clearInterval(appState.animationIntervalId);
                appState.animationIntervalId = null;
            }
        }

        function stopCamera() {
            stopAnimation();
            if (video.srcObject) {
                video.srcObject.getTracks().forEach(track => track.stop());
                video.srcObject = null;
            }
        }

        async function startCamera() {
            stopAnimation();
            try {
                if (!video.srcObject) {
                    const videoConstraints = {
                        width: { ideal: 1280 },
                        height: { ideal: 720 },
                        facingMode: 'user'
                    };
                    const stream = await navigator.mediaDevices.getUserMedia({ video: videoConstraints, audio: false });
                    video.srcObject = stream;
                    await video.play();
                }
                return true;
            } catch (err) {
                console.error("Camera access error (ideal):", err);
                if (err.name === "OverconstrainedError") {
                    console.warn("Ideal resolution failed. Trying default camera...");
                    try {
                        const fallbackStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
                        video.srcObject = fallbackStream;
                        await video.play();
                        return true;
                    } catch (fallbackErr) {
                        console.error("Fallback camera error:", fallbackErr);
                        alert("Hindi talaga ma-access ang camera. Pakisuri ang iyong settings.");
                        return false;
                    }
                } else if (err.name === "NotAllowedError") {
                    alert("Kailangan mong payagan ang pag-access sa camera. I-refresh ang page at i-click ang 'Allow'.");
                } else {
                    alert("Could not access the camera. Please allow camera permissions.");
                }
                return false;
            }
        }

        function showScreen(screenName) {
            Object.values(screens).forEach(s => s.classList.add('hidden'));
            screens[screenName].classList.remove('hidden');
            screens[screenName].classList.add('flex'); 
        }

        async function initializeApp() {
            showScreen('start');
            startTitle.textContent = 'Connecting to admin session...';
            
            (async () => {
                try {
                    appState.logoImage = await new Promise((res, rej) => {
                        const i = new Image();
                        i.onload = () => res(i);
                        i.onerror = rej;
                        i.src = 'public/images/marahuyologo.jpg'; 
                    });
                } catch (e) {
                    console.warn('Logo could not be loaded. Check path: public/images/marahuyologo.jpg');
                }
            })();

            try {
                const cacheBuster = `?t=${new Date().getTime()}`;
                const response = await fetch(`admin/get_session.php${cacheBuster}`, {
                    method: 'GET',
                    cache: 'no-store', 
                    headers: {
                        'Cache-Control': 'no-cache',
                        'Pragma': 'no-cache'
                    }
                });
                
                if (!response.ok) throw new Error('Could not connect to server');
                
                const data = await response.json();
                appState.sessionLayouts = data.layouts || [];
                updateStartScreen();

            } catch (error) {
                console.error('Failed to initialize app:', error);
                startTitle.textContent = 'Connection Error. Please ask admin for help.';
            }
        }

        function updateStartScreen() {
            const layoutCounts = {};
            appState.sessionLayouts.forEach(layout => {
                layoutCounts[layout] = (layoutCounts[layout] || 0) + 1;
            });

            if (appState.sessionLayouts.length === 0) {
                startTitle.textContent = 'No active session. Please contact admin.';
            } else {
                startTitle.textContent = `Choose your next photo ( ${appState.sessionLayouts.length} left )`;
            }

            layoutButtons.forEach(button => {
                const layout = button.dataset.layout;
                const count = layoutCounts[layout] || 0;
                const counter = button.querySelector('.layout-counter');

                if (count > 0) {
                    button.disabled = false;
                    counter.textContent = count;
                    counter.style.display = 'flex';
                } else {
                    button.disabled = true;
                    counter.style.display = 'none';
                }
            });
        }
        
        function waitForElementVisible(element) {
            return new Promise(resolve => {
                if (element.clientWidth > 0 && element.clientHeight > 0) {
                    return resolve();
                }
                const checkVisibility = () => {
                    if (element.clientWidth > 0 && element.clientHeight > 0) {
                        resolve();
                    } else {
                        requestAnimationFrame(checkVisibility);
                    }
                };
                requestAnimationFrame(checkVisibility);
            });
        }

        async function loadFramesForSelection() {
            frameOptionsContainer.innerHTML = `
                <button data-frame="none" class="frame-btn group bg-gray-800 p-4 rounded-lg btn-choice flex flex-col items-center justify-center aspect-square">
                    <span class="material-icons text-5xl text-gray-500 group-hover:text-red-400">block</span>
                    <h3 class="text-lg font-semibold mt-3">No Frame</h3>
                </button>
                <button data-frame="black" class="frame-btn group bg-gray-800 p-4 rounded-lg btn-choice flex flex-col items-center justify-center aspect-square relative">
                    <div class="w-full h-full bg-black border-4 border-gray-500 rounded-md flex items-center justify-center">
                        <span class="material-icons text-4xl text-white">check_box_outline_blank</span>
                    </div>
                    <h3 class="text-lg font-semibold mt-3 absolute bottom-4">Black Frame</h3>
                </button>
                <button data-frame="white" class="frame-btn group bg-gray-800 p-4 rounded-lg btn-choice flex flex-col items-center justify-center aspect-square relative">
                    <div class="w-full h-full bg-white border-4 border-gray-500 rounded-md flex items-center justify-center">
                        <span class="material-icons text-4xl text-black">check_box_outline_blank</span>
                    </div>
                    <h3 class="text-lg font-semibold mt-3 absolute bottom-4 text-black">White Frame</h3>
                </button>
                <button data-frame="red" class="frame-btn group bg-gray-800 p-4 rounded-lg btn-choice flex flex-col items-center justify-center aspect-square relative">
                    <div class="w-full h-full bg-red-600 border-4 border-gray-500 rounded-md flex items-center justify-center">
                        <span class="material-icons text-4xl text-white">check_box_outline_blank</span>
                    </div>
                    <h3 class="text-lg font-semibold mt-3 absolute bottom-4">Red Frame</h3>
                </button>
                <button data-frame="blue" class="frame-btn group bg-gray-800 p-4 rounded-lg btn-choice flex flex-col items-center justify-center aspect-square relative">
                    <div class="w-full h-full bg-blue-600 border-4 border-gray-500 rounded-md flex items-center justify-center">
                        <span class="material-icons text-4xl text-white">check_box_outline_blank</span>
                    </div>
                    <h3 class="text-lg font-semibold mt-3 absolute bottom-4">Blue Frame</h3>
                </button>
                <button data-frame="vignette" class="frame-btn group bg-gray-800 p-4 rounded-lg btn-choice flex flex-col items-center justify-center aspect-square relative">
                    <div class="w-full h-full rounded-md flex items-center justify-center" style="background: radial-gradient(ellipse at center, rgba(0,0,0,0) 50%, rgba(0,0,0,0.8) 100%); border: 2px solid #555;">
                        <span class="material-icons text-4xl text-white">filter_vintage</span>
                    </div>
                    <h3 class="text-lg font-semibold mt-3 absolute bottom-4">Vignette</h3>
                </button>
                <button data-frame="vintage" class="frame-btn group bg-gray-800 p-4 rounded-lg btn-choice flex flex-col items-center justify-center aspect-square relative">
                    <div class="w-full h-full rounded-md flex items-center justify-center" style="background-color: #fdf6e3; border: 2px solid #555;">
                        <span class="material-icons text-4xl text-yellow-900">tonality</span>
                    </div>
                    <h3 class="text-lg font-semibold mt-3 absolute bottom-4">Vintage</h3>
                </button>
            `;
            try {
                const response = await fetch(`admin/get_frames.php?layout=${appState.layout}`);
                if (!response.ok) throw new Error('Failed to fetch frames');
                const framePaths = await response.json();
                framePaths.forEach(path => {
                    const frameBtn = document.createElement('button');
                    frameBtn.dataset.frame = path;
                    frameBtn.className = 'frame-btn group bg-gray-800 p-2 rounded-lg btn-choice aspect-square';
                    frameBtn.innerHTML = `<img src="admin/${path}" class="w-full h-full object-contain" alt="Frame preview">`;
                    frameOptionsContainer.appendChild(frameBtn);
                });
            } catch (error) { 
                console.error('Failed to load frames:', error); 
                frameOptionsContainer.innerHTML += `<p class="text-yellow-400 col-span-full">Note: Frame loading failed.</p>`;
            }
            
            const frameButtons = document.querySelectorAll('.frame-btn');
            frameButtons.forEach(button => {
                button.replaceWith(button.cloneNode(true));
            });
            
            document.querySelectorAll('.frame-btn').forEach(button => {
                button.addEventListener('click', async () => {
                    document.querySelectorAll('.frame-btn').forEach(btn => btn.classList.remove('selected'));
                    button.classList.add('selected');
                    
                    const frameType = button.dataset.frame;
                    appState.frameImage = null; 
                    appState.frameColor = null; 
                    appState.frameOverlay = null; 
                    appState.frameFilter = null; 
                    
                    if (frameType === 'none') {
                        appState.frame = null;
                    } else if (frameType === 'black') {
                        appState.frameColor = '#000000';
                    } else if (frameType === 'white') {
                        appState.frameColor = '#ffffff';
                    } else if (frameType === 'red') {
                        appState.frameColor = '#dc2626'; 
                    } else if (frameType === 'blue') {
                        appState.frameColor = '#2563eb'; 
                    } else if (frameType === 'vignette') {
                        appState.frameOverlay = 'vignette';
                    } else if (frameType === 'vintage') {
                        appState.frameColor = '#fdf6e3'; 
                        appState.frameFilter = 'sepia(100%)'; 
                        appState.frameOverlay = 'vignette'; 
                    } else {
                        appState.frame = `admin/${frameType}`;
                        appState.frameImage = await new Promise(res => { const i = new Image(); i.onload = () => res(i); i.src = appState.frame; });
                    }
                    
                    await updateFramePreview();
                    confirmSaveBtn.classList.remove('hidden');
                });
            });

            showScreen('frame');
            await waitForElementVisible(framePreviewCanvas.parentElement);
            await updateFramePreview(); 
            confirmSaveBtn.classList.add('hidden');
        }
       
        function updateBoothControls(state) {
            boothControls.innerHTML = '';
            let button;
            if (state === 'start') {
                button = document.createElement('button');
                button.className = 'btn btn-primary py-3 px-10 text-xl pop-in inline-flex items-center gap-2';
                button.innerHTML = `<span class="material-icons">camera</span> Start Session`;
                button.onclick = takeSingleShot;
            } else if (state === 'next') {
                button = document.createElement('button');
                button.className = 'btn btn-primary py-3 px-10 text-xl pop-in bg-green-500 inline-flex items-center gap-2';
                button.innerHTML = `<span class="material-icons">camera_alt</span> Next Shot`;
                button.onclick = takeSingleShot;
            }
            else { 
                button = document.createElement('button');
                button.className = 'btn btn-primary py-3 px-10 text-xl opacity-75 cursor-not-allowed';
                button.innerText = 'Processing...';
                button.disabled = true;
            }
            if (button) boothControls.appendChild(button);
        }

        async function setupBooth() {
            appState.currentShotIndex = 0;
            appState.staticShots = [];
            appState.staticShotImages = [];
            appState.boomerangCollections = [];
            appState.frame = null;
            appState.frameImage = null;
            appState.frameColor = null; 
            appState.frameOverlay = null; 
            appState.frameFilter = null;
            
            if (!await startCamera()) { 
                initializeApp(); 
                return; 
            }

            showScreen('booth');
            await waitForElementVisible(livePreviewCanvas.parentElement); 

            drawLivePreview();
            updateShotIndicator(0);
            updateBoothControls('start');
            instructionsText.textContent = 'Press Start to begin!';
            
            retakeBtn.disabled = (appState.retriesLeft <= 0);
            retakeBtn.innerHTML = `<span class="material-icons">refresh</span>Retake (${appState.retriesLeft} left)`;
        }

        function drawLivePreview() {
            const config = layoutConfig[appState.layout];
            if (!config) {
                console.error("Invalid layout config for:", appState.layout);
                return;
            }
            const ctx = livePreviewCanvas.getContext('2d');
            const parentWidth = livePreviewCanvas.parentElement.clientWidth;
            
            if (parentWidth === 0) { 
                 setTimeout(drawLivePreview, 100);
                 return;
            }

            const canvasW = parentWidth;
            const canvasH = parentWidth / (config.w / config.h);
            livePreviewCanvas.width = canvasW * 2;
            livePreviewCanvas.height = canvasH * 2;
            livePreviewCanvas.style.height = `${canvasH}px`; 
            livePreviewCanvas.style.width = `${canvasW}px`;
            
            ctx.fillStyle = '#1f2937';
            ctx.fillRect(0,0, livePreviewCanvas.width, livePreviewCanvas.height);

            config.guides.forEach((g, i) => {
                 if (!appState.staticShotImages[i]) {
                     ctx.fillStyle = "rgba(255, 255, 255, 0.1)";
                     ctx.fillRect(livePreviewCanvas.width * g.x, livePreviewCanvas.height * g.y, livePreviewCanvas.width * g.w, livePreviewCanvas.height * g.h);
                     if (i === appState.currentShotIndex) {
                         ctx.strokeStyle = '#ef4444';
                         ctx.lineWidth = 4;
                         ctx.strokeRect(livePreviewCanvas.width * g.x, livePreviewCanvas.height * g.y, livePreviewCanvas.width * g.w, livePreviewCanvas.height * g.h);
                     }
                 }
            });

            appState.staticShotImages.forEach((img, i) => {
                const g = config.guides[i];
                if(g) drawImageCropped(ctx, img, livePreviewCanvas.width * g.x, livePreviewCanvas.height * g.y, livePreviewCanvas.width * g.w, livePreviewCanvas.height * g.h);
            });

            if (appState.frameImage) {
                ctx.drawImage(appState.frameImage, 0, 0, livePreviewCanvas.width, livePreviewCanvas.height);
            }
        }

        function updateShotIndicator(index) {
            const totalShots = layoutConfig[appState.layout].shots;
            if (index < totalShots) {
                shotIndicator.textContent = `Shot ${index + 1} of ${totalShots}`;
            } else {
                shotIndicator.textContent = 'All shots taken!';
            }
        }

        function showOverlayText(text, duration = 800) {
            return new Promise(resolve => {
                overlayText.textContent = text;
                overlayText.style.display = 'flex';
                setTimeout(() => {
                    overlayText.style.display = 'none';
                    resolve();
                }, duration);
            });
        }

        async function takeSingleShot() {
            updateBoothControls('processing');
            instructionsText.textContent = 'Get Ready!';
            
            const framesPromise = new Promise(async (resolve) => {
                const frames = [];
                const captureInterval = setInterval(() => {
                    if (video.videoWidth === 0) return; 
                    captureCanvas.width = video.videoWidth;
                    captureCanvas.height = video.videoHeight;
                    const ctx = captureCanvas.getContext('2d');
                    ctx.drawImage(video, 0, 0, video.videoWidth, video.videoHeight);
                    frames.push(captureCanvas.toDataURL('image/jpeg', 0.5));
                }, 100);

                await showOverlayText('Look at the camera!', 1200);
                
                audioCountdown.currentTime = 0;
                audioCountdown.play().catch(e => console.error("Audio play failed:", e)); 
                
                await showOverlayText('3', 1000); 
                await showOverlayText('2', 1000); 
                await showOverlayText('1', 1000); 
                
                await new Promise(r => setTimeout(r, 500)); 

                clearInterval(captureInterval);
                resolve(frames.slice(-15));
            });

            const frames = await framesPromise;
            
            flashEffect.classList.add('flash');
            setTimeout(() => flashEffect.classList.remove('flash'), 350);
            
            appState.boomerangCollections.push(frames);
            const staticShotDataUrl = frames[frames.length - 1]; 
            appState.staticShots.push(staticShotDataUrl);
            appState.staticShotImages.push(await new Promise(res => { const i = new Image(); i.onload = () => res(i); i.src = staticShotDataUrl; }));
            
            appState.currentShotIndex++;
            drawLivePreview();
            updateShotIndicator(appState.currentShotIndex-1);

            const totalShots = layoutConfig[appState.layout].shots;

            if (appState.currentShotIndex < totalShots) {
                updateBoothControls('next');
                instructionsText.textContent = 'Great shot! Get ready for the next one.';
                updateShotIndicator(appState.currentShotIndex);
                drawLivePreview();
            } else {
                updateShotIndicator(totalShots);
                instructionsText.textContent = 'All shots done! Processing...';
                await new Promise(resolve => setTimeout(resolve, 1500));
                await showPreviewScreen();
            }
        }


        function drawImageCropped(ctx, img, dx, dy, dw, dh) {
            const imgRatio = img.width / img.height;
            const boxRatio = dw / dh;
            let sw = img.width, sh = img.height, sx = 0, sy = 0;
            if (imgRatio > boxRatio) {
                sw = img.height * boxRatio;
                sx = (img.width - sw) / 2;
            } else {
                sh = img.width / boxRatio;
                sy = (img.height - sh) / 2;
            }
            ctx.drawImage(img, sx, sy, sw, sh, dx, dy, dw, dh);
        }

        async function generateInitialBoomerang() {
            stopAnimation();
            const config = layoutConfig[appState.layout];

            boomerangPreviewContainer.innerHTML = `<canvas id="initial-boomerang-canvas"></canvas>`;
            const boomerangCanvas = document.getElementById('initial-boomerang-canvas');
            if (!boomerangCanvas) return;

            const container = boomerangPreviewContainer;
            
            await waitForElementVisible(container);
            
            const containerW = container.clientWidth;
            const containerH = container.clientHeight;
            if (containerW === 0 || containerH === 0) return; 
            const layoutRatio = config.w / config.h;

            let canvasW = containerW;
            let canvasH = containerW / layoutRatio;
            if (canvasH > containerH) {
                canvasH = containerH;
                canvasW = containerH * layoutRatio;
            }

            boomerangCanvas.style.width = `${canvasW}px`;
            boomerangCanvas.style.height = `${canvasH}px`;
            boomerangCanvas.width = canvasW * 2; 
            boomerangCanvas.height = canvasH * 2;
            
            const boomerangCtx = boomerangCanvas.getContext('2d');
            const allFrameImages = await Promise.all(appState.boomerangCollections.map(collection => 
                Promise.all(collection.map(src => new Promise(res => { const i = new Image(); i.onload = () => res(i); i.src = src; })))
            ));
            
            let frameIndex = 0;
            let direction = 1;
            const longestCollectionLength = Math.max(0, ...allFrameImages.map(c => c.length));
            
            appState.animationIntervalId = setInterval(() => {
                if(!longestCollectionLength) return;
                
                boomerangCtx.fillStyle = 'white';
                boomerangCtx.fillRect(0, 0, boomerangCanvas.width, boomerangCanvas.height);
                
                boomerangCtx.filter = 'none';
                config.guides.forEach((g, i) => {
                    const currentCollection = allFrameImages[i];
                    if (currentCollection && currentCollection.length > 0) {
                        const img = currentCollection[frameIndex % currentCollection.length];
                        drawImageCropped(boomerangCtx, img, boomerangCanvas.width * g.x, boomerangCanvas.height * g.y, boomerangCanvas.width * g.w, boomerangCanvas.height * g.h);
                    }
                });

                frameIndex += direction;
                if (frameIndex >= longestCollectionLength - 1 || frameIndex <= 0) {
                    direction *= -1;
                    if(frameIndex < 0) frameIndex = 0;
                }
            }, 100);
        }

        async function generateInitialPrintPreview() {
            const config = layoutConfig[appState.layout];
            const printCtx = finalCanvas.getContext('2d');
            const DPI = 300;
            finalCanvas.width = config.w * DPI;
            finalCanvas.height = config.h * DPI;
            printCtx.fillStyle = 'white';
            printCtx.fillRect(0,0, finalCanvas.width, finalCanvas.height);
            
            appState.staticShotImages.forEach((img, i) => {
                const g = config.guides[i];
                if(g) drawImageCropped(printCtx, img, finalCanvas.width * g.x, finalCanvas.height * g.y, finalCanvas.width * g.w, finalCanvas.height * g.h);
            });
            printPreviewContainer.innerHTML = `<img src="${finalCanvas.toDataURL('image/png')}" alt="Print Preview" class="max-w-full max-h-full object-contain mx-auto rounded-md"/>`;
        }

        async function showPreviewScreen() {
            showScreen('preview');
            
            await generateInitialBoomerang();
            await generateInitialPrintPreview();
            
            showPreviewTab('boomerang');
            
            stopCamera();
        }
        
        function showPreviewTab(tabName) {
            stopAnimation(); 
            if (tabName === 'boomerang') {
                boomerangPreviewContainer.style.display = 'flex';
                printPreviewContainer.style.display = 'none';
                initialBoomerangTab.classList.add('active');
                initialPrintTab.classList.remove('active');
                generateInitialBoomerang(); 
            } else { 
                boomerangPreviewContainer.style.display = 'none';
                printPreviewContainer.style.display = 'flex';
                initialBoomerangTab.classList.remove('active');
                initialPrintTab.classList.add('active');
            }
        }
        
        async function updateFramePreview() {
            const config = layoutConfig[appState.layout];
            const ctx = framePreviewCanvas.getContext('2d');
            
            const parentWidth = framePreviewCanvas.parentElement.clientWidth;
            if (parentWidth === 0) {
                setTimeout(updateFramePreview, 100); 
                return;
            }
            const canvasW = parentWidth;
            const canvasH = parentWidth / (config.w / config.h);
            framePreviewCanvas.width = canvasW * 2; 
            framePreviewCanvas.height = canvasH * 2;
            framePreviewCanvas.style.height = `${canvasH}px`;
            
            if (appState.frameColor) {
                ctx.fillStyle = appState.frameColor;
                ctx.fillRect(0, 0, framePreviewCanvas.width, framePreviewCanvas.height);
            } else {
                ctx.fillStyle = 'white'; 
                ctx.fillRect(0, 0, framePreviewCanvas.width, framePreviewCanvas.height);
            }
            
            ctx.filter = appState.frameFilter || 'none';
            appState.staticShotImages.forEach((img, i) => {
                const g = config.guides[i];
                if(g) drawImageCropped(ctx, img, framePreviewCanvas.width * g.x, framePreviewCanvas.height * g.y, framePreviewCanvas.width * g.w, framePreviewCanvas.height * g.h);
            });
            ctx.filter = 'none'; 
            
            if (appState.frameImage) {
                ctx.drawImage(appState.frameImage, 0, 0, framePreviewCanvas.width, framePreviewCanvas.height);
            }

            if (appState.frameOverlay === 'vignette') {
                const innerR = ctx.canvas.height / 3;
                const outerR = ctx.canvas.width / 1.5;
                const gradient = ctx.createRadialGradient(
                    ctx.canvas.width / 2, ctx.canvas.height / 2, innerR, 
                    ctx.canvas.width / 2, ctx.canvas.height / 2, outerR
                );
                gradient.addColorStop(0.5, 'rgba(0,0,0,0)');
                gradient.addColorStop(1, 'rgba(0,0,0,0.8)');
                ctx.fillStyle = gradient;
                ctx.fillRect(0, 0, ctx.canvas.width, ctx.canvas.height);
            }

            if (appState.logoImage) {
                const logoHeight = framePreviewCanvas.width * 0.15 / (config.w / config.h); 
                const logoAspect = appState.logoImage.width / appState.logoImage.height;
                const logoWidth = logoHeight * logoAspect;
                const logoY = framePreviewCanvas.height * 0.90; 
                const logoX = (framePreviewCanvas.width - logoWidth) / 2;
                ctx.drawImage(appState.logoImage, logoX, logoY, logoWidth, logoHeight);
            }
        }

        async function generateAndSaveFinalImage() {
            savingStatusMessage.textContent = 'Generating final image...';
            qrCodeContainer.innerHTML = 'Saving...';
            showScreen('result'); 

            await new Promise(resolve => setTimeout(resolve, 100)); 

            const config = layoutConfig[appState.layout];
            const printCtx = finalCanvas.getContext('2d');
            const DPI = 300;
            finalCanvas.width = config.w * DPI;
            finalCanvas.height = config.h * DPI;
            
            if (appState.frameColor) {
                printCtx.fillStyle = appState.frameColor;
                printCtx.fillRect(0, 0, finalCanvas.width, finalCanvas.height);
            } else {
                printCtx.fillStyle = 'white';
                printCtx.fillRect(0, 0, finalCanvas.width, finalCanvas.height);
            }
            
            printCtx.filter = appState.frameFilter || 'none';
            appState.staticShotImages.forEach((img, i) => {
                const g = config.guides[i];
                if(g) drawImageCropped(printCtx, img, finalCanvas.width * g.x, finalCanvas.height * g.y, finalCanvas.width * g.w, finalCanvas.height * g.h);
            });
            printCtx.filter = 'none';
            
            if (appState.frameImage) {
                printCtx.drawImage(appState.frameImage, 0, 0, finalCanvas.width, finalCanvas.height);
            }

            if (appState.frameOverlay === 'vignette') {
                const innerR = printCtx.canvas.height / 3;
                const outerR = printCtx.canvas.width / 1.5;
                const gradient = printCtx.createRadialGradient(
                    printCtx.canvas.width / 2, printCtx.canvas.height / 2, innerR, 
                    printCtx.canvas.width / 2, printCtx.canvas.height / 2, outerR
                );
                gradient.addColorStop(0.5, 'rgba(0,0,0,0)');
                gradient.addColorStop(1, 'rgba(0,0,0,0.8)');
                printCtx.fillStyle = gradient;
                printCtx.fillRect(0, 0, printCtx.canvas.width, printCtx.canvas.height);
            }

            if (appState.logoImage) {
                const logoHeight = finalCanvas.width * 0.15 / (config.w / config.h);
                const logoAspect = appState.logoImage.width / appState.logoImage.height;
                const logoWidth = logoHeight * logoAspect;
                const logoY = finalCanvas.height * 0.90; 
                const logoX = (finalCanvas.width - logoWidth) / 2;
                printCtx.drawImage(appState.logoImage, logoX, logoY, logoWidth, logoHeight);
            }

            appState.finalImage = finalCanvas.toDataURL('image/png');
            appState.fileUrl = null; 
            
            savingStatusMessage.textContent = 'Saving to admin...';

            try {
                const response = await fetch('admin/save_image.php', { 
                    method: 'POST', 
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, 
                    body: 'imageData=' + encodeURIComponent(appState.finalImage) 
                });
                
                if (!response.ok) throw new Error('Server error during save');
                const result = await response.json(); 
                
                if (result.success && result.fileUrl) {
                    appState.fileUrl = result.fileUrl; 
                    await fetch('admin/use_session.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'layout=' + encodeURIComponent(appState.currentSessionLayout)
                    });
                } else {
                    throw new Error(result.message || 'Failed to get file URL');
                }

            } catch (error) {
                console.error('Error saving image:', error);
                savingStatusMessage.textContent = 'Error saving photo.';
                qrCodeContainer.innerHTML = 'Error';
            }
            
            await showFinalPreviewScreen(appState.fileUrl);
        }

        async function showFinalPreviewScreen(fileUrl) {
            stopAnimation(); 
            
            resultPrintContainer.innerHTML = `<img src="${appState.finalImage}" alt="Final Print" class="max-w-full max-h-full object-contain mx-auto rounded-md"/>`;
            
            if (fileUrl) {
                savingStatusMessage.textContent = 'Saved!';
                generateQRCode(fileUrl, qrCodeContainer);
            } else {
                savingStatusMessage.textContent = 'Save failed.';
                qrCodeContainer.innerHTML = 'Error';
            }
            
            resultBoomerangContainer.innerHTML = `<canvas id="result-boomerang-canvas"></canvas>`;
            
            showResultTab('boomerang'); 
            await generateFinalBoomerang();
        }

        async function generateFinalBoomerang() {
            const boomerangCanvas = document.getElementById('result-boomerang-canvas');
            if (!boomerangCanvas) return;
            
            const config = layoutConfig[appState.layout];
            const container = resultBoomerangContainer;
            
            await waitForElementVisible(container);
            
            const containerW = container.clientWidth;
            const containerH = container.clientHeight;
            if (containerW === 0 || containerH === 0) {
                return;
            }
            const layoutRatio = config.w / config.h;

            let canvasW = containerW;
            let canvasH = containerW / layoutRatio;
            if (canvasH > containerH) {
                canvasH = containerH;
                canvasW = containerH * layoutRatio;
            }

            boomerangCanvas.style.width = `${canvasW}px`;
            boomerangCanvas.style.height = `${canvasH}px`;
            boomerangCanvas.width = canvasW * 2; 
            boomerangCanvas.height = canvasH * 2;
            
            const boomerangCtx = boomerangCanvas.getContext('2d');
            const allFrameImages = await Promise.all(appState.boomerangCollections.map(collection => 
                Promise.all(collection.map(src => new Promise(res => { const i = new Image(); i.onload = () => res(i); i.src = src; })))
            ));
            
            let frameIndex = 0;
            let direction = 1;
            const longestCollectionLength = Math.max(0, ...allFrameImages.map(c => c.length));
            
            appState.animationIntervalId = setInterval(() => {
                if(!longestCollectionLength) return;
                
                if (appState.frameColor) {
                    boomerangCtx.fillStyle = appState.frameColor;
                    boomerangCtx.fillRect(0, 0, boomerangCanvas.width, boomerangCanvas.height);
                } else {
                    boomerangCtx.fillStyle = 'white';
                    boomerangCtx.fillRect(0, 0, boomerangCanvas.width, boomerangCanvas.height);
                }
                
                boomerangCtx.filter = appState.frameFilter || 'none';
                config.guides.forEach((g, i) => {
                    const currentCollection = allFrameImages[i];
                    if (currentCollection && currentCollection.length > 0) {
                        const img = currentCollection[frameIndex % currentCollection.length];
                        drawImageCropped(boomerangCtx, img, boomerangCanvas.width * g.x, boomerangCanvas.height * g.y, boomerangCanvas.width * g.w, boomerangCanvas.height * g.h);
                    }
                });
                boomerangCtx.filter = 'none';

                if (appState.frameImage) {
                    boomerangCtx.drawImage(appState.frameImage, 0, 0, boomerangCanvas.width, boomerangCanvas.height);
                }

                if (appState.frameOverlay === 'vignette') {
                    const innerR = boomerangCtx.canvas.height / 3;
                    const outerR = boomerangCtx.canvas.width / 1.5;
                    const gradient = boomerangCtx.createRadialGradient(
                        boomerangCtx.canvas.width / 2, boomerangCtx.canvas.height / 2, innerR, 
                        boomerangCtx.canvas.width / 2, boomerangCtx.canvas.height / 2, outerR
                    );
                    gradient.addColorStop(0.5, 'rgba(0,0,0,0)');
                    gradient.addColorStop(1, 'rgba(0,0,0,0.8)');
                    boomerangCtx.fillStyle = gradient;
                    boomerangCtx.fillRect(0, 0, boomerangCtx.canvas.width, boomerangCtx.canvas.height);
                }

                if (appState.logoImage) {
                    const logoHeight = boomerangCanvas.width * 0.15 / (config.w / config.h); 
                    const logoAspect = appState.logoImage.width / appState.logoImage.height;
                    const logoWidth = logoHeight * logoAspect;
                    const logoY = boomerangCanvas.height * 0.90; 
                    const logoX = (boomerangCanvas.width - logoWidth) / 2;
                    boomerangCtx.drawImage(appState.logoImage, logoX, logoY, logoWidth, logoHeight);
                }

                frameIndex += direction;
                if (frameIndex >= longestCollectionLength - 1 || frameIndex <= 0) {
                    direction *= -1;
                    if(frameIndex < 0) frameIndex = 0;
                }
            }, 100);
        }

        function showResultTab(tabName) {
            stopAnimation(); 
            if (tabName === 'boomerang') {
                resultBoomerangContainer.style.display = 'flex';
                resultPrintContainer.style.display = 'none';
                resultBoomerangTab.classList.add('active');
                resultPrintTab.classList.remove('active');
                generateFinalBoomerang(); 
            } else { 
                resultBoomerangContainer.style.display = 'none';
                resultPrintContainer.style.display = 'flex';
                resultBoomerangTab.classList.remove('active');
                resultPrintTab.classList.add('active');
            }
        }

        function generateQRCode(url, container) {
            try {
                container.innerHTML = ''; 
                const typeNumber = 0; 
                const errorCorrectionLevel = 'L'; 
                const qr = qrcode(typeNumber, errorCorrectionLevel);
                qr.addData(url);
                qr.make();
                
                const imgTag = qr.createImgTag(6, 2); 
                const imgEl = document.createElement('div');
                imgEl.innerHTML = imgTag;
                const qrImage = imgEl.firstChild;
                
                qrImage.style.width = '100%';
                qrImage.style.height = 'auto';
                qrImage.style.imageRendering = 'pixelated'; 
                
                container.appendChild(qrImage);
                
            } catch (e) {
                console.error('QR Code generation error:', e);
                container.innerHTML = 'QR Error';
            }
        }


        layoutButtons.forEach(b => b.addEventListener('click', () => { 
            appState.currentSessionLayout = b.dataset.layout; 
            appState.layout = b.dataset.layout; 
            appState.retriesLeft = MAX_RETRIES; 
            setupBooth(); 
        }));

        retakeBtn.addEventListener('click', () => {
            stopAnimation();
            if (appState.retriesLeft > 0) {
                appState.retriesLeft--;
                setupBooth(); 
            }
        });

        confirmFrameBtn.addEventListener('click', async () => {
            stopAnimation();
            stopCamera();
            await loadFramesForSelection(); 
        });

        backToPreviewBtn.addEventListener('click', () => {
            showPreviewScreen(); 
        });

        confirmSaveBtn.addEventListener('click', generateAndSaveFinalImage);

        nextSessionBtn.addEventListener('click', () => {
            stopAnimation(); 
            initializeApp(); 
        });

        initialBoomerangTab.addEventListener('click', () => showPreviewTab('boomerang'));
        initialPrintTab.addEventListener('click', () => showPreviewTab('print'));
        
        resultBoomerangTab.addEventListener('click', () => showResultTab('boomerang'));
        resultPrintTab.addEventListener('click', () => showResultTab('print'));
        
        const fullscreenBtn = document.getElementById('fullscreen-btn');
        const fullscreenIcon = document.getElementById('fullscreen-icon');
        const minimizeIcon = document.getElementById('minimize-icon');

        if (fullscreenBtn && fullscreenIcon && minimizeIcon) {
            fullscreenBtn.addEventListener('click', () => {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen().catch(err => {
                        console.error(`Error sa pag-fullscreen: ${err.message} (${err.name})`);
                    });
                } else {
                    if (document.exitFullscreen) {
                        document.exitFullscreen();
                    }
                }
            });
            document.addEventListener('fullscreenchange', () => {
                if (document.fullscreenElement) {
                    fullscreenIcon.classList.add('hidden');
                    minimizeIcon.classList.remove('hidden');
                } else {
                    fullscreenIcon.classList.remove('hidden');
                    minimizeIcon.classList.add('hidden');
                }
            });
        }

        document.addEventListener('DOMContentLoaded', initializeApp);

    </script>
    
</body>
</html>