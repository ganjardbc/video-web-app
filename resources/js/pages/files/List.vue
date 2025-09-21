<script setup lang="ts">
import FileController from '@/actions/App/Http/Controllers/Api/FileController';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, watch } from 'vue';
import { Trash2, Eye, EyeOff, Share2, Download, Search, Plus } from 'lucide-vue-next';
import dayjs from 'dayjs';

import { Button } from '@/components/ui/button';
import DataTable from '@/components/DataTable.vue';
import Column from '@/components/Column.vue';
import Pagination from '@/components/Pagination.vue';
import { getNoTable } from '@/utils/table';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Files',
        href: '/files',
    },
];

interface FileItem {
    id: number;
    name: string;
    original_name: string;
    file_path: string;
    mime_type: string;
    formatted_size: string;
    is_public: boolean;
    share_id: string | null;
    expires_at: string | null;
    created_at: string;
    updated_at: string;
    download_url: string;
    preview_url: string | null;
}

interface PaginationData {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

const files = ref<FileItem[]>([]);
const pagination = ref<PaginationData>({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0,
    from: 0,
    to: 0,
});
const loading = ref(false);
const selectedFiles = ref<number[]>([]);

// Filters and sorting
const searchQuery = ref('');
const typeFilter = ref('all');
const visibilityFilter = ref('all');
const sortBy = ref('created_at');
const sortOrder = ref<'asc' | 'desc'>('desc');
const currentPage = ref(1);

// Filter options
const typeOptions = [
    { value: 'all', label: 'All Types' },
    { value: 'image', label: 'Images' },
    { value: 'video', label: 'Videos' },
    { value: 'audio', label: 'Audio' },
    { value: 'document', label: 'Documents' },
    { value: 'other', label: 'Other' },
];

const visibilityOptions = [
    { value: 'all', label: 'All Files' },
    { value: 'public', label: 'Public' },
    { value: 'private', label: 'Private' },
];

const sortOptions = [
    { value: 'created_at', label: 'Date Created' },
    { value: 'name', label: 'Name' },
    { value: 'file_size', label: 'Size' },
    { value: 'file_type', label: 'Type' },
];

// Methods
const fetchFiles = async () => {
    loading.value = true;
    try {
        // Build query parameters for the API request
        const queryParams = {
            page: currentPage.value,
            per_page: pagination.value.per_page,
            search: searchQuery.value,
            type: typeFilter.value !== 'all' ? typeFilter.value : '',
            is_public: visibilityFilter.value !== 'all' ? (visibilityFilter.value === 'public' ? '1' : '0') : '',
            sort: sortBy.value,
            order: sortOrder.value,
        };

        // Use FileController URL but with manual fetch (properly authenticated)
        const url = FileController.index.url({ query: queryParams });

        // Make authenticated request using session-based auth (like Profile.vue pattern)
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            const errorText = await response.text();
            console.error('API Error:', response.status, errorText);
            throw new Error(`Failed to fetch files: ${response.status} ${errorText}`);
        }

        const data = await response.json();

        files.value = data.data;
        pagination.value = {
            current_page: data.meta.current_page,
            last_page: data.meta.last_page,
            per_page: data.meta.per_page,
            total: data.meta.total,
            from: data.meta.from,
            to: data.meta.to,
        };
    } catch (error) {
        console.error('Error fetching files:', error);
    } finally {
        loading.value = false;
    }
};

const toggleVisibility = async (file: FileItem) => {
    try {
        const url = FileController.toggleVisibility.url(file.id);

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error('Failed to toggle visibility');
        }

        // Update the file in the list
        const index = files.value.findIndex(f => f.id === file.id);
        if (index > -1) {
            files.value[index].is_public = !files.value[index].is_public;
        }
    } catch (error) {
        console.error('Error toggling visibility:', error);
    }
};

const deleteFile = async (file: FileItem) => {
    if (!confirm(`Are you sure you want to delete "${file.original_name}"?`)) {
        return;
    }

    try {
        const url = FileController.destroy.url(file.id);

        const response = await fetch(url, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error('Failed to delete file');
        }

        // Remove the file from the list
        const index = files.value.findIndex(f => f.id === file.id);
        if (index > -1) {
            files.value.splice(index, 1);
        }

        // Update pagination
        pagination.value.total--;
    } catch (error) {
        console.error('Error deleting file:', error);
    }
};

const deleteSelected = async () => {
    if (selectedFiles.value.length === 0) return;

    if (!confirm(`Are you sure you want to delete ${selectedFiles.value.length} selected files?`)) {
        return;
    }

    const promises = selectedFiles.value.map(fileId => {
        const url = FileController.destroy.url(fileId);
        return fetch(url, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });
    });

    try {
        await Promise.all(promises);
        await fetchFiles(); // Refresh the list
        selectedFiles.value = [];
    } catch (error) {
        console.error('Error deleting files:', error);
    }
};

const copyShareLink = async (file: FileItem) => {
    if (file.share_id) {
        const shareUrl = `${window.location.origin}/share/${file.share_id}`;
        await navigator.clipboard.writeText(shareUrl);
        alert('Share link copied to clipboard!');
    }
};

const changePage = (page: number) => {
    currentPage.value = page;
};

// Watchers
watch([searchQuery, typeFilter, visibilityFilter, sortBy, sortOrder, currentPage], () => {
    fetchFiles();
}, { deep: true });

// Lifecycle
onMounted(() => {
    fetchFiles();
});
</script>

<template>
    <Head title="File Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">File Management</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Manage your uploaded files, control visibility, and share with others
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button
                        v-if="selectedFiles.length > 0"
                        @click="deleteSelected"
                        class="inline-flex items-center gap-2 rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600"
                    >
                        <Trash2 class="h-4 w-4" />
                        Delete Selected ({{ selectedFiles.length }})
                    </button>

                    <Link href="/files/create">
                        <Button>
                            <Plus class="h-4 w-4" />
                            Create New File
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Filters -->
            <div class="grid gap-4 md:grid-cols-4">
                <!-- Search -->
                <div class="relative">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search files..."
                        class="w-full rounded-md border border-gray-300 pl-10 pr-3 py-2 text-sm placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-500"
                    />
                </div>

                <!-- Type Filter -->
                <select
                    v-model="typeFilter"
                    class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                >
                    <option v-for="option in typeOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>

                <!-- Visibility Filter -->
                <select
                    v-model="visibilityFilter"
                    class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                >
                    <option v-for="option in visibilityOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>

                <!-- Sort -->
                <select
                    v-model="sortBy"
                    class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                >
                    <option v-for="option in sortOptions" :key="option.value" :value="option.value">
                        Sort by {{ option.label }}
                    </option>
                </select>
            </div>

            <DataTable :value="files" scrollable tableStyle="min-width: 50rem">
                <Column header="No">
                    <template #body="slotProps">
                        {{ getNoTable(slotProps.index, pagination.current_page, pagination.per_page) }}
                    </template>
                </Column>
                <Column field="name" header="Name">
                    <template #body="slotProps">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <img v-if="slotProps.data.preview_url && slotProps.data.mime_type.startsWith('image')" :src="slotProps.data.preview_url" :alt="slotProps.data.original_name" class="h-10 w-10 rounded object-cover">
                                <div v-else class="h-10 w-10 rounded bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                    <span class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ slotProps.data.mime_type.split('/')[1] || 'FILE' }}</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <Link :href="`/files/${slotProps.data.id}`" class="hover:underline">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400">{{ slotProps.data.original_name }}</div>
                                </Link>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ slotProps.data.name }}</div>
                            </div>
                        </div>
                    </template>
                </Column>
                <Column field="mime_type" header="Type"></Column>
                <Column field="formatted_size" header="Size"></Column>
                <Column field="visibility" header="Visibility">
                    <template #body="slotProps">
                        <button
                            @click="toggleVisibility(slotProps.data)"
                            :class="[
                                'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer',
                                slotProps.data.is_public
                                    ? 'bg-green-100 text-green-800 hover:bg-green-200 dark:bg-green-800 dark:text-green-200'
                                    : 'bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-200'
                            ]"
                        >
                            <Eye v-if="slotProps.data.is_public" class="h-3 w-3" />
                            <EyeOff v-else class="h-3 w-3" />
                            {{ slotProps.data.is_public ? 'Public' : 'Private' }}
                        </button>
                    </template>
                </Column>
                <Column field="createdAt" header="Created">
                    <template #body="slotProps">
                        {{ dayjs(slotProps.data.createdAt).format('DD/MM/YYYY, HH:mm:ss') }}
                    </template>
                </Column>
                <Column class="text-right" header="Actions">
                    <template #body="slotProps">
                        <div class="flex items-center justify-end gap-2">
                            <Link :href="`/files/${slotProps.data.id}`">
                                <Button
                                    size="sm"
                                    variant="outline"
                                    title="View Details"
                                >
                                    <Eye class="h-4 w-4" />
                                </Button>
                            </Link>
                            <a :href="slotProps.data.download_url" download>
                                <Button
                                    size="sm"
                                    variant="outline"
                                    title="Download"
                                >
                                    <Download class="h-4 w-4" />
                                </Button>
                            </a>
                            <Button
                                v-if="slotProps.data.is_public && slotProps.data.share_id"
                                size="sm"
                                variant="outline"
                                title="Share"
                                @click="copyShareLink(slotProps.data)"
                            >
                                <Share2 class="h-4 w-4" />
                            </Button>
                            <Button
                                size="sm"
                                variant="outline"
                                title="Delete"
                                class="text-red-600 hover:text-red-700"
                                @click="deleteFile(slotProps.data)"
                            >
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>
                    </template>
                </Column>
            </DataTable>

            <!-- Pagination Component -->
            <Pagination
                v-if="pagination.total > 0"
                :current-page="pagination.current_page"
                :total-pages="pagination.last_page"
                :total-items="pagination.total"
                :items-per-page="pagination.per_page"
                :show-info="true"
                :show-items-per-page="false"
                @page-change="changePage"
                @update:items-per-page="(perPage) => {
                    pagination.per_page = perPage;
                    currentPage = 1;
                    fetchFiles();
                }"
            />
        </div>
    </AppLayout>
</template>
