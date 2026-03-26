<?php
$outputDir = __DIR__ . '/output/';
if (!is_dir($outputDir)) mkdir($outputDir, 0777, true);
if (!is_writable($outputDir)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Internal Server Error: Output directory is not writable.']);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filePathInput = $_POST['filePath'] ?? '';
    $targetFormat = $_POST['targetFormat'] ?? '';
    $resolution = $_POST['resolution'] ?? 'original';
    
    if (empty($filePathInput) || strpos($filePathInput, '..') !== false || !file_exists(__DIR__ . '/' . $filePathInput)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid file path or file not found.']);
        exit;
    }
    
    $fullInputPath = __DIR__ . '/' . $filePathInput;
    $fileNameWithoutExt = pathinfo($fullInputPath, PATHINFO_FILENAME);
    $targetFormatClean = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($targetFormat));
    $outputFileName = $fileNameWithoutExt . '_converted.' . $targetFormatClean;
    
    $fullOutputPath = $outputDir . $outputFileName;
    
    $cmd = ['ffmpeg', '-y', '-i', escapeshellarg($fullInputPath)];
    
    // Add compression balancing
    $isVideo = in_array($targetFormatClean, ['mp4', 'avi', 'mov', 'mkv', 'webm', 'flv', '3gp', 'mpg', 'mpeg', 'wmv']);
    $isAudio = in_array($targetFormatClean, ['mp3', 'wav', 'aac', 'ogg', 'flac', 'm4a', 'wma', 'opus']);
    $isImage = in_array($targetFormatClean, ['jpg', 'jpeg', 'png', 'webp', 'bmp', 'gif', 'svg', 'tiff']);

    
    if ($resolution !== 'original') {
        $height = intval($resolution);
        if ($height > 0) {
            $cmd[] = '-vf';
            $cmd[] = escapeshellarg("scale=-2:$height");
        }
    }
    
    if ($isVideo) {
        $cmd[] = '-crf 28';
        $cmd[] = '-preset fast';
    } elseif ($isAudio) {
        $cmd[] = '-b:a 128k';
    } elseif ($isImage) {
        $cmd[] = '-q:v 2';
    }
    
    $cmd[] = escapeshellarg($fullOutputPath);
    $commandStr = implode(' ', $cmd) . ' 2>&1';
    
    exec($commandStr, $output, $returnVar);
    
    if ($returnVar === 0 && file_exists($fullOutputPath)) {
        echo json_encode([
             'success' => true, 
             'downloadUrl' => 'download.php?file=' . urlencode($outputFileName) . '&orig=' . urlencode(basename($filePathInput))
        ]);
        exit;
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'FFmpeg conversion mathematically failed.', 'debug' => $output]);
        if(file_exists($fullOutputPath)) unlink($fullOutputPath);
        exit;
    }
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Invalid request variables.']);
