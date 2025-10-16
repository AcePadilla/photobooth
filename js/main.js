// --- Element Selectors ---
const screens = { start: document.getElementById('start-screen'), frame: document.getElementById('frame-screen'), booth: document.getElementById('booth-screen'), preview: document.getElementById('preview-screen'), result: document.getElementById('result-screen'),};
const layoutButtons = document.querySelectorAll('.layout-btn');
const backToStartBtn = document.getElementById('back-to-start-btn');
const frameOptionsContainer = document.getElementById('frame-options-container');
const backToLayoutBtn = document.getElementById('back-to-layout-btn');
const startCaptureBtn = document.getElementById('start-capture-btn');
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
const previewContainer = document.getElementById('preview-container');
const resultContainer = document.getElementById('result-container');
const captureCanvas = document.getElementById('capture-canvas');
const finalCanvas = document.getElementById('final-canvas');

// --- State and Config (UPDATED) ---
const layoutConfig = {
    'strip-3': { 
        shots: 3, w: 2, h: 6, 
        guides: [
            { x: 0.075, y: 0.025, w: 0.85, h: 0.3 }, 
            { x: 0.075, y: 0.35, w: 0.85, h: 0.3 }, 
            { x: 0.075, y: 0.675, w: 0.85, h: 0.3 }
        ] 
    },
    'strip-4': { // NEW LAYOUT
        shots: 4, w: 2, h: 8, // Longer 2x8 inch print for 4 shots
        guides: [
            { x: 0.06, y: 0.02, w: 0.88, h: 0.22 },
            { x: 0.06, y: 0.26, w: 0.88, h: 0.22 },
            { x: 0.06, y: 0.50, w: 0.88, h: 0.22 },
            { x: 0.06, y: 0.74, w: 0.88, h: 0.22 }
        ]
    },
    'grid-4': { 
        shots: 4, w: 4, h: 6, 
        guides: [
            { x: 0.05, y: 0.033, w: 0.425, h: 0.45 }, { x: 0.525, y: 0.033, w: 0.425, h: 0.45 }, 
            { x: 0.05, y: 0.517, w: 0.425, h: 0.45 }, { x: 0.525, y: 0.517, w: 0.425, h: 0.45 }
        ] 
    },
    'spotlight-3': { 
        shots: 3, w: 6, h: 4, 
        guides: [
            { x: 0.033, y: 0.05, w: 0.6, h: 0.9 }, 
            { x: 0.666, y: 0.05, w: 0.3, h: 0.43 }, 
            { x: 0.666, y: 0.52, w: 0.3, h: 0.43 }
        ] 
    }
};
let selectedLayout = null;
let selectedFrame = null;
const capturedShots = [];
let finalImageDataUrl = null;

// --- Core Functions ---
function showScreen(screenName) { Object.values(screens).forEach(s => s.classList.add('hidden')); screens[screenName].classList.remove('hidden'); screens[screenName].classList.add('fade-in'); }

async function loadFrames() {
    try {
        const response = await fetch(`admin/get_frames.php?layout=${selectedLayout}`);
        const framePaths = await response.json();
        frameOptionsContainer.innerHTML = `<button data-frame="none" class="frame-btn group bg-gray-800 p-4 rounded-lg border-2 border-gray-700 hover:border-amber-400 transition-all duration-300 transform hover:-translate-y-2 flex flex-col items-center justify-center"><span class="material-icons text-5xl text-gray-500 group-hover:text-amber-300">block</span><h3 class="text-lg font-semibold mt-3">No Frame</h3></button>`;
        framePaths.forEach(path => {
            const frameBtn = document.createElement('button'); frameBtn.dataset.frame = path; frameBtn.className = 'frame-btn group bg-gray-800 p-2 rounded-lg border-2 border-gray-700 hover:border-amber-400 transition-all duration-300 transform hover:-translate-y-2';
            frameBtn.innerHTML = `<img src="admin/${path}" class="w-full h-full object-contain">`;
            frameOptionsContainer.appendChild(frameBtn);
        });
        document.querySelectorAll('.frame-btn').forEach(button => button.addEventListener('click', () => { selectedFrame = button.dataset.frame === 'none' ? null : `admin/${button.dataset.frame}`; startCamera(); }));
    } catch (error) {
        console.error('Failed to load frames:', error);
        document.querySelector('.frame-btn[data-frame="none"]').addEventListener('click', () => { selectedFrame = null; startCamera(); });
    }
}

function generatePreviewPlaceholders(shotsNeeded) {
    shotPreviewsContainer.innerHTML = '';
    for (let i = 1; i <= shotsNeeded; i++) {
        const slot = document.createElement('div'); slot.className = 'preview-slot bg-gray-700/50 rounded-lg aspect-[4/3] flex items-center justify-center flex-col text-gray-500';
        slot.innerHTML = `<span class="material-icons text-4xl">photo_camera</span><span class="font-semibold mt-1">Shot ${i}</span>`;
        shotPreviewsContainer.appendChild(slot);
    }
}

async function startCamera() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: { width: 1280, height: 720, facingMode: 'user' }, audio: false });
        video.srcObject = stream; await video.play();
        generatePreviewPlaceholders(layoutConfig[selectedLayout].shots); showScreen('booth');
    } catch (err) { console.error("Camera access error:", err); alert("Could not access the camera."); resetBooth(); }
}

function updateShotIndicator(current, total) { shotIndicator.innerHTML = `📸 Shot ${current} of ${total}`; }
function showOverlayText(text, duration = 1000) { return new Promise(resolve => { overlayText.innerText = text; overlayText.style.display = 'flex'; setTimeout(() => { overlayText.style.display = 'none'; resolve(); }, duration); }); }
function triggerFlash() { flashEffect.classList.add('flash'); setTimeout(() => flashEffect.classList.remove('flash'), 350); }

async function startPhotoSequence() {
    startCaptureBtn.disabled = true; startCaptureBtn.style.cursor = 'not-allowed'; backToLayoutBtn.style.display = 'none';
    capturedShots.length = 0;
    const shotsNeeded = layoutConfig[selectedLayout].shots;
    for (let i = 0; i < shotsNeeded; i++) {
        updateShotIndicator(i + 1, shotsNeeded);
        await new Promise(resolve => setTimeout(resolve, 1500));
        await showOverlayText('3'); await showOverlayText('2'); await showOverlayText('1');
        triggerFlash();
        const context = captureCanvas.getContext('2d'); captureCanvas.width = video.videoWidth; captureCanvas.height = video.videoHeight;
        context.drawImage(video, 0, 0, captureCanvas.width, captureCanvas.height);
        const currentShotDataUrl = captureCanvas.toDataURL('image/jpeg', 0.9);
        capturedShots.push(currentShotDataUrl);
        shotPreviewsContainer.children[i].innerHTML = `<img src="${currentShotDataUrl}" class="w-full h-full object-cover rounded-lg">`;
    }
    shotIndicator.innerHTML = ''; await createFinalImage();
}

async function createFinalImage() {
    await showOverlayText('Processing high-quality print...', 2500);
    const ctx = finalCanvas.getContext('2d');
    const DPI = 300;
    const config = layoutConfig[selectedLayout];
    if (!config) { console.error("Invalid layout:", selectedLayout); return; }

    finalCanvas.width = config.w * DPI;
    finalCanvas.height = config.h * DPI;

    const imagePromises = capturedShots.map(src => new Promise(resolve => { const img = new Image(); img.onload = () => resolve(img); img.src = src; }));
    const images = await Promise.all(imagePromises);

    config.guides.forEach((g, i) => { if (images[i]) { ctx.drawImage(images[i], finalCanvas.width * g.x, finalCanvas.height * g.y, finalCanvas.width * g.w, finalCanvas.height * g.h); } });

    if (selectedFrame) {
        try {
            const frameImg = await new Promise((resolve, reject) => { const img = new Image(); img.onload = () => resolve(img); img.onerror = reject; img.src = selectedFrame; });
            ctx.drawImage(frameImg, 0, 0, finalCanvas.width, finalCanvas.height);
        } catch (error) { console.error("Failed to draw frame:", error); }
    }
    finalImageDataUrl = finalCanvas.toDataURL('image/png');
    previewContainer.innerHTML = `<img src="${finalImageDataUrl}" alt="Preview" class="max-w-full mx-auto rounded-md shadow-lg"/>`;
    showScreen('preview');
}

function printResultImage() {
    const config = layoutConfig[selectedLayout];
    if (!config) return;
    const dims = { width: `${config.w}in`, height: `${config.h}in` };
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`<html><head><title>Print Marahuyo Photo</title><style>body,html{margin:0;padding:0;}@page{size:auto;margin:0;}#print-container{width:${dims.width};height:${dims.height};}#print-container img{width:100%;height:100%;display:block;}</style></head><body onload="window.print();window.close();"><div id="print-container"><img src="${finalImageDataUrl}"/></div></body></html>`);
    printWindow.document.close();
}

function resetBooth() {
    capturedShots.length = 0; finalImageDataUrl = null; selectedLayout = null; selectedFrame = null;
    startCaptureBtn.disabled = false; startCaptureBtn.style.cursor = 'pointer'; backToLayoutBtn.style.display = 'inline-flex';
    shotIndicator.innerHTML = ''; shotPreviewsContainer.innerHTML = '';
    if (video.srcObject) { video.srcObject.getTracks().forEach(track => track.stop()); video.srcObject = null; }
    showScreen('start');
}

// --- Event Listeners ---
layoutButtons.forEach(button => {
    button.addEventListener('click', () => {
        selectedLayout = button.dataset.layout; 
        loadFrames(); 
        showScreen('frame');
    });
});

backToStartBtn.addEventListener('click', () => showScreen('start'));
backToLayoutBtn.addEventListener('click', resetBooth);
startCaptureBtn.addEventListener('click', startPhotoSequence);
retakeBtn.addEventListener('click', () => {
    generatePreviewPlaceholders(layoutConfig[selectedLayout].shots);
    backToLayoutBtn.style.display = 'inline-flex';
    startCaptureBtn.disabled = false;
    startCaptureBtn.style.cursor = 'pointer';
    showScreen('booth');
});
confirmSaveBtn.addEventListener('click', () => {
    resultContainer.innerHTML = `<img src="${finalImageDataUrl}" alt="Final Photo" class="max-w-full mx-auto rounded-md shadow-lg"/>`;
    downloadBtn.href = finalImageDataUrl;
    fetch('admin/save_image.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'imageData=' + encodeURIComponent(finalImageDataUrl) })
    .then(res => res.text()).then(data => console.log('Image saved:', data)).catch(error => console.error('Error saving image:', error));
    showScreen('result');
});
restartBtn.addEventListener('click', resetBooth);
printBtn.addEventListener('click', printResultImage);
