/**
 * Mobile-First Avatar Cropper & Front Camera Capture
 * Rankawat Samaj Web App
 */

class AvatarCropperManager {
    constructor() {
        this.currentStream = null;
        this.facingMode = 'user'; // Front camera by default
        this.currentImage = null;
        
        // Cropper state
        this.scale = 1;
        this.minScale = 0.5;
        this.maxScale = 3.5;
        this.posX = 0;
        this.posY = 0;
        this.rotation = 0; // in degrees
        this.maskSize = 260; // diameter of circular mask in px
        
        // Drag / Touch state
        this.isDragging = false;
        this.startX = 0;
        this.startY = 0;
        this.initialTouchDistance = null;
        this.initialTouchScale = 1;
        
        // Target form & input IDs
        this.targetFormId = 'profilePhotoForm';
        this.targetInputId = 'profilePhotoInput';

        this.initDOM();
        this.bindEvents();
    }

    initDOM() {
        // Build Action Sheet Modal, Live Camera Modal, and Cropper Modal if not present
        if (document.getElementById('avatar-cropper-components')) return;

        const container = document.createElement('div');
        container.id = 'avatar-cropper-components';
        container.innerHTML = `
            <!-- Hidden Inputs for Fallbacks -->
            <input type="file" id="nativeCameraInput" accept="image/*" capture="user" style="display:none;">
            <input type="file" id="nativeGalleryInput" accept="image/*" style="display:none;">

            <!-- 1. Bottom Sheet Source Selection -->
            <div class="avatar-action-sheet-backdrop" id="avatarActionSheet">
                <div class="avatar-action-sheet">
                    <div class="avatar-sheet-handle"></div>
                    <div class="avatar-sheet-title">प्रोफ़ाइल फ़ोटो चुनें</div>
                    <div class="avatar-sheet-subtitle">सामने के कैमरे से फ़ोटो लें या गैलरी से चुनें</div>
                    
                    <div class="avatar-sheet-options">
                        <button type="button" class="avatar-option-btn" id="btnOptionCamera">
                            <span class="avatar-option-icon">🤳</span>
                            <span class="avatar-option-label">सेल्फी / कैमरा</span>
                        </button>
                        <button type="button" class="avatar-option-btn" id="btnOptionGallery">
                            <span class="avatar-option-icon">🖼️</span>
                            <span class="avatar-option-label">गैलरी / फ़ोटो</span>
                        </button>
                    </div>

                    <button type="button" class="avatar-sheet-cancel" id="btnCancelSheet">रद्द करें (Cancel)</button>
                </div>
            </div>

            <!-- 2. Live Front Camera Modal -->
            <div class="camera-modal-backdrop" id="cameraModal">
                <div class="camera-header">
                    <div class="camera-title">
                        <span>📸</span>
                        <span>कैमरा (Front Camera)</span>
                    </div>
                    <button type="button" class="camera-close-btn" id="btnCloseCamera" title="Close">✕</button>
                </div>

                <div class="camera-viewport">
                    <video id="cameraVideo" autoplay playsinline class="mirror-mode"></video>
                    <div class="camera-face-guide"></div>
                </div>

                <div class="camera-controls">
                    <button type="button" class="camera-switch-btn" id="btnSwitchCamera" title="Flip Camera">🔄</button>
                    <button type="button" class="camera-shutter-btn" id="btnShutter" title="Take Photo">
                        <div class="camera-shutter-inner"></div>
                    </button>
                    <div style="width: 48px;"></div> <!-- placeholder to center shutter -->
                </div>
            </div>

            <!-- 3. Circular Crop & Adjust Modal -->
            <div class="cropper-modal-backdrop" id="cropperModal">
                <div class="cropper-header">
                    <div class="cropper-title">✨ फ़ोटो सेट करें (Adjust Photo)</div>
                    <button type="button" class="camera-close-btn" id="btnCloseCropper" title="Cancel">✕</button>
                </div>

                <div class="cropper-sub-hint">
                    गोले के अंदर फ़ोटो को खींचकर (Drag) या ज़ूम (Zoom) करके सेट करें
                </div>

                <!-- Cropper Viewport with Canvas and Circular Mask Overlay -->
                <div class="cropper-viewport-container" id="cropperViewport">
                    <canvas class="cropper-image-canvas" id="cropperCanvas"></canvas>
                    <div class="cropper-circle-mask"></div>
                </div>

                <!-- Controls -->
                <div class="cropper-controls-wrapper">
                    <div class="cropper-zoom-bar">
                        <span class="cropper-zoom-icon">🔍 -</span>
                        <input type="range" class="cropper-slider" id="cropperZoomSlider" min="0.5" max="3.5" step="0.01" value="1">
                        <span class="cropper-zoom-icon">🔍 +</span>
                    </div>

                    <div class="cropper-tool-buttons">
                        <button type="button" class="cropper-tool-btn" id="btnRotate">
                            ↻ घुमाएं (Rotate)
                        </button>
                        <button type="button" class="cropper-tool-btn" id="btnReset">
                            ↺ रीसेट (Reset)
                        </button>
                    </div>

                    <div class="cropper-actions-bar">
                        <button type="button" class="cropper-btn-cancel" id="btnRetakeCropper">
                            रद्द करें
                        </button>
                        <button type="button" class="cropper-btn-save" id="btnSaveCropped">
                            ✓ फ़ोटो सेव करें
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(container);
    }

    bindEvents() {
        // Trigger action sheet when clicking upload button or avatar badge
        document.addEventListener('click', (e) => {
            const trigger = e.target.closest('[data-avatar-trigger], .profile-upload-btn, .avatar-badge-btn');
            if (trigger) {
                e.preventDefault();
                this.openActionSheet();
            }
        });

        // Sheet options
        document.getElementById('btnOptionCamera').addEventListener('click', () => {
            this.closeActionSheet();
            this.startCamera();
        });

        document.getElementById('btnOptionGallery').addEventListener('click', () => {
            this.closeActionSheet();
            document.getElementById('nativeGalleryInput').click();
        });

        document.getElementById('btnCancelSheet').addEventListener('click', () => {
            this.closeActionSheet();
        });

        document.getElementById('avatarActionSheet').addEventListener('click', (e) => {
            if (e.target.id === 'avatarActionSheet') {
                this.closeActionSheet();
            }
        });

        // File Inputs Change
        document.getElementById('nativeGalleryInput').addEventListener('change', (e) => {
            if (e.target.files && e.target.files[0]) {
                this.loadSourceImage(e.target.files[0]);
                e.target.value = '';
            }
        });

        document.getElementById('nativeCameraInput').addEventListener('change', (e) => {
            if (e.target.files && e.target.files[0]) {
                this.loadSourceImage(e.target.files[0]);
                e.target.value = '';
            }
        });

        // Camera controls
        document.getElementById('btnCloseCamera').addEventListener('click', () => {
            this.stopCamera();
            document.getElementById('cameraModal').classList.remove('active');
        });

        document.getElementById('btnSwitchCamera').addEventListener('click', () => {
            this.facingMode = (this.facingMode === 'user') ? 'environment' : 'user';
            const video = document.getElementById('cameraVideo');
            if (this.facingMode === 'user') {
                video.classList.add('mirror-mode');
            } else {
                video.classList.remove('mirror-mode');
            }
            this.startCameraStream();
        });

        document.getElementById('btnShutter').addEventListener('click', () => {
            this.captureFrameFromCamera();
        });

        // Cropper controls
        document.getElementById('btnCloseCropper').addEventListener('click', () => {
            this.closeCropper();
        });

        document.getElementById('btnRetakeCropper').addEventListener('click', () => {
            this.closeCropper();
        });

        document.getElementById('cropperZoomSlider').addEventListener('input', (e) => {
            this.scale = parseFloat(e.target.value);
            this.renderCropperCanvas();
        });

        document.getElementById('btnRotate').addEventListener('click', () => {
            this.rotation = (this.rotation + 90) % 360;
            this.renderCropperCanvas();
        });

        document.getElementById('btnReset').addEventListener('click', () => {
            this.resetTransform();
        });

        document.getElementById('btnSaveCropped').addEventListener('click', () => {
            this.cropAndSubmit();
        });

        // Cropper Dragging & Pinch-to-Zoom Gestures
        const viewport = document.getElementById('cropperViewport');

        // Mouse Drag
        viewport.addEventListener('mousedown', (e) => {
            this.isDragging = true;
            this.startX = e.clientX - this.posX;
            this.startY = e.clientY - this.posY;
        });

        window.addEventListener('mousemove', (e) => {
            if (!this.isDragging) return;
            this.posX = e.clientX - this.startX;
            this.posY = e.clientY - this.startY;
            this.renderCropperCanvas();
        });

        window.addEventListener('mouseup', () => {
            this.isDragging = false;
        });

        // Mouse Wheel Zoom
        viewport.addEventListener('wheel', (e) => {
            e.preventDefault();
            const delta = e.deltaY > 0 ? -0.1 : 0.1;
            this.updateScale(this.scale + delta);
        }, { passive: false });

        // Touch Drag & Pinch-to-Zoom (Mobile Gestures)
        viewport.addEventListener('touchstart', (e) => {
            if (e.touches.length === 1) {
                this.isDragging = true;
                this.startX = e.touches[0].clientX - this.posX;
                this.startY = e.touches[0].clientY - this.posY;
                this.initialTouchDistance = null;
            } else if (e.touches.length === 2) {
                this.isDragging = false;
                this.initialTouchDistance = this.getTouchDistance(e.touches[0], e.touches[1]);
                this.initialTouchScale = this.scale;
            }
        }, { passive: false });

        viewport.addEventListener('touchmove', (e) => {
            e.preventDefault(); // Stop whole page scrolling
            if (e.touches.length === 1 && this.isDragging) {
                this.posX = e.touches[0].clientX - this.startX;
                this.posY = e.touches[0].clientY - this.startY;
                this.renderCropperCanvas();
            } else if (e.touches.length === 2 && this.initialTouchDistance) {
                const currentDistance = this.getTouchDistance(e.touches[0], e.touches[1]);
                const factor = currentDistance / this.initialTouchDistance;
                this.updateScale(this.initialTouchScale * factor);
            }
        }, { passive: false });

        viewport.addEventListener('touchend', (e) => {
            if (e.touches.length === 0) {
                this.isDragging = false;
                this.initialTouchDistance = null;
            } else if (e.touches.length === 1) {
                this.isDragging = true;
                this.startX = e.touches[0].clientX - this.posX;
                this.startY = e.touches[0].clientY - this.posY;
                this.initialTouchDistance = null;
            }
        });
    }

    getTouchDistance(t1, t2) {
        const dx = t1.clientX - t2.clientX;
        const dy = t1.clientY - t2.clientY;
        return Math.sqrt(dx * dx + dy * dy);
    }

    updateScale(newScale) {
        this.scale = Math.min(Math.max(newScale, this.minScale), this.maxScale);
        document.getElementById('cropperZoomSlider').value = this.scale;
        this.renderCropperCanvas();
    }

    openActionSheet() {
        const sheet = document.getElementById('avatarActionSheet');
        sheet.classList.add('active');
    }

    closeActionSheet() {
        const sheet = document.getElementById('avatarActionSheet');
        sheet.classList.remove('active');
    }

    /* Live Camera Stream with getUserMedia */
    async startCamera() {
        const modal = document.getElementById('cameraModal');
        modal.classList.add('active');

        const video = document.getElementById('cameraVideo');
        if (this.facingMode === 'user') {
            video.classList.add('mirror-mode');
        } else {
            video.classList.remove('mirror-mode');
        }

        const success = await this.startCameraStream();
        if (!success) {
            // If live camera is blocked/denied or unsupported, fallback to native camera input
            modal.classList.remove('active');
            document.getElementById('nativeCameraInput').click();
        }
    }

    async startCameraStream() {
        this.stopCamera();

        const video = document.getElementById('cameraVideo');
        const constraints = {
            video: {
                facingMode: { ideal: this.facingMode },
                width: { ideal: 1280 },
                height: { ideal: 1280 }
            },
            audio: false
        };

        try {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                throw new Error("getUserMedia not supported");
            }
            this.currentStream = await navigator.mediaDevices.getUserMedia(constraints);
            video.srcObject = this.currentStream;
            await video.play();
            return true;
        } catch (err) {
            console.warn("Front camera live stream error:", err);
            return false;
        }
    }

    stopCamera() {
        if (this.currentStream) {
            this.currentStream.getTracks().forEach(track => track.stop());
            this.currentStream = null;
        }
        const video = document.getElementById('cameraVideo');
        if (video) {
            video.srcObject = null;
        }
    }

    captureFrameFromCamera() {
        const video = document.getElementById('cameraVideo');
        if (!video || !video.videoWidth) return;

        const offCanvas = document.createElement('canvas');
        offCanvas.width = video.videoWidth;
        offCanvas.height = video.videoHeight;
        const ctx = offCanvas.getContext('2d');

        // If front camera, mirror image so preview matches selfie expectations
        if (this.facingMode === 'user') {
            ctx.translate(offCanvas.width, 0);
            ctx.scale(-1, 1);
        }
        ctx.drawImage(video, 0, 0, offCanvas.width, offCanvas.height);

        this.stopCamera();
        document.getElementById('cameraModal').classList.remove('active');

        // Load into Cropper
        offCanvas.toBlob((blob) => {
            if (blob) {
                this.loadSourceImage(blob);
            }
        }, 'image/jpeg', 0.95);
    }

    /* Cropper & Image Loading */
    loadSourceImage(fileOrBlob) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const img = new Image();
            img.onload = () => {
                this.currentImage = img;
                this.openCropper();
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(fileOrBlob);
    }

    openCropper() {
        const modal = document.getElementById('cropperModal');
        modal.classList.add('active');

        this.setupCanvasDimensions();
        this.resetTransform();
    }

    closeCropper() {
        const modal = document.getElementById('cropperModal');
        modal.classList.remove('active');
        this.currentImage = null;
    }

    setupCanvasDimensions() {
        const viewport = document.getElementById('cropperViewport');
        const canvas = document.getElementById('cropperCanvas');
        const dpr = window.devicePixelRatio || 1;
        const rect = viewport.getBoundingClientRect();

        canvas.width = rect.width * dpr;
        canvas.height = rect.height * dpr;
        canvas.style.width = `${rect.width}px`;
        canvas.style.height = `${rect.height}px`;

        const ctx = canvas.getContext('2d');
        ctx.scale(dpr, dpr);
    }

    resetTransform() {
        if (!this.currentImage) return;

        const viewport = document.getElementById('cropperViewport');
        const vWidth = viewport.clientWidth;
        const vHeight = viewport.clientHeight;

        // Fit image so it covers at least the 260px circle
        const minDim = Math.min(this.currentImage.width, this.currentImage.height);
        const fitScale = (this.maskSize + 20) / minDim;

        this.scale = Math.max(fitScale, 0.6);
        this.minScale = Math.max(fitScale * 0.5, 0.3);
        this.maxScale = Math.max(fitScale * 3.5, 3.5);

        const slider = document.getElementById('cropperZoomSlider');
        slider.min = this.minScale;
        slider.max = this.maxScale;
        slider.value = this.scale;

        this.posX = 0;
        this.posY = 0;
        this.rotation = 0;

        this.renderCropperCanvas();
    }

    renderCropperCanvas() {
        if (!this.currentImage) return;

        const canvas = document.getElementById('cropperCanvas');
        const ctx = canvas.getContext('2d');
        const dpr = window.devicePixelRatio || 1;
        const width = canvas.width / dpr;
        const height = canvas.height / dpr;

        ctx.clearRect(0, 0, width, height);
        ctx.save();

        // Translate to center + user drag offset
        ctx.translate(width / 2 + this.posX, height / 2 + this.posY);
        ctx.rotate((this.rotation * Math.PI) / 180);
        ctx.scale(this.scale, this.scale);

        // Draw centered
        ctx.drawImage(
            this.currentImage,
            -this.currentImage.width / 2,
            -this.currentImage.height / 2
        );

        ctx.restore();
    }

    /* Produce Final High-Quality 600x600 Circle/Square Cropped Blob */
    cropAndSubmit() {
        if (!this.currentImage) return;

        const saveBtn = document.getElementById('btnSaveCropped');
        saveBtn.disabled = true;
        saveBtn.innerHTML = "⏳ सेव हो रहा है...";

        // Destination resolution for crystal clear avatars
        const outSize = 600;
        const outCanvas = document.createElement('canvas');
        outCanvas.width = outSize;
        outCanvas.height = outSize;
        const ctx = outCanvas.getContext('2d');

        // Ratio between output size and viewport mask size (260px)
        const exportRatio = outSize / this.maskSize;

        ctx.save();
        ctx.translate(outSize / 2 + (this.posX * exportRatio), outSize / 2 + (this.posY * exportRatio));
        ctx.rotate((this.rotation * Math.PI) / 180);
        ctx.scale(this.scale * exportRatio, this.scale * exportRatio);

        ctx.drawImage(
            this.currentImage,
            -this.currentImage.width / 2,
            -this.currentImage.height / 2
        );
        ctx.restore();

        // Convert to Blob <= 500KB
        outCanvas.toBlob(async (blob) => {
            if (!blob) {
                alert("फ़ोटो प्रोसेस करने में त्रुटि हुई। कृपया पुनः प्रयास करें।");
                saveBtn.disabled = false;
                saveBtn.innerHTML = "✓ फ़ोटो सेव करें";
                return;
            }

            // Create a File object
            const croppedFile = new File([blob], `avatar_${Date.now()}.jpg`, {
                type: 'image/jpeg',
                lastModified: Date.now()
            });

            // Put file into target form
            const targetInput = document.getElementById(this.targetInputId);
            const targetForm = document.getElementById(this.targetFormId);

            if (targetInput && targetForm) {
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(croppedFile);
                targetInput.files = dataTransfer.files;

                // Show global loader if available
                const globalLoader = document.getElementById('global-upload-loader');
                if (globalLoader) {
                    globalLoader.style.display = 'flex';
                }

                // Submit target form
                targetForm.submit();
            } else {
                console.error("Target form or input not found for avatar upload");
            }
        }, 'image/jpeg', 0.88);
    }
}

// Auto-initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.avatarCropper = new AvatarCropperManager();
});
