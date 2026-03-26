<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All In One Convertor - Free Online File Converter</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="#" class="logo">
                <img src="assets/logo.png" alt="All In One Convertor Logo" class="site-logo">
            </a>
            <div class="nav-links">
                <a href="#converter">Converter</a>
                <a href="#features">Features</a>
                <a href="#pricing">Pricing</a>
                <a href="#converter" class="btn btn-dark">Get Started</a>
            </div>
        </div>
    </nav>

    <header class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <div class="badge"><i class="fa-solid fa-sparkles"></i> Free & No Login Required</div>
                <h1 class="hero-title">Convert Any File,<br><span class="text-primary italic">Any Format</span></h1>
                <p class="hero-desc">Transform videos, audio, images and documents in seconds. Fast, private, and completely free — right in your browser.</p>
                <div class="hero-actions">
                    <a href="#converter" class="btn btn-primary btn-lg">Start Converting <i class="fa-solid fa-arrow-right-long"></i></a>
                    <a href="#features" class="btn btn-outline btn-lg">Learn More</a>
                </div>
                <div class="hero-stats">
                    <div class="stat"><div class="stat-value">50+</div><div class="stat-label">File formats</div></div>
                    <div class="stat"><div class="stat-value">2M+</div><div class="stat-label">Files converted</div></div>
                    <div class="stat"><div class="stat-value">100%</div><div class="stat-label">Secure & private</div></div>
                </div>
            </div>
            
            <div class="hero-visual">
                <div class="glass-card visual-grid">
                    <div class="format-card">
                        <div class="format-icon bg-orange"><i class="fa-solid fa-play"></i></div>
                        <div><h4>Video</h4><p>MP4, AVI, MOV, MKV</p></div>
                    </div>
                    <div class="format-card">
                        <div class="format-icon bg-pink"><i class="fa-solid fa-music"></i></div>
                        <div><h4>Audio</h4><p>MP3, WAV, AAC, FLAC</p></div>
                    </div>
                    <div class="format-card">
                        <div class="format-icon bg-green"><i class="fa-solid fa-image"></i></div>
                        <div><h4>Image</h4><p>JPG, PNG, WEBP, SVG</p></div>
                    </div>
                    <div class="format-card">
                        <div class="format-icon bg-purple"><i class="fa-solid fa-file-lines"></i></div>
                        <div><h4>Document</h4><p>PDF, DOCX, TXT, CSV</p></div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section id="converter" class="converter-section">
        <div class="converter-container">
            <div class="section-heading">
                <span class="subtext">CONVERTER</span>
                <h2 class="serif-title">Drop Your File Here</h2>
                <p>Upload any file and select the output format to get started instantly.</p>
            </div>

            <div id="server-status-banner" hidden style="margin-bottom:20px; padding:15px; background:#fff1f2; border:1px solid #fda4af; border-radius:12px; color:#991b1b; font-size:14px; text-align:left;">
                <i class="fa-solid fa-triangle-exclamation" style="margin-right:8px;"></i>
                <strong>Action Required:</strong> <span id="server-status-msg">Server limits are too low.</span>
                <div style="margin-top:8px; font-family:monospace; background:#000; color:#fff; padding:10px; border-radius:6px; font-size:12px; word-break:break-all;">
                    php -S localhost:8000 -d upload_max_filesize=200M -d post_max_size=200M
                </div>
            </div>


            <div class="converter-box shadow-card">
                <form id="upload-form" method="POST" enctype="multipart/form-data">
                    <div id="upload-section" class="upload-area active">
                        <div class="drop-zone" id="drop-zone">
                            <div class="upload-icon"><i class="fa-solid fa-arrow-up-from-bracket"></i></div>
                            <h3>Drag & drop your file here</h3>
                            <p>or click the button below to browse your files</p>
                            <button type="button" class="btn btn-dark" id="browse-btn" style="margin-top: 10px;"><i class="fa-solid fa-upload"></i> Browse File</button>
                            <input type="file" name="file" id="file-input" hidden>
                            <p class="supported-text">Supports: MP4, MP3, JPG, PNG, PDF, DOCX, AVI, MOV, WAV, WEBP and more</p>
                        </div>
                        <div id="upload-progress-container" hidden style="margin-top:20px; padding:20px; border:1px solid #e2e8f0; border-radius:12px; background:#f8fafc; text-align:left;">
                            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                                <span id="upload-text-name" style="font-weight:600; font-size:14px;">Uploading...</span>
                                <span id="upload-text-percent" style="font-size:14px; color:#64748b;">0%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" id="upload-fill"></div>
                            </div>
                        </div>
                    </div>
                </form>
                
                <div id="convert-section" class="file-selected-area fade-in-up scale-up" hidden style="position: relative;">
                    <div class="file-info-preview" style="position: relative;">
                        <button class="btn-icon" id="cancel-upload-btn" title="Remove file"><i class="fa-solid fa-times"></i></button>
                        <div class="file-icon" id="preview-icon" style="margin-bottom: 0.5rem;"><i class="fa-solid fa-file"></i></div>
                        <div class="file-details">
                            <div class="file-name" id="display-file-name">filename.mp4</div>
                            <div class="file-size" id="display-file-size">12.5 MB</div>
                        </div>
                    </div>
                    <div id="file-preview-container" hidden style="margin-bottom:20px; padding:20px; background:#f8fafc; border:1px solid var(--border); border-radius:var(--radius-md); transition: all 0.5s; text-align:center; width:100%; max-width:100%;">
                        <img id="file-preview-img" style="width:80%; max-height:500px; border-radius:var(--radius-md); object-fit:contain; box-shadow: var(--shadow);" hidden />
                        <iframe id="file-preview-frame" style="width:100%; height:500px; border:none; border-radius:var(--radius-md);" hidden></iframe>
                        <pre id="file-preview-text" style="width:100%; height:400px; overflow:auto; padding:20px; margin:0; font-family:monospace; background:white; font-size:14px; border-radius:var(--radius-md); text-align:left; border:1px solid var(--border); line-height:1.6;" hidden></pre>
                    </div>
                </div>

                <div class="conversion-controls">
                    <div class="control-group">
                        <label>Original Format (detected)</label>
                        <input type="text" id="detected-format" value="Auto-detected" disabled readonly class="custom-input">
                    </div>
                    <div class="control-group">
                        <label>Convert To</label>
                        <div class="custom-select">
                            <select id="output-format" disabled><option value="" selected disabled>Select file first</option></select>
                        </div>
                    </div>
                </div>
                
                <div id="resolution-option" class="control-group" hidden style="margin-top:-10px; margin-bottom:20px;">
                    <label>Video Resolution</label>
                    <div class="custom-select">
                        <select id="video-resolution">
                            <option value="original">Original Quality</option>
                            <option value="1080">1080p (FHD)</option>
                            <option value="720">720p (HD)</option>
                            <option value="480">480p (SD)</option>
                        </select>
                    </div>
                </div>

                <div id="error-message" class="error-toast fade-in-up" hidden>
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span id="error-text"></span>
                </div>

                <button type="button" class="btn btn-primary btn-block btn-convert" id="convert-submit-btn" disabled>

                    <i class="fa-solid fa-rotate"></i> Convert File
                </button>
                
                <div id="convert-loading" hidden style="text-align:center; padding: 20px;" class="fade-in-up">
                    <div id="convert-loading-spinner" class="spinner"></div><p id="convert-status-text" style="margin-top:10px; color:#475569;">Compressing / Processing file... please wait.</p>
                </div>

                <div id="result-section" class="result-area fade-in-up" hidden>
                    <div class="success-message" style="text-align:center; color:#10b981; font-weight:500; font-size:1.25rem;">
                        <i class="fa-regular fa-circle-check"></i> <span style="margin-left:5px;">File converted successfully</span>
                    </div>

                    <div id="download-progress-container" hidden style="margin-top:20px; padding:20px; border:1px solid #e2e8f0; border-radius:12px; background:#f8fafc; text-align:left; width: 100%;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                            <span id="download-text-name" style="font-weight:600; font-size:14px;">Preparing download...</span>
                            <span id="download-text-percent" style="font-size:14px; color:#64748b;">0%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" id="download-fill"></div>
                        </div>
                    </div>

                    <div class="result-actions" id="result-actions-container" style="display:flex; flex-direction:column; align-items:center; gap:10px; margin-top:25px; position:relative;">
                        <div style="display:flex; justify-content:center; gap:10px;">
                            <button class="btn btn-success btn-lg" id="download-btn" style="position:relative; overflow:hidden;">
                                <i class="fa-solid fa-download"></i> Download Converted File
                            </button>
                            <button class="btn btn-outline btn-lg" id="convert-another-btn">Convert Another</button>
                        </div>
                        <div id="burst-anchor" style="position:absolute; top:50%; left:50%; width:1px; height:1px;"></div>
                        <span style="font-size:0.875rem; color:#64748b; margin-top:5px;"><i class="fa-solid fa-clock-rotate-left"></i> File will be deleted automatically after download</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="features-section">
        <div class="features-container">
            <div class="features-header">
                <span class="subtext color-primary" style="color:var(--primary)">WHY ALL IN ONE CONVERTOR</span>
                <h2 class="serif-title lg" style="font-size:3rem; margin-bottom:1.5rem;">Built for speed.<br>Designed for everyone.</h2>
                <p style="font-size:1.125rem; color:#94a3b8;">No complex settings. No account required. Just upload, convert, download.</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon bg-yellow"><i class="fa-solid fa-bolt"></i></div>
                    <h3>Lightning Fast</h3>
                    <p>Optimized conversion engine processes files up to 10x faster than traditional tools. No waiting around.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon bg-indigo"><i class="fa-solid fa-clapperboard"></i></div>
                    <h3>HD Quality Output</h3>
                    <p>Preserve original quality during conversion. Our algorithms ensure zero visible quality loss.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon bg-emerald"><i class="fa-solid fa-lock"></i></div>
                    <h3>100% Secure & Private</h3>
                    <p>Files are processed locally or securely on our servers and automatically deleted. Your data stays yours.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon bg-rose"><i class="fa-solid fa-id-card-clip"></i></div>
                    <h3>No Login Needed</h3>
                    <p>Just open the page and start converting. No account, no subscription, no credit card — ever.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="footer-container">
            <div class="footer-left"><a href="#" class="logo" style="color:white;"><img src="assets/logo.png" alt="All In One Convertor Logo" class="site-logo footer-logo"></a></div>
            <div class="footer-right"><a href="#">Privacy Policy</a><a href="#">Terms of Use</a><a href="#">Contact</a><a href="#">Support</a></div>
        </div>
        <div class="footer-bottom"><p>&copy; 2026 All In One Convertor. All rights reserved. Made with care for everyone.</p></div>
    </footer>

    <script src="js/script.js?v=1.3"></script>
</body>
</html>
