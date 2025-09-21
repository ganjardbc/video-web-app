<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import {
    FileText, Download, Loader2,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import type { FilePreview } from '@/types/file';

interface FileViewerProps {
    file: FilePreview;
    showControls?: boolean;
    maxHeight?: string;
}

const props = withDefaults(defineProps<FileViewerProps>(), {
    showControls: true,
    maxHeight: '70vh',
});

// Reactive state
const isLoading = ref(false);
const hasError = ref(false);
const isFullscreen = ref(false);

// Computed properties
const fileUrl = computed(() => {
    // For images and videos, prefer file_url (direct storage URL) for better performance
    // but fallback to download_url (API endpoint) which always works
    if (props.file.file_url) {
        return props.file.file_url;
    }
    return props.file.download_url;
});

const isImage = computed(() =>
    props.file.type === 'image' || props.file.mime_type.startsWith('image/')
);

const isVideo = computed(() =>
    props.file.type === 'video' || props.file.mime_type.startsWith('video/')
);

const isPdf = computed(() =>
    props.file.mime_type === 'application/pdf'
);

// Methods
const onImageLoad = () => {
    isLoading.value = false;
    hasError.value = false;
};

const onImageError = () => {
    isLoading.value = false;
    hasError.value = true;
};

const downloadFile = () => {
    const link = document.createElement('a');
    link.href = props.file.download_url ?? '';
    link.download = props.file.original_name ?? '';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

// Lifecycle
onMounted(() => {
    // Listen for fullscreen changes
    document.addEventListener('fullscreenchange', () => {
        isFullscreen.value = !!document.fullscreenElement;
    });
});
</script>

<template>
    <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-sm border overflow-hidden">
         <!-- Loading State -->
        <div v-if="isLoading" class="z-10 absolute top-0 left-0 w-full h-full flex items-center justify-center">
            <Loader2 class="h-8 w-8 animate-spin text-gray-400" />
            <span class="ml-2 text-gray-600 dark:text-gray-400">Loading...</span>
        </div>

        <!-- Image Preview -->
        <div v-if="isImage" class="relative">
            <img
                :src="props.file.download_url"
                :alt="props.file.original_name"
                class="w-full h-auto max-h-[70vh] object-contain bg-gray-50"
                @load="onImageLoad"
                @error="(e) => (e.target as HTMLImageElement).style.display = 'none'"
            />
        </div>

        <!-- Video Preview -->
        <div v-else-if="isVideo" class="relative bg-black">
            <video
                :src="props.file.download_url"
                controls
                class="w-full h-auto max-h-[70vh] object-contain"
                @load="onImageLoad"
                @error="(e) => (e.target as HTMLVideoElement).style.display = 'none'"
            >
                Your browser does not support the video tag.
            </video>
        </div>

        <!-- PDF Preview -->
        <div v-else-if="isPdf" class="relative">
            <iframe
                :src="fileUrl"
                class="w-full border-0"
                :style="{ height: props.maxHeight }"
                @load="onImageLoad"
                @error="onImageError"
            ></iframe>

            <!-- PDF Controls -->
            <div v-if="props.showControls" class="absolute top-4 right-4">
                <Button @click="downloadFile" size="sm" class="bg-black/50 backdrop-blur-sm text-white hover:bg-black/70">
                    <Download class="h-4 w-4 mr-2" />
                    Download PDF
                </Button>
            </div>
        </div>

        <!-- Other File Types -->
        <div v-else class="p-12 text-center">
            <FileText class="h-16 w-16 text-gray-400 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Preview not available</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-2">
                {{ props.file.mime_type }}
            </p>
            <p class="text-gray-600 mb-4">
                This file type cannot be previewed in the browser.
            </p>
            <Button @click="downloadFile" class="cursor-pointer">
                <Download class="h-4 w-4 mr-2" />
                Download to view
            </Button>
        </div>

        <!-- Error State -->
        <div v-if="hasError" class="p-12 text-center">
            <FileText class="h-16 w-16 text-red-400 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                Failed to load file
            </h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                There was an error loading this file. You can try downloading it instead.
            </p>
            <Button @click="downloadFile" variant="outline">
                <Download class="h-4 w-4 mr-2" />
                Download File
            </Button>
        </div>
    </div>
</template>
