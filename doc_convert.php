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
    
    if (empty($filePathInput) || strpos($filePathInput, '..') !== false || !file_exists(__DIR__ . '/' . $filePathInput)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid file selection or path corrupted.']);
        exit;
    }
    
    $fullInputPath = __DIR__ . '/' . $filePathInput;
    $originalExt = strtolower(pathinfo($fullInputPath, PATHINFO_EXTENSION));
    $originalName = pathinfo($fullInputPath, PATHINFO_FILENAME);
    $targetFormatClean = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($targetFormat));
    
    $outputFileName = $originalName . '_converted.' . $targetFormatClean;
    $fullOutputPath = $outputDir . $outputFileName;
    
    $commandStr = '';
    
    if ($originalExt === 'pdf' && $targetFormatClean === 'docx') {
        // LibreOffice specifically uses writer_pdf_import for PDF -> DOCX
        $uniqueOutDir = sys_get_temp_dir() . '/' . uniqid('lodir_', true);
        mkdir($uniqueOutDir);
        $commandStr = 'export HOME=/tmp && libreoffice --headless --infilter="writer_pdf_import" --convert-to docx ' . escapeshellarg($fullInputPath) . ' --outdir ' . escapeshellarg($uniqueOutDir) . ' 2>&1';
        $fullOutputPath = $uniqueOutDir . '/' . $originalName . '.docx';
        $outputFileName = $originalName . '.docx';
    } elseif ($originalExt === 'pdf' && $targetFormatClean === 'txt') {
        $commandStr = 'pdftotext ' . escapeshellarg($fullInputPath) . ' ' . escapeshellarg($fullOutputPath) . ' 2>&1';
    } elseif (in_array($originalExt, ['doc', 'docx', 'txt', 'csv', 'odt', 'rtf']) && $targetFormatClean === 'pdf') {
        $uniqueOutDir = sys_get_temp_dir() . '/' . uniqid('lodir_', true);
        mkdir($uniqueOutDir);
        $commandStr = 'export HOME=/tmp && libreoffice --headless --convert-to pdf ' . escapeshellarg($fullInputPath) . ' --outdir ' . escapeshellarg($uniqueOutDir) . ' 2>&1';
        $fullOutputPath = $uniqueOutDir . '/' . $originalName . '.pdf';
        $outputFileName = $originalName . '.pdf';

    } elseif (in_array($originalExt, ['doc', 'docx', 'pdf', 'csv']) && $targetFormatClean === 'txt') {
         // Libreoffice can do to TXT
        $uniqueOutDir = sys_get_temp_dir() . '/' . uniqid('lodir_', true);
        mkdir($uniqueOutDir);
        $commandStr = 'export HOME=/tmp && libreoffice --headless --convert-to txt:Text ' . escapeshellarg($fullInputPath) . ' --outdir ' . escapeshellarg($uniqueOutDir) . ' 2>&1';
        $fullOutputPath = $uniqueOutDir . '/' . $originalName . '.txt';
        $outputFileName = $originalName . '.txt';
    } elseif (in_array($originalExt, ['txt']) && in_array($targetFormatClean, ['doc', 'docx'])) {
        $uniqueOutDir = sys_get_temp_dir() . '/' . uniqid('lodir_', true);
        mkdir($uniqueOutDir);
        $commandStr = 'export HOME=/tmp && libreoffice --headless --convert-to docx ' . escapeshellarg($fullInputPath) . ' --outdir ' . escapeshellarg($uniqueOutDir) . ' 2>&1';
        $fullOutputPath = $uniqueOutDir . '/' . $originalName . '.docx';
        $outputFileName = $originalName . '.docx';
    } elseif (in_array($originalExt, ['doc', 'docx']) && in_array($targetFormatClean, ['doc', 'docx'])) {
        // DOC to DOCX or DOCX to DOC etc.
        $uniqueOutDir = sys_get_temp_dir() . '/' . uniqid('lodir_', true);
        mkdir($uniqueOutDir);
        $commandStr = 'export HOME=/tmp && libreoffice --headless --convert-to ' . $targetFormatClean . ' ' . escapeshellarg($fullInputPath) . ' --outdir ' . escapeshellarg($uniqueOutDir) . ' 2>&1';
        $fullOutputPath = $uniqueOutDir . '/' . $originalName . '.' . $targetFormatClean;
        $outputFileName = $originalName . '.' . $targetFormatClean;
    } elseif ($originalExt === 'pdf' && $targetFormatClean === 'pdf') {
        $outputFileName = $originalName . '_compressed.pdf';
        $fullOutputPath = $outputDir . $outputFileName;
        $commandStr = 'gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/screen -dNOPAUSE -dQUIET -dBATCH -sOutputFile=' . escapeshellarg($fullOutputPath) . ' ' . escapeshellarg($fullInputPath) . ' 2>&1';
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Conversion combination unsupported dynamically.']);
        exit;
    }
    
    exec($commandStr, $output, $returnVar);
    
    if (file_exists($fullOutputPath) && filesize($fullOutputPath) > 0) {
        // Instead of streaming and destroying natively here, give URL map since requirement says after UI download triggers deletion natively via download.php map.
        // We will move the output file to permanent output folder if it was written strictly to tmp uniquely mapped.
        if (isset($uniqueOutDir)) {
            $destPath = $outputDir . $outputFileName;
            copy($fullOutputPath, $destPath);
            unlink($fullOutputPath);
            rmdir($uniqueOutDir);
        }
        
        echo json_encode(['success' => true, 'downloadUrl' => 'download.php?file=' . urlencode($outputFileName) . '&orig=' . urlencode(basename($filePathInput))]);
        exit;
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Failed to successfully resolve convert action. Input incompatible natively with system architecture.', 'debug' => implode("\n", $output)]);
        if (isset($uniqueOutDir) && is_dir($uniqueOutDir)) rmdir($uniqueOutDir);
        exit;
    }
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Invalid request']);
