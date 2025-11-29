<?php
    // CSP Headers (Standard Security)
    $csp = "default-src 'self'; " .
           "script-src 'self' https://cdn.tailwindcss.com https://unpkg.com 'unsafe-inline'; " .
           "style-src 'self' https://fonts.googleapis.com https://unpkg.com 'unsafe-inline'; " .
           "font-src 'self' https://fonts.googleapis.com https://fonts.gstatic.com; " .
           "img-src 'self' data:; " .
           "media-src 'self' blob: data:; " .
           "object-src 'none'; " .
           "frame-ancestors 'none';";
    header("Content-Security-Policy: " . $csp);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>point five by Marahuyo</title>
    
    <link rel="icon" type="image/png" href="public/images/blackbg.jpg">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        /* --- GLOBAL THEME --- */
        body { 
            font-family: 'Poppins', sans-serif; 
            background-color: #050505;
            background-image: 
                radial-gradient(circle at 50% 0%, #450a0a 0%, transparent 60%),
                linear-gradient(0deg, #000000 0%, #1a0505 100%);
            background-attachment: fixed;
            overflow-x: hidden;
            color: white;
            overscroll-behavior: none;
        }

        .font-bauhaus { font-family: 'Bauhaus 93', 'Arial Black', sans-serif; font-weight: normal; }

        .photobooth-card {
            background: rgba(10, 10, 10, 0.9);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(220, 38, 38, 0.3);
            box-shadow: 0 0 30px rgba(220, 38, 38, 0.1);
            border-radius: 16px;
        }

        /* --- SPLASH SCREEN STYLES --- */
        #splash-screen {
            position: fixed; inset: 0; z-index: 9999;
            background-color: #050505;
            background-image: radial-gradient(circle at 50% 50%, #2a0101 0%, #000000 100%);
            transition: opacity 0.8s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.8s;
            display: flex; flex-col; justify-center; items-center;
        }
        #splash-screen.fade-out { opacity: 0; visibility: hidden; }
        
        /* Logo Animation */
        .splash-logo {
            opacity: 0; transform: scale(0.8);
            transition: opacity 1s ease-out, transform 1s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .splash-logo.visible { opacity: 1; transform: scale(1); }

        /* Text Styles */
        .brand-name { color: #ef4444; font-weight: 700; text-shadow: 0 0 25px rgba(220, 38, 38, 0.8); }
        .typing-text { display: inline-block; min-height: 1.5em; }

        /* Blinking Cursor */
        .cursor {
            display: inline-block; background-color: #ef4444; margin-left: 3px; width: 3px; height: 1.2em;
            vertical-align: middle; animation: blink 0.9s infinite;
        }
        .brand-name .cursor { height: 1em; width: 6px; background-color: #ef4444; }
        @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0; } }

        /* --- BUTTONS --- */
        .btn { transition: all 0.2s; border-radius: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; text-decoration: none; }
        .btn:active { transform: scale(0.97); }
        .btn-primary { background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); color: white; border: 1px solid #ef4444; box-shadow: 0 2px 10px rgba(220, 38, 38, 0.3); }
        .btn-secondary { background: rgba(255,255,255,0.05); color: #e5e5e5; border: 1px solid #525252; }

        /* --- PRINTER ANIMATION --- */
        .printer-chassis {
            height: 20px; background: linear-gradient(180deg, #262626 0%, #171717 100%);
            border-radius: 6px 6px 0 0; display: flex; align-items: center; justify-content: center;
            border: 1px solid #404040; border-bottom: none;
        }
        .status-light { width: 6px; height: 6px; background-color: #ef4444; border-radius: 50%; box-shadow: 0 0 5px #ef4444; animation: pulse 1.5s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        .printer-slot { height: 4px; background-color: #000; border-left: 1px solid #404040; border-right: 1px solid #404040; position: relative; z-index: 20; box-shadow: 0 4px 8px rgba(0,0,0,0.8); }
        .print-output-area { background-color: rgba(0,0,0,0.3); border-radius: 0 0 8px 8px; border: 1px solid #404040; border-top: none; overflow: hidden; position: relative; z-index: 10; }
        
        .photo-to-print { transform: translateY(-110%); opacity: 0; }
        @keyframes slide-down { 
            0% { transform: translateY(-110%); opacity: 1; } 
            100% { transform: translateY(0); opacity: 1; } 
        }
        .print-animation { animation: slide-down 2.5s cubic-bezier(0.25, 1, 0.5, 1) forwards; }
    </style>
</head>
<body class="flex flex-col justify-start items-center min-h-screen p-3 pt-6 md:justify-center">

    <div id="splash-screen" class="flex flex-col justify-center items-center text-center p-6">
        <div class="max-w-xs mx-auto flex flex-col items-center">
            <img id="splash-logo" src="public/images/nobglogo.png" alt="Logo" class="splash-logo w-24 mb-8 drop-shadow-2xl">
            
            <div class="h-6 mb-2">
                <h1 id="splash-line-1" class="text-xs md:text-sm text-gray-400 tracking-widest uppercase font-medium typing-text"></h1>
            </div>
            
            <div class="h-12">
                <h2 id="splash-line-2" class="text-3xl md:text-4xl brand-name font-bauhaus tracking-tighter typing-text"></h2>
            </div>
        </div>
    </div>

    <div id="main-wrapper" style="visibility: hidden;" class="w-full max-w-4xl mx-auto flex flex-col h-[90vh] md:h-auto justify-center">
        
        <div class="photobooth-card p-4 md:p-8 w-full flex flex-col" data-aos="fade-up" data-aos-delay="100">
            
            <div class="text-center mb-4 flex-shrink-0">
                <img src="public/images/nobglogo.png" alt="Logo" class="w-12 md:w-16 mx-auto mb-2 opacity-90" data-aos="fade-in">
                <h1 class="text-lg md:text-2xl font-bold text-white uppercase tracking-tight">Your Memories</h1>
            </div>

            <?php
                $image_name_png = '';
                $image_name_video = ''; 
                $png_path = '';
                $video_path = '';
                $png_exists = false;
                $video_exists = false;

                if (isset($_GET['image'])) {
                    $filename_input = basename($_GET['image']);
                    if (preg_match('/^[a-zA-Z0-9_-]+\.png$/', $filename_input)) {
                        $image_name_png = $filename_input;
                    }
                }

                if (!empty($image_name_png)) {
                    $png_path = 'uploads/' . $image_name_png;
                    $upload_dir = realpath(__DIR__ . '/uploads');
                    $requested_file_path = realpath($png_path);

                    if ($requested_file_path && strpos($requested_file_path, $upload_dir) === 0 && file_exists($requested_file_path)) {
                        $png_exists = true;
                        $image_name_video = str_replace('.png', '.mp4', $image_name_png);
                        $video_path = 'uploads/' . $image_name_video;
                        if (file_exists($video_path)) $video_exists = true;
                    }
                }

                if ($png_exists) {
                    // Side-by-side grid (responsive)
                    echo '<div class="grid grid-cols-2 gap-3 md:gap-8 items-end overflow-hidden">';

                    // 1. PRINT LAYOUT
                    echo '<div>';
                    echo '  <div class="printer-chassis">
                                <span class="text-[8px] md:text-[10px] text-gray-500 uppercase tracking-widest mr-1">Photo</span>
                                <div class="status-light"></div>
                            </div>';
                    echo '  <div class="printer-slot"></div>';
                    echo '  <div class="print-output-area p-1.5 bg-black/40">';
                    echo '      <img src="' . htmlspecialchars($png_path, ENT_QUOTES, 'UTF-8') . '" alt="Print Layout" 
                                     class="w-full max-h-[50vh] object-contain mx-auto rounded shadow-lg photo-to-print border border-gray-800" 
                                     data-print-delay="0.2s">';
                    echo '  </div>';
                    echo '  <a href="' . htmlspecialchars($png_path, ENT_QUOTES, 'UTF-8') . '" download="' . htmlspecialchars($image_name_png, ENT_QUOTES, 'UTF-8') . '"
                               class="mt-2 btn btn-primary py-2 px-2 w-full flex items-center justify-center gap-1 text-[10px] md:text-sm shadow-lg">
                                <span class="material-icons text-sm">download</span> Save
                            </a>';
                    echo '</div>';

                    // 2. BOOMERANG
                    if ($video_exists) {
                        echo '<div>';
                        echo '  <div class="printer-chassis">
                                    <span class="text-[8px] md:text-[10px] text-gray-500 uppercase tracking-widest mr-1">Video</span>
                                    <div class="status-light" style="background-color:#3b82f6;box-shadow:0 0 4px #3b82f6;"></div>
                                </div>';
                        echo '  <div class="printer-slot"></div>';
                        echo '  <div class="print-output-area p-1.5 bg-black/40">';
                        echo '      <video src="' . htmlspecialchars($video_path, ENT_QUOTES, 'UTF-8') . '" 
                                           autoplay loop muted playsinline type="video/mp4"
                                           class="w-full max-h-[50vh] object-contain mx-auto rounded shadow-lg photo-to-print border border-gray-800" 
                                           data-print-delay="0.6s"></video>';
                        echo '  </div>';
                        echo '  <a href="' . htmlspecialchars($video_path, ENT_QUOTES, 'UTF-8') . '" download="' . htmlspecialchars($image_name_video, ENT_QUOTES, 'UTF-8') . '"
                                   class="mt-2 btn btn-secondary py-2 px-2 w-full flex items-center justify-center gap-1 text-[10px] md:text-sm">
                                    <span class="material-icons text-sm">videocam</span> Save
                                </a>';
                        echo '</div>';
                    } else {
                        echo '<div class="flex items-center justify-center h-full text-gray-600 text-xs border border-gray-800 rounded bg-black/20">Video processing...</div>';
                    }

                    echo '</div>'; 
                } else {
                    echo '
                        <div class="text-center py-12">
                            <span class="material-icons text-4xl text-red-500 mb-2">broken_image</span>
                            <h1 class="text-lg font-bold text-white">Not Found</h1>
                            <a href="/" class="mt-4 btn btn-secondary px-4 py-1 text-xs inline-block">Home</a>
                        </div>
                    ';
                }
            ?>
            
            <div class="mt-auto pt-4 text-center flex-shrink-0">
                <p class="text-[10px] text-gray-600 uppercase tracking-wider">© 2025 pointfivebymarahuyo</p>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script>
        // Initialize Animations
        AOS.init({ duration: 600, once: true });

        // Elements
        const splash = document.getElementById('splash-screen');
        const logo = document.getElementById('splash-logo');
        const line1 = document.getElementById('splash-line-1');
        const line2 = document.getElementById('splash-line-2');
        const mainWrapper = document.getElementById('main-wrapper');

        // Typewriter Function
        function typeWriter(element, text, speed, callback) {
            let i = 0;
            element.innerHTML = '<span class="cursor"></span>'; // Start with cursor
            
            function type() {
                if (i < text.length) {
                    // Insert text before the cursor
                    element.innerHTML = text.substring(0, i + 1) + '<span class="cursor"></span>';
                    i++;
                    setTimeout(type, speed);
                } else {
                    // Finish typing
                    element.innerHTML = text; // Remove cursor? Or keep it blinking?
                    // Let's keep cursor for a moment then remove in callback
                    setTimeout(() => {
                         element.innerHTML = text; // Finalize text (remove cursor)
                         if (callback) callback();
                    }, 600);
                }
            }
            type();
        }

        // Sequence Logic
        document.addEventListener('DOMContentLoaded', () => {
            
            // 1. Show Logo after tiny delay
            setTimeout(() => {
                logo.classList.add('visible');
            }, 300);

            // 2. Type Line 1 after Logo
            setTimeout(() => {
                typeWriter(line1, "THANK YOU FOR CAPTURING WITH", 40, () => {
                    
                    // 3. Type Line 2 (Brand) after Line 1
                    typeWriter(line2, "pointfivebymarahuyo", 80, () => {
                        
                        // 4. Wait to read, then Fade Out
                        setTimeout(() => {
                            splash.classList.add('fade-out');
                            
                            // 5. Reveal Main Content
                            setTimeout(() => {
                                splash.style.display = 'none';
                                mainWrapper.style.visibility = 'visible';
                                
                                // Trigger Print Animations
                                document.querySelectorAll('.photo-to-print').forEach(el => {
                                    el.classList.add('print-animation');
                                });
                                
                                AOS.refresh(); // Refresh scroll animations
                            }, 800); // Match CSS transition time

                        }, 1000); // Reading pause
                    });
                });
            }, 1800); // Wait for logo zoom
        });
    </script>
</body>
</html>