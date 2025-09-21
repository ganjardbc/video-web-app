<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, onUnmounted } from 'vue';
import {
    Upload, X, Check, AlertCircle,
    Calendar, Eye, EyeOff, Loader2, FileImage, FileVideo,
    FileIcon
} from 'lucide-vue-next';
import dayjs from 'dayjs';

import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface UploadFile {
    id: string;
    file: File;
    preview?: string;
    progress: number;
    status: 'pending' | 'uploading' | 'success' | 'error';
    error?: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Files',
        href: '/files',
    },
    {
        title: 'Upload New File',
        href: '/files/create',
    },
];

// Reactive state
const isDragOver = ref(false);
const files = ref<UploadFile[]>([]);
const isPublic = ref(true);
const expiresAt = ref<string>('');
const isUploading = ref(false);
const uploadProgress = ref(0);

// File validation constants
const MAX_FILE_SIZE = 50 * 1024 * 1024; // 50MB in bytes
const ALLOWED_TYPES = [
    'image/jpeg', 'image/png', 'image/gif', 'image/webp',
    'video/mp4', 'video/webm', 'video/quicktime', 'video/x-msvideo'
];
const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm', 'mov', 'avi'];

// Computed properties
const hasFiles = computed(() => files.value.length > 0);
const totalSize = computed(() => files.value.reduce((total, f) => total + f.file.size, 0));
const formattedTotalSize = computed(() => formatFileSize(totalSize.value));
const canUpload = computed(() => hasFiles.value && !isUploading.value);
const uploadedFiles = computed(() => files.value.filter(f => f.status === 'success'));
const errorFiles = computed(() => files.value.filter(f => f.status === 'error'));

// Utility functions
const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const generateId = (): string => {
    return Math.random().toString(36).substr(2, 9);
};

const isImage = (file: File): boolean => {
    return file.type.startsWith('image/');
};

const isVideo = (file: File): boolean => {
    return file.type.startsWith('video/');
};

const validateFile = (file: File): { valid: boolean; error?: string } => {
    if (file.size > MAX_FILE_SIZE) {
        return { valid: false, error: `File size exceeds 100MB limit` };
    }

    if (!ALLOWED_TYPES.includes(file.type)) {
        return { valid: false, error: `File type not supported. Allowed: ${ALLOWED_EXTENSIONS.join(', ')}` };
    }

    return { valid: true };
};

const createFilePreview = (file: File): Promise<string | undefined> => {
    return new Promise((resolve) => {
        if (isImage(file)) {
            const reader = new FileReader();
            reader.onload = (e) => resolve(e.target?.result as string);
            reader.onerror = () => resolve(undefined);
            reader.readAsDataURL(file);
        } else {
            resolve(undefined);
        }
    });
};

// File handling methods
const handleFileSelect = async (selectedFiles: FileList | File[]) => {
    const fileArray = Array.from(selectedFiles);

    for (const file of fileArray) {
        const validation = validateFile(file);
        const preview = await createFilePreview(file);

        const uploadFile: UploadFile = {
            id: generateId(),
            file,
            preview,
            progress: 0,
            status: validation.valid ? 'pending' : 'error',
            error: validation.error,
        };

        files.value.push(uploadFile);
    }
};

const removeFile = (id: string) => {
    const index = files.value.findIndex(f => f.id === id);
    if (index > -1) {
        // Revoke preview URL to prevent memory leaks
        const file = files.value[index];
        if (file.preview) {
            URL.revokeObjectURL(file.preview);
        }
        files.value.splice(index, 1);
    }
};

const clearAllFiles = () => {
    // Revoke all preview URLs
    files.value.forEach(f => {
        if (f.preview) {
            URL.revokeObjectURL(f.preview);
        }
    });
    files.value = [];
};

// Upload methods
const uploadFile = async (uploadFile: UploadFile): Promise<void> => {
    const formData = new FormData();
    formData.append('file', uploadFile.file);
    formData.append('is_public', isPublic.value ? '1' : '0');

    if (expiresAt.value) {
        formData.append('expires_at', expiresAt.value);
    }

    uploadFile.status = 'uploading';
    uploadFile.progress = 0;

    try {
        // Get CSRF token from meta tag
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        const response = await fetch('/api/files', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken || '',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin', // Include cookies for session-based auth
        });

        const data = await response.json();

        if (response.ok && data.success) {
            uploadFile.status = 'success';
            uploadFile.progress = 100;
        } else {
            uploadFile.status = 'error';
            uploadFile.error = data.message || `Upload failed (${response.status})`;
        }
    } catch {
        uploadFile.status = 'error';
        uploadFile.error = 'Network error occurred';
    }
};

const uploadAllFiles = async () => {
    isUploading.value = true;
    uploadProgress.value = 0;

    const validFiles = files.value.filter(f => f.status === 'pending');
    let completed = 0;

    for (const file of validFiles) {
        await uploadFile(file);
        completed++;
        uploadProgress.value = (completed / validFiles.length) * 100;
    }

    isUploading.value = false;

    // Show success message and redirect if all uploads successful
    if (errorFiles.value.length === 0 && uploadedFiles.value.length > 0) {
        setTimeout(() => {
            router.visit('/files', {
                onSuccess: () => {
                    // Optional: Show success notification
                    console.log(`Successfully uploaded ${uploadedFiles.value.length} file(s)`);
                }
            });
        }, 1500);
    }
};

// Drag and drop handlers
const handleDragOver = (e: DragEvent) => {
    e.preventDefault();
    isDragOver.value = true;
};

const handleDragLeave = (e: DragEvent) => {
    e.preventDefault();
    isDragOver.value = false;
};

const handleDrop = async (e: DragEvent) => {
    e.preventDefault();
    isDragOver.value = false;

    const droppedFiles = e.dataTransfer?.files;
    if (droppedFiles) {
        await handleFileSelect(droppedFiles);
    }
};

// File input handler
const handleFileInput = async (e: Event) => {
    const input = e.target as HTMLInputElement;
    if (input.files) {
        await handleFileSelect(input.files);
        input.value = ''; // Reset input
    }
};

// Get file type icon
const getFileIcon = (file: File) => {
    if (isImage(file)) return FileImage;
    if (isVideo(file)) return FileVideo;
    return FileIcon;
};

// Get file type color
const getFileTypeColor = (file: File) => {
    if (isImage(file)) return 'text-blue-600';
    if (isVideo(file)) return 'text-purple-600';
    return 'text-gray-600';
};

// Cleanup on unmount
onUnmounted(() => {
    files.value.forEach(f => {
        if (f.preview) {
            URL.revokeObjectURL(f.preview);
        }
    });
});
</script>

<template>
    <Head title="Upload New File" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                        Upload New File
                    </h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Upload images and videos to share with others
                    </p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Upload Area -->
                <div class="lg:col-span-2">
                    <Card class="p-6">
                        <!-- Drag and Drop Area -->
                        <div
                            :class="[
                                'relative border-2 border-dashed rounded-lg p-8 text-center transition-all duration-200',
                                isDragOver
                                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                                    : 'border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500'
                            ]"
                            @dragover="handleDragOver"
                            @dragleave="handleDragLeave"
                            @drop="handleDrop"
                        >
                            <input
                                type="file"
                                multiple
                                accept="image/*,video/*"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                @change="handleFileInput"
                            />

                            <div class="space-y-4">
                                <div class="mx-auto w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                    <Upload class="w-8 h-8 text-gray-400" />
                                </div>

                                <div>
                                    <p class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                        Drop files here or click to browse
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Support for images (JPG, PNG, GIF, WebP) and videos (MP4, WebM, MOV, AVI)
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                        Maximum file size: 50MB
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- File List -->
                        <div v-if="hasFiles" class="mt-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                    Selected Files ({{ files.length }})
                                </h3>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                        Total: {{ formattedTotalSize }}
                                    </span>
                                    <Button
                                        @click="clearAllFiles"
                                        size="sm"
                                        variant="outline"
                                        :disabled="isUploading"
                                    >
                                        Clear All
                                    </Button>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div
                                    v-for="file in files"
                                    :key="file.id"
                                    class="flex items-center gap-4 p-4 border border-gray-200 dark:border-gray-700 rounded-lg"
                                >
                                    <!-- File Preview/Icon -->
                                    <div class="flex-shrink-0">
                                        <div v-if="file.preview" class="w-12 h-12 rounded-lg overflow-hidden">
                                            <img
                                                :src="file.preview"
                                                :alt="file.file.name"
                                                class="w-full h-full object-cover"
                                            />
                                        </div>
                                        <div v-else class="w-12 h-12 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                            <component
                                                :is="getFileIcon(file.file)"
                                                :class="['w-6 h-6', getFileTypeColor(file.file)]"
                                            />
                                        </div>
                                    </div>

                                    <!-- File Info -->
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                            {{ file.file.name }}
                                        </p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">
                                            {{ formatFileSize(file.file.size) }} • {{ file.file.type }}
                                        </p>

                                        <!-- Progress Bar -->
                                        <div v-if="file.status === 'uploading'" class="mt-2">
                                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                                <div
                                                    class="bg-blue-600 h-1.5 rounded-full transition-all duration-300"
                                                    :style="{ width: file.progress + '%' }"
                                                ></div>
                                            </div>
                                        </div>

                                        <!-- Error Message -->
                                        <div v-if="file.error" class="mt-1">
                                            <p class="text-xs text-red-600 dark:text-red-400">
                                                {{ file.error }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Status Icon -->
                                    <div class="flex-shrink-0">
                                        <Loader2 v-if="file.status === 'uploading'" class="w-5 h-5 text-blue-600 animate-spin" />
                                        <Check v-else-if="file.status === 'success'" class="w-5 h-5 text-green-600" />
                                        <AlertCircle v-else-if="file.status === 'error'" class="w-5 h-5 text-red-600" />
                                        <Button
                                            v-else
                                            @click="removeFile(file.id)"
                                            size="sm"
                                            variant="ghost"
                                            :disabled="isUploading"
                                        >
                                            <X class="w-4 h-4" />
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Card>
                </div>

                <!-- Upload Settings -->
                <div class="lg:col-span-1">
                    <Card class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Upload Settings
                        </h3>

                        <div class="space-y-6">
                            <!-- Visibility Setting -->
                            <div>
                                <Label class="text-sm font-medium text-gray-900 dark:text-white">
                                    File Visibility
                                </Label>
                                <div class="mt-2 space-y-2">
                                    <label class="flex items-center">
                                        <input
                                            v-model="isPublic"
                                            type="radio"
                                            :value="true"
                                            class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                        />
                                        <span class="ml-2 text-sm text-gray-900 dark:text-white flex items-center gap-2">
                                            <Eye class="w-4 h-4" />
                                            Public - Anyone with the link can view
                                        </span>
                                    </label>
                                    <label class="flex items-center">
                                        <input
                                            v-model="isPublic"
                                            type="radio"
                                            :value="false"
                                            class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                        />
                                        <span class="ml-2 text-sm text-gray-900 dark:text-white flex items-center gap-2">
                                            <EyeOff class="w-4 h-4" />
                                            Private - Only you can view
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <!-- Expiration Date -->
                            <div>
                                <Label for="expires_at" class="text-sm font-medium text-gray-900 dark:text-white">
                                    Expiration Date (Optional)
                                </Label>
                                <div class="mt-2 relative">
                                    <Input
                                        id="expires_at"
                                        v-model="expiresAt"
                                        type="datetime-local"
                                        :min="dayjs().format('YYYY-MM-DDTHH:mm')"
                                        :max="dayjs().add(1, 'year').format('YYYY-MM-DDTHH:mm')"
                                        class="pl-10"
                                    />
                                    <Calendar class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                                </div>
                                <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                    Files will be automatically deleted after this date
                                </p>
                            </div>

                            <!-- Upload Button -->
                            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                <Button
                                    @click="uploadAllFiles"
                                    :disabled="!canUpload"
                                    class="w-full"
                                    size="lg"
                                >
                                    <Loader2 v-if="isUploading" class="w-4 h-4 mr-2 animate-spin" />
                                    <Upload v-else class="w-4 h-4 mr-2" />
                                    {{ isUploading ? 'Uploading...' : `Upload ${files.length} File${files.length !== 1 ? 's' : ''}` }}
                                </Button>

                                <!-- Upload Progress -->
                                <div v-if="isUploading" class="mt-4">
                                    <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-1">
                                        <span>Upload Progress</span>
                                        <span>{{ Math.round(uploadProgress) }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div
                                            class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                                            :style="{ width: uploadProgress + '%' }"
                                        ></div>
                                    </div>
                                </div>

                                <!-- Success/Error Summary -->
                                <div v-if="uploadedFiles.length > 0 || errorFiles.length > 0" class="mt-4 space-y-2">
                                    <div v-if="uploadedFiles.length > 0" class="flex items-center gap-2 text-sm text-green-600 dark:text-green-400">
                                        <Check class="w-4 h-4" />
                                        {{ uploadedFiles.length }} file{{ uploadedFiles.length !== 1 ? 's' : '' }} uploaded successfully
                                    </div>
                                    <div v-if="errorFiles.length > 0" class="flex items-center gap-2 text-sm text-red-600 dark:text-red-400">
                                        <AlertCircle class="w-4 h-4" />
                                        {{ errorFiles.length }} file{{ errorFiles.length !== 1 ? 's' : '' }} failed to upload
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
