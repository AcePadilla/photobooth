<?php
// Kunin ang filename mula sa URL (e.g., print_image.php?file=uploads/xxxxx.png)
$file_path = $_GET['file'] ?? '';

// --- Mas Pinahusay na Security Check ---
// 1. Siguraduhing hindi empty ang file path
// 2. Siguraduhing nagsisimula ito sa 'uploads/' para maiwasan ang access sa ibang folders (Path Traversal Vulnerability)
// 3. Siguraduhing existing ang file
// Ang 'strpos($file_path, 'uploads/') !== 0' ay tinitiyak na ang path ay NAGSISIMULA sa 'uploads/'
if (empty($file_path) || strpos($file_path, '../uploads/') !== 0 || !file_exists($file_path)) {
    die("Error: Invalid, missing, or disallowed image file path.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Drag and Print Photo - Marahuyo</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #333;
            font-family: sans-serif;
            /* Pinipigilan ang user na mag-select ng text habang nagda-drag */
            user-select: none;
        }

        /* Container para sa papel sa screen view */
        .page-container {
            width: 8.5in; /* Standard letter size width */
            height: 11in; /* Standard letter size height */
            margin: 20px auto;
            background-color: white;
            position: relative; /* Mahalaga para sa positioning ng image */
            overflow: hidden; /* Itatago ang anumang parte ng image na lalagpas */
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
        }

        /* Style ng image na idra-drag */
        #draggable-image {
            position: absolute; /* Susi para maigalaw-galaw */
            top: 20px; /* Initial position */
            left: 20px; /* Initial position */
            cursor: grab; /* Para malaman ng user na pwede i-drag */
            max-width: 95%; /* Para hindi lumagpas sa papel initially */
            max-height: 95%;
            object-fit: contain;
        }

        #draggable-image:active {
            cursor: grabbing; /* Cursor habang naka-press ang mouse */
        }
        
        /* Container para sa button */
        .controls {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 100;
        }

        .print-button {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
        }

        /* Styles na para lang sa pag-PRINT */
        @media print {
            /* Itago ang lahat maliban sa page-container at ang laman nito */
            body * {
                visibility: hidden;
            }
            .page-container, .page-container * {
                visibility: visible;
            }
            
            /* I-reset ang styles para magkasya sa tunay na papel */
            .page-container {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none; /* Alisin ang shadow sa print */
            }

            /* Itago ang print button */
            .controls {
                display: none;
            }
            
            @page {
                size: auto; 
                margin: 2mm; /* Maliit na margin sa gilid ng papel */
            }
        }
    </style>
</head>
<body>

    <div class="controls">
        <button onclick="window.print()" class="print-button">Print Image</button>
    </div>

    <div class="page-container">
        <img id="draggable-image" src="<?php echo htmlspecialchars($file_path); ?>" alt="Photo to print">
    </div>

    <script>
        const image = document.getElementById('draggable-image');
        const container = document.querySelector('.page-container');

        let isDragging = false;
        let offsetX, offsetY;

        // Function kapag pinindot ang mouse sa image
        image.addEventListener('mousedown', (e) => {
            isDragging = true;
            // Kinukuha ang initial na layo ng cursor mula sa top-left corner ng image
            offsetX = e.clientX - image.offsetLeft;
            offsetY = e.clientY - image.offsetTop;
            // Pinipigilan ang default browser behavior (gaya ng pag-drag ng ghost image)
            e.preventDefault();
        });

        // Function kapag ginalaw ang mouse (kahit saan sa page)
        document.addEventListener('mousemove', (e) => {
            if (!isDragging) return;

            // Kinakalkula ang bagong pwesto ng image
            let newX = e.clientX - offsetX;
            let newY = e.clientY - offsetY;

            // Kinukuha ang boundaries ng container
            const containerRect = container.getBoundingClientRect();
            const imageRect = image.getBoundingClientRect();
            
            // Tinitiyak na hindi lalagpas ang image sa loob ng container
            if (newX < 0) newX = 0;
            if (newY < 0) newY = 0;
            if (newX + image.width > container.width) newX = container.width - image.width;
            if (newY + image.height > container.height) newY = container.height - image.height;


            // Ina-apply ang bagong pwesto sa style ng image
            image.style.left = `${newX}px`;
            image.style.top = `${newY}px`;
        });

        // Function kapag binitawan ang mouse
        document.addEventListener('mouseup', () => {
            isDragging = false;
        });

        // Para sa pag-close ng window pagkatapos mag-print
        window.onafterprint = function() {
            window.close();
        };

    </script>

</body>
</html>