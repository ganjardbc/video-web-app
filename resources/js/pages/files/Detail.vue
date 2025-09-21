<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Share2, Edit, Trash2, Eye, EyeOff, ExternalLink, CheckCircle } from 'lucide-vue-next';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

dayjs.extend(relativeTime);

import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import FileViewer from '@/components/FileViewer.vue';
import FileInformation from '@/components/FileInformation.vue';
import FileHeader from '@/components/FileHeader.vue';

import type { FilePreview } from '@/types/file';

interface FileDetailPageProps {
    file: FilePreview;
}

const props = defineProps<FileDetailPageProps>();

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
        title: props.file.original_name,
        href: `/files/${props.file.id}`,
    },
];

// Reactive state
const isLoading = ref(false);
const copySuccess = ref(false);

// Methods
const toggleVisibility = () => {
    isLoading.value = true;
    router.post(`/files/${props.file.id}/toggle-visibility`, {}, {
        onFinish: () => {
            isLoading.value = false;
        },
    });
};

const editFile = () => {
    router.visit(`/files/${props.file.id}/edit`);
};

const deleteFile = () => {
    if (!confirm(`Are you sure you want to delete "${props.file.original_name}"? This action cannot be undone.`)) {
        return;
    }

    isLoading.value = true;
    router.delete(`/files/${props.file.id}`, {
        onFinish: () => {
            isLoading.value = false;
        },
    });
};

const copyShareLink = async () => {
    if (!props.file.is_public) {
        alert('File must be public to share');
        return;
    }

    try {
        await navigator.clipboard.writeText(props.file.share_url ?? '');
        copySuccess.value = true;
        setTimeout(() => {
            copySuccess.value = false;
        }, 2000);
    } catch (err) {
        console.error('Failed to copy link:', err);
        alert('Failed to copy link to clipboard');
    }
};

const openInNewTab = () => {
    if (props.file.share_id) {
        window.open(`/share/${props.file.share_id}`, '_blank');
    }
};
</script>

<template>
    <Head :title="`File: ${props.file.original_name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <FileHeader
                :file="file"
            />

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- File Preview -->
                <div class="lg:col-span-2">
                    <div class="w-full space-y-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                File Preview
                            </h3>
                            <div class="flex items-center gap-2">
                                <Button
                                    v-if="props.file.file_url"
                                    @click="openInNewTab"
                                    variant="outline"
                                    size="sm"
                                >
                                    <ExternalLink class="h-4 w-4 mr-2" />
                                    Open
                                </Button>
                            </div>
                        </div>

                        <!-- File Viewer Component -->
                        <FileViewer
                            :file="props.file"
                            :show-controls="true"
                        />

                        <!-- File Information -->
                        <FileInformation
                            :file="props.file"
                        />
                    </div>
                </div>

                <!-- File Information & Actions -->
                <div class="lg:col-span-1">
                    <!-- Actions Card -->
                    <Card class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Actions
                        </h3>

                        <div class="space-y-3">

                            <!-- Share -->
                            <Button
                                @click="copyShareLink"
                                :disabled="!props.file.is_public"
                                class="w-full justify-start"
                                variant="outline"
                            >
                                <CheckCircle v-if="copySuccess" class="h-4 w-4 mr-2 text-green-600" />
                                <Share2 v-else class="h-4 w-4 mr-2" />
                                {{ copySuccess ? 'Link Copied!' : 'Copy Share Link' }}
                            </Button>

                            <!-- Toggle Visibility -->
                            <Button
                                @click="toggleVisibility"
                                :disabled="isLoading"
                                class="w-full justify-start"
                                variant="outline"
                            >
                                <EyeOff v-if="props.file.is_public" class="h-4 w-4 mr-2" />
                                <Eye v-else class="h-4 w-4 mr-2" />
                                Make {{ props.file.is_public ? 'Private' : 'Public' }}
                            </Button>

                            <!-- Edit -->
                            <Button
                                @click="editFile"
                                class="w-full justify-start"
                                variant="outline"
                            >
                                <Edit class="h-4 w-4 mr-2" />
                                Edit Details
                            </Button>

                            <!-- Delete -->
                            <Button
                                @click="deleteFile"
                                :disabled="isLoading"
                                class="w-full justify-start text-red-600 hover:text-red-700"
                                variant="outline"
                            >
                                <Trash2 class="h-4 w-4 mr-2" />
                                Delete File
                            </Button>
                        </div>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
