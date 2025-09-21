<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { User, Mail, Calendar, CheckCircle, XCircle, Edit } from 'lucide-vue-next';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

dayjs.extend(relativeTime);

import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';

interface UserData {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

interface UserShowPageProps {
    user: UserData;
}

const props = defineProps<UserShowPageProps>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Users',
        href: '/users',
    },
    {
        title: props.user.name,
        href: `/users/${props.user.id}`,
    },
];
</script>

<template>
    <Head :title="`User: ${props.user.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ props.user.name }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        User details and account information
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="`/users/${props.user.id}/edit`">
                        <Button>
                            <Edit class="h-4 w-4 mr-2" />
                            Edit User
                        </Button>
                    </Link>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <!-- User Avatar and Basic Info -->
                <div class="md:col-span-1">
                    <Card class="p-6">
                        <div class="flex flex-col items-center text-center">
                            <!-- Avatar -->
                            <div class="h-20 w-20 rounded-full bg-blue-500 flex items-center justify-center mb-4">
                                <span class="text-2xl font-bold text-white">
                                    {{ props.user.name.charAt(0).toUpperCase() }}
                                </span>
                            </div>

                            <!-- Name -->
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                {{ props.user.name }}
                            </h2>

                            <!-- Email -->
                            <p class="text-gray-600 dark:text-gray-400 mb-4">
                                {{ props.user.email }}
                            </p>

                            <!-- Status Badge -->
                            <div class="flex items-center gap-2">
                                <span
                                    :class="[
                                        'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium gap-1',
                                        props.user.email_verified_at
                                            ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-200'
                                            : 'bg-orange-100 text-orange-800 dark:bg-orange-800 dark:text-orange-200'
                                    ]"
                                >
                                    <CheckCircle v-if="props.user.email_verified_at" class="h-4 w-4" />
                                    <XCircle v-else class="h-4 w-4" />
                                    {{ props.user.email_verified_at ? 'Verified' : 'Unverified' }}
                                </span>
                            </div>
                        </div>
                    </Card>
                </div>

                <!-- User Details -->
                <div class="md:col-span-2">
                    <Card class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Account Information
                        </h3>

                        <div class="space-y-4">
                            <!-- User ID -->
                            <div class="flex items-start gap-3">
                                <User class="h-5 w-5 text-gray-400 mt-0.5" />
                                <div>
                                    <dt class="text-sm font-medium text-gray-900 dark:text-white">
                                        User ID
                                    </dt>
                                    <dd class="text-sm text-gray-600 dark:text-gray-400">
                                        #{{ props.user.id }}
                                    </dd>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="flex items-start gap-3">
                                <Mail class="h-5 w-5 text-gray-400 mt-0.5" />
                                <div>
                                    <dt class="text-sm font-medium text-gray-900 dark:text-white">
                                        Email Address
                                    </dt>
                                    <dd class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ props.user.email }}
                                    </dd>
                                </div>
                            </div>

                            <!-- Email Verification -->
                            <div class="flex items-start gap-3">
                                <CheckCircle v-if="props.user.email_verified_at" class="h-5 w-5 text-green-500 mt-0.5" />
                                <XCircle v-else class="h-5 w-5 text-orange-500 mt-0.5" />
                                <div>
                                    <dt class="text-sm font-medium text-gray-900 dark:text-white">
                                        Email Verification
                                    </dt>
                                    <dd class="text-sm text-gray-600 dark:text-gray-400">
                                        <span v-if="props.user.email_verified_at">
                                            Verified on {{ dayjs(props.user.email_verified_at).format('DD MMM YYYY, HH:mm') }}
                                        </span>
                                        <span v-else>
                                            Not verified
                                        </span>
                                    </dd>
                                </div>
                            </div>

                            <!-- Registration Date -->
                            <div class="flex items-start gap-3">
                                <Calendar class="h-5 w-5 text-gray-400 mt-0.5" />
                                <div>
                                    <dt class="text-sm font-medium text-gray-900 dark:text-white">
                                        Registration Date
                                    </dt>
                                    <dd class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ dayjs(props.user.created_at).format('DD MMM YYYY, HH:mm') }}
                                        ({{ dayjs(props.user.created_at).fromNow() }})
                                    </dd>
                                </div>
                            </div>

                            <!-- Last Updated -->
                            <div class="flex items-start gap-3">
                                <Calendar class="h-5 w-5 text-gray-400 mt-0.5" />
                                <div>
                                    <dt class="text-sm font-medium text-gray-900 dark:text-white">
                                        Last Updated
                                    </dt>
                                    <dd class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ dayjs(props.user.updated_at).format('DD MMM YYYY, HH:mm') }}
                                        ({{ dayjs(props.user.updated_at).fromNow() }})
                                    </dd>
                                </div>
                            </div>
                        </div>
                    </Card>
                </div>
            </div>

            <!-- Account Activity -->
            <Card class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Recent Activity
                </h3>
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <p>Activity tracking is not yet implemented</p>
                    <p class="text-sm mt-1">This section will show user login history, actions, etc.</p>
                </div>
            </Card>
        </div>
    </AppLayout>
</template>
