/**
 * Image Compressor Utility
 * Compresses images on the client side using HTML5 Canvas before uploading to the server.
 */

/**
 * Compress a single image file
 */
function compressImageFile(file, maxWidth = 1200, maxHeight = 1200, quality = 0.7) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = (event) => {
            const img = new Image();
            img.src = event.target.result;
            img.onload = () => {
                let width = img.width;
                let height = img.height;

                if (width > maxWidth || height > maxHeight) {
                    const ratio = Math.min(maxWidth / width, maxHeight / height);
                    width = width * ratio;
                    height = height * ratio;
                }

                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;

                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                canvas.toBlob(
                    (blob) => {
                        if (blob) {
                            const newFile = new File([blob], file.name, {
                                type: 'image/jpeg',
                                lastModified: Date.now()
                            });
                            resolve(newFile);
                        } else {
                            reject(new Error("Canvas toBlob failed"));
                        }
                    },
                    'image/jpeg',
                    quality
                );
            };
            img.onerror = (error) => reject(error);
        };
        reader.onerror = (error) => reject(error);
    });
}

/**
 * Helper to compress and immediately submit a form (useful for inline onchange events)
 */
async function compressAndSubmit(inputElement, formId) {
    if (inputElement.files && inputElement.files.length > 0) {
        // Show loading state if possible
        const label = document.querySelector(`label[for="${inputElement.id}"]`);
        let originalText = "";
        if (label) {
            originalText = label.innerHTML;
            label.innerHTML = "⏳ Compressing...";
            label.style.pointerEvents = "none";
        }

        try {
            const dataTransfer = new DataTransfer();
            for (let i = 0; i < inputElement.files.length; i++) {
                let file = inputElement.files[i];
                if (file.type.startsWith('image/')) {
                    const compressedFile = await compressImageFile(file, 1200, 1200, 0.7);
                    dataTransfer.items.add(compressedFile);
                } else {
                    dataTransfer.items.add(file);
                }
            }
            inputElement.files = dataTransfer.files;
        } catch (err) {
            console.error("Compression error:", err);
        }

        if (label) {
            label.innerHTML = originalText;
            label.style.pointerEvents = "auto";
        }
    }
    
    // Submit the form
    document.getElementById(formId).submit();
}

/**
 * Global form submit interceptor to compress images in forms automatically
 */
document.addEventListener('submit', async function(e) {
    if (e.target.dataset.compressing === 'true') {
        return; // Allow the submission to proceed
    }

    const fileInputs = e.target.querySelectorAll('input[type="file"][accept*="image"]');
    if (fileInputs.length === 0) return;

    let hasImageFiles = false;
    for (let input of fileInputs) {
        for (let i = 0; i < input.files.length; i++) {
            if (input.files[i].type.startsWith('image/')) {
                hasImageFiles = true;
            }
        }
    }

    if (!hasImageFiles) return;

    e.preventDefault(); // Stop normal submission
    e.target.dataset.compressing = 'true'; // Mark as currently compressing

    // Update button UI
    const submitBtns = e.target.querySelectorAll('[type="submit"]');
    const originalTexts = [];
    submitBtns.forEach((btn, idx) => {
        originalTexts[idx] = btn.innerHTML;
        btn.innerHTML = '⏳ Compressing...';
        btn.disabled = true;
    });

    try {
        for (let input of fileInputs) {
            if (input.files.length > 0) {
                const dataTransfer = new DataTransfer();
                for (let i = 0; i < input.files.length; i++) {
                    let file = input.files[i];
                    if (file.type.startsWith('image/')) {
                        const compressedFile = await compressImageFile(file, 1200, 1200, 0.7);
                        dataTransfer.items.add(compressedFile);
                    } else {
                        dataTransfer.items.add(file);
                    }
                }
                input.files = dataTransfer.files;
            }
        }
    } catch (err) {
        console.error("Compression error:", err);
    }
    
    // Resume submit
    e.target.submit();
});
