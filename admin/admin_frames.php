<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$frame_dir = 'frames/';
$message = '';
$message_type = ''; // 'success' or 'error'

// --- Handle File Upload ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['frame_image'])) {
    $target_file = $frame_dir . basename($_FILES["frame_image"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    if (getimagesize($_FILES["frame_image"]["tmp_name"]) === false) {
        $message = "File is not an image.";
        $message_type = 'error';
    } // Check if file already exists
    elseif (file_exists($target_file)) {
        $message = "Sorry, file already exists.";
        $message_type = 'error';
    } // Allow only PNG format
    elseif ($imageFileType != "png") {
        $message = "Sorry, only PNG files are allowed for transparency.";
        $message_type = 'error';
    } // Try to upload file
    else {
        if (move_uploaded_file($_FILES["frame_image"]["tmp_name"], $target_file)) {
            $message = "The frame ". htmlspecialchars(basename($_FILES["frame_image"]["name"])) . " has been uploaded.";
            $message_type = 'success';
        } else {
            $message = "Sorry, there was an error uploading your file.";
            $message_type = 'error';
        }
    }
}

// --- Handle File Deletion ---
if (isset($_GET['delete'])) {
    $file_to_delete = basename($_GET['delete']);
    $file_path = $frame_dir . $file_to_delete;
    
    // Security check: ensure file is within the frames directory
    if (file_exists($file_path) && strpos(realpath($file_path), realpath($frame_dir)) === 0) {
        unlink($file_path);
        $message = "Frame '" . htmlspecialchars($file_to_delete) . "' has been deleted.";
        $message_type = 'success';
    }
}

$frames = glob($frame_dir . '*.png');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Manage Frames</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="bg-gray-800 text-white font-sans">
    <nav class="bg-gray-900 p-4 shadow-lg flex justify-between items-center">
        <div>
            <a href="admin_gallery.php" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Photo Gallery</a>
            <a href="admin_frames.php" class="bg-amber-500 text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Manage Frames</a>
        </div>
        <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Log Out</a>
    </nav>

    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold mb-6">Manage Frame Templates</h1>

        <div class="bg-gray-900 p-6 rounded-lg mb-8">
            <h2 class="text-xl font-semibold mb-4">Upload New Frame</h2>
            <form action="admin_frames.php" method="post" enctype="multipart/form-data">
                <p class="text-gray-400 mb-4">Select a PNG image with a transparent center to upload.</p>
                <div class="flex items-center space-x-4">
                    <input type="file" name="frame_image" id="frame_image" class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100" required>
                    <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-gray-900 font-bold py-2 px-6 rounded-full">Upload</button>
                </div>
            </form>
            <?php if ($message): ?>
                <div class="mt-4 p-3 rounded-md text-sm <?php echo $message_type === 'success' ? 'bg-green-600/50 text-green-200' : 'bg-red-600/50 text-red-200'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
        </div>

        <div>
            <h2 class="text-xl font-semibold mb-4">Available Frames</h2>
            <?php if (empty($frames)): ?>
                <p class="text-gray-400">No frames have been uploaded yet.</p>
            <?php else: ?>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
                    <?php foreach ($frames as $frame): ?>
                        <div class="bg-gray-900 rounded-lg p-3 group relative">
                            <img src="<?php echo $frame; ?>" alt="Frame template" class="w-full h-auto rounded-md bg-cover" style="background-image: url('https://via.placeholder.com/300/4B5563/FFFFFF?text=PREVIEW');">
                            <a href="?delete=<?php echo basename($frame); ?>" onclick="return confirm('Are you sure you want to delete this frame?');" class="absolute top-2 right-2 bg-red-600/80 hover:bg-red-500 rounded-full p-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <span class="material-icons text-white">delete</span>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>