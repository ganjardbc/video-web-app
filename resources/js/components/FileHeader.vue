<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { Download, Share2, Clock, FileText, Image, Video } from 'lucide-vue-next';
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

const loading = ref(true);
const error = ref<string | null>(null);

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

const isImage = computed(() => props.file?.type.startsWith('image'));
const isVideo = computed(() => props.file?.type.startsWith('video'));
const copyingState = ref(false);

const copyToClipboard = async (text: string): Promise<void> => {
    try {
        copyingState.value = true;
        await navigator.clipboard.writeText(text);

        // Reset copying state after 2 seconds
        setTimeout(() => {
            copyingState.value = false;
        }, 2000);
    } catch (err) {
        console.error('Failed to copy: ', err);
        copyingState.value = false;
    }
};

const copyShareUrl = async () => {
    const shareUrl = window.location.href;
    try {
        if (navigator.share && shareUrl && props.file) {
            try {
                await navigator.share({
                    title: props.file.original_name,
                    text: `Check out this ${props.file.type.startsWith('image/') ? 'image' : 'video'}: ${props.file.original_name}`,
                    url: shareUrl,
                });
            } catch (err) {
                console.error('Sharing failed:', err);
                // If native sharing fails, fall back to copying
                await copyToClipboard(shareUrl);
            }
        } else if (shareUrl) {
            // Fallback to copying URL
            await copyToClipboard(shareUrl);
        }
    } catch (err) {
        console.error('Failed to copy URL:', err);
    }
};
const downloadFile = () => {
    if (props.file?.download_url) {
        window.open(props.file.download_url, '_blank');
    }
};

const fetchSharedFile = async () => {
    try {
        loading.value = true;
        error.value = null;

        // You may want to handle fetching and updating the file prop in the parent component instead.
        // This function can be removed or refactored as needed.

    } catch (err) {
        error.value = 'Failed to load file';
        console.error('Error fetching shared file:', err);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchSharedFile();
});
</script>

<template>
    <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border p-6">
        <div class="flex-1 flex flex-col lg:flex-row gap-4 items-start justify-between">
            <div class="flex-1 w-full flex flex-col md:flex-row gap-4">
                <!-- File Type Icon -->
                <div class="w-14 h-14 mx-auto md:mx-0 rounded-lg bg-primary/10 flex items-center justify-center">
                    <Image v-if="isImage" class="h-6 w-6 text-primary" />
                    <Video v-else-if="isVideo" class="h-6 w-6 text-primary" />
                    <FileText v-else class="h-6 w-6 text-primary" />
                </div>

                <!-- File Info -->
                <div class="flex-1 w-full flex flex-col gap-1 min-w-0">
                    <h1 class="text-sm sm:text-lg font-semibold text-gray-900 dark:text-white break-words">
                        {{ props.file.original_name }}
                    </h1>
                    <div class="mt-1 flex flex-col sm:flex-row space-x-4 text-sm text-gray-500">
                        <span class="flex items-center space-x-2 uppercase truncate">
                            {{ formatFileSize(props.file.size) }} | {{ props.file.mime_type }}
                        </span>
                        <span v-if="props.file.created_at" class="flex-shrink-0 flex items-center break-words">
                            <Clock class="h-3 w-3 mr-1" />
                            {{ formatDate(props.file.created_at) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="w-full lg:w-auto flex flex-col lg:flex-row items-center justify-end gap-2">
                <Button
                    variant="outline"
                    size="sm"
                    class="w-full lg:w-auto cursor-pointer"
                    @click="copyShareUrl"
                >
                    <Share2 class="h-4 w-4 mr-2" />
                    Share
                </Button>
                <Button
                    size="sm"
                    class="w-full lg:w-auto cursor-pointer"
                    @click="downloadFile"
                >
                    <Download class="h-4 w-4 mr-2" />
                    Download
                </Button>
            </div>
        </div>
    </div>
</template>
