<?php
/**
 * download.php
 * Handles secure file delivery and automatic cleanup of temporary files.
 */

$file = $_GET['file'] ?? '';
$original = $_GET['orig'] ?? '';


if (empty($file)) {
    header("HTTP/1.1 400 Bad Request");
    die("Error: No file specified.");
}

// Security: Prevent directory traversal
$safeFileName = basename($file);
$safeOriginalName = basename($original);

$filePath = __DIR__ . '/output/' . $safeFileName;
$originalPath = __DIR__ . '/uploads/' . $safeOriginalName;

if (file_exists($filePath)) {
    // Set headers for download
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $safeFileName . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filePath));
    
    // Register shutdown function to delete files after delivery
    register_shutdown_function(function() use ($filePath, $originalPath) {
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
        if (!empty($originalPath) && file_exists($originalPath)) {
            @unlink($originalPath);
        }
    });

    // Clear output buffer and stream file
    if (ob_get_level()) ob_end_clean();
    readfile($filePath);
    exit;
} else {
    header("HTTP/1.1 404 Not Found");
    echo "Error: The requested file has already been deleted or does not exist.";
}
