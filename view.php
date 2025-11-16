<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download Your Photo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
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
            background-color: rgba(17, 24, 39, 0.7); /* Mas solid ng konti */
            backdrop-filter: blur(12px);
            background-image: radial-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 0);
            background-size: 30px 30px;
        }
        
        /* --- IMPROVED BUTTONS (UI/UX) --- */
        .btn {
            /* Mas smooth na transition */
            transition: all 0.2s ease-in-out;
            border-radius: 9999px;
            text-decoration: none;
            /* UX: Added active state for tap feedback */
            transform-origin: center;
        }
        .btn:active {
            transform: scale(0.95); /* Feedback kapag na-tap */
        }
        .btn:hover {
            transform: translateY(-2px); /* Mas kitang hover */
        }
        .btn-primary {
            background: linear-gradient(45deg, #ef4444, #dc2626);
            color: white;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }
        .btn-primary:hover {
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4); /* Improved hover */
        }
        .btn-secondary {
            background-color: #374151; 
            color: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        .btn-secondary:hover {
            background-color: #4b5563; 
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3); /* Improved hover */
        }
        /* Mas malaking icon ng konti */
        .material-icons.icon-xs { font-size: 16px; }
    </style>
</head>
<body class="text-white flex items-center justify-center min-h-screen p-2">

    <div class="w-full max-w-md mx-auto text-center card-bg rounded-2xl shadow-2xl p-3">
        
        <img src="public/images/nobglogo.png" alt="Logo" class="w-20 mx-auto mb-2">
        
        <h1 class="text-base font-bold mb-2 text-gray-100">Your Photos Are Ready!</h1>

        <?php
            $image_name_png = '';
            if (isset($_GET['image'])) {
                $image_name_png = basename($_GET['image']);
            }
            $png_path = 'uploads/' . $image_name_png;
            $image_name_gif = str_replace('.png', '.gif', $image_name_png);
            $gif_path = 'uploads/' . $image_name_gif;

            $png_exists = !empty($image_name_png) && file_exists($png_path);
            $gif_exists = !empty($image_name_gif) && file_exists($gif_path);

            if ($png_exists && $gif_exists) {
                echo '<div classmax-w-lg mx-auto">';
                /* IMPROVED UI: Mas maluwag na gap (gap-2.5) */
                echo '<div class="grid grid-cols-2 gap-2.5">';

                echo '
                        <div> 
                            <h2 class="text-sm font-semibold text-gray-300 mb-1">Print Layout</h2>
                            <div class="bg-black/20 p-1.5 rounded-lg shadow-lg mb-2">
                                <img src="' . htmlspecialchars($png_path) . '" alt="Print Layout" class="w-full h-auto rounded-md">
                            </div>
                            <a 
                                href="' . htmlspecialchars($png_path) . '" 
                                download="' . htmlspecialchars($image_name_png) . '"
                                class="btn btn-primary py-1.5 px-2 text-xs leading-snug inline-flex items-center justify-center gap-1 w-full">
                                <span class="material-icons icon-xs">download</span>
                                Download Print
                            </a>
                        </div>
                ';

                echo '
                        <div>
                            <h2 class="text-sm font-semibold text-gray-300 mb-1">Boomerang</h2>
                            <div class="bg-black/20 p-1.5 rounded-lg shadow-lg mb-2">
                                <img src="' . htmlspecialchars($gif_path) . '" alt="Boomerang" class="w-full h-auto rounded-md">
                            </div>
                            <a 
                                href="' . htmlspecialchars($gif_path) . '" 
                                download="' . htmlspecialchars($image_name_gif) . '"
                                class="btn btn-secondary py-1.5 px-2 text-xs leading-snug inline-flex items-center justify-center gap-1 w-full">
                                <span class="material-icons icon-xs">gif</span>
                                Download GIF
                            </a>
                        </div>
                ';

                echo '</div>';
                echo '</div>';

            } 
            else if ($png_exists) {
                echo '
                        <div class="mb-1 max-w-[280px] mx-auto"> 
                            <h2 class="text-sm font-semibold text-gray-300 mb-1">Print Layout</h2>
                            <div class="bg-black/20 p-1.5 rounded-lg shadow-lg mb-2">
                                <img src="' . htmlspecialchars($png_path) . '" alt="Print Layout" class="w-full h-auto rounded-md">
                            </div>
                            <a 
                                href="' . htmlspecialchars($png_path) . '" 
                                download="' . htmlspecialchars($image_name_png) . '"
                                class="btn btn-primary py-1.5 px-3 text-xs leading-snug inline-flex items-center justify-center gap-1 w-auto">
                                <span class="material-icons icon-xs">download</span>
                                Download Print
                            </a>
                        </div>
                ';
            } 
            else if ($gif_exists) {
                echo '
                        <div class="mb-1 max-w-[280px] mx-auto">
                            <h2 class="text-sm font-semibold text-gray-300 mb-1">Boomerang</h2>
                            <div class="bg-black/20 p-1.5 rounded-lg shadow-lg mb-2">
                                <img src="' . htmlspecialchars($gif_path) . '" alt="Boomerang" class="w-full h-auto rounded-md">
                            </div>
                            <a 
                                href="' . htmlspecialchars($gif_path) . '" 
                                download="' . htmlspecialchars($image_name_gif) . '"
                                class="btn btn-secondary py-1.5 px-3 text-xs leading-snug inline-flex items-center justify-center gap-1 w-auto">
                                <span class="material-icons icon-xs">gif</span>
                                Download GIF
                            </a>
                        </div>
                ';
            } 
            else {
                echo '
                        <h1 class="text-lg font-bold mb-1 text-yellow-400">Oops!</h1>
                        <p class="text-sm">Photo not found. Please try again.</p>
                ';
            }
        ?>
    </div>

</body>
</html>