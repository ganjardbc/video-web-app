<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { FileText, Eye, EyeOff, Calendar, Save } from 'lucide-vue-next';
import dayjs from 'dayjs';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card } from '@/components/ui/card';
import InputError from '@/components/InputError.vue';

interface FileData {
    id: number;
    share_id: string;
    original_name: string;
    mime_type: string;
    formatted_size: string;
    type: string;
    is_public: boolean;
    expires_at: string | null;
    created_at: string;
}

interface FileEditPageProps {
    file: FileData;
}

const props = defineProps<FileEditPageProps>();

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
    {
        title: 'Edit',
        href: `/files/${props.file.id}/edit`,
    },
];

const form = useForm({
    original_name: props.file.original_name,
    is_public: props.file.is_public,
    expires_at: props.file.expires_at ? dayjs(props.file.expires_at).format('YYYY-MM-DDTHH:mm') : '',
});

const submit = () => {
    form.put(`/files/${props.file.id}`, {
        onSuccess: () => {
            // Form submission success will be handled by redirect from controller
        },
    });
};
</script>

<template>
    <Head :title="`Edit File: ${props.file.original_name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                        Edit File
                    </h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Update file information and settings
                    </p>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <!-- Form -->
                <div class="md:col-span-2">
                    <Card class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            File Information
                        </h3>

                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- File Name -->
                            <div class="grid gap-2">
                                <Label for="original_name" class="flex items-center gap-2">
                                    <FileText class="h-4 w-4" />
                                    File Name
                                </Label>
                                <Input
                                    id="original_name"
                                    v-model="form.original_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                    placeholder="Enter file name"
                                />
                                <InputError :message="form.errors.original_name" />
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    This is the display name for your file.
                                </p>
                            </div>

                            <!-- Visibility -->
                            <div class="grid gap-3">
                                <Label class="flex items-center gap-2">
                                    <Eye v-if="form.is_public" class="h-4 w-4" />
                                    <EyeOff v-else class="h-4 w-4" />
                                    Visibility
                                </Label>
                                <div class="space-y-3">
                                    <label class="flex items-center space-x-3 cursor-pointer">
                                        <input
                                            v-model="form.is_public"
                                            :value="true"
                                            type="radio"
                                            class="form-radio h-4 w-4 text-blue-600"
                                        />
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-white">Public</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                Anyone with the link can view and download this file
                                            </div>
                                        </div>
                                    </label>
                                    <label class="flex items-center space-x-3 cursor-pointer">
                                        <input
                                            v-model="form.is_public"
                                            :value="false"
                                            type="radio"
                                            class="form-radio h-4 w-4 text-blue-600"
                                        />
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-white">Private</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                Only you can access this file
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <InputError :message="form.errors.is_public" />
                            </div>

                            <!-- Expiration Date -->
                            <div class="grid gap-2">
                                <Label for="expires_at" class="flex items-center gap-2">
                                    <Calendar class="h-4 w-4" />
                                    Expiration Date (Optional)
                                </Label>
                                <Input
                                    id="expires_at"
                                    v-model="form.expires_at"
                                    type="datetime-local"
                                    class="mt-1 block w-full"
                                    :min="dayjs().format('YYYY-MM-DDTHH:mm')"
                                />
                                <InputError :message="form.errors.expires_at" />
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Leave empty for no expiration. File will automatically become inaccessible after this date.
                                </p>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex items-center gap-4 pt-4">
                                <Button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="flex items-center gap-2"
                                >
                                    <Save class="h-4 w-4" />
                                    {{ form.processing ? 'Updating...' : 'Update File' }}
                                </Button>

                                <Link :href="`/files/${props.file.id}`">
                                    <Button variant="outline" type="button">
                                        Cancel
                                    </Button>
                                </Link>
                            </div>
                        </form>
                    </Card>
                </div>

                <!-- File Info -->
                <div class="md:col-span-1">
                    <Card class="p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Current File
                        </h3>

                        <div class="space-y-3 text-sm">
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">File Name:</span>
                                <p class="font-medium text-gray-900 dark:text-white break-words">
                                    {{ props.file.original_name }}
                                </p>
                            </div>
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Type:</span>
                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ props.file.mime_type }}
                                </p>
                            </div>
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Size:</span>
                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ props.file.formatted_size }}
                                </p>
                            </div>
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Created:</span>
                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ dayjs(props.file.created_at).format('DD MMM YYYY') }}
                                </p>
                            </div>
                        </div>
                    </Card>

                    <Card class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Information
                        </h3>

                        <div class="space-y-4 text-sm text-gray-600 dark:text-gray-400">
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white mb-1">
                                    File Name
                                </h4>
                                <p>
                                    Changes to the file name only affect how it's displayed.
                                    The actual file remains unchanged.
                                </p>
                            </div>

                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white mb-1">
                                    Visibility
                                </h4>
                                <p>
                                    Public files can be accessed by anyone with the share link.
                                    Private files are only accessible to you.
                                </p>
                            </div>

                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white mb-1">
                                    Expiration
                                </h4>
                                <p>
                                    Expired files become inaccessible but are not automatically deleted.
                                    You can remove the expiration date to make them accessible again.
                                </p>
                            </div>
                        </div>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
