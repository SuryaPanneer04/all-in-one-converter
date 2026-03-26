<?php
header('Content-Type: application/json');

function return_bytes($val) {
    if (empty($val)) return 0;
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int)$val;
    switch($last) {
        case 'g': $val *= 1024;
        case 'm': $val *= 1024;
        case 'k': $val *= 1024;
    }
    return $val;
}

// 1. Proactive Limit Checks
$maxSize = 200 * 1024 * 1024; // 200MB intended
$uploadLimit = ini_get('upload_max_filesize');
$postLimit = ini_get('post_max_size');
$uploadBytes = return_bytes($uploadLimit);
$postBytes = return_bytes($postLimit);

// Detect if server is capped at something too low (like 2M/8M)
if (($uploadBytes < $maxSize && $uploadBytes < 100 * 1024 * 1024) || ($postBytes < $maxSize && $postBytes < 100 * 1024 * 1024)) {
    echo json_encode([
        'success' => false,
        'message' => "CRITICAL: Server limits too low (Upload: $uploadLimit, Post: $postLimit). Please restart server with: php -S localhost:8000 -d upload_max_filesize=200M -d post_max_size=200M -d max_execution_time=300"
    ]);
    exit;
}

// 2. Upload Directory Setup
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        echo json_encode(['success' => false, 'message' => 'Internal Error: Could not create upload directory. Check permissions.']);
        exit;
    }
}
if (!is_writable($uploadDir)) {
    echo json_encode(['success' => false, 'message' => 'Internal Error: Upload directory is not writable.']);
    exit;
}

// 3. Post Data Validation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0) {
    echo json_encode(['success' => false, 'message' => "File too large for 'post_max_size' ($postLimit). The server rejected the request entirely."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMsg = 'Upload failed. Error code: ' . $file['error'];
        if ($file['error'] === UPLOAD_ERR_INI_SIZE) $errorMsg = "File exceeds 'upload_max_filesize' ($uploadLimit).";
        if ($file['error'] === UPLOAD_ERR_FORM_SIZE) $errorMsg = "File exceeds HTML form limit.";
        if ($file['error'] === UPLOAD_ERR_PARTIAL) $errorMsg = "File was only partially uploaded.";
        if ($file['error'] === UPLOAD_ERR_NO_FILE) $errorMsg = "No file was uploaded.";
        
        echo json_encode(['success' => false, 'message' => $errorMsg]);
        exit;
    }

    if ($file['size'] > $maxSize) {
        echo json_encode(['success' => false, 'message' => 'File exceeds the 200MB system limit.']);
        exit;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExts = [
        'pdf', 'doc', 'docx', 'txt', 'csv', 'odt', 'rtf',
        'mp4', 'avi', 'mov', 'mkv', 'webm', 'flv', '3gp', 'mpg', 'mpeg', 'wmv',
        'mp3', 'wav', 'aac', 'ogg', 'flac', 'm4a', 'wma', 'opus',
        'jpg', 'jpeg', 'png', 'webp', 'bmp', 'gif', 'svg', 'tiff'
    ];

    if (!in_array($ext, $allowedExts)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file format: .' . strtoupper($ext)]);
        exit;
    }

    $filename = uniqid('file_') . '.' . $ext;
    $targetPath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        echo json_encode([
            'success' => true, 
            'filePath' => 'uploads/' . $filename,
            'fileName' => $file['name'],
            'message' => 'Upload successful.'
        ]);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Final move failed. Check disk space or directory permissions.']);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid request: No file data received.']);
