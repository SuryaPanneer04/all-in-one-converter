document.addEventListener('DOMContentLoaded', () => {
    window.onerror = function(msg, url, line) {
        console.error("Global Error:", msg, "at", url, ":", line);
        if (typeof showError === "function") showError("JS Error: " + msg);
        else alert("JavaScript Error: " + msg);
        return false;
    };


    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('file-input');
    const browseBtn = document.getElementById('browse-btn');
    
    // UI Panels
    const uploadArea = document.getElementById('upload-section');
    const fileSelectedArea = document.getElementById('convert-section');
    const resultSection = document.getElementById('result-section');
    
    // Progress
    const uploadProgressContainer = document.getElementById('upload-progress-container');
    const uploadTextName = document.getElementById('upload-text-name');
    const uploadTextPercent = document.getElementById('upload-text-percent');
    const uploadFill = document.getElementById('upload-fill');
    
    const errorToast = document.getElementById('error-message');
    const errorText = document.getElementById('error-text');
    
    // File Selected
    const previewIcon = document.getElementById('preview-icon');
    const displayFileName = document.getElementById('display-file-name');
    const displayFileSize = document.getElementById('display-file-size');
    const cancelUploadBtn = document.getElementById('cancel-upload-btn');
    
    // Convert Controls
    const detectedFormatInput = document.getElementById('detected-format');
    const outputFormatSelect = document.getElementById('output-format');
    const resolutionOption = document.getElementById('resolution-option');
    const videoResolution = document.getElementById('video-resolution');
    
    const convertSubmitBtn = document.getElementById('convert-submit-btn');
    const convertLoading = document.getElementById('convert-loading');
    const downloadBtn = document.getElementById('download-btn');
    const convertAnotherBtn = document.getElementById('convert-another-btn');
    
    // Download Progress
    const downloadProgressContainer = document.getElementById('download-progress-container');
    const downloadTextPercent = document.getElementById('download-text-percent');
    const downloadFill = document.getElementById('download-fill');
    const resultActionsContainer = document.getElementById('result-actions-container');

    // File Preview Elements (Global Scope)
    const filePreviewContainer = document.getElementById('file-preview-container');
    const filePreviewImg = document.getElementById('file-preview-img');
    const filePreviewFrame = document.getElementById('file-preview-frame');
    const filePreviewText = document.getElementById('file-preview-text');
    const serverStatusBanner = document.getElementById('server-status-banner');
    const serverStatusMsg = document.getElementById('server-status-msg');



    let currentFile = null;
    let uploadedFilePath = null;
    let fileCategory = null;
    let currentExt = null;

    const formats = {
        video: ['mp4', 'avi', 'mov', 'mkv', 'webm', 'flv', '3gp', 'mpg', 'mpeg', 'wmv'],
        audio: ['mp3', 'wav', 'aac', 'ogg', 'flac', 'm4a', 'wma', 'opus'],
        image: ['jpg', 'jpeg', 'png', 'webp', 'bmp', 'gif', 'svg', 'tiff'],
        document: ['pdf', 'doc', 'docx', 'txt', 'csv', 'odt', 'rtf']
    };


    browseBtn.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length) handleFile(e.target.files[0]);
    });

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('dragover');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('dragover');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        if (e.dataTransfer.files.length) handleFile(e.dataTransfer.files[0]);
    });

    cancelUploadBtn.addEventListener('click', () => resetApp());
    convertAnotherBtn.addEventListener('click', () => resetApp());

    outputFormatSelect.addEventListener('change', () => {
        if (fileCategory === 'video' && ['mp4', 'avi', 'mov', 'mkv', 'webm'].includes(outputFormatSelect.value)) {
            resolutionOption.hidden = false;
        } else {
            resolutionOption.hidden = true;
        }
        
        if (outputFormatSelect.value) convertSubmitBtn.disabled = false;
    });

    // Proactive Server Status Check
    fetch('upload.php')
        .then(res => res.json())
        .then(data => {
            if (!data.success && data.message.includes('CRITICAL')) {
                serverStatusBanner.hidden = false;
                serverStatusMsg.textContent = data.message.split('Please')[0];
            }
        }).catch(err => console.error("Status check failed:", err));


    function handleFile(file) {
        hideError();
        
        const parts = file.name.split('.');
        const ext = parts.length > 1 ? parts.pop().toLowerCase() : '';
        if(!ext) {
            showError("Invalid file. File lacks format extension.");
            return;
        }
        
        let foundCategory = null;
        for (const [cat, exts] of Object.entries(formats)) {
            if (exts.includes(ext)) {
                foundCategory = cat;
                break;
            }
        }

        if (!foundCategory) {
            showError("File format ." + ext.toUpperCase() + " is currently unsupported.");
            return;
        }

        const maxSize = 200 * 1024 * 1024; // 200MB limit
        if (file.size > maxSize) {
            const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
            showError(`File size exceeds 200MB limit. Your file is ${sizeMB}MB.`);
            return;
        }

        currentFile = file;
        fileCategory = foundCategory;
        currentExt = ext;
        
        uploadFile(file);
    }

    function uploadFile(file) {
        const formData = new FormData();
        formData.append('file', file);

        dropZone.style.display = 'none';
        uploadProgressContainer.hidden = false;
        uploadTextName.textContent = "Uploading " + file.name;
        uploadTextPercent.textContent = "0%";
        uploadFill.style.width = '0%';

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'upload.php', true);

        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                uploadFill.style.width = percent + '%';
                uploadTextPercent.textContent = percent + '%';
                if (percent === 100) {
                    uploadTextName.textContent = "Processing upload securely...";
                }
            }
        };

        xhr.onload = function() {
            console.log("Server Response:", xhr.responseText);
            if (xhr.status === 200) {
                try {
                    const res = JSON.parse(xhr.responseText);
                    if (res.success) {
                        uploadedFilePath = res.filePath;
                        showConvertOptions(file, currentExt);
                    } else {
                        console.error("Server Logic Error:", res.message);
                        showError(res.message || "Server reported failure without message.");
                        resetApp();
                    }
                } catch(e) {
                    console.error("JSON Parse Error:", e, xhr.responseText);
                    showError('Server returned an invalid response. Error: ' + e.message);
                    resetApp();
                }
            } else {
                console.error("Server HTTP Error:", xhr.status);
                showError(`Server error (${xhr.status}). Contact support if this persists.`);
                resetApp();
            }
        };

        xhr.onerror = function() {
            console.error("XHR Network Error");
            showError('Network error occurred. The upload could not be completed.');
            resetApp();
        };

        xhr.send(formData);
    }

    function getFileIcon(cat, ext) {
        if (cat === 'video') return '<i class="fa-solid fa-file-video" style="color:#ef4444;"></i>';
        if (cat === 'audio') return '<i class="fa-solid fa-file-audio" style="color:#f59e0b;"></i>';
        if (cat === 'image') return '<i class="fa-solid fa-file-image" style="color:#10b981;"></i>';
        if (ext === 'pdf') return '<i class="fa-solid fa-file-pdf" style="color:#dc2626;"></i>';
        if (['doc', 'docx'].includes(ext)) return '<i class="fa-solid fa-file-word" style="color:#2563eb;"></i>';
        if (['csv'].includes(ext)) return '<i class="fa-solid fa-file-csv" style="color:#059669;"></i>';
        return '<i class="fa-solid fa-file-lines" style="color:#64748b;"></i>'; // TXT fallback default
    }

    function showConvertOptions(file, ext) {
        uploadArea.hidden = true;
        fileSelectedArea.hidden = false;
        
        displayFileName.textContent = file.name;
        displayFileSize.textContent = formatBytes(file.size);
        
        // Icon as fallback inside the details or for doc types
        previewIcon.innerHTML = getFileIcon(fileCategory, ext);
        previewIcon.style.display = 'block';
        
        // Reset and hide all previews initially
        if (filePreviewContainer) filePreviewContainer.hidden = true;
        if (filePreviewImg) filePreviewImg.hidden = true;
        if (filePreviewFrame) filePreviewFrame.hidden = true;
        if (filePreviewText) filePreviewText.hidden = true;

        if (fileCategory === 'image') {
            const objUrl = URL.createObjectURL(file);
            filePreviewImg.src = objUrl;
            filePreviewImg.onload = () => {
                filePreviewImg.hidden = false;
                filePreviewContainer.hidden = false;
                previewIcon.style.display = 'none';
            };
        } else if (ext === 'txt' || ext === 'csv') {
            const reader = new FileReader();
            reader.onload = e => {
                filePreviewText.textContent = e.target.result.substring(0, 5000);
                if (e.target.result.length > 5000) filePreviewText.textContent += '\n... [Content Truncated]';
                filePreviewText.hidden = false;
                filePreviewContainer.hidden = false;
                previewIcon.style.display = 'none';
            };
            reader.readAsText(file);
        } else if (ext === 'pdf') {
            const objUrl = URL.createObjectURL(file);
            filePreviewFrame.src = objUrl;
            filePreviewFrame.hidden = false;
            filePreviewContainer.hidden = false;
            previewIcon.style.display = 'none';
        }

        let niceName = ext.toUpperCase();
        if (fileCategory === 'video') niceName = ext.toUpperCase() + ' Video';
        else if (fileCategory === 'audio') niceName = ext.toUpperCase() + ' Audio';
        else if (fileCategory === 'image') niceName = ext.toUpperCase() + ' Image';
        else if (ext === 'pdf') niceName = 'PDF Document';
        else if (['doc', 'docx'].includes(ext)) niceName = 'Word Document';
        else if (ext === 'txt') niceName = 'Text Document';
        else if (ext === 'csv') niceName = 'CSV Spreadsheet';

        
        detectedFormatInput.value = niceName;

        outputFormatSelect.innerHTML = '<option value="" selected disabled>Select Format Here</option>';
        outputFormatSelect.disabled = false;
        
        let availableOptions = [];
        if (ext === 'pdf') {
            availableOptions = ['docx', 'txt', 'pdf']; // Explicit PDF logic handling correctly
        } else if (['doc', 'docx'].includes(ext)) {
            availableOptions = ['pdf', 'txt']; // Explicit Word maps safely
        } else if (ext === 'txt') {
            availableOptions = ['pdf', 'docx']; 
        } else if (ext === 'csv') {
            availableOptions = ['pdf'];
        } else {
            availableOptions = formats[fileCategory].filter(e => e !== ext && e !== 'jpeg');
            if (fileCategory === 'video') {
                availableOptions = [...availableOptions, 'mp3', 'wav'];
                availableOptions = [...new Set(availableOptions)];
            }
        }

        availableOptions.forEach(opt => {
            const el = document.createElement('option');
            el.value = opt;
            el.textContent = opt.toUpperCase();
            if (opt === 'pdf' && ext === 'pdf') el.textContent = 'PDF (Compress File Size)';
            if (opt === 'docx') el.textContent = 'MS Word (.docx)';
            if (opt === 'doc') el.textContent = 'MS Word (.doc)';
            outputFormatSelect.appendChild(el);
        });

        outputFormatSelect.dispatchEvent(new Event('change'));
    }

    convertSubmitBtn.addEventListener('click', (e) => {
        e.preventDefault();

        if (!uploadedFilePath || !outputFormatSelect.value) {
            showError("Select an output format dropdown safely first.");
            return;
        }

        const targetFormat = outputFormatSelect.value;
        const resolution = fileCategory === 'video' ? videoResolution.value : 'original';
        
        convertSubmitBtn.hidden = true;
        convertLoading.hidden = false;
        outputFormatSelect.disabled = true;
        videoResolution.disabled = true;
        cancelUploadBtn.hidden = true;
        hideError();
        
        const statusText = document.getElementById('convert-status-text');
        statusText.textContent = 'Compressing file... please wait.';
        setTimeout(() => {
            if(!convertLoading.hidden) statusText.textContent = 'Converting file... please wait.';
        }, 3000);

        const endpoint = (fileCategory === 'document') ? 'doc_convert.php' : 'convert.php';

        const formData = new FormData();
        formData.append('filePath', uploadedFilePath);
        formData.append('targetFormat', targetFormat);
        if (fileCategory === 'video') formData.append('resolution', resolution);

        fetch(endpoint, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            convertLoading.hidden = true;
            if (data.success && data.downloadUrl) {
                showResultSection(data.downloadUrl);
            } else {
                showError(data.message || 'Conversion stream encountered server bug resolving output.');
                restoreUI();
            }
        })
        .catch(err => {
            convertLoading.hidden = true;
            showError('Conversion network fetch crashed natively directly interacting with backend endpoint.');
            restoreUI();
        });
    });

    let convertedFileUrl = '';
    
    function showResultSection(url) {
        fileSelectedArea.hidden = true;
        document.querySelector('.conversion-controls').style.display = 'none';
        resolutionOption.style.display = 'none';
        
        resultSection.hidden = false;
        resultSection.classList.add('scale-up');
        convertedFileUrl = url;
        
        downloadProgressContainer.hidden = true;
        resultActionsContainer.hidden = false;
        downloadFill.style.width = '0%';
    }
    
    downloadBtn.addEventListener('click', (e) => {
        e.preventDefault();
        
        // 1. Ripple Effect
        const ripple = document.createElement('span');
        ripple.classList.add('ripple');
        const rect = downloadBtn.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = (e.clientX - rect.left - size/2) + 'px';
        ripple.style.top = (e.clientY - rect.top - size/2) + 'px';
        downloadBtn.appendChild(ripple);
        setTimeout(() => ripple.remove(), 600);

        // 2. Shake and Flash
        downloadBtn.classList.add('shake');
        document.body.classList.add('flash-effect');
        
        // 3. Burst Animation
        setTimeout(() => {
            downloadBtn.classList.remove('shake');
            document.body.classList.remove('flash-effect');
            createBurst(e.clientX, e.clientY);
            
            // 4. Proceed to Download after burst
            setTimeout(() => {
                resultActionsContainer.hidden = true;
                downloadProgressContainer.hidden = false;
                downloadTextPercent.textContent = '0%';
                downloadFill.style.width = '0%';
                
                startActualDownload();
            }, 400);
        }, 300);
    });

    function createBurst(x, y) {
        const container = document.createElement('div');
        container.classList.add('burst-container');
        document.body.appendChild(container);
        
        const particleCount = 40;
        const colors = ['#3b82f6', '#2563eb', '#60a5fa', '#93c5fd', '#ffffff'];
        
        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div');
            particle.classList.add('particle');
            
            const size = Math.random() * 8 + 4;
            particle.style.width = size + 'px';
            particle.style.height = size + 'px';
            particle.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            
            // Random direction
            const angle = Math.random() * Math.PI * 2;
            const velocity = Math.random() * 150 + 100;
            const tx = Math.cos(angle) * velocity;
            const ty = Math.sin(angle) * velocity;
            
            particle.style.setProperty('--tx', `${tx}px`);
            particle.style.setProperty('--ty', `${ty}px`);
            particle.style.left = x + 'px';
            particle.style.top = y + 'px';
            
            particle.style.animation = `particle-fade ${Math.random() * 0.5 + 0.5}s ease-out forwards`;
            
            container.appendChild(particle);
        }
        
        setTimeout(() => container.remove(), 1000);
    }

    function startActualDownload() {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', convertedFileUrl, true);
        xhr.responseType = 'blob';
        
        xhr.onprogress = function(event) {
            if (event.lengthComputable) {
                const percent = Math.round((event.loaded / event.total) * 100);
                downloadFill.style.width = percent + '%';
                downloadTextPercent.textContent = percent + '%';
            } else {
                downloadTextPercent.textContent = 'Downloading...';
            }
        };
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                downloadFill.style.width = '100%';
                downloadTextPercent.textContent = '100%';
                
                const blobUrl = window.URL.createObjectURL(xhr.response);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = blobUrl;
                
                const params = new URLSearchParams(convertedFileUrl.split('?')[1]);
                a.download = params.get('file') || 'converted_file';

                
                document.body.appendChild(a);
                a.click();
                setTimeout(() => {
                    window.URL.revokeObjectURL(blobUrl);
                    document.body.removeChild(a);
                    downloadProgressContainer.hidden = true;
                    resultActionsContainer.hidden = false;
                }, 100);
            } else {
                showError('Download request actively failed.');
                downloadProgressContainer.hidden = true;
                resultActionsContainer.hidden = false;
            }
        };
        
        xhr.onerror = function() {
            showError('Network connectivity broken completely during download.');
            downloadProgressContainer.hidden = true;
            resultActionsContainer.hidden = false;
        };
        
        xhr.send();
    }

    function restoreUI() {
        convertSubmitBtn.hidden = false;
        outputFormatSelect.disabled = false;
        videoResolution.disabled = false;
        cancelUploadBtn.hidden = false;
    }

    function resetApp() {
        currentFile = null;
        fileCategory = null;
        uploadedFilePath = null;
        currentExt = null;
        
        hideError();
        
        uploadArea.hidden = false;
        fileSelectedArea.hidden = true;
        resultSection.hidden = true;
        document.querySelector('.conversion-controls').style.display = 'grid';
        
        dropZone.style.display = 'block';
        uploadProgressContainer.hidden = true;
        uploadFill.style.width = '0%';
        
        detectedFormatInput.value = 'Auto-detected safely';
        outputFormatSelect.innerHTML = '<option value="" selected disabled>Upload file actively</option>';
        outputFormatSelect.disabled = true;
        videoResolution.disabled = false;
        resolutionOption.hidden = true;
        
        convertSubmitBtn.hidden = false;
        convertSubmitBtn.disabled = true;
        convertLoading.hidden = true;
        cancelUploadBtn.hidden = false;
        
        if (filePreviewContainer) {
            filePreviewContainer.hidden = true;
            if (filePreviewFrame.src) URL.revokeObjectURL(filePreviewFrame.src);
            if (filePreviewImg.src) URL.revokeObjectURL(filePreviewImg.src);
            filePreviewFrame.src = '';
            filePreviewImg.src = '';
        }
        
        fileInput.value = '';
    }

    function formatBytes(bytes, decimals = 2) {
        if (!+bytes) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
    }

    let errorTimeout;
    function showError(msg) {
        console.log("Showing Error:", msg);
        if (errorText) errorText.textContent = msg;
        if (errorToast) errorToast.hidden = false;
        clearTimeout(errorTimeout);
        errorTimeout = setTimeout(() => { if(errorToast) errorToast.hidden = true; }, 10000);
    }
    
    function hideError() {
        errorToast.hidden = true;
        clearTimeout(errorTimeout);
    }
});
