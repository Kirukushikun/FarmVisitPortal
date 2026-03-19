@props([
    'label' => '', 
    'name' => '', 
    'required' => false,
    'existingPhotos' => [],
    'canUpload' => true
])

<div class="mb-6" x-data="{ 
    showCameraModal: false,
    showCancelConfirmation: false,
    showCarouselModal: false,
    showRemoveConfirmation: false,
    attachMode: 'camera',
    photoKey: '{{ $name }}',
    stream: null,
    photos: [],
    attachedPhotos: [],
    attachedFiles: [],
    suppressInputChange: false,
    currentPhotoIndex: 0,
    cameraActive: false,
    uploading: false,
    processingGallery: false,
    serverPhotoQueue: [],
    toast(type, message) {
        window.dispatchEvent(new CustomEvent('showToast', {
            detail: { type, message }
        }));
    },
    init() {
        const existing = Array.isArray({{ \Illuminate\Support\Js::from($existingPhotos) }}) ? {{ \Illuminate\Support\Js::from($existingPhotos) }} : [];
        this.attachedPhotos = existing.map(p => ({
            id: p.id || (Date.now() + Math.random()),
            data: p.url,
            serverPhotoId: p.id,
            serverUrl: p.url,
        }));
        this.attachedFiles = this.attachedPhotos.map(() => null);

        window.addEventListener('photoStored', (event) => {
            if (!event || !event.detail) {
                return;
            }

            if (event.detail.photoKey !== this.photoKey) {
                return;
            }

            this.serverPhotoQueue.push({
                photoId: event.detail.photoId,
                url: event.detail.url,
            });
        });

        window.addEventListener('formSubmitted', () => {
            this.photos = [];
            this.attachedPhotos = [];
            this.attachedFiles = [];
            this.serverPhotoQueue = [];
            this.currentPhotoIndex = 0;
            this.showCarouselModal = false;
            this.showCameraModal = false;
            this.showCancelConfirmation = false;
            this.showRemoveConfirmation = false;

            if (this.$refs && this.$refs.originalInput) {
                this.suppressInputChange = true;
                this.$refs.originalInput.value = '';
                this.suppressInputChange = false;
            }

            this.stopCamera();
        });

        window.addEventListener('formReset', () => {
            this.photos = [];
            this.attachedPhotos = [];
            this.attachedFiles = [];
            this.serverPhotoQueue = [];
            this.currentPhotoIndex = 0;
            this.showCarouselModal = false;
            this.showCameraModal = false;
            this.showCancelConfirmation = false;
            this.showRemoveConfirmation = false;

            if (this.$refs && this.$refs.originalInput) {
                this.suppressInputChange = true;
                this.$refs.originalInput.value = '';
                this.suppressInputChange = false;
            }

            this.stopCamera();
        });
    },
    assignServerPhotosToLastAttached(count) {
        if (!count || count <= 0) {
            return;
        }

        const startIndex = this.attachedPhotos.length - count;
        for (let i = 0; i < count; i++) {
            const queueItem = this.serverPhotoQueue.shift();
            if (!queueItem) {
                continue;
            }

            const index = startIndex + i;
            if (!this.attachedPhotos[index]) {
                continue;
            }

            this.attachedPhotos[index].serverPhotoId = queueItem.photoId;
            this.attachedPhotos[index].serverUrl = queueItem.url;
        }
    },
    async uploadFilesToServer(files) {
        if (!files || files.length === 0) {
            return;
        }

        if (!this.$wire) {
            console.warn('[photo-attach] Livewire ($wire) is not available; cannot upload to server');
            return;
        }

        await new Promise((resolve, reject) => {
            this.$wire.uploadMultiple('photoUploads.' + this.photoKey, files,
                () => resolve(true),
                (err) => reject(err)
            );
        });
    },
    openAttachAction() {
        if (this.attachMode === 'upload') {
            this.triggerUpload();
            return;
        }
        this.showCameraModal = true;
    },
    triggerUpload() {
        if (this.uploading || this.processingGallery) {
            return;
        }
        this.$refs.originalInput.click();
    },
    async handleInputChange(e) {
        if (this.suppressInputChange) {
            return;
        }

        const selected = e && e.target && e.target.files ? Array.from(e.target.files) : [];
        if (selected.length === 0) {
            return;
        }

        this.processingGallery = true;
        try {
            const processed = [];
            for (const file of selected) {
                const result = await this.processUploadFile(file);
                if (result) {
                    processed.push(result);
                }
            }

            const newFiles = processed.map(p => p.file);
            const newPhotos = processed.map(p => p.photo);

            const dataTransfer = new DataTransfer();
            const allFiles = [...this.attachedFiles, ...newFiles];
            allFiles.forEach(file => {
                if (!file) {
                    return;
                }
                dataTransfer.items.add(file);
            });

            this.suppressInputChange = true;
            this.$refs.originalInput.files = dataTransfer.files;
            this.suppressInputChange = false;

            this.attachedFiles = allFiles;
            this.attachedPhotos = [...this.attachedPhotos, ...newPhotos];

            this.uploading = true;
            try {
                await this.uploadFilesToServer(newFiles);
                this.assignServerPhotosToLastAttached(newPhotos.length);
            } finally {
                this.uploading = false;
            }
        } finally {
            this.processingGallery = false;
        }
    },
    async processUploadFile(file) {
        return new Promise((resolve) => {
            const reader = new FileReader();

            reader.onload = (e) => {
                const img = new Image();

                img.onload = () => {
                    const canvas = this.$refs.canvas;
                    const ctx = canvas.getContext('2d');

                    const maxDimension = 640;
                    let targetWidth = img.width;
                    let targetHeight = img.height;

                    if (img.width > maxDimension || img.height > maxDimension) {
                        const scale = Math.min(maxDimension / img.width, maxDimension / img.height);
                        targetWidth = Math.floor(img.width * scale);
                        targetHeight = Math.floor(img.height * scale);
                    }

                    canvas.width = targetWidth;
                    canvas.height = targetHeight;

                    ctx.drawImage(img, 0, 0, targetWidth, targetHeight);
                    this.addTimestampWatermark(ctx, canvas.width, canvas.height);

                    const dataUrl = canvas.toDataURL('image/jpeg', 0.85);

                    fetch(dataUrl)
                        .then(r => r.blob())
                        .then((blob) => {
                            const processedFile = new File([blob], file.name, { type: 'image/jpeg' });
                            resolve({
                                file: processedFile,
                                photo: { id: Date.now() + Math.random(), data: dataUrl }
                            });
                        })
                        .catch(() => resolve(null));
                };

                img.onerror = () => resolve(null);
                img.src = e.target.result;
            };

            reader.onerror = () => resolve(null);
            reader.readAsDataURL(file);
        });
    },
    async startCamera() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            this.toast('error', 'Camera not supported! You need HTTPS or localhost.');
            return;
        }
        
        try {
            this.stream = await navigator.mediaDevices.getUserMedia({ 
                video: { facingMode: 'environment', width: { ideal: 640 }, height: { ideal: 640 } },
                audio: false 
            });
            this.$refs.video.srcObject = this.stream;
            this.cameraActive = true;
        } catch(err) {
            this.toast('error', 'Camera error: ' + err.message);
        }
    },
    capturePhoto() {
        const video = this.$refs.video;
        const canvas = this.$refs.canvas;
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        
        ctx.drawImage(video, 0, 0);
        
        this.addTimestampWatermark(ctx, canvas.width, canvas.height);
        
        const imageData = canvas.toDataURL('image/jpeg', 0.85);
        this.photos.push({ id: Date.now(), data: imageData });
    },
    addTimestampWatermark(ctx, width, height) {
        const now = new Date(new Date().toLocaleString('en-US', {timeZone: 'Asia/Manila'}));
        const dateStr = now.toLocaleDateString('en-US', { 
            timeZone: 'Asia/Manila',
            year: 'numeric', 
            month: '2-digit', 
            day: '2-digit' 
        });
        const timeStr = now.toLocaleTimeString('en-US', { 
            timeZone: 'Asia/Manila',
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit',
            hour12: false 
        });
        const timestamp = `${dateStr} ${timeStr}`;
        
        const fontSize = Math.max(16, height * 0.03);
        ctx.font = `bold ${fontSize}px Arial`;
        ctx.textBaseline = 'bottom';
        
        const padding = fontSize * 0.3;
        const textWidth = ctx.measureText(timestamp).width;
        const textHeight = fontSize;
        const bgX = padding;
        const bgY = height - textHeight - padding * 2;
        const bgWidth = textWidth + padding * 2;
        const bgHeight = textHeight + padding * 2;
        
        ctx.fillStyle = 'rgba(0, 0, 0, 0.7)';
        ctx.fillRect(bgX, bgY, bgWidth, bgHeight);
        
        const textX = padding * 2;
        const textY = height - padding * 2;
        
        ctx.strokeStyle = '#000000';
        ctx.lineWidth = fontSize * 0.15;
        ctx.lineJoin = 'round';
        ctx.miterLimit = 2;
        ctx.strokeText(timestamp, textX, textY);
        
        ctx.fillStyle = '#FFFFFF';
        ctx.fillText(timestamp, textX, textY);
    },
    async processGalleryImage(file) {
        return new Promise((resolve) => {
            const reader = new FileReader();
            
            reader.onload = (e) => {
                const img = new Image();
                
                img.onload = () => {
                    const canvas = this.$refs.canvas;
                    const ctx = canvas.getContext('2d');
                    
                    const maxDimension = 640;
                    let targetWidth = img.width;
                    let targetHeight = img.height;
                    
                    if (img.width > maxDimension || img.height > maxDimension) {
                        const scale = Math.min(maxDimension / img.width, maxDimension / img.height);
                        targetWidth = Math.floor(img.width * scale);
                        targetHeight = Math.floor(img.height * scale);
                    }
                    
                    canvas.width = targetWidth;
                    canvas.height = targetHeight;
                    
                    ctx.drawImage(img, 0, 0, targetWidth, targetHeight);
                    
                    this.addTimestampWatermark(ctx, canvas.width, canvas.height);
                    
                    const imageData = canvas.toDataURL('image/jpeg', 0.85);
                    
                    const base64Length = imageData.length - 'data:image/jpeg;base64,'.length;
                    const sizeInBytes = (base64Length * 3) / 4;
                    
                    if (sizeInBytes > 15 * 1024 * 1024) {
                        this.toast('error', 'Photo \'' + file.name + '\' is too large even after resizing');
                        resolve();
                        return;
                    }
                    
                    this.photos.push({ id: Date.now(), data: imageData });
                    resolve();
                };
                
                img.onerror = () => {
                    this.toast('error', 'Failed to load image: ' + file.name);
                    resolve();
                };
                
                img.src = e.target.result;
            };
            
            reader.onerror = () => {
                this.toast('error', 'Failed to read file: ' + file.name);
                resolve();
            };
            
            reader.readAsDataURL(file);
        });
    },
    stopCamera() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }
        this.$refs.video.srcObject = null;
        this.cameraActive = false;
    },
    tryCancel() {
        if (this.photos.length > 0 || this.cameraActive) {
            this.showCancelConfirmation = true;
        } else {
            this.confirmCancel();
        }
    },
    confirmCancel() {
        this.stopCamera();
        this.photos = [];
        this.showCancelConfirmation = false;
        this.showCameraModal = false;
        this.showGalleryUpload = false;
        this.uploading = false;
        this.processingGallery = false;
    },
    openCarousel(index = 0) {
        if (this.attachedPhotos.length === 0) {
            return;
        }
        this.currentPhotoIndex = Math.min(Math.max(index, 0), this.attachedPhotos.length - 1);
        this.showCarouselModal = true;
    },
    nextPhoto() {
        if (this.attachedPhotos.length === 0) {
            return;
        }
        this.currentPhotoIndex = (this.currentPhotoIndex + 1) % this.attachedPhotos.length;
    },
    prevPhoto() {
        if (this.attachedPhotos.length === 0) {
            return;
        }
        this.currentPhotoIndex = (this.currentPhotoIndex - 1 + this.attachedPhotos.length) % this.attachedPhotos.length;
    },
    tryRemoveCurrentAttachedPhoto() {
        this.showRemoveConfirmation = true;
    },
    async removeCurrentAttachedPhoto() {
        this.showRemoveConfirmation = false;

        if (this.attachedPhotos.length === 0) {
            return;
        }

        const index = this.currentPhotoIndex;
        const photo = this.attachedPhotos[index];
        const serverPhotoId = photo && photo.serverPhotoId ? photo.serverPhotoId : null;

        const file = (this.attachedFiles && this.attachedFiles.length > index) ? this.attachedFiles[index] : null;

        if (this.$wire && serverPhotoId) {
            try {
                await this.$wire.call('deleteUploadedPhoto', this.photoKey, serverPhotoId);
            } catch (err) {
                this.toast('error', 'Failed to remove photo: ' + (err && err.message ? err.message : 'Unknown error'));
                return;
            }
        }

        this.attachedPhotos.splice(index, 1);
        if (this.attachedFiles && this.attachedFiles.length > index) {
            this.attachedFiles.splice(index, 1);
        }

        const dataTransfer = new DataTransfer();
        (this.attachedFiles || []).forEach(f => {
            if (!f) {
                return;
            }
            dataTransfer.items.add(f);
        });
        this.suppressInputChange = true;
        this.$refs.originalInput.files = dataTransfer.files;
        this.suppressInputChange = false;

        if (this.attachedPhotos.length === 0) {
            this.showCarouselModal = false;
            this.currentPhotoIndex = 0;
            this.toast('success', 'Photo removed');
            return;
        }

        this.currentPhotoIndex = Math.min(this.currentPhotoIndex, this.attachedPhotos.length - 1);
        this.toast('success', 'Photo removed');
    },
    deletePhoto(id) {
        this.photos = this.photos.filter(p => p.id !== id);
    },
    validatePhotos() {
        @if($required)
        const originalInput = this.$refs.originalInput;
        
        const hasFiles = originalInput && originalInput.files && originalInput.files.length > 0;
        
        if (!hasFiles) {
            return 'Please take at least one photo';
        }
        @endif
        return null;
    },
    async uploadPhotos() {
        if (this.photos.length === 0) {
            console.warn('[photo-attach] No photos to upload');
            this.toast('warning', 'No photos to upload!');
            return;
        }
        
        this.uploading = true;
        console.log('[photo-attach] Preparing upload', { count: this.photos.length });
        
        try {
            const files = await Promise.all(this.photos.map(async (photo, index) => {
                const response = await fetch(photo.data);
                const blob = await response.blob();
                console.log('[photo-attach] Created blob', { index, size: blob.size, type: blob.type });
                return new File([blob], 'photo_' + (index + 1) + '.jpg', { type: 'image/jpeg' });
            }));
            
            console.log('[photo-attach] Files ready', files.map(file => ({ name: file.name, size: file.size, type: file.type })));
            
            await this.uploadFilesToServer(files);

            const dataTransfer = new DataTransfer();
            const allFiles = [...this.attachedFiles, ...files];
            allFiles.forEach(file => {
                if (!file) {
                    return;
                }
                dataTransfer.items.add(file);
            });
            
            console.log('[photo-attach] DataTransfer size', { items: dataTransfer.items.length });
            
            this.$refs.originalInput.files = dataTransfer.files;
            console.log('[photo-attach] Input files set', { files: this.$refs.originalInput.files.length });
            
            this.suppressInputChange = true;
            this.suppressInputChange = false;
            
            this.toast('success', files.length + ' photo(s) uploaded successfully!');
            
            this.attachedFiles = allFiles;
            this.attachedPhotos = [...this.attachedPhotos, ...this.photos];
            this.assignServerPhotosToLastAttached(this.photos.length);
            this.photos = [];
            this.stopCamera();
            this.showCameraModal = false;
        } catch(err) {
            console.error('[photo-attach] Upload error:', err);
            this.toast('error', 'Upload failed: ' + err.message);
        } finally {
            this.uploading = false;
        }
    },
}">
    @if($label)
        <div class="flex items-center justify-between gap-3 mb-1">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ $label }}
                @if($required)
                    <span class="text-red-500">*</span>
                @endif
            </label>
            @if($canUpload)
                <button type="button"
                        @click="attachMode = (attachMode === 'camera' ? 'upload' : 'camera')"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md border border-gray-300 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 7l3-3M7 7l3 3M17 17H7m10 0l-3-3m3 3l-3 3"></path>
                    </svg>
                    <span x-text="attachMode === 'camera' ? 'Camera' : 'Gallery'"></span>
                </button>
            @endif
        </div>
    @endif

<template x-if="attachedPhotos.length === 0">
    <div class="flex items-center gap-2 mb-6">
        @if($canUpload)
            <button 
                @click="openAttachAction(); $event.preventDefault()"
                type="button"
                class="flex-1 flex items-center justify-center px-4 py-6 border-2 border-dashed rounded-lg cursor-pointer hover:border-blue-500 transition border-gray-300 dark:border-gray-600 bg-blue-50 dark:bg-gray-700 hover:bg-blue-100 dark:hover:bg-gray-600"
            >
                <template x-if="attachMode === 'camera'">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </template>
                <template x-if="attachMode === 'upload'">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </template>
                <span class="ml-2 text-blue-600 dark:text-blue-300" x-text="attachMode === 'upload' ? 'Upload photo' : 'Take photo'"></span>
            </button>
        @endif
    </div>
</template>

    <template x-if="attachedPhotos.length > 0">
        <div class="flex flex-col sm:flex-row gap-2 mb-6">
            <button 
                @click="openCarousel(0)"
                type="button"
                class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-gray-600 dark:bg-gray-700 hover:bg-gray-700 dark:hover:bg-gray-600 text-white text-sm font-medium rounded-lg transition-colors duration-150"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                See Photos (<span x-text="attachedPhotos.length"></span>)
            </button>
            @if($canUpload)
                <button 
                    @click="openAttachAction(); $event.preventDefault()"
                    type="button"
                    class="flex-1 flex items-center justify-center px-4 py-2 border-2 border-dashed rounded-lg cursor-pointer hover:border-blue-500 transition border-gray-300 dark:border-gray-600 bg-blue-50 dark:bg-gray-700 hover:bg-blue-100 dark:hover:bg-gray-600"
                >
                    <template x-if="attachMode === 'camera'">
                        <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </template>
                    <template x-if="attachMode === 'upload'">
                        <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </template>
                    <span class="text-blue-600 dark:text-blue-300" x-text="attachMode === 'upload' ? 'Upload Photo' : 'Take Photo'"></span>
                </button>
            @endif
        </div>
    </template>

    <input 
        type="file" 
        name="{{ $name }}[]" 
        id="{{ $name }}" 
        class="hidden"
        x-ref="originalInput"
        @change="handleInputChange($event)"
        accept="image/*"
        multiple
    >

    <div x-show="showCameraModal" 
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;">

    <div class="fixed inset-0 bg-black/80" @click="tryCancel()"></div>

    <div class="relative min-h-screen flex items-start sm:items-center justify-center p-2 sm:p-4">
        <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full p-4 sm:p-6 my-4 sm:my-8 max-h-[95vh] overflow-y-auto">
            
            <div class="flex items-center justify-between mb-4 sticky top-0 bg-white dark:bg-gray-800 z-10 pb-2">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">Add Photos</h3>
                <button @click="tryCancel()" type="button" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="flex flex-col items-center space-y-3 sm:space-y-4">
                <div class="w-full text-center py-2 px-3 sm:px-4 rounded-lg font-medium text-xs sm:text-sm"
                    :class="uploading ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300' : (processingGallery ? 'bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300' : (cameraActive ? 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'))">
                    <span x-text="uploading ? 'Uploading...' : (processingGallery ? 'Processing images...' : (cameraActive ? 'Camera is active' : 'Capture or select photos'))"></span>
                </div>
                
                <div class="relative w-full max-w-sm aspect-square bg-gray-900 rounded-lg overflow-hidden"
                    x-show="cameraActive">
                    <video x-ref="video" class="w-full h-full object-cover" autoplay playsinline></video>
                    <canvas x-ref="canvas" class="hidden"></canvas>
                </div>

                <div class="w-full" x-show="photos.length > 0">
                    <h4 class="text-base sm:text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2 sm:mb-3">Captured Photos (<span x-text="photos.length"></span>)</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-3 max-h-[40vh] overflow-y-auto pr-1">
                        <template x-for="photo in photos" :key="photo.id">
                            <div class="relative rounded-lg overflow-hidden shadow-md">
                                <img :src="photo.data" class="w-full h-24 sm:h-32 object-cover">
                                <button @click="deletePhoto(photo.id)" 
                                    class="absolute top-1 right-1 bg-red-500 dark:bg-red-600 hover:bg-red-600 dark:hover:bg-red-500 text-white text-xs px-1.5 sm:px-2 py-0.5 sm:py-1 rounded">
                                    Delete
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap justify-center sm:justify-end gap-2 mt-4 sm:mt-6 sticky bottom-0 bg-white dark:bg-gray-800 pt-2 pb-1 border-t border-gray-100 dark:border-gray-700">
                <button @click="tryCancel()" type="button"
                        :disabled="uploading || processingGallery"
                        class="px-3 sm:px-4 py-1.5 sm:py-2 text-sm sm:text-base bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed">
                    Cancel
                </button>
                
                <button @click="startCamera()" type="button"
                        x-show="!cameraActive && !uploading && !processingGallery"
                        class="px-3 sm:px-4 py-1.5 sm:py-2 text-sm sm:text-base bg-blue-500 dark:bg-blue-600 text-white rounded-md hover:bg-blue-600 dark:hover:bg-blue-500">
                    Start Camera
                </button>
                
                <button @click="capturePhoto()" type="button"
                        x-show="cameraActive && !uploading && !processingGallery"
                        class="px-3 sm:px-4 py-1.5 sm:py-2 text-sm sm:text-base bg-green-500 dark:bg-green-600 text-white rounded-md hover:bg-green-600 dark:hover:bg-green-500">
                    Capture Photo
                </button>
                
                <button @click="stopCamera()" type="button"
                        x-show="cameraActive && !uploading && !processingGallery"
                        class="px-3 sm:px-4 py-1.5 sm:py-2 text-sm sm:text-base bg-orange-500 dark:bg-orange-600 text-white rounded-md hover:bg-orange-600 dark:hover:bg-orange-500">
                    Stop Camera
                </button>
                
                <button @click="uploadPhotos()" type="button"
                        x-show="photos.length > 0 && !uploading && !processingGallery"
                        :disabled="uploading || processingGallery"
                        class="px-3 sm:px-4 py-1.5 sm:py-2 text-sm sm:text-base bg-blue-600 dark:bg-blue-700 text-white rounded-md hover:bg-blue-700 dark:hover:bg-blue-600 font-semibold disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!uploading">Upload <span x-text="photos.length"></span> Photo<span x-show="photos.length > 1">s</span></span>
                    <span x-show="uploading" class="inline-flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Uploading...
                    </span>
                </button>
            </div>

        <div x-show="showCancelConfirmation"
            x-cloak
            class="fixed inset-0 z-60 overflow-y-auto"
            style="display: none;">
            <div class="fixed inset-0 bg-black/50"></div>
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
                    <div class="text-center">
                        <div class="mx-auto mb-4 text-yellow-500 w-16 h-16">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Cancel Photo Capture?</h3>
                        <p class="text-gray-700 dark:text-gray-300 mb-4">Any captured photos that haven't been uploaded will be lost.</p>
                        <div class="flex gap-3 justify-center">
                            <button @click="showCancelConfirmation = false" type="button"
                                    class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                                Continue Capturing
                            </button>
                            <button @click="confirmCancel()" type="button"
                                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                Yes, Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> 
</div>
</div>

    <div x-show="showCarouselModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-black/80" @click="showCarouselModal = false"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-3xl p-4 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">Attached Photos</h3>
                    <button type="button" @click="showCarouselModal = false" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="relative w-full aspect-video bg-gray-900 rounded-lg overflow-hidden max-h-[70vh]">
                    <img :src="attachedPhotos[currentPhotoIndex]?.data" class="w-full h-full object-contain">

                    <template x-if="attachedPhotos.length > 1">
                        <button type="button" @click="prevPhoto()" class="absolute left-2 top-1/2 -translate-y-1/2 z-10 bg-black/60 hover:bg-black/80 text-white p-3 rounded-full shadow-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                    </template>

                    <template x-if="attachedPhotos.length > 1">
                        <button type="button" @click="nextPhoto()" class="absolute right-2 top-1/2 -translate-y-1/2 z-10 bg-black/60 hover:bg-black/80 text-white p-3 rounded-full shadow-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </template>
                </div>

                <div class="mt-3 text-center text-sm text-gray-600 dark:text-gray-300">
                    <span x-text="currentPhotoIndex + 1"></span> / <span x-text="attachedPhotos.length"></span>
                </div>

                @if($canUpload)
                    <div class="mt-4 flex justify-end">
                        <button type="button"
                                @click="tryRemoveCurrentAttachedPhoto()"
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            Remove
                        </button>
                    </div>
                @endif

                <div x-show="showRemoveConfirmation"
                    x-cloak
                    class="fixed inset-0 z-60 overflow-y-auto"
                    style="display: none;">
                    <div class="fixed inset-0 bg-black/50"></div>
                    <div class="relative min-h-screen flex items-center justify-center p-4">
                        <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
                            <div class="text-center">
                                <div class="mx-auto mb-4 text-yellow-500 w-16 h-16">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Remove Photo?</h3>
                                <p class="text-gray-700 dark:text-gray-300 mb-4">Are you sure you want to remove this photo? This action cannot be undone.</p>
                                <div class="flex gap-3 justify-center">
                                    <button @click="showRemoveConfirmation = false" type="button"
                                            class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        Keep Photo
                                    </button>
                                    <button @click="removeCurrentAttachedPhoto()" type="button"
                                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                        Yes, Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

 </div>
