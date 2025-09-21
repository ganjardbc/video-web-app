<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { LoaderCircle, AlertCircle } from 'lucide-vue-next';
import type { FilePreview } from '@/types/file';

import WelcomeBase from '@/layouts/WelcomeLayout.vue';
import FileViewer from '@/components/FileViewer.vue';
import FileInformation from '@/components/FileInformation.vue';
import FileHeader from '@/components/FileHeader.vue';

interface SharePageProps {
    shareId: string;
}

const props = defineProps<SharePageProps>();

const file = ref<FilePreview | null>(null);
const loading = ref(true);
const error = ref<string | null>(null);

const fetchSharedFile = async () => {
    try {
        loading.value = true;
        error.value = null;

        const response = await fetch(`/api/files/public/${props.shareId}`);
        const data = await response.json();

        if (data.success) {
            file.value = data.data;
        } else {
            error.value = data.message || 'File not found';
        }
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
    <WelcomeBase>
        <Head>
            <title>{{ file?.original_name || 'Shared File' }} - Video Web App</title>
            <meta name="description" :content="`View shared file: ${file?.original_name || 'Unknown file'}`" />
        </Head>

        <div class="w-full bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
            <div class="w-full flex flex-col gap-4">
                <!-- Loading State -->
                <div v-if="loading" class="flex items-center justify-center min-h-[400px]">
                    <div class="text-center">
                        <LoaderCircle class="h-8 w-8 animate-spin text-primary mx-auto mb-4" />
                        <p class="text-gray-600">Loading shared file...</p>
                    </div>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="flex items-center justify-center min-h-[400px]">
                    <div class="text-center max-w-md">
                        <AlertCircle class="h-12 w-12 text-red-500 mx-auto mb-4" />
                        <h2 class="text-xl font-semibold text-gray-900 mb-2">File Not Found</h2>
                        <p class="text-gray-600 mb-4">{{ error }}</p>
                        <p class="text-sm text-gray-500">This file may have been removed, expired, or made private.</p>
                    </div>
                </div>

                <!-- File Content -->
                <div v-else-if="file" class="space-y-6">
                    <!-- File Header -->
                    <FileHeader
                        :file="file"
                    />

                    <!-- File Viewer -->
                    <FileViewer
                        :file="file"
                        :show-controls="true"
                    />

                    <!-- File Information -->
                    <FileInformation
                        :file="file"
                    />
                </div>
            </div>
        </div>
    </WelcomeBase>
</template>
