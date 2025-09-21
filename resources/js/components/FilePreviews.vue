<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { LoaderCircle, Grid3X3, List, Copy, Share2, Eye, Trash2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import type { FilePreview } from '@/types/file';

interface FilePreviewsProps {
    previews: FilePreview[];
}

defineProps<FilePreviewsProps>();

const viewMode = ref<'grid' | 'list'>('grid');

const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const handleKeyDown = (e: KeyboardEvent) => {
    if (e.key === '1') {
        viewMode.value = 'grid';
    } else if (e.key === '2') {
        viewMode.value = 'list';
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
});
</script>

<template>
    <div v-if="previews.length > 0" class="w-full">
        <!-- View Toggle -->
        <div class="w-full flex flex-col sm:flex-row gap-4 items-center justify-between pb-4">
            <h2 class="text-md text-gray-900 dark:text-gray-100 font-semibold">
                File Uploaded ({{ previews.length }})
            </h2>
            <div class="w-full sm:w-auto flex items-center justify-between sm:justify-end gap-3">
                <Button
                    size="sm"
                    variant="destructive"
                    @click="$emit('clearAll')"
                    class="cursor-pointer"
                >
                    Clear All ({{ previews.length }})
                </Button>

                <!-- View Mode Toggle -->
                <div class="flex items-center gap-1 bg-gray-100 dark:bg-gray-900 p-1 rounded-md">
                    <Button
                        :variant="viewMode === 'grid' ? 'default' : 'outline'"
                        size="sm"
                        @click="viewMode = 'grid'"
                        class="cursor-pointer"
                    >
                        <Grid3X3 class="h-4 w-4" />
                        Grid
                    </Button>
                    <Button
                        :variant="viewMode === 'list' ? 'default' : 'outline'"
                        size="sm"
                        @click="viewMode = 'list'"
                        class="cursor-pointer"
                    >
                        <List class="h-4 w-4" />
                        List
                    </Button>
                </div>
            </div>
        </div>

        <!-- Grid View -->
        <div v-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
                v-for="preview in previews"
                :key="preview.id"
                class="border rounded-lg p-4 space-y-4 relative bg-white dark:bg-gray-800"
            >
                <!-- Remove -->
                <Button
                    variant="destructive"
                    class="rounded-full w-8 h-8 p-0 absolute top-6 right-6 cursor-pointer z-5"
                    @click="$emit('remove', previews.indexOf(preview))"
                >
                    <Trash2 class="h-4 w-4" />
                </Button>

                <!-- Media Preview -->
                <div class="w-full aspect-video flex items-center justify-center rounded-lg overflow-hidden relative">
                    <img
                        v-if="preview?.type.startsWith('image')"
                        :src="preview?.download_url || preview?.localUrl"
                        :alt="preview?.original_name"
                        class="object-cover w-full h-full rounded overflow-hidden"
                        :class="{ 'opacity-50': preview?.uploading }"
                    />
                    <video
                        v-else-if="preview?.type.startsWith('video')"
                        :src="preview?.download_url || preview?.localUrl"
                        class="object-cover w-full h-full rounded overflow-hidden"
                        :class="{ 'opacity-50': preview?.uploading }"
                        muted
                    />
                    <div
                        v-else
                        class="w-full h-full bg-gray-100 dark:bg-gray-900 flex items-center justify-center rounded overflow-hidden"
                        :class="{ 'opacity-50': preview?.uploading }"
                    >
                        <div class="text-center">
                            <div class="text-2xl">📄</div>
                            <div class="text-sm text-gray-500 mt-1">{{ preview?.type }}</div>
                        </div>
                    </div>

                    <!-- Loading overlay -->
                    <div
                        v-if="preview?.uploading"
                        class="absolute inset-0 bg-black/20 flex items-center justify-center"
                    >
                        <LoaderCircle class="h-8 w-8 animate-spin text-white" />
                    </div>
                </div>

                <!-- File Info -->
                <div class="space-y-1">
                    <h3 class="text-black dark:text-white font-medium text-sm truncate" :title="preview?.original_name">
                        {{ preview?.original_name }}
                    </h3>
                    <p class="text-xs text-gray-500">{{ formatFileSize(preview?.size || 0) }}</p>

                    <div class="mt-3 bg-gray-100 dark:bg-gray-900 p-2 rounded-lg text-xs text-blue-600 dark:text-blue-400 truncate">
                        {{ preview?.share_url || 'No shareable link available' }}
                    </div>
                </div>

                <!-- Actions -->
                <div v-if="!preview?.uploading" class="flex gap-1">
                    <Button
                        variant="outline"
                        size="sm"
                        class="flex-1 cursor-pointer"
                        @click="$emit('copyToClipboard', preview?.share_url, previews.indexOf(preview))"
                    >
                        <Copy class="h-3 w-3 mr-1" />
                        Copy
                    </Button>
                    <Button
                        variant="outline"
                        size="sm"
                        class="flex-1 cursor-pointer"
                        @click="$emit('shareFile', preview, previews.indexOf(preview))"
                    >
                        <Share2 class="h-3 w-3 mr-1" />
                        Share
                    </Button>
                    <Button
                        variant="outline"
                        size="sm"
                        class="flex-1 cursor-pointer"
                        @click="$emit('openFile', preview)"
                    >
                        <Eye class="h-3 w-3 mr-1" />
                        View
                    </Button>
                </div>
            </div>
        </div>

        <!-- List View -->
        <div v-else class="space-y-3">
            <div
                v-for="preview in previews"
                :key="preview.id"
                class="border rounded-lg p-4 flex flex-col md:flex-row gap-4 bg-white dark:bg-gray-800"
            >
                <div class="flex-1 flex gap-4 w-full">
                    <!-- Media Thumbnail -->
                    <div class="w-16 h-16 rounded-lg overflow-hidden flex-shrink-0">
                        <img
                            v-if="preview?.type.startsWith('image')"
                            :src="preview?.download_url || preview?.localUrl"
                            :alt="preview?.original_name"
                            class="object-cover w-full h-full"
                            :class="{ 'opacity-50': preview?.uploading }"
                        />
                        <video
                            v-else-if="preview?.type.startsWith('video')"
                            :src="preview?.download_url || preview?.localUrl"
                            class="object-cover w-full h-full"
                            :class="{ 'opacity-50': preview?.uploading }"
                            muted
                        />
                        <div
                            v-else
                            class="w-full h-full bg-gray-100 flex items-center justify-center"
                            :class="{ 'opacity-50': preview?.uploading }"
                        >
                            <div class="text-center">
                                <div class="text-lg">📄</div>
                            </div>
                        </div>
                    </div>

                    <!-- File Info -->
                    <div class="flex-1 min-w-0 space-y-1">
                        <h3 class="text-black dark:text-white font-medium text-sm truncate" :title="preview?.original_name">
                            {{ preview?.original_name }}
                        </h3>
                        <p class="text-xs text-gray-500">{{ formatFileSize(preview?.size || 0) }}</p>

                        <div class="mt-3 bg-gray-100 dark:bg-gray-900 p-2 rounded-lg text-xs text-blue-600 dark:text-blue-400 truncate">
                            {{ preview?.share_url || 'No shareable link available' }}
                        </div>
                    </div>
                </div>

                <!-- Loading -->
                <div v-if="preview?.uploading" class="flex-shrink-0">
                    <LoaderCircle class="h-5 w-5 animate-spin text-primary" />
                </div>

                <!-- Actions -->
                <div v-else class="flex gap-1 justify-between flex-shrink-0">
                    <div class="flex gap-1">
                        <Button
                            variant="outline"
                            size="sm"
                            class="cursor-pointer"
                            @click="$emit('copyToClipboard', preview?.share_url, previews.indexOf(preview))"
                        >
                            <Copy class="h-3 w-3" />
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            class="cursor-pointer"
                            @click="$emit('shareFile', preview, previews.indexOf(preview))"
                        >
                            <Share2 class="h-3 w-3" />
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            class="cursor-pointer"
                            @click="$emit('openFile', preview)"
                        >
                            <Eye class="h-3 w-3" />
                        </Button>
                    </div>
                    <!-- Remove -->
                    <Button
                        variant="destructive"
                        size="sm"
                        class="cursor-pointer"
                        @click="$emit('remove', previews.indexOf(preview))"
                    >
                        <Trash2 class="h-3 w-3" />
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
