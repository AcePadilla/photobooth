// --- Element Selectors ---
const screens = {
    start: document.getElementById('start-screen'),
    frame: document.getElementById('frame-screen'),
    booth: document.getElementById('booth-screen'),
    preview: document.getElementById('preview-screen'),
    result: document.getElementById('result-screen'),
};
const layoutButtons = document.querySelectorAll('.layout-btn');
const backToStartBtn = document.getElementById('back-to-start-btn');
const frameOptionsContainer = document.getElementById('frame-options-container');
const backToFrameBtn = document.getElementById('back-to-frame-btn');
const boothControls = document.getElementById('booth-controls');
const retakeBtn = document.getElementById('retake-btn');
const confirmSaveBtn = document.getElementById('confirm-save-btn');
const restartBtn = document.getElementById('restart-btn');
const downloadBtn = document.getElementById('download-btn');
const printBtn = document.getElementById('print-btn');
const video = document.getElementById('camera-feed');
const shotIndicator = document.getElementById('shot-indicator');
const shotPreviewsContainer = document.getElementById('shot-previews-container');
const overlayText = document.getElementById('overlay-text');
const flashEffect = document.getElementById('flash-effect');
const captureCanvas = document.getElementById('capture-canvas');
const finalCanvas = document.getElementById('final-canvas');
const progressBar = document.getElementById('progress-bar');
const boomerangTab = document.getElementById('boomerang-tab');
const printTab = document.getElementById('print-tab');
const boomerangPreviewContainer = document.getElementById('boomerang-preview-container');
const printPreviewContainer = document.getElementById('print-preview-container');

// --- State and Config ---
const layoutConfig = {
    'strip-3': { shots: 3, w: 2, h: 6, guides: [{ x: 0.075, y: 0.025, w: 0.85, h: 0.3 }, { x: 0.075, y: 0.35, w: 0.85, h: 0.3 }, { x: 0.075, y: 0.675, w: 0.85, h: 0.3 }] },
    'strip-4': { shots: 4, w: 2, h: 8, guides: [{ x: 0.06, y: 0.02, w: 0.88, h: 0.22 }, { x: 0.06, y: 0.26, w: 0.88, h: 0.22 }, { x: 0.06, y: 0.50, w: 0.88, h: 0.22 }, { x: 0.06, y: 0.74, w: 0.88, h: 0.22 }] },
    'grid-4': { shots: 4, w: 4, h: 6, guides: [{ x: 0.05, y: 0.033, w: 0.425, h: 0.45 }, { x: 0.525, y: 0.033, w: 0.425, h: 0.45 }, { x: 0.05, y: 0.517, w: 0.425, h: 0.45 }, { x: 0.525, y: 0.517, w: 0.425, h: 0.45 }] },
    'spotlight-3': { shots: 3, w: 6, h: 4, guides: [{ x: 0.033, y: 0.05, w: 0.6, h: 0.9 }, { x: 0.666, y: 0.05, w: 0.3, h: 0.43 }, { x: 0.666, y: 0.52, w: 0.3, h: 0.43 }] }
};

let appState = {
    layout: null, frame: null, staticShots: [], boomerangCollections: [],
    finalImage: null, animationIntervalId: null, currentShotIndex: 0, frameImage: null,
};

// --- Animation & Camera Management ---
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
            const stream = await navigator.mediaDevices.getUserMedia({ video: { width: 1280, height: 720, facingMode: 'user' }, audio: false });
            video.srcObject = stream;
            await video.play();
        }
        return true;
    } catch (err) {
        console.error("Camera access error:", err);
        alert("Could not access the camera. Please allow camera permissions.");
        return false;
    }
}

// --- UI & Screen Flow ---
function showScreen(screenName) {
    Object.values(screens).forEach(s => s.classList.add('hidden'));
    screens[screenName].classList.remove('hidden');
    screens[screenName].classList.add('fade-in');
}

async function loadFrames() {
    frameOptionsContainer.innerHTML = `<button data-frame="none" class="frame-btn group bg-gray-800 p-4 rounded-lg border-2 border-gray-700 hover:border-amber-400 btn flex flex-col items-center justify-center aspect-square"><span class="material-icons text-5xl text-gray-500 group-hover:text-amber-300">block</span><h3 class="text-lg font-semibold mt-3">No Frame</h3></button>`;
    try {
        const response = await fetch(`admin/get_frames.php?layout=${appState.layout}`);
        if (!response.ok) throw new Error('Failed to fetch frames');
        const framePaths = await response.json();
        framePaths.forEach(path => {
            const frameBtn = document.createElement('button');
            frameBtn.dataset.frame = path;
            frameBtn.className = 'frame-btn group bg-gray-800 p-2 rounded-lg border-2 border-gray-700 hover:border-amber-400 btn aspect-square';
            frameBtn.innerHTML = `<img src="admin/${path}" class="w-full h-full object-contain" alt="Frame preview">`;
            frameOptionsContainer.appendChild(frameBtn);
        });
    } catch (error) { console.error('Failed to load frames:', error); }
    
    document.querySelectorAll('.frame-btn').forEach(button => {
        button.addEventListener('click', () => {
            appState.frame = button.dataset.frame === 'none' ? null : `admin/${button.dataset.frame}`;
            setupBooth();
        });
    });
}

function generatePreviewPlaceholders() {
    shotPreviewsContainer.innerHTML = '';
    const shotsNeeded = layoutConfig[appState.layout].shots;
    for (let i = 1; i <= shotsNeeded; i++) {
        const slot = document.createElement('div');
        slot.id = `shot-preview-${i-1}`;
        slot.className = 'preview-slot bg-gray-700/50 rounded-lg aspect-[4/3] flex items-center justify-center flex-col text-gray-500 transition-all duration-300';
        slot.innerHTML = `<span class="material-icons text-4xl">photo_camera</span><span class="font-semibold mt-1">Shot ${i}</span>`;
        shotPreviewsContainer.appendChild(slot);
    }
}

function updateBoothControls(state) {
    boothControls.innerHTML = '';
    let button;
    if (state === 'ready') {
        button = document.createElement('button');
        button.id = 'start-sequence-btn';
        button.className = 'bg-amber-500 hover:bg-amber-600 text-gray-900 font-bold py-3 px-10 rounded-full text-xl btn pop-in';
        button.innerHTML = `<span class="material-icons" style="vertical-align: middle; margin-right: 8px;">camera</span> Start Session`;
        button.onclick = runPhotoSequence;
    } else {
        button = document.createElement('button');
        button.className = 'bg-gray-500 text-gray-900 font-bold py-3 px-10 rounded-full text-xl btn opacity-75 cursor-not-allowed';
        button.innerText = 'Processing...';
        button.disabled = true;
    }
    if (button) boothControls.appendChild(button);
}

async function setupBooth() {
    if (!await startCamera()) { resetApp(); return; }
    appState.currentShotIndex = 0;
    appState.staticShots = [];
    appState.boomerangCollections = [];
    generatePreviewPlaceholders();
    updateShotIndicator(0);
    updateProgressBar(0);
    updateBoothControls('ready');
    showScreen('booth');
}

function updateShotIndicator(index) {
    const totalShots = layoutConfig[appState.layout].shots;
    if (index < totalShots) {
        shotIndicator.textContent = `Shot ${index + 1} of ${totalShots}`;
    } else {
        shotIndicator.textContent = 'All shots taken!';
    }
}

function updateProgressBar(progress) {
    progressBar.style.width = `${progress * 100}%`;
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

// --- Main Photo Sequence ---
async function runPhotoSequence() {
    updateBoothControls('processing');
    backToFrameBtn.style.display = 'none';
    const totalShots = layoutConfig[appState.layout].shots;

    for (let i = 0; i < totalShots; i++) {
        appState.currentShotIndex = i;
        updateShotIndicator(i);
        updateProgressBar(i / totalShots);

        const framesPromise = new Promise(async (resolve) => {
            const frames = [];
            const captureInterval = setInterval(() => {
                captureCanvas.width = video.videoWidth;
                captureCanvas.height = video.videoHeight;
                const ctx = captureCanvas.getContext('2d');
                ctx.drawImage(video, 0, 0, video.videoWidth, video.videoHeight);
                frames.push(captureCanvas.toDataURL('image/jpeg', 0.5));
            }, 100);

            await showOverlayText('3');
            await showOverlayText('2');
            await showOverlayText('1');
            await showOverlayText('SMILE!', 500);

            clearInterval(captureInterval);
            resolve(frames.slice(-15));
        });

        const frames = await framesPromise;
        
        flashEffect.classList.add('flash');
        setTimeout(() => flashEffect.classList.remove('flash'), 350);
        
        appState.boomerangCollections.push(frames);
        const staticShotDataUrl = frames[frames.length - 1]; 
        appState.staticShots.push(staticShotDataUrl);
        
        const previewSlot = document.getElementById(`shot-preview-${i}`);
        previewSlot.innerHTML = `<img src="${staticShotDataUrl}" class="w-full h-full object-cover rounded-lg pop-in">`;
        previewSlot.classList.remove('bg-gray-700/50', 'text-gray-500');

        if (i < totalShots - 1) await new Promise(resolve => setTimeout(resolve, 2000));
    }
    
    updateProgressBar(1);
    await new Promise(resolve => setTimeout(resolve, 500));
    await showPreviewScreen();
}

// --- Aspect Ratio-Correct Drawing (Crop to fit) ---
function drawImageCropped(ctx, img, dx, dy, dw, dh) {
    const imgRatio = img.width / img.height;
    const boxRatio = dw / dh;
    let sw = img.width, sh = img.height, sx = 0, sy = 0;
    if (imgRatio > boxRatio) { // Image is wider, crop sides
        sw = img.height * boxRatio;
        sx = (img.width - sw) / 2;
    } else { // Image is taller, crop top/bottom
        sh = img.width / boxRatio;
        sy = (img.height - sh) / 2;
    }
    ctx.drawImage(img, sx, sy, sw, sh, dx, dy, dw, dh);
}

// --- Preview Generation ---
async function generatePreviews() {
    stopAnimation();
    const config = layoutConfig[appState.layout];
    
    // 1. Generate Print Preview
    const printCtx = finalCanvas.getContext('2d');
    const DPI = 300;
    finalCanvas.width = config.w * DPI;
    finalCanvas.height = config.h * DPI;
    printCtx.fillStyle = 'white';
    printCtx.fillRect(0,0, finalCanvas.width, finalCanvas.height);
    
    const staticImages = await Promise.all(appState.staticShots.map(src => new Promise(res => { const i = new Image(); i.onload = () => res(i); i.src = src; })));
    
    config.guides.forEach((g, i) => {
        if(staticImages[i]) {
            drawImageCropped(printCtx, staticImages[i], finalCanvas.width * g.x, finalCanvas.height * g.y, finalCanvas.width * g.w, finalCanvas.height * g.h);
        }
    });

    if (appState.frame) {
        const frameImg = await new Promise(res => { const i = new Image(); i.onload = () => res(i); i.src = appState.frame; });
        printCtx.drawImage(frameImg, 0, 0, finalCanvas.width, finalCanvas.height);
    }
    appState.finalImage = finalCanvas.toDataURL('image/png');
    printPreviewContainer.innerHTML = `<img src="${appState.finalImage}" alt="Print Preview" class="max-w-full max-h-full object-contain mx-auto rounded-md shadow-lg"/>`;

    // 2. Setup Live Layout Boomerang
    boomerangPreviewContainer.innerHTML = `<canvas id="boomerang-main-canvas"></canvas>`;
    const boomerangCanvas = document.getElementById('boomerang-main-canvas');
    if (!boomerangCanvas) return;
    
    // **NEW RESPONSIVE SIZING LOGIC**
    const container = boomerangPreviewContainer;
    const containerW = container.clientWidth;
    const containerH = container.clientHeight;
    const layoutRatio = config.w / config.h;

    let canvasW = containerW;
    let canvasH = containerW / layoutRatio;

    if (canvasH > containerH) {
        canvasH = containerH;
        canvasW = containerH * layoutRatio;
    }
    boomerangCanvas.style.width = `${canvasW}px`;
    boomerangCanvas.style.height = `${canvasH}px`;
    boomerangCanvas.width = canvasW;
    boomerangCanvas.height = canvasH;
    
    const boomerangCtx = boomerangCanvas.getContext('2d');
    const allFrameImages = await Promise.all(appState.boomerangCollections.map(collection => 
        Promise.all(collection.map(src => new Promise(res => { const i = new Image(); i.onload = () => res(i); i.src = src; })))
    ));

    if (appState.frame) {
        appState.frameImage = await new Promise(res => { const i = new Image(); i.onload = () => res(i); i.src = appState.frame; });
    } else {
        appState.frameImage = null;
    }
    
    let frameIndex = 0;
    let direction = 1;
    const longestCollectionLength = Math.max(0, ...allFrameImages.map(c => c.length));
    
    appState.animationIntervalId = setInterval(() => {
        boomerangCtx.clearRect(0, 0, boomerangCanvas.width, boomerangCanvas.height);
        
        config.guides.forEach((g, i) => {
            const currentCollection = allFrameImages[i];
            if (currentCollection && currentCollection.length > 0) {
                const img = currentCollection[frameIndex % currentCollection.length];
                drawImageCropped(boomerangCtx, img, boomerangCanvas.width * g.x, boomerangCanvas.height * g.y, boomerangCanvas.width * g.w, boomerangCanvas.height * g.h);
            }
        });

        if (appState.frameImage) {
            boomerangCtx.drawImage(appState.frameImage, 0, 0, boomerangCanvas.width, boomerangCanvas.height);
        }

        frameIndex += direction;
        if (frameIndex >= longestCollectionLength - 1 || frameIndex <= 0) {
            direction *= -1;
            if(frameIndex < 0) frameIndex = 0;
        }
    }, 100); // 100ms interval = 10fps (normal speed)
}

async function showPreviewScreen() {
    await showOverlayText('Processing...', 1500);
    showScreen('preview');
    await new Promise(resolve => setTimeout(resolve, 50)); // Delay for container to get dimensions
    await generatePreviews();
    showPreviewTab('boomerang');
    stopCamera();
    backToFrameBtn.style.display = 'inline-flex';
}

function showPreviewTab(tabName) {
    stopAnimation();
    if (tabName === 'boomerang') {
        boomerangPreviewContainer.classList.remove('hidden');
        printPreviewContainer.classList.add('hidden');
        boomerangTab.classList.add('text-amber-400', 'border-amber-400');
        printTab.classList.remove('text-amber-400', 'border-amber-400');
        printTab.classList.add('text-gray-400');
        generatePreviews();
    } else {
        boomerangPreviewContainer.classList.add('hidden');
        printPreviewContainer.classList.remove('hidden');
        boomerangTab.classList.remove('text-amber-400', 'border-amber-400');
        printTab.classList.add('text-amber-400', 'border-amber-400');
        boomerangTab.classList.add('text-gray-400');
    }
}

function printResultImage() {
    const config = layoutConfig[appState.layout];
    if (!config || !appState.finalImage) return;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`<html><head><title>Print Marahuyo Photo</title><style>@media print {@page{size:${config.w}in ${config.h}in;margin:0;}body{margin:0;}img{width:100%;height:100%;display:block;}}</style></head><body onload="window.print();window.close();"><img src="${appState.finalImage}"/></body></html>`);
    printWindow.document.close();
}

function resetApp() {
    stopCamera();
    appState = { layout: null, frame: null, staticShots: [], boomerangCollections: [], finalImage: null, animationIntervalId: null, currentShotIndex: 0, frameImage: null };
    showScreen('start');
}

// --- Event Listeners ---
layoutButtons.forEach(b => b.addEventListener('click', () => { appState.layout = b.dataset.layout; loadFrames(); showScreen('frame'); }));
backToStartBtn.addEventListener('click', () => showScreen('start'));
backToFrameBtn.addEventListener('click', () => { stopCamera(); showScreen('frame'); });
retakeBtn.addEventListener('click', setupBooth);

confirmSaveBtn.addEventListener('click', () => {
    stopAnimation();
    const resultContainer = document.getElementById('result-container');
    resultContainer.innerHTML = `<img src="${appState.finalImage}" alt="Final Photo" class="max-w-full max-h-full mx-auto rounded-md shadow-lg object-contain"/>`;
    downloadBtn.href = appState.finalImage;
    
    fetch('admin/save_image.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'imageData=' + encodeURIComponent(appState.finalImage) })
    .then(res => res.text()).then(data => console.log('Image saved:', data)).catch(error => console.error('Error saving image:', error));
    
    showScreen('result');
});

restartBtn.addEventListener('click', resetApp);
printBtn.addEventListener('click', printResultImage);
boomerangTab.addEventListener('click', () => showPreviewTab('boomerang'));
printTab.addEventListener('click', () => showPreviewTab('print'));