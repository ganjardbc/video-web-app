<script setup lang="ts">
import { ref, onUnmounted } from 'vue';

import WelcomeBase from '@/layouts/WelcomeLayout.vue';
import FileUpload from '@/components/FileUpload.vue';
import FilePreviews from '@/components/FilePreviews.vue';
import type { FilePreview } from '@/types/file';

const isLoading = ref(false);
const uploadErrors = ref<string[]>([]);
const copyingStates = ref<Record<number, boolean>>({});
const previews = ref<FilePreview[]>([]);

// File validation constants
const MAX_FILE_SIZE = 50 * 1024 * 1024; // 50MB
const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'video/webm', 'video/quicktime'];

const validateFile = (file: File): string | null => {
    if (file.size > MAX_FILE_SIZE) {
        return `File "${file.name}" is too large. Maximum size is 50MB. Please compress your file before uploading.`;
    }

    if (!ALLOWED_TYPES.includes(file.type)) {
        return `File "${file.name}" has unsupported format. Only images and videos are allowed.`;
    }

    return null;
};

const processFiles = async (files: FileList | File[]) => {
    isLoading.value = true;
    uploadErrors.value = [];

    const fileArray = Array.from(files);

    for (const file of fileArray) {
        // Validate file before creating preview
        const validationError = validateFile(file);
        if (validationError) {
            uploadErrors.value.push(validationError);
            continue;
        }

        // Create local preview first
        const localUrl = URL.createObjectURL(file);
        const filePreview = {
            original_name: file.name,
            mime_type: file.type,
            size: file.size,
            type: file.type.startsWith('image') ? 'image' : 'video',
            uploading: true,
            progress: 0,
            localUrl: localUrl,
            expires_at: '', // default empty string or appropriate value
            download_count: 0,
            file_url: '',
            extension: '',
            share_url: '',
            error: undefined // assuming error is optional
        } as FilePreview;

        previews.value.push(filePreview);

        try {
            const currentIndex = previews.value.length - 1;

            // Upload file to API
            await uploadFileToAPI(file, currentIndex);
        } catch (uploadError) {
            console.error('Upload failed:', uploadError);
            filePreview.uploading = false;
            filePreview.error = uploadError instanceof Error ? uploadError.message : 'Upload failed';
        }
    }

    isLoading.value = false;
};

const uploadFileToAPI = async (file: File, index: number): Promise<void> => {
    const preview = previews.value[index];
    const formData = new FormData();
    formData.append('file', file);
    formData.append('is_public', '1'); // Send as string '1' for true

    try {
        // Simulate progress updates
        const progressInterval = setInterval(() => {
            if (preview?.progress !== undefined && preview?.progress < 90) {
                preview.progress += Math.random() * 10 + 5;
            }
        }, 200);

        const response = await fetch('/api/files', {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        clearInterval(progressInterval);

        if (!response.ok) {
            let errorMessage = 'Upload failed';

            try {
                const errorData = await response.json();
                errorMessage = errorData.message || 'Upload failed';
            } catch {
                // If JSON parsing fails, try reading as text
                try {
                    const errorText = await response.text();
                    if (errorText.includes('413') || errorText.includes('upload_max_filesize')) {
                        errorMessage = `File too large. Maximum upload size is 50MB. Please compress your video or use a smaller file.`;
                    } else if (errorText.includes('PHP Fatal error') || errorText.includes('Maximum execution time')) {
                        errorMessage = 'Server error during upload. File may be too large or complex to process.';
                    } else {
                        errorMessage = 'Server error. Please try with a smaller file.';
                    }
                } catch {
                    errorMessage = 'Unknown server error occurred.';
                }
            }

            throw new Error(errorMessage);
        }

        // Try to parse JSON response for successful requests
        try {
            const responseText = await response.text();

            // Check if response contains PHP warnings/errors even with 200 status
            if (responseText.includes('POST Content-Length') && responseText.includes('exceeds the limit')) {
                throw new Error('File too large for server. Please compress your video or use a smaller file (server may still have limits).');
            }

            if (responseText.includes('PostTooLargeException')) {
                throw new Error('File too large for server. Please compress your video or use a smaller file.');
            }            // Try to parse as JSON (might have HTML prefix due to PHP warnings)
            const jsonStart = responseText.indexOf('{');
            if (jsonStart === -1) {
                throw new Error('Server returned invalid response.');
            }

            const jsonPart = responseText.substring(jsonStart);
            const result = JSON.parse(jsonPart);

            if (result.success && result.data) {
                // Update preview with API response data while preserving localUrl
                Object.assign(preview, {
                    ...result.data,
                    uploading: false,
                    progress: 100,
                    error: undefined,
                    localUrl: preview.localUrl // Preserve the blob URL for preview
                });
            } else {
                throw new Error(result.message || 'Upload failed');
            }
        } catch (parseError) {
            console.error('Failed to parse successful response:', parseError);
            if (parseError instanceof Error && parseError.message.includes('File too large')) {
                throw parseError; // Re-throw our custom error
            }
            throw new Error('Server returned unexpected response format.');
        }

    } catch (error) {
        console.error('API upload error:', error);
        preview.uploading = false;
        preview.error = error instanceof Error ? error.message : 'Upload failed';
        throw error;
    }
};

const copyToClipboard = async (text: string, index: number): Promise<void> => {
    try {
        copyingStates.value[index] = true;
        await navigator.clipboard.writeText(text);

        alert('Link copied to clipboard!');

        // Reset copying state after 2 seconds
        setTimeout(() => {
            copyingStates.value[index] = false;
        }, 2000);
    } catch (err) {
        console.error('Failed to copy: ', err);
        copyingStates.value[index] = false;
    }
};

const shareFile = async (preview: FilePreview, index: number): Promise<void> => {
    const shareUrl = preview?.share_url || preview?.localUrl;
    if (navigator.share && shareUrl) {
        try {
            await navigator.share({
                title: preview?.original_name,
                text: `Check out this ${preview?.type.startsWith('image/') ? 'image' : 'video'}: ${preview?.original_name}`,
                url: shareUrl,
            });
        } catch (err) {
            console.error('Sharing failed:', err);
        }
    } else if (shareUrl) {
        // Fallback to copying URL
        await copyToClipboard(shareUrl, index);
    }
};

const openFileInNewTab = (preview: FilePreview): void => {
    const url = preview?.share_url || preview?.localUrl;
    if (url) {
        window.open(url, '_blank');
    }
};

const removePreview = (index: number) => {
    const preview = previews.value[index];
    if (preview?.localUrl) {
        URL.revokeObjectURL(preview?.localUrl);
    }
    previews.value.splice(index, 1);
};

const clearAll = () => {
    previews.value.forEach(preview => {
        if (preview?.localUrl) {
            URL.revokeObjectURL(preview?.localUrl);
        }
    });
    previews.value = [];
    uploadErrors.value = [];
};

// Event handlers for child components
const handleFilesSelected = (files: FileList | File[]) => {
    processFiles(files);
};

const handleRemoveFile = (index: number) => {
    removePreview(index);
};

const handleClearAll = () => {
    clearAll();
};

const handleCopyToClipboard = (text: string, index: number) => {
    copyToClipboard(text, index);
};

const handleShareFile = (preview: FilePreview, index: number) => {
    shareFile(preview, index);
};

const handleOpenFile = (preview: FilePreview) => {
    openFileInNewTab(preview);
};

onUnmounted(() => {
    // Cleanup object URLs
    previews.value.forEach(preview => {
        if (preview?.localUrl) {
            URL.revokeObjectURL(preview?.localUrl);
        }
    });
});
</script>

<template>
    <WelcomeBase>
        <div class="w-full flex flex-col items-center gap-4">
            <h1 class="text-2xl lg:text-4xl font-bold text-gray-700 dark:text-gray-200 text-center">
                Upload and Share Easily
            </h1>
            <p class="text-center text-sm lg:text-md text-gray-600 dark:text-gray-400">
                A simple platform to upload and share your images and videos without logging in.
                <br />
                <span class="text-xs text-orange-600 dark:text-orange-400">
                    Current limit: 50MB per file. For larger files, please compress before uploading.
                </span>
            </p>
        </div>

        <!-- File Upload Component -->
        <FileUpload
            v-if="!previews.length"
            :is-loading="isLoading"
            :upload-errors="uploadErrors"
            @files-selected="handleFilesSelected"
        />

        <!-- File Previews Component -->
        <FilePreviews
            v-if="previews.length"
            :previews="previews"
            :copying-states="copyingStates"
            @remove="handleRemoveFile"
            @clear-all="handleClearAll"
            @copy-to-clipboard="handleCopyToClipboard"
            @share-file="handleShareFile"
            @open-file="handleOpenFile"
        />
    </WelcomeBase>
</template>
