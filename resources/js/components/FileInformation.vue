<script lang="ts" setup>
import type { FilePreview } from '@/types/file';

interface FileViewerProps {
    file: FilePreview;
    showControls?: boolean;
    maxHeight?: string;
}

withDefaults(defineProps<FileViewerProps>(), {
    showControls: true,
    maxHeight: '70vh',
});

const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            File Informations
        </h2>
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <dt class="text-sm font-medium text-gray-500">File name</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white break-all">
                    {{ file.original_name || 'N/A' }}
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">File size</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                    {{ formatFileSize(file.size) || 'N/A' }}
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">File type</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                    {{ file.mime_type || 'N/A' }}
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Dimensions</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                    {{ file.dimensions || 'N/A' }}
                </dd>
            </div>
            <div v-if="file.created_at">
                <dt class="text-sm font-medium text-gray-500">Uploaded</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                    {{ formatDate(file.created_at) || 'N/A' }}
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Share ID</dt>
                <dd class="bg-gray-100 dark:bg-gray-500 p-1 rounded-lg mt-1 text-sm text-gray-900 dark:text-white font-mono">
                    {{ file.share_id || 'N/A' }}
                </dd>
            </div>
        </dl>
    </div>
</template>
