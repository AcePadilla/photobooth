<?php
// Kunin ang filename mula sa URL (e.g., print_image.php?file=uploads/xxxxx.png)
$file_path = $_GET['file'] ?? '';

// --- Basic Security Check ---
// 1. Siguraduhing hindi empty ang file path
// 2. Siguraduhing nagsisimula ito sa 'uploads/' para maiwasan ang access sa ibang folders
// 3. Siguraduhing existing ang file
if (empty($file_path) || strpos($file_path, '../uploads/') !== 0 || !file_exists($file_path)) {
    die("Error: Invalid or missing image file.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Photo - Marahuyo</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #333;
        }

        /* Container para sa screen view */
        .image-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100vw;
            height: 100vh;
        }

        /* Style ng image sa screen at print */
        #printable-image {
            /* Tinitiyak na hindi lalagpas sa container */
            max-width: 95%;
            max-height: 95%;
            
            /* ITO ANG SUSI: Pinapanatili ang aspect ratio */
            object-fit: contain; 
            
            /* Optional: para mas maganda sa screen */
            border: 5px solid white;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
        }

        /* Styles na para lang sa pag-PRINT */
        @media print {
            /* Itago ang lahat maliban sa kailangan i-print */
            body * {
                visibility: hidden;
            }
            .image-container, #printable-image {
                visibility: visible;
            }
            
            /* I-reset ang styles para sa papel */
            body {
                background-color: #fff; /* White background sa papel */
            }
            .image-container {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                padding: 0;
                margin: 0;
            }
            #printable-image {
                border: none;
                box-shadow: none;
                /* Siguraduhing kasya sa printable area ng papel */
                max-width: 100%;
                max-height: 100vh; /* Buong taas ng page */
            }
            /* Itakda ang orientation base sa aspect ratio (optional but recommended) */
            @page {
                size: auto; /* Awtomatikong pipiliin ng browser ang landscape o portrait */
                margin: 2mm; /* Maliit na margin sa gilid */
            }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="image-container">
        <img id="printable-image" src="<?php echo htmlspecialchars($browser_path); ?>" alt="Photo to print">
    </div>

    <script>
        // Pagkatapos mag-print (o i-cancel), awtomatikong isara ang tab/window
        let print_finished = false;
        window.onafterprint = function() {
            print_finished = true;
            window.close();
        };
        setTimeout(function() {
            if (!print_finished) {
                window.close();
            }
        }, 2000); // Isara after 2 seconds kung sakaling hindi gumana ang onafterprint
    </script>

</body>
</html>